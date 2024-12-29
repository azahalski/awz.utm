<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Awz\Utm\Access\AccessController;
use Awz\Utm\Access\Custom\ActionDictionary;

global $APPLICATION;
$dirs = explode('/',dirname(__DIR__ . '../'));
$module_id = array_pop($dirs);
unset($dirs);
Loc::loadMessages(__FILE__);

//if(!Loader::includeModule('awz.admin')) return;
if(!Loader::includeModule($module_id)) return;

if(!AccessController::can(0, ActionDictionary::ACTION_STAT_VIEW))
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

require_once('check_awz_admin.php');

/* "Awz\Utm\AdminPages\UtmList" replace generator */
use Awz\Utm\AdminPages\UtmList as PageList;

$APPLICATION->SetTitle(PageList::getTitle());
$arParams = PageList::getParams();

include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/awz.admin/include/handler.php");
/* @var bool $customPrint */
if(!$customPrint) {
    $adminCustom = new PageList($arParams);
    $adminCustom->defaultInterface();
}