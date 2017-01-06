<?
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php'; // первый общий пролог
require_once($_SERVER["DOCUMENT_ROOT"]."/local/modules/lyrmin.tables/include.php"); // инициализация модуля
//require_once($_SERVER["DOCUMENT_ROOT"]."/local/modules/lyrmin.tables/prolog.php"); // пролог модуля

use \Bitrix\Main\Loader,
    \Bitrix\Main\Localization\Loc,
    \Lyrmin\Tables\ImportTable;

$module_id = "lyrmin.tables";
$module_id_alt = "lyrmin_tables";
$module_id_loc = "LYRMIN_TABLES_";

if(!Loader::includeModule($module_id)) die("Модуль $module_id не подключен");

CJSCore::Init(array('date'));

// подключим языковой файл
//IncludeModuleLangFile(__FILE__);
Loc::loadMessages(__FILE__);

//IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/admin_lib.php");

// получим права доступа текущего пользователя на модуль
$USER_RIGHT = $APPLICATION->GetGroupRight($module_id);

// если нет прав - отправим к форме авторизации с сообщением об ошибке
if ($USER_RIGHT == "D")
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

// здесь будет вся серверная обработка и подготовка данных

// Обработка и подготовка данных
$sTableID = "tbl_" . $module_id_alt; // ID таблицы
$oSort = new CAdminSorting($sTableID, "ID", "desc"); // объект сортировки
$lAdmin = new CAdminList($sTableID, $oSort); // основной объект списка

//==========

$arModuleTableFields = ImportTable::getMap();

$arHeaders = array();
$FilterArr = array();

foreach ($arModuleTableFields as $arField)
{
    $FilterArr[] = strtolower($arField->getName()); // опишем элементы фильтра

    $arHeaders[] = array(
        "id" => $arField->getName(), // Идентификатор колонки
        "content" => $arField->getTitle(), // Заголовок колонки
        "sort" => strtolower($arField->getName()), // Значение параметра GET-запроса для сортировки
        "default" => true, // Параметр, показывающий, будет ли колонка по умолчанию отображаться в списке (true|false)
    ); // опишем колонки заголовка

    if($arField->isPrimary())
        $arHeadersEx["PRIMARY_KEY"] = $arField->getName();

    $arHeadersEx[$arField->getName()] = array(
        "data_type" => $arField->getDataType(),
        "title" => $arField->getTitle(),
        "primay" => $arField->isPrimary()
    );
}

// инициализируем фильтр
$lAdmin->InitFilter($FilterArr);

$lAdmin->AddHeaders($arHeaders);

// выберем список данных
$rsData = ImportTable::getList(array());

// преобразуем список в экземпляр класса CAdminResult
$rsData = new CAdminResult($rsData, $sTableID);

// аналогично CDBResult инициализируем постраничную навигацию.
$rsData->NavStart();

// отправим вывод переключателя страниц в основной объект $lAdmin
$lAdmin->NavText($rsData->GetNavPrint("Далее"));

//$lAdmin->AddRow($newRow["ID"], $newRow);

//var_dump($rsData);

while($arRes = $rsData->NavNext(true, "f_")):
//while(false):

    // создаем строку. результат - экземпляр класса CAdminListRow

    $row = &$lAdmin->AddRow($arRes[$arHeadersEx["PRIMARY_KEY"]], $arRes);

    //echo "<br>" . $arRes[$arHeadersEx["PRIMARY_KEY"]] . ":<br>";
    //print_r($arRes);

    // далее настроим отображение значений при просмотре и редактировании списка
    foreach ($arRes as $key => $value)
    {
        if($key == $arHeadersEx["PRIMARY_KEY"])
            continue;

        $fieldType = $arHeadersEx[$key]["data_type"]; // Тип данных поля

        switch ($fieldType)
        {
            case "float":
                //echo $key . " - " . $fieldType . "<br>";
                //$row->AddViewField($key, $value);
                //$row->AddInputField($key);
                break;
            case "string":
                //echo $key . " - " . $fieldType . "<br>";

                //$row->AddInputField($key);

                // параметр NAME будет отображаться ссылкой
                if($key == "NAME")
                    $value = '<a href="' . $module_id . '_element_edit.php?ID=' . $arRes[$arHeadersEx["PRIMARY_KEY"]] . '&lang=' . LANG . '">' . $arRes[$key] . '</a>';

                //$row->AddViewField($key, $value);
                break;
            case "text":
                //echo $key . " - " . $fieldType . "<br>";
                //$row->AddViewField($key, $value);
                //$row->AddEditField($key, "<textarea>" . $arRes[$key] . "</textarea>");
                break;
            case "datetime":
                //echo $key . " - " . $fieldType . "<br>";
                //$row->AddCalendarField($key, array(), true);
                //$row->AddViewField($key, $value);
                break;
            case "date":
                //echo $key . " - " . $fieldType . "<br>";
                //$row->AddCalendarField($key);
                //$row->AddViewField($key, $value);
                break;
            case "integer":
                echo $key . " - " . $fieldType . "<br>";
                break;
            case "enum":
                //echo $key . " - " . $fieldType . "<br>";
                //$row->AddSelectField($key, $arRes[$key]);
                //$row->AddViewField($key, $arRes[$key]);
                break;
            case "boolean":
                //echo $key . " - " . $fieldType . "<br>";
                //$row->AddCheckField($key);
                //$row->AddViewField($key, $value);
                break;

            default:
                echo $key . " - DEFAULT<br>";
                break;
        }
    }

    // далее настроим отображение значений при просмотре и редактировании списка
    /*
    // параметр NAME будет редактироваться как текст, а отображаться ссылкой
    $row->AddInputField("NAME", array("size"=>20));
    $row->AddViewField("NAME", '<a href="rubric_edit.php?ID='.$f_ID.'&lang='.LANG.'">'.$f_NAME.'</a>');

    // параметр LID будет редактироваться в виде выпадающего списка языков
    $row->AddEditField("LID", CLang::SelectBox("LID", $f_LID));

    // параметр SORT будет редактироваться текстом
    $row->AddInputField("SORT", array("size"=>20));

    // флаги ACTIVE и VISIBLE будут редактироваться чекбоксами
    $row->AddCheckField("ACTIVE");
    $row->AddCheckField("VISIBLE");

    // параметр AUTO будет отображаться в виде "Да" или "Нет", полужирным при редактировании
    $row->AddViewField("AUTO", $f_AUTO=="Y"?GetMessage("POST_U_YES"):GetMessage("POST_U_NO"));
    $row->AddEditField("AUTO", "<b>".($f_AUTO=="Y"?GetMessage("POST_U_YES"):GetMessage("POST_U_NO"))."</b>");
    */

    // сформируем контекстное меню
    $arActions = Array();

    // редактирование элемента
    $arActions[] = array(
        "ICON" => "edit",
        "DEFAULT" => true,
        "TEXT" => Loc::getMessage($module_id_loc . "ELEMENT_EDIT"),
        "ACTION"=>$lAdmin->ActionRedirect($module_id . "_element_edit.php?ID=" . $arRes[$arHeadersEx["PRIMARY_KEY"]])
    );

    // удаление элемента
    if ($USER_RIGHT >= "W")
        $arActions[] = array(
            "ICON" => "delete",
            "TEXT" => Loc::getMessage($module_id_loc . "ELEMENT_DEL"),
            "ACTION"=>"if(confirm('".Loc::getMessage($module_id_loc . "ELEMENT_DEL_CONFIRM")."')) ".$lAdmin->ActionDoGroup($arRes[$arHeadersEx["PRIMARY_KEY"]], "delete")
        );

    // вставим разделитель
    $arActions[] = array("SEPARATOR"=>true);

    // если последний элемент - разделитель, почистим мусор.
    if(is_set($arActions[count($arActions)-1], "SEPARATOR"))
        unset($arActions[count($arActions)-1]);

    // применим контекстное меню к строке
    $row->AddActions($arActions);

endwhile;
//====================

// резюме таблицы
$lAdmin->AddFooter(
    array(
        /*
        array(
            "title" => Loc::getMessage("MAIN_ADMIN_LIST_SELECTED"),
            "value" => $rsData->SelectedRowsCount()
        ), // кол-во элементов
        */
        array(
            "counter" => true,
            "title" => Loc::getMessage("MAIN_ADMIN_LIST_CHECKED"),
            "value"=>"0"
        ), // счетчик выбранных элементов (чекбоксами) справа внизу
    )
);

// групповые действия над элементами, выбранными чекбоксами
$lAdmin->AddGroupActionTable(Array(
    "delete"=>Loc::getMessage("MAIN_ADMIN_LIST_DELETE"), // удалить выбранные элементы
    "activate"=>Loc::getMessage("MAIN_ADMIN_LIST_ACTIVATE"), // активировать выбранные элементы
    "deactivate"=>Loc::getMessage("MAIN_ADMIN_LIST_DEACTIVATE"), // деактивировать выбранные элементы
));

// сформируем меню из одного пункта - добавление элемента
$aContext = array(
    array(
        "TEXT" => Loc::getMessage($module_id_loc . "ELEMENT_ADD"),
        "LINK" => "element_edit.php?lang=" . LANG,
        "TITLE" => Loc::getMessage($module_id_loc . "ELEMENT_ADD_TITLE"),
        "ICON" => "btn_new",
    ),
);

/**
 * Ключ         Описание
 * TEXT         Текст пункта меню.
 * TITLE        Текст всплывающей подсказки пункта меню.
 * LINK         Ссылка на кнопке.
 * LINK_PARAM   Дополнительные параметры ссылки (напрямую подставляются в тэг <A>).
 * ICON         CSS-класс иконки действия.
 * HTML         Задание пункта меню напрямую HTML-кодом.
 * SEPARATOR    Разделитель между пунктами меню (true|false).
 * NEWBAR	    Новый блок элементов меню (true|false).
 * MENU         Создание выпадающего подменю. Значение задается аналогично контекстному меню строки таблицы.
 */

// и прикрепим меню к списку
$lAdmin->AddAdminContextMenu($aContext);

// альтернативный вывод
$lAdmin->CheckListMode();

// Устанавливаем заголовок страницы
$APPLICATION->SetTitle("Импорт таблиц");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php"); // второй общий пролог

// здесь будет вывод страницы с формой

?><pre><?
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
?></pre>
<script>
    console.log(BX.adminPanel);
</script>
<?

// выведем таблицу списка элементов
$lAdmin->DisplayList();

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php';