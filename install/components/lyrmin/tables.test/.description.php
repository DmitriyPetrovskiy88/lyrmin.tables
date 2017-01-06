<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
    "NAME" => GetMessage("LYRMIN_TABLES_TEST_TEMPLATE_NAME"),
    "DESCRIPTION" => GetMessage("LYRMIN_TABLES_TEST_TEMPLATE_DESCRIPTION"),
    //"ICON" => "/images/cat_detail.gif",
    "CACHE_PATH" => "Y",
    "SORT" => 10,
    "PATH" => array(
        "ID" => "content",
        "CHILD" => array(
            "ID" => "lyrmin.tables",
            "NAME" => GetMessage("T_LYRMIN_TABLES_DESC_TEST"),
            "SORT" => 10
        ),
    ),
);