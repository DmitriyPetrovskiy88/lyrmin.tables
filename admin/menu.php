<?
IncludeModuleLangFile(__FILE__);

if($APPLICATION->GetGroupRight("form")>"D")
{
    $menu = array(
        "parent_menu" => "global_menu_settings", //Идентификатор раздела меню. Имеет смысл только для элемента верхнего уровня дерева меню модуля. Может принимать одно из следующих значений:
        /*
         * global_menu_content - раздел "Контент"
         * global_menu_marketing - раздел "Маркетинг"
         * global_menu_store - раздел "Магазин"
         * global_menu_services - раздел "Сервисы"
         * global_menu_statistics - раздел "Аналитика"
         * global_menu_marketplace - раздел "Marketplace"
         * global_menu_settings - раздел "Настройки"
        */
        "section" => "lyrmintables",
        "sort" => 2000,
        "url" => "lyrmin.tables_index.php?lang=" . LANGUAGE_ID, //Ссылка пункта меню. При переходе пользователем на страницу с таким URL данный пункт меню будет подсвечен.
        "more_url"  => array(), //Список дополнительных URL, по которым данный пункт меню должен быть подсвечен.
        //"text" => GetMessage("BCL_MENU_ITEM"),
        "text" => "Импорт таблиц",
        "title" => "Импорт таблиц", //Текст всплывающей подсказки пункта меню.
        "icon" => "lyrmintables_menu_icon", //CSS-класс иконки пункта меню.
        "page_icon" => "lyrmintables_page_icon", //CSS-класс иконки пункта меню для вывода на странице индекса (класс увеличенной иконки).

        "module_id" => "lyrmin.tables", //Идентификатор модуля, к которому относится меню.
        "dynamic" => false, //Флаг, показывающий, должна ли ветвь, начинающаяся с текущего пункта, подгружаться динамически.
        "items_id" => "menu_lyrmintables", //Идентификатор ветви меню. Используется для динамического обновления ветви.
        "items" => array(), //Список дочерних пунктов меню. Представляет собой массив, каждый элемент которого является ассоциативным массивом аналогичной структуры.
    );

    // Формируем подменю
    $menu["items"][] = array(
        //"text" => GetMessage("BCL_MENU_CONTROL_ITEM"),
        "text" => "Пункт подменю",
        "url" => "lyrmin.tables_submenu.php?lang=".LANGUAGE_ID,
        "more_url" => array(
            //"bitrixcloud_cdn.php",
        ),
    );

    $menu["items"][] = array(
        //"text" => GetMessage("BCL_MENU_CONTROL_ITEM"),
        "text" => "Пример таблицы",
        "url" => "lyrmin.tables_example_1.php?lang=".LANGUAGE_ID,
    );
}

if(is_array($menu))
    return $menu;
else return false;