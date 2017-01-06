<?
/**
 * Created by PhpStorm.
 * User: las
 * Date: 20/12/2016
 * Time: 21:48
 */

use \Bitrix\Main\Localization\Loc;

if(!check_bitrix_sessid())
    return;

if($ex = $APPLICATION->GetException())
{
    echo CAdminMessage::ShowMessage(array(
        "TYPE" => "ERROR",
        "MESSAGE" => Loc::getMessage("MOD_INST_ERR"),
        "DETAILS" => $ex->GetString(),
        "HTML" => true
    ));
}
else
{
    echo CAdminMessage::ShowMessage(array(
        "TYPE" => "OK",
        "MESSAGE" => Loc::getMessage("MOD_INST_OK"),
        "HTML" => true
    ));
}
?>
<form action="<?=$APPLICATION->GetCurPage();?>">
    <input type="hidden" name="lang" value="<?=LANGUAGE_ID?>" />
    <input type="submit" name="" value="<?=Loc::getMessage("MOD_BACK")?>" />
</form>