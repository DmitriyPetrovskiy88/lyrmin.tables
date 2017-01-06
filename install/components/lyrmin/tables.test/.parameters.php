<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var array $arCurrentValues */

use Bitrix\Main\Loader;

if (!Loader::includeModule('lyrmin.tables'))
    return;

$arComponentParameters = array(

);