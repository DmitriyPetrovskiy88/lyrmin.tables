<?
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */

use \Bitrix\Main\Loader,
    \Bitrix\Main\Localization\Loc,
    \Bitrix\Main\Type,
    \Lyrmin\Tables\ImportTable;

$module_id = "lyrmin.tables"; // ID модуля указываем в переменной, для универсальности в работе данной страницы
$module_id_alt = "lyrmin_tables"; // Альтернативний вид ID модуля для id таблиц и прочих.
$module_id_loc = "LYRMIN_TABLES_"; // ID модуля для префикса ключа в массиве локализации lang $MESS.

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php'; // Первый общий пролог, обязательно
if(!Loader::includeModule($module_id)) die("Модуль $module_id не подключен"); // Подключаем наш модуль
require_once(dirname(__DIR__) . "/include.php"); // Инициализация модуля, обязательно
require_once(dirname(__DIR__) . "/prolog.php"); // Пролог модуля, используется если есть

Loc::loadMessages(__FILE__); // Подключим языковой файл

$USER_RIGHT = $APPLICATION->GetGroupRight($module_id); // Получаем права доступа текущего пользователя на модуль

// Если нет прав - отправляем к форме авторизации с сообщением об ошибке
if ($USER_RIGHT == "D")
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

/**
 *      НАЧАЛО ОБРАБОТКИ И ПОДГОТОВКИ ДАННЫХ
**/

$sTableID = "tbl_" . $module_id_alt; // ID таблицы

$oSort = new CAdminSorting($sTableID, "ID", "desc"); // Объект сортировки
//http://dev.1c-bitrix.ru/api_help/main/general/admin.section/classes/cadminsorting/cadminsorting.php

$lAdmin = new CAdminList($sTableID, $oSort); // Основной объект списка
//http://dev.1c-bitrix.ru/api_help/main/general/admin.section/classes/cadminlist/index.php

// Получаем список колонок таблицы базы данных
// Чтобы на их основе создать колонки таблицы списка
$arModuleTableFields = ImportTable::getMap();

$arHeaders = array(); // Массив заголовков колонок таблицы списка
$arHeadersEx = array(); // Массив дополняющий $arHeaders, ключи хранятся в верхнем регистре так как возможно совместное использование с $FilterArr
// Содержит доп. поля: тип данных, на какой колонке стоит метка PRIMARY, Title.
$FilterArr = array("find", "find_type"); // Массив полей фильтра на основе колонок таблицы списка. Хранит ключи в нижнем регистре.

// Перебираем массив колонок таблицы и формируем массивы $arHeaders и $FilterArr
foreach ($arModuleTableFields as $arField)
{
    $FilterArr[] = "find_" . strtolower($arField->getName()); // Сформируем поля фильтра

    $arHeaders[] = array(
        "id" => $arField->getName(), // Идентификатор колонки
        "content" => $arField->getTitle(), // Заголовок колонки
        "sort" => strtolower($arField->getName()), // Значение параметра GET-запроса для сортировки
        "default" => true, // Параметр, показывающий, будет ли колонка по умолчанию отображаться в списке (true|false)
    ); // опишем колонки заголовка

    if($arField->isPrimary())
        $arHeadersEx["PRIMARY_KEY"] = strtoupper($arField->getName()); // Определяем главную колонку, обычно это ID ! Важный момент без него не будет работать скрипт

    $arHeadersEx[strtoupper($arField->getName())] = array(
        "data_type" => $arField->getDataType(), // Тип данных [float, string, text, datetime, date, integer, enum, boolean]
        "title" => $arField->getTitle(), // Заголовок таблицы, например: Название, Сортировка и т.д.
        "primay" => $arField->isPrimary(), // Проверяем установлен ли флаг PRIMARY в БД. Обычно ставится для поля ID.
        "values" => ($arField instanceof \Bitrix\Main\Entity\EnumField ? $arField->getValues() : "") // Получаем значения для поля списка
    );
}

//
//      ПОДКЛЮЧАЕМ ФИЛЬТР
//

// Функция проверки значений фильтра. Если значения не верные, то будем добавлять ошибки в .......
function checkFilter()
{
    global $FilterArr, $lAdmin, $module_id_loc;

    foreach ($FilterArr as $field)
    {
        // Перебираются поля и если в каком-то поле ошибка, то добавляется элемент массива ошибок:
        // $lAdmin->AddFilterError(Loc::getMessage($module_id_loc . ""));
    }

    return (count($lAdmin->arFilterErrors) == 0); // Проверяем количество ошибок, если 0, то вернётся true.
}

$lAdmin->InitFilter($FilterArr); // Инициализируем фильтр

if(checkFilter()) //todo Сделать фильтр
{
    //
}

//
//      ОБРАБОТКА ДЕЙСТВИЙ НАД ЭЛЕМЕНТАМИ
//

// Проверяем было ли запущено редактирование элементов и есть ли у пользователя права на редактирование
// Если всё ок, то мы обновляем данные
if($lAdmin->EditAction() && $USER_RIGHT == "W")
{
    //$FIELDS массив у которого ключи - ID элементов, являются массивами вида Array ([2] => Array([NAME] => Новый элемент))
    foreach ($FIELDS as $ID => &$arFields)
    {
        if(!$lAdmin->isUpdated($ID)) continue; // Проверяем было ли изменено поле

        //Для дат и времени надо установить тип данных - объект вместо строки, иначе не срабатывает ::update()
        foreach ($arFields as $field => &$fieldValue)
        {
            $fieldType = $arHeadersEx[$field]["data_type"];
            switch ($fieldType)
            {
                case "float":
                    $fieldValue = floatval($fieldValue);
                    break;
                case "string":
                    break;
                case "text":
                    break;
                case "datetime":
                    $fieldValue = Type\DateTime::createFromUserTime($fieldValue); //Преобразуем строку в объект даты/времени
                    break;
                case "date":
                    $fieldValue = Type\DateTime::createFromUserTime($fieldValue);
                    break;
                case "integer":
                    break;
                case "enum":
                    $fieldValue = $arHeadersEx[$field]["values"][$fieldValue]; // Устанавливаем верное значение элементов списка text\html вместо ключей 0\1
                    break;
                case "boolean":
                    break;
            }
        }

        $DB->StartTransaction(); // Открываем транзакцию. На случай если надо откатить изменения в БД
        $ID = intval($ID);

        $r = ImportTable::getById($ID)->fetch(); // Проверяем существует ли элемент с таким ID в БД
        if(is_array($r) && intval($r["ID"]) > 0) // Если существует запускаем процесс обновления
        {
            $ur = ImportTable::update($ID, $arFields);
            if(!$ur->isSuccess())
            {
                $lAdmin->AddGroupError(Loc::getMessage($module_id_loc . 'GROUP_SAVE_ERROR') . $ur->getErrorMessages(), $ID);
                $DB->Rollback(); // Откатываем изменения БД
            }
        }
        else // Иначе выдаём ошибку что такого элемента нет
        {
            $lAdmin->AddGroupError(Loc::getMessage($module_id_loc . 'GROUP_SAVE_ERROR') . Loc::getMessage($module_id_loc . 'ELEMENT_NOT_FOUND'), $ID);
            $DB->Rollback();
        }
        $DB->Commit(); // Подтверждаем изменения БД
    }
}

// Обработка одиночных и групповых действий
if(($arID = $lAdmin->GroupAction()) && ($USER_RIGHT == "W"))
{
    // если выбрано "Для всех элементов"
    if($_REQUEST['action_target']=='selected')
    {
        $r = ImportTable::getList(array(
            "order" => array(strtoupper($by) => strtoupper($order)) //Важно указать поля сортировки. Используйте именно эти переменные $by и $order
        ));
        while ($arRes = $r->fetch())
            $arID[] = $arRes['ID'];
    }

    // пройдем по списку элементов
    foreach($arID as $ID)
    {
        if(strlen($ID)<=0)
            continue;
        $ID = IntVal($ID);

        // для каждого элемента совершим требуемое действие
        switch($_REQUEST['action'])
        {
            // удаление
            case "delete":
                @set_time_limit(0);
                $DB->StartTransaction();
                ImportTable::delete($ID);
                $r = ImportTable::getById($ID)->fetch(); // Проверяем существует ли элемент с таким ID в БД после удаления
                if($r["ID"] == $ID)
                {
                    $DB->Rollback();
                    $lAdmin->AddGroupError(Loc::getMessage($module_id_loc . 'ELEMENT_DEL_ERROR'), $ID);
                }
                $DB->Commit();
                break;

            // активация/деактивация выбранных элементов списка
            case "activate":
            case "deactivate":
                $r = ImportTable::getById($ID)->fetch(); // Проверяем существует ли элемент с таким ID в БД перед обновлением
                if($r["ID"] > 0)
                {
                    $arFields["ACTIVE"]=($_REQUEST['action'] == "activate" ? "Y":"N");

                    $ur = ImportTable::update($ID, $arFields);
                    if(!$ur->isSuccess())
                    {
                        $lAdmin->AddGroupError(Loc::getMessage($module_id_loc . 'GROUP_SAVE_ERROR') . $ur->getErrorMessages(), $ID);
                        $DB->Rollback(); // Откатываем изменения БД
                    }
                }
                else
                    $lAdmin->AddGroupError(Loc::getMessage($module_id_loc . 'GROUP_SAVE_ERROR') . Loc::getMessage($module_id_loc . 'ELEMENT_NOT_FOUND'), $ID);
                break;
        }

    }





}

$lAdmin->AddHeaders($arHeaders); // Добавляем заголовок таблицы

// выберем список данных
$rsData = ImportTable::getList(array(
    "order" => array(strtoupper($by) => strtoupper($order)) //Важно указать поля сортировки. Используйте именно эти переменные $by и $order
));

// Преобразуем список в экземпляр класса CAdminResult. Для работы со списком в таблице.
$rsData = new CAdminResult($rsData, $sTableID);

$rsData->NavStart(); // Инициализируем постраничную навигацию.

$lAdmin->NavText($rsData->GetNavPrint(Loc::getMessage($module_id_loc . 'NAV_TEXT'))); // Инициализируем переключатель страниц

while($arRes = $rsData->Fetch())
{
    array_change_key_case($arRes, CASE_UPPER); // Переведём ключи массива $arRes в верхний регистр для соответсвия регистра в $arHeadersEx

    $row = &$lAdmin->AddRow($arRes[$arHeadersEx["PRIMARY_KEY"]], $arRes); // Создаем строку. результат - экземпляр класса CAdminListRow

    // Настроим отображение значений при просмотре и редактировании списка
    foreach ($arRes as $field => $value)
    {
        if ($field == $arHeadersEx["PRIMARY_KEY"]) continue; // Пропускаем поле ID

        $fieldName = "name='FIELDS[" . $arRes[$arHeadersEx["PRIMARY_KEY"]] . "][" . $field . "]'"; //name для кастомных поллей редактирования AddEditField()

        $fieldType = $arHeadersEx[$field]["data_type"]; // Тип данных поля
        // https://dev.1c-bitrix.ru/api_help/main/general/admin.section/classes/cadminlistrow/index.php
        switch ($fieldType)
        {
            case "float":
                $row->AddInputField($field);
                $row->AddViewField($field, $value);
                break;
            case "string":
                // параметр NAME будет отображаться ссылкой
                if ($field == "NAME")
                {
                    $value = '<a href="' . $module_id . '_element_edit.php?ID=' . $arRes[$arHeadersEx["PRIMARY_KEY"]] . '&lang=' . LANG . '">' . $arRes[$field] . '</a>';
                }
                $row->AddInputField($field);
                $row->AddViewField($field, $value);
                break;
            case "text":
                $row->AddEditField($field, "<textarea " . $fieldName . ">" . $value . "</textarea>");
                $row->AddViewField($field, $value);
                break;
            case "datetime":
                $row->AddCalendarField($field, array(), true);
                $row->AddViewField($field, $value);
                break;
            case "date":
                $row->AddCalendarField($field, array());
                $row->AddViewField($field, $value);
                break;
            case "integer":
                break;
            case "enum":
                $row->AddSelectField($field, $arHeadersEx[$field]["values"]);
                $row->AddViewField($field, $value);
                break;
            case "boolean":
                $row->AddCheckField($field);
                $row->AddViewField($field, ($value == "Y" ? Loc::getMessage($module_id_loc . "YES") : Loc::getMessage($module_id_loc . "NO")));
                break;

            default:
                echo $field . " - DEFAULT<br>";
                break;
        }
    }

    $arActions = Array(); // Сформируем контекстное меню для каждого элемента, например: Изменить, Удалить.

    // Редактирование элемента
    if ($USER_RIGHT >= "W")
        $arActions[] = array(
            "ICON" => "edit",
            "DEFAULT" => true,
            "TEXT" => Loc::getMessage($module_id_loc . "ELEMENT_EDIT"),
            "ACTION" => $lAdmin->ActionRedirect($module_id . "_element_edit.php?ID=" . $arRes[$arHeadersEx["PRIMARY_KEY"]])
        );

    // Удаление элемента
    if ($USER_RIGHT >= "W")
        $arActions[] = array(
            "ICON" => "delete",
            "TEXT" => Loc::getMessage($module_id_loc . "ELEMENT_DEL"),
            "ACTION" => "if(confirm('" . Loc::getMessage($module_id_loc . "ELEMENT_DEL_CONFIRM") . "')) " . $lAdmin->ActionDoGroup($arRes[$arHeadersEx["PRIMARY_KEY"]], "delete")
        );

    // вставим разделитель
    $arActions[] = array("SEPARATOR" => true);

    // если последний элемент - разделитель, почистим мусор.
    if (is_set($arActions[count($arActions) - 1], "SEPARATOR"))
        unset($arActions[count($arActions) - 1]);

    $row->AddActions($arActions); // Применим контекстное меню к строке
}

// Футер таблицы
$lAdmin->AddFooter(
    array(
        array(
            "title" => Loc::getMessage("MAIN_ADMIN_LIST_SELECTED"),
            "value" => $rsData->SelectedRowsCount()
        ), // кол-во элементов
        array(
            "counter" => true,
            "title" => Loc::getMessage("MAIN_ADMIN_LIST_CHECKED"),
            "value"=>"0"
        ) // счетчик выбранных элементов (чекбоксами) справа внизу
    )
);

// Групповые действия над элементами, выбранными чекбоксами
$lAdmin->AddGroupActionTable(array(
    "delete"    => GetMessage("MAIN_ADMIN_LIST_DELETE"),
    "activate"  => GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
    "deactivate"=> GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
));

// Сформируем контекстное меню из одного пункта - добавление элемента
$aContext = array(
    array(
        "TEXT" => Loc::getMessage($module_id_loc . "ELEMENT_ADD"),
        "LINK" => "element_edit.php?lang=" . LANG,
        "TITLE" => Loc::getMessage($module_id_loc . "ELEMENT_ADD_TITLE"),
        "ICON" => "btn_new", // Уберите чтобы сделать кнопку серой
    )
);

// * Ключ         Описание
// * TEXT         Текст пункта меню.
// * TITLE        Текст всплывающей подсказки пункта меню.
// * LINK         Ссылка на кнопке.
// * LINK_PARAM   Дополнительные параметры ссылки (напрямую подставляются в тэг <A>).
// * ICON         CSS-класс иконки действия.
// * HTML         Задание пункта меню напрямую HTML-кодом.
// * SEPARATOR    Разделитель между пунктами меню (true|false).
// * NEWBAR	    Новый блок элементов меню (true|false).
// * MENU         Создание выпадающего подменю. Значение задается аналогично контекстному меню строки таблицы.

$lAdmin->AddAdminContextMenu($aContext); // Выведем контекстное меню

$lAdmin->CheckListMode(); // Режим правки в списке. Альтернативный вывод полей, в виде инпутов и прочего для редактирования данных

$APPLICATION->SetTitle("Импорт таблиц"); // Устанавливаем заголовок страницы

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php"); // Второй общий пролог

// Вывод страницы с формой
?>

<?
// Вывод таблицы списка элементов
$lAdmin->DisplayList();
?><pre><?

//echo '$FIELDS';print_r($FIELDS);echo PHP_EOL;
//var_dump($_REQUEST);
//var_dump($FilterArr);
//echo "Права доступа пользователя: " . $USER_RIGHT . PHP_EOL;
//echo "lAdmin: "; print_r($lAdmin); echo PHP_EOL;
//echo "arFilter: "; print_r($arFilter); echo PHP_EOL;

//echo "arHeaders:" . PHP_EOL;
//print_r($arHeaders); echo PHP_EOL;

//echo "FilterArr:" . PHP_EOL;
//print_r($FilterArr); echo PHP_EOL;

//echo "arHeadersEx:" . PHP_EOL;
//print_r($arHeadersEx); echo PHP_EOL;

//foreach ($arModuleTableFields as $key => $arField)
//{
//    echo $arField->getName() . ": "; print_r($arField->getTitle());
//    echo ", dataType: "; print_r($arField->getDataType());
//    echo PHP_EOL;
//}

//echo "Права доступа: "; print_r($USER_RIGHT); echo PHP_EOL;
?></pre><?
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php';