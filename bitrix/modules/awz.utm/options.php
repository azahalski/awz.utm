<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\SiteTable;
use Bitrix\Main\Application;
use Bitrix\Main\UI\Extension;
use Awz\Utm\Access\AccessController;

Loc::loadMessages(__FILE__);
global $APPLICATION;
$module_id = "awz.utm";
if(!Loader::includeModule($module_id)) return;
Extension::load('ui.sidepanel-content');
$request = Application::getInstance()->getContext()->getRequest();
$APPLICATION->SetTitle(Loc::getMessage('AWZ_UTM_OPT_TITLE'));

if($request->get('IFRAME_TYPE')==='SIDE_SLIDER'){
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
require_once('lib/access/include/moduleright.php');
CMain::finalActions();
die();
}

if(!AccessController::isViewSettings())
$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$siteRes = SiteTable::getList(['select'=>['LID','NAME'],'filter'=>['ACTIVE'=>'Y']])->fetchAll();

if ($request->getRequestMethod()==='POST' && AccessController::isEditSettings() && $request->get('Update'))
{
    Option::set($module_id, "EXPIRED_DAYS", (int) $request->get("EXPIRED_DAYS"), "");
    Option::set($module_id, "MAIL_EVENT", $request->get("MAIL_EVENT") == 'Y' ? 'Y' : 'N', "");
    $cookies1 = $request->get("COOKIES");
    $utm0 = $request->get("SALE_UTM");
    $utm1 = $request->get("SALE_UTM_SOURCE");
    $utm2 = $request->get("SALE_UTM_MEDIUM");
    $utm3 = $request->get("SALE_UTM_TERM");
    $utm4 = $request->get("SALE_UTM_CAMPAIGN");
    $utm5 = $request->get("SALE_UTM_CONTENT");
    $saleEv = $request->get('SALE_EVENT');
    foreach($siteRes as $arSite){
        $c1 = (is_array($cookies1) && isset($cookies1[$arSite['LID']]) && $cookies1[$arSite['LID']]) ? intval($cookies1[$arSite['LID']]) : "0";
        $sv1 = (is_array($saleEv) && isset($saleEv[$arSite['LID']]) && $saleEv[$arSite['LID']] == 'Y') ? 'Y' : 'N';
        if(!is_array($utm0[$arSite['LID']])) $utm0[$arSite['LID']] = [];
        if(!is_array($utm1[$arSite['LID']])) $utm1[$arSite['LID']] = [];
        if(!is_array($utm2[$arSite['LID']])) $utm2[$arSite['LID']] = [];
        if(!is_array($utm3[$arSite['LID']])) $utm3[$arSite['LID']] = [];
        if(!is_array($utm4[$arSite['LID']])) $utm4[$arSite['LID']] = [];
        if(!is_array($utm5[$arSite['LID']])) $utm5[$arSite['LID']] = [];
        Option::set($module_id, "COOKIES", $c1, $arSite['LID']);
        Option::set($module_id, "SALE_EVENT", $sv1, $arSite['LID']);
        Option::set($module_id, "SALE_UTM", implode(',',$utm0[$arSite['LID']]), $arSite['LID']);
        Option::set($module_id, "SALE_UTM_SOURCE", implode(',',$utm1[$arSite['LID']]), $arSite['LID']);
        Option::set($module_id, "SALE_UTM_MEDIUM", implode(',',$utm2[$arSite['LID']]), $arSite['LID']);
        Option::set($module_id, "SALE_UTM_TERM", implode(',',$utm3[$arSite['LID']]), $arSite['LID']);
        Option::set($module_id, "SALE_UTM_CAMPAIGN", implode(',',$utm4[$arSite['LID']]), $arSite['LID']);
        Option::set($module_id, "SALE_UTM_CONTENT", implode(',',$utm5[$arSite['LID']]), $arSite['LID']);
    }
}

$aTabs = array();

$aTabs[] = array(
"DIV" => "edit1",
"TAB" => Loc::getMessage('AWZ_UTM_OPT_SECT1'),
"ICON" => "vote_settings",
"TITLE" => Loc::getMessage('AWZ_UTM_OPT_SECT1')
);

$saveUrl = $APPLICATION->GetCurPage(false).'?mid='.htmlspecialcharsbx($module_id).'&lang='.LANGUAGE_ID.'&mid_menu=1';
$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>
<style>.adm-workarea option:checked {background-color: rgb(206, 206, 206);}</style>
<form method="POST" action="<?=$saveUrl?>" id="FORMACTION">
    <?
    $tabControl->BeginNextTab();
    Extension::load("ui.alerts");
    ?>
    <tr>
        <td style="width:240px;"><?=Loc::getMessage('AWZ_UTM_OPT_EXPIRED_DAYS_TITLE')?></td>
        <td>
            <?$val = Option::get($module_id, "EXPIRED_DAYS", 0,"");?>
            <input type="text" value="<?=$val?>" name="EXPIRED_DAYS"></td>
        </td>
    </tr>
    <tr>
        <td style="width:240px;"><?=Loc::getMessage('AWZ_UTM_OPT_MAIL_EVENT_TITLE')?></td>
        <td>
            <?$val = Option::get($module_id, "MAIL_EVENT", 'N',"");?>
            <input type="checkbox" value="Y" name="MAIL_EVENT"<?if($val=='Y'){?> checked="checked"<?}?>></td>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <div class="ui-alert ui-alert-primary">
        <span class="ui-alert-message">
            <?=Loc::getMessage('AWZ_UTM_OPT_COOKIES2_TITLE_DESC')?>
        </span>
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <div class="ui-alert ui-alert-danger">
        <span class="ui-alert-message">
            <?=Loc::getMessage('AWZ_UTM_OPT_COOKIES2_TITLE_DESC2')?>
        </span>
            </div>
        </td>
    </tr>

    <?foreach($siteRes as $arSite){?>
        <tr class="heading">
            <td colspan="2">
                <b>[<?=$arSite['LID']?>] - <?=$arSite['NAME']?></b>
            </td>
        </tr>
        <tr>
            <td style="width:240px;"><?=Loc::getMessage('AWZ_UTM_OPT_COOKIES_TITLE')?></td>
            <td>
                <?$val = Option::get($module_id, "COOKIES", "0",$arSite['LID']);?>
                <select name="COOKIES[<?=$arSite['LID']?>]">
                    <option value="0"<?if($val==0){?> selected="selected"<?}?>><?=Loc::getMessage('AWZ_UTM_OPT_COOKIES_TITLE_SEL1')?></option>
                    <option value="1"<?if($val==1){?> selected="selected"<?}?>><?=Loc::getMessage('AWZ_UTM_OPT_COOKIES_TITLE_SEL2')?></option>
                    <option value="2"<?if($val==2){?> selected="selected"<?}?>><?=Loc::getMessage('AWZ_UTM_OPT_COOKIES_TITLE_SEL3')?></option>
                    <option value="3"<?if($val==3){?> selected="selected"<?}?>><?=Loc::getMessage('AWZ_UTM_OPT_COOKIES_TITLE_SEL4')?></option>
                    <option value="4"<?if($val==4){?> selected="selected"<?}?>><?=Loc::getMessage('AWZ_UTM_OPT_COOKIES_TITLE_SEL5')?></option>
                </select>
            </td>
        </tr>
        <?
        if(Loader::includeModule('sale')){
            $allProps = [];
            $candidatesInn = 0;
            if(Loader::includeModule('sale')){
                $r = \Bitrix\Sale\Internals\OrderPropsTable::getList([
                    'filter'=>['=ACTIVE'=>'Y','TYPE'=>'string','=PERSON_TYPE.PERSON_TYPE_SITE.SITE_ID'=>$arSite['LID']],
                    'select'=>['ID','NAME','CODE','PERSON_TYPE_NAME'=>'PERSON_TYPE.NAME'],
                    'order'=>['PERSON_TYPE.SORT'=>'ASC','PERSON_TYPE.NAME'=>'ASC','SORT'=>'ASC']
                ]);
                while($data = $r->fetch()){
                    $allProps[] = $data;
                }
            }
            ?>
            <tr>
                <td style="width:240px;"><?=Loc::getMessage('AWZ_UTM_OPT_SALE_EVENT_TITLE')?></td>
                <td>
                    <?$val = Option::get($module_id, "SALE_EVENT", 'N',$arSite['LID']);?>
                    <input type="checkbox" value="Y" name="SALE_EVENT[<?=$arSite['LID']?>]"<?if($val=='Y'){?> checked="checked"<?}?>></td>
                </td>
            </tr>
            <tr>
                <td style="width:240px;"><?=Loc::getMessage('AWZ_UTM_OPT_SALE_UTM_TITLE')?></td>
                <td>
                    <?$val = explode(",",Option::get($module_id, "SALE_UTM", "",$arSite['LID']));?>
                    <select name="SALE_UTM[<?=$arSite['LID']?>][]" multiple="multiple">
                        <?foreach($allProps as $prop){?>
                            <option value="<?=$prop['ID']?>"<?if(in_array($prop['ID'],$val)){?> selected="selected"<?}?>>[<?=$prop['ID']?>][<?=$prop['CODE']?>] = <?=$prop['NAME']?> (<?=$prop['PERSON_TYPE_NAME']?>)</option>
                        <?}?>
                    </select>
                </td>
            </tr>
            <tr>
                <td style="width:240px;"><?=Loc::getMessage('AWZ_UTM_OPT_SALE_UTM_SOURCE_TITLE')?></td>
                <td>
                    <?$val = explode(",",Option::get($module_id, "SALE_UTM_SOURCE", "",$arSite['LID']));?>
                    <select name="SALE_UTM_SOURCE[<?=$arSite['LID']?>][]" multiple="multiple">
                        <?foreach($allProps as $prop){?>
                            <option value="<?=$prop['ID']?>"<?if(in_array($prop['ID'],$val)){?> selected="selected"<?}?>>[<?=$prop['ID']?>][<?=$prop['CODE']?>] = <?=$prop['NAME']?> (<?=$prop['PERSON_TYPE_NAME']?>)</option>
                        <?}?>
                    </select>
                </td>
            </tr>
            <tr>
                <td style="width:240px;"><?=Loc::getMessage('AWZ_UTM_OPT_SALE_UTM_MEDIUM_TITLE')?></td>
                <td>
                    <?$val = explode(",",Option::get($module_id, "SALE_UTM_MEDIUM", "",$arSite['LID']));?>
                    <select name="SALE_UTM_MEDIUM[<?=$arSite['LID']?>][]" multiple="multiple">
                        <?foreach($allProps as $prop){?>
                            <option value="<?=$prop['ID']?>"<?if(in_array($prop['ID'],$val)){?> selected="selected"<?}?>>[<?=$prop['ID']?>][<?=$prop['CODE']?>] = <?=$prop['NAME']?> (<?=$prop['PERSON_TYPE_NAME']?>)</option>
                        <?}?>
                    </select>
                </td>
            </tr>
            <tr>
                <td style="width:240px;"><?=Loc::getMessage('AWZ_UTM_OPT_SALE_UTM_TERM_TITLE')?></td>
                <td>
                    <?$val = explode(",",Option::get($module_id, "SALE_UTM_TERM", "",$arSite['LID']));?>
                    <select name="SALE_UTM_TERM[<?=$arSite['LID']?>][]" multiple="multiple">
                        <?foreach($allProps as $prop){?>
                            <option value="<?=$prop['ID']?>"<?if(in_array($prop['ID'],$val)){?> selected="selected"<?}?>>[<?=$prop['ID']?>][<?=$prop['CODE']?>] = <?=$prop['NAME']?> (<?=$prop['PERSON_TYPE_NAME']?>)</option>
                        <?}?>
                    </select>
                </td>
            </tr>
            <tr>
                <td style="width:240px;"><?=Loc::getMessage('AWZ_UTM_OPT_SALE_UTM_CAMPAIGN_TITLE')?></td>
                <td>
                    <?$val = explode(",",Option::get($module_id, "SALE_UTM_CAMPAIGN", "",$arSite['LID']));?>
                    <select name="SALE_UTM_CAMPAIGN[<?=$arSite['LID']?>][]" multiple="multiple">
                        <?foreach($allProps as $prop){?>
                            <option value="<?=$prop['ID']?>"<?if(in_array($prop['ID'],$val)){?> selected="selected"<?}?>>[<?=$prop['ID']?>][<?=$prop['CODE']?>] = <?=$prop['NAME']?> (<?=$prop['PERSON_TYPE_NAME']?>)</option>
                        <?}?>
                    </select>
                </td>
            </tr>
            <tr>
                <td style="width:240px;"><?=Loc::getMessage('AWZ_UTM_OPT_SALE_UTM_CONTENT_TITLE')?></td>
                <td>
                    <?$val = explode(",",Option::get($module_id, "SALE_UTM_CONTENT", "",$arSite['LID']));?>
                    <select name="SALE_UTM_CONTENT[<?=$arSite['LID']?>][]" multiple="multiple">
                        <?foreach($allProps as $prop){?>
                            <option value="<?=$prop['ID']?>"<?if(in_array($prop['ID'],$val)){?> selected="selected"<?}?>>[<?=$prop['ID']?>][<?=$prop['CODE']?>] = <?=$prop['NAME']?> (<?=$prop['PERSON_TYPE_NAME']?>)</option>
                        <?}?>
                    </select>
                </td>
            </tr>
            <?
        }
        ?>

    <?}?>
    <?
    $tabControl->Buttons();
    ?>
    <input <?if (!AccessController::isEditSettings()) echo "disabled" ?> type="submit" class="adm-btn-green" name="Update" value="<?=Loc::getMessage('AWZ_UTM_OPT_L_BTN_SAVE')?>" />
    <input type="hidden" name="Update" value="Y" />
    <?if(AccessController::isViewRight()){?>
        <button class="adm-header-btn adm-security-btn" onclick="BX.SidePanel.Instance.open('<?=$saveUrl?>');return false;">
            <?=Loc::getMessage('AWZ_UTM_OPT_SECT2')?>
        </button>
    <?}?>
    <?$tabControl->End();?>
</form>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");