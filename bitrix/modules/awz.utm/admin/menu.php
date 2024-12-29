<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Awz\Utm\Access\AccessController;
use Awz\Utm\Access\Custom\ActionDictionary;
Loc::loadMessages(__FILE__);
$module_id = "awz.utm";
if(!Loader::includeModule($module_id)) return;

$items = [];
if(AccessController::can(0, ActionDictionary::ACTION_STAT_VIEW)){
    $items[] = [
        "text" => Loc::getMessage('AWZ_UTM_MENU_NAME_STAT'),
        "url" => "awz_utm_utm_list.php?lang=".LANGUAGE_ID
    ];
}
if(AccessController::isViewSettings() || AccessController::isViewRight()){
    $level2 = [];
    if(AccessController::isViewSettings()){
        $level2[] = [
            "text" => Loc::getMessage('AWZ_ADMIN_MENU_NAME_SETT_1'),
            "url" => "settings.php?lang=".LANGUAGE_ID.'&mid='.$module_id.'&mid_menu=1'
        ];
    }
    if(AccessController::isViewRight()){
        $level2[] = [
            "text" => Loc::getMessage('AWZ_ADMIN_MENU_NAME_SETT_2'),
            "url" => "javascript:BX.SidePanel.Instance.open('/bitrix/admin/settings.php?mid=".$module_id."&lang=".LANGUAGE_ID."&mid_menu=1');"
        ];
    }
    $items[] = [
        "text" => Loc::getMessage('AWZ_ADMIN_MENU_NAME_SETT'),
        "items_id" => str_replace('.','_',$module_id).'_sett',
        "items"=>$level2
    ];
}

if(empty($items)) return;
$aMenu[] = array(
    "parent_menu" => "global_menu_marketing",
    "section" => str_replace('.','_',$module_id),
    "sort" => 100,
    "module_id" => $module_id,
    "text" => Loc::getMessage('AWZ_ADMIN_MENU_NAME_UTM'),
    "title" => Loc::getMessage('AWZ_ADMIN_MENU_NAME_UTM'),
    "items_id" => str_replace('.','_',$module_id),
    "items" => $items,
);
return $aMenu;