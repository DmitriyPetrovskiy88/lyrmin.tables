<?
/**
 * Created by PhpStorm.
 * User: las
 * Date: 20/12/2016
 * Time: 19:42
 */

use \Bitrix\Main\Localization\Loc,
    \Bitrix\Main\ModuleManager,
    \Bitrix\Main\Application,
    \Bitrix\Main\Loader,
    \Bitrix\Main\Entity\Base,
    \Bitrix\Main\Config\Option,
    \Bitrix\Main\IO\Directory,
    \Lyrmin\Tables;

Loc::loadMessages(__FILE__);

Class lyrmin_tables extends CModule
{
    private $exclusionAdminFiles = array(".", "..", "menu.php", "operation_description.php", "task_description.php");

    function __construct()
    {
        $arModuleVersion = array();
        include(__DIR__."/version.php");

        $this->MODULE_ID = "lyrmin.tables";
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("LYRMIN_TABLES_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("LYRMIN_TABLES_MODULE_DESC");

        $this->PARTNER_NAME = Loc::getMessage("LYRMIN_TABLES_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("LYRMIN_TABLES_PARTNER_URI");

        $this->MODULE_GROUP_RIGHTS = 'Y';
    }

    function DoInstall()
    {
        global $APPLICATION;

        if($this->isVersionD7())
        {
            ModuleManager::registerModule($this->MODULE_ID);

            if($this->InstallDB()) // Создаём таблицы БД
            {
                $this->InstallFiles();
            }
        }
        else
        {
            $APPLICATION->ThrowException("LYRMIN_TABLES_INSTALL_ERROR_VERSION");
        }

        $APPLICATION->IncludeAdminFile(Loc::getMessage("LYRMIN_TABLES_INSTALL_TITLE"), $this->GetPath() . "/install/step.php");
    }

    function DoUninstall() // Удаление модуля
    {
        global $APPLICATION;
        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();

        switch ($request["step"])
        {
            case "2":
                $this->UnInstallEvents(); // Удаляем события модуля

                if($request["savedata"] != "Y") $this->UnInstallDB(); // Удаляем ли таблицы БД модуля?
                if($request["saveoptions"] != "Y") $this->UnInstallOptions(); // Удаляем ли настройки модуля?

                //$GLOBALS["CACHE_MANAGER"]->CleanAll();
                $this->UnInstallFiles(); // Удаляем файлы модуля

                ModuleManager::unRegisterModule($this->MODULE_ID);

                $APPLICATION->IncludeAdminFile(Loc::getMessage("LYRMIN_TABLES_UNINSTALL_TITLE"), $this->GetPath() . "/install/unstep2.php");
                break;

            case "3":

                break;

            default:
                // Шаг 1
                $APPLICATION->IncludeAdminFile(Loc::getMessage("LYRMIN_TABLES_UNINSTALL_TITLE"), $this->GetPath() . "/install/unstep1.php");
                break;
        }

    }

    function GetModuleRightList() // Устанавливает права доступа к модулю
    {
        return array(
            "reference_id" => array("D", "K", "S", "W"),
            "reference" => array(
                "[D] " . Loc::getMessage("LYRMIN_TABLES_DENIED"),
                "[K] " . Loc::getMessage("LYRMIN_TABLES_READ_COMPONENT"),
                "[S] " . Loc::getMessage("LYRMIN_TABLES_WRITE_SETTINGS"),
                "[W] " . Loc::getMessage("LYRMIN_TABLES_FULL"),
            ),
        );
    }

    function UnInstallOptions() // Удаляет настройки модуля
    {
        Option::delete($this->MODULE_ID);
    }

    function UnInstallDB() // Удаляет таблицы БД
    {
        Loader::includeModule($this->MODULE_ID);

        Application::getConnection(Tables\ImportTable::getConnectionName())->
            queryExecute('drop table if exists ' . Tables\ImportTable::getEntity()->getDBTableName());
    }

    function InstallDB() // Создаёт таблицы БД
    {
        global $APPLICATION;

        if (Loader::includeModule($this->MODULE_ID))
        {
            if(!Application::getConnection(Tables\ImportTable::getConnectionName())->isTableExists(
                Tables\ImportTable::getEntity()->getDBTableName()
            ))
            {
                Tables\ImportTable::getEntity()->createDbTable();
            }
        }
        else
        {
            $APPLICATION->ThrowException(Loc::getMessage("LYRMIN_TABLES_INCLUDE_MODULE_ERROR"));
        }

        return true;
    }

    function InstallFiles($arParams = array())
    {
        $path = $this->GetPath(); // Путь к корню модуля
        $dir = opendir($path . "/admin"); // Содержимое папки /admin

        // Копируем компоненты
        CopyDirFiles($path . "/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);

        // Копируем страницы админки
        if(Directory::isDirectoryExists($path . "/admin") && $dir)
        {
            while ($item = readdir($dir))
            {
                if(in_array($item, $this->exclusionAdminFiles))
                    continue;

                file_put_contents(
                    $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin/" . $this->MODULE_ID . "_" . $item,
                    '<' . '?require($_SERVER["DOCUMENT_ROOT"] . "' . $this->GetPath(true) . '/admin/' . $item . '");?' . '>'
                );
            }
        }

        closedir($dir);

        if($_ENV["COMPUTERNAME"] != 'BX')
        {
            // local/modules/lyrmin.tables

            //$this->GetPath() . "/install/step.php"
            //CopyDirFiles($_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/iblock/install/admin', $_SERVER['DOCUMENT_ROOT']."/bitrix/admin");
            //CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/", true, true);
            //CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/images", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/iblock", true, true);
            //if(file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/public/rss.php"))
                //@copy($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/public/rss.php", $_SERVER["DOCUMENT_ROOT"]."/bitrix/rss.php");
            //CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/themes", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
            //CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
            //CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/gadgets", $_SERVER["DOCUMENT_ROOT"]."/bitrix/gadgets", true, true);
            //CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/panel", $_SERVER["DOCUMENT_ROOT"]."/bitrix/panel", true, true);
        }
        return true;
    }

    function UnInstallFiles()
    {
        $path = $this->GetPath(); // Путь к корню модуля
        $dir = opendir($path . "/admin"); // Содержимое папки /admin

        $pathFrom = $path . "/install/components/lyrmin/";
        $pathTo = $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/lyrmin/";

        $dirsAndFiles = array_diff(
            scandir($pathFrom),
            array(".", "..")
        );

        foreach ($dirsAndFiles as $item)
        {
            Directory::deleteDirectory($pathTo . $item);
        }

        if($this->isEmptyDir($pathTo))
            Directory::deleteDirectory($pathTo);

        //DeleteDirFiles($_SERVER["DOCUMENT_ROOT"] . "/local/modules/lyrmin.tables/install/components/lyrmin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/lyrmin");


        if(Directory::isDirectoryExists($path . "/admin") && $dir)
        {
            while ($item = readdir($dir))
            {
                if(in_array($item, $this->exclusionAdminFiles))
                    continue;

                \Bitrix\Main\IO\File::deleteFile($_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin/" . $this->MODULE_ID . "_" . $item);
            }
        }

        closedir($dir);

        if($_ENV["COMPUTERNAME"]!='BX')
        {
            //DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
            //DeleteDirFilesEx("/bitrix/images/iblock/");//images
            //DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/public/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/");
            //DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");//css
            //DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/panel/iblock/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/panel/iblock/");//css sku
            //DeleteDirFilesEx("/bitrix/themes/.default/icons/iblock/");//icons
            //DeleteDirFilesEx("/bitrix/js/iblock/");//javascript
        }
        return true;
    }

    function isVersionD7() // Проверяет поддержку D7
    {
        return CheckVersion(ModuleManager::getVersion('main'), '14.00.00'); // Ядро D7 доступно с 14 версии главного модуля
    }

    public function GetPath($notDocumentRoot = false) // Определяет место размещения модуля в /local/ или /bitrix/
    {
        // true - от корня сайта, false - абсолютный путь
        return ($notDocumentRoot ? str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__)) : dirname(__DIR__));
    }

    public function isEmptyDir($path = "") // Проверяет, является ли дириктория пустой
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
}