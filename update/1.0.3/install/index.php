<?php
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\EventManager,
    Bitrix\Main\ModuleManager,
    Bitrix\Main\Application;

Loc::loadMessages(__FILE__);

class awz_utm extends CModule
{
	var $MODULE_ID = "awz.utm";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;

    public function __construct()
	{
        $arModuleVersion = array();
        include(__DIR__.'/version.php');

        $dirs = explode('/',dirname(__DIR__ . '../'));
        $this->MODULE_ID = array_pop($dirs);
        unset($dirs);

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

        $this->MODULE_NAME = Loc::getMessage("AWZ_UTM_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("AWZ_UTM_MODULE_DESCRIPTION");
		$this->PARTNER_NAME = Loc::getMessage("AWZ_PARTNER_NAME");
		$this->PARTNER_URI = "https://zahalski.dev/";

		return true;
	}

    function DoInstall()
    {
        global $APPLICATION, $step;

        $this->InstallFiles();
        $this->InstallDB();
        $this->checkOldInstallTables();
        $this->InstallEvents();
        $this->createAgents();

        ModuleManager::RegisterModule($this->MODULE_ID);

        $filePath = dirname(__DIR__) . '/options.php';
        if(file_exists($filePath)){
            LocalRedirect('/bitrix/admin/settings.php?lang='.LANG.'&mid='.$this->MODULE_ID);
        }

        return true;
    }

    function DoUninstall()
    {
        global $APPLICATION, $step;

        $step = intval($step);
        if($step < 2) { //выводим предупреждение
            $APPLICATION->IncludeAdminFile(Loc::getMessage('AWZ_UTM_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'. $this->MODULE_ID .'/install/unstep.php');
        }
        elseif($step == 2) {
            //проверяем условие
            if($_REQUEST['save'] != 'Y' && !isset($_REQUEST['save'])) {
                $this->UnInstallDB();
            }
            $this->UnInstallFiles();
            $this->UnInstallEvents();
            $this->deleteAgents();

            ModuleManager::UnRegisterModule($this->MODULE_ID);

            return true;
        }
    }

    function InstallDB()
    {
        global $DB, $DBType, $APPLICATION;
        $connection = \Bitrix\Main\Application::getConnection();
        $this->errors = false;
        if(!$this->errors && !$DB->TableExists('b_'.implode('_', explode('.',$this->MODULE_ID)).'')) {
            $this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/". $this->MODULE_ID ."/install/db/".$connection->getType()."/install.sql");
        }
        if(!$this->errors && !$DB->TableExists(implode('_', explode('.',$this->MODULE_ID)).'_permission')) {
            $this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/" . $this->MODULE_ID . "/install/db/".$connection->getType()."/access.sql");
        }
        if (!$this->errors) {
            return true;
        } else {
            $APPLICATION->ThrowException(implode("", $this->errors));
            return $this->errors;
        }
    }

    function UnInstallDB()
    {
        global $DB, $DBType, $APPLICATION;
        $connection = \Bitrix\Main\Application::getConnection();
        $this->errors = false;
        if (!$this->errors) {
            $this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/" . $this->MODULE_ID . "/install/db/" . $connection->getType() . "/uninstall.sql");
        }
        if (!$this->errors) {
            $this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/" . $this->MODULE_ID . "/install/db/" . $connection->getType() . "/unaccess.sql");
        }
        if (!$this->errors) {
            return true;
        }
        else {
            $APPLICATION->ThrowException(implode("", $this->errors));
            return $this->errors;
        }
    }

    function InstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandlerCompatible(
            'main', 'OnProlog',
            $this->MODULE_ID, '\\Awz\\Utm\\Handlers', 'OnProlog'
        );
        $eventManager->registerEventHandlerCompatible(
            'main', 'OnBeforeEventAdd',
            $this->MODULE_ID, '\\Awz\\Utm\\Handlers', 'OnBeforeEventAdd'
        );
        $eventManager->registerEventHandler(
            'sale', 'OnSaleOrderBeforeSaved',
            $this->MODULE_ID, '\\Awz\\Utm\\Handlers', 'OnSaleOrderBeforeSaved'
        );
        $eventManager->registerEventHandlerCompatible(
            'main', 'OnAfterUserUpdate',
            $this->MODULE_ID, '\\Awz\\Utm\\Access\\Handlers', 'OnAfterUserUpdate'
        );
        $eventManager->registerEventHandlerCompatible(
            'main', 'OnAfterUserAdd',
            $this->MODULE_ID, '\\Awz\\Utm\\Access\\Handlers', 'OnAfterUserUpdate'
        );
        return true;
    }

    function UnInstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'main', 'OnProlog',
            $this->MODULE_ID, '\\Awz\\Utm\\Handlers', 'OnProlog'
        );
        $eventManager->unRegisterEventHandler(
            'main', 'OnBeforeEventAdd',
            $this->MODULE_ID, '\\Awz\\Utm\\Handlers', 'OnBeforeEventAdd'
        );
        $eventManager->unRegisterEventHandler(
            'sale', 'OnSaleOrderBeforeSaved',
            $this->MODULE_ID, '\\Awz\\Utm\\Handlers', 'OnSaleOrderBeforeSaved'
        );
        $eventManager->unRegisterEventHandler(
            'main', 'OnAfterUserUpdate',
            $this->MODULE_ID, '\\Awz\\Utm\\Access\\Handlers', 'OnAfterUserUpdate'
        );
        $eventManager->unRegisterEventHandler(
            'main', 'OnAfterUserAdd',
            $this->MODULE_ID, '\\Awz\\Utm\\Access\\Handlers', 'OnAfterUserUpdate'
        );
        return true;
    }

    function InstallFiles()
    {
        CopyDirFiles($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$this->MODULE_ID."/install/admin/", $_SERVER['DOCUMENT_ROOT']."/bitrix/admin/", true);
        CopyDirFiles($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$this->MODULE_ID."/install/components/utm.config.permissions/", $_SERVER['DOCUMENT_ROOT']."/bitrix/components/awz/utm.config.permissions", true, true);
        return true;
    }

    function UnInstallFiles()
    {
        DeleteDirFiles(
            $_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$this->MODULE_ID."/install/admin",
            $_SERVER['DOCUMENT_ROOT']."/bitrix/admin"
        );
        DeleteDirFilesEx("/bitrix/components/awz/utm.config.permissions");
        return true;
    }

    function createAgents() {
        CAgent::AddAgent(
            "\\Awz\\Utm\\Agents::deleteOld();",
            $this->MODULE_ID,
            "N",
            1200);
        return true;
    }

    function deleteAgents() {
        CAgent::RemoveAgent(
            "\\Awz\\Utm\\Agents::deleteOld();",
            $this->MODULE_ID
        );
        return true;
    }

    function checkOldInstallTables()
    {
        $connection = Application::getConnection();
        $checkColumn = false;
        $checkTable = false;
        $recordsRes = $connection->query("select * from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME='b_awz_utm'");
        while($dt = $recordsRes->fetch()){
            $checkTable = true;
            if($dt['COLUMN_NAME'] == 'REFERER'){
                $checkColumn = true;
                break;
            }
        }
        if($checkTable && !$checkColumn){
            $sql = 'ALTER TABLE `b_awz_utm` ADD `REFERER` varchar(255) DEFAULT NULL';
            $connection->queryExecute($sql);
        }
        return true;
    }

}