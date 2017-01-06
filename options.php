<?
/**
 * Created by PhpStorm.
 * User: las
 * Date: 24/12/2016
 * Time: 14:31
 */

use \Bitrix\Main\Localization\Loc,
    \Bitrix\Main\Config\Option,
    \Lyrmin\Tables;

$module_id = 'lyrmin.tables';
// Обязательно использовать переменную $module_id так как она прописана в файлах старого ядра

Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/options.php");
Loc::loadMessages(__FILE__);

if($APPLICATION->GetGroupRight($module_id) < "S")
{
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

\Bitrix\Main\Loader::includeModule($module_id);

$request = \Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest();

// Описание опций (закладок)

$aTabs = array(
    array(
        "DIV" => "edit1", // id для div, используется для JS
        "TAB" => Loc::getMessage("LYRMIN_TABLES_TAB_SETTINGS"), // Надпись на вкладке
        "OPTIONS" => array(
            array(
                "field_text", //name для input
                Loc::getMessage("LYRMIN_TABLES_FIELD_TEXT_TITLE"), // Имя поля, LABEL
                "", // Значение по умолчанию
                array(
                    "textarea", // Тип поля
                    4, // Высота
                    50 // Ширина
                ), // Массив описывающий поле формы. Для каждого типа он разный.
            ),

            array(
                "field_line",
                Loc::getMessage("LYRMIN_TABLES_FIELD_LINE_TITLE"),
                "",
                array(
                    "text",
                    30, // Длинна
                ),
            ),

            array(
                "field_list",
                Loc::getMessage("LYRMIN_TABLES_FIELD_LIST_TITLE"),
                "",
                array(
                    "multiselectbox",
                    array(
                        "VAR1" => "Значение 1",
                        "VAR2" => "Значение 2",
                        "VAR3" => "Значение 3",
                        "VAR4" => "Значение 4",
                    )
                ),
            ),

        ) // Массив опций, который будет выводиться ниже для каждой закладки
    ),

    array(
        "DIV" => "edit2",
        "TAB" => Loc::getMessage("MAIN_TAB_RIGHTS"),
        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_RIGHTS"), //Всплывающая подсказка на вкладке, аналочно <a title>
    )
);

// Сохранение данных формы

if($request->isPost() && $request["Update"] && check_bitrix_sessid())
{
    foreach ($aTabs as $aTab)
    {
        foreach ($aTab["OPTIONS"] as $arOption)
        {
            if(!is_array($arOption) || $arOption["note"]) // Строка с подсветкой - разделитель блоков опций или уведомление с подсветкой
                continue;

            $optionName = $arOption[0];

            $optionValue = $request->getPost($optionName);

            Option::Set($module_id, $optionName, is_array($optionValue) ? implode(",", $optionValue) : $optionValue);
        }
    }
}

// Вывод закладок

$tabControl = new CAdminTabControl("tabControl", $aTabs);

$tabControl->Begin();
?>
<form method="post" action="<?=$APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($request["mid"])?>&amp;lang=<?=htmlspecialcharsbx($request["lang"])?>" name="<?=str_replace(".", $module_id)?>_settings">
    <?
    foreach ($aTabs as $aTab)
    {
        if($aTab["OPTIONS"])
        {
            $tabControl->BeginNextTab();
            __AdmSettingsDrawList($module_id, $aTab["OPTIONS"]);
        }
    }

    $tabControl->BeginNextTab();

    // Добавляем вывод закладки с правами используя файл главного модуля и метод GetModuleRightList() в нашем модуле
    require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/admin/group_rights.php"); // Здесь используется переменная $module_id

    $tabControl->Buttons();

    echo bitrix_sessid_post();
    ?>
    <input type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" /><?// name="Update" использовать именно такое название?>
    <input type="reset" name="reset" value="<?=GetMessage("MAIN_RESET")?>" />
</form>
<?$tabControl->End();?>
