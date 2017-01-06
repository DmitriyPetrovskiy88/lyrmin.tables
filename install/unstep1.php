<?php
/**
 * Created by PhpStorm.
 * User: las
 * Date: 20/12/2016
 * Time: 21:48
 */

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Application;
use \Bitrix\Main\IO\Directory;

if(!check_bitrix_sessid())
    return;

Loc::loadMessages(__DIR__ . "/index.php");
?>
<pre><?
    function GetPath($notDocumentRoot = false) // Определяет место размещения модуля в /local/ или /bitrix/
    {
        // true - от корня сайта, false - абсолютный путь
        return ($notDocumentRoot ? str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__)) : dirname(__DIR__));
    }

    function isEmptyDir($path = "")
    {
        if(strlen($path))
        {
            $r = scandir($path);
            $r = array_diff($r, array(".", ".."));

            if(empty($r))
                return true;
        }

        return false;
    }

    //$path = GetPath() . "/install/components/lyrmin/";

    //print_r($path); echo PHP_EOL;

    $dirsAndFiles = array_diff(scandir($path), array(".", ".."));

    //print_r($dirsAndFiles); echo PHP_EOL;

    foreach ($dirsAndFiles as $item)
    {
        //echo "get:      " . $path . $item . PHP_EOL;
        //echo "delete:   " . $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/lyrmin/" . $item . PHP_EOL;

        //Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/lyrmin/" . $item);
    }
    ?></pre>
<form action="<?=$APPLICATION->GetCurPage();?>">
    <?=bitrix_sessid_post()?>
    <input type="hidden" name="lang" value="<?=LANGUAGE_ID?>" />
    <input type="hidden" name="id" value="lyrmin.tables" />
    <input type="hidden" name="uninstall" value="Y" />
    <input type="hidden" name="step" value="2" />
    <?=CAdminMessage::ShowMessage(GetMessage("MOD_UNINST_WARN"))?>
    <p><?=GetMessage("MOD_UNINST_SAVE")?></p>
    <p>
        <input type="checkbox" name="saveoptions" id="saveoptions" value="Y" checked />
        <label for="saveoptions"><?=GetMessage("LYRMIN_TABLES_SAVE_OPTIONS")?></label><br />
        <input type="checkbox" name="savedata" id="savedata" value="Y" checked />
        <label for="savedata"><?=GetMessage("MOD_UNINST_SAVE_TABLES")?></label>
    </p>
    <input type="submit" name="" value="<?=Loc::getMessage("MOD_UNINST_DEL")?>" />
</form>
