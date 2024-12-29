<?php
namespace Awz\Utm;

use Awz\BxApi\Agent;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

class Handlers {

    public static function OnProlog()
    {

        $context = Application::getInstance()->getContext();
        if($context->getRequest()->isAdminSection()) return;
        $utmData = array('utm_source','utm_medium','utm_term','utm_campaign','utm_content');
        $request = $context->getRequest();
        if($request->get('utm_source') || $request->get('utm_medium') || $request->get('utm_term') || $request->get('utm_campaign') || $request->get('utm_content'))
        {
            $appUtm = App::getInstance();
            $createNew = false;
            foreach($utmData as $v){
                if($value = $request->get($v)){
                    if($value == $appUtm->get($v)) continue;
                    $createNew = true;
                    $appUtm->set($v, $value);
                }
            }
            if($createNew) $appUtm->save(true);
        }

    }


    public static function OnBeforeEventAdd(&$ev, &$lid, &$arFields, &$message_id, &$files, &$languageId)
    {

        $context = Application::getInstance()->getContext();
        if($context->getRequest()->isAdminSection()) return;
        if(Option::get(Agents::MODULE, 'MAIL_EVENT', 'N', '')=="N")
            return;

        if(!is_array($arFields)) $arFields = [];

        $utmData = array('utm_source','utm_medium','utm_term','utm_campaign','utm_content');

        $appUtm = App::getInstance();
        $arFields['AWZ_UTM'] = str_replace("<br>","; ",$appUtm->getHtml());

        foreach($utmData as $code){
            $arFields['AWZ_'.mb_strtoupper($code)] = $appUtm->get($code);
        }

    }

    public static function OnSaleOrderBeforeSaved(\Bitrix\Main\Event $event)
    {
        /* @var \Bitrix\Sale\Order $order*/
        $order = $event->getParameter("ENTITY");
        if(!$order) return;
        if(!$order->isNew()) return;
        //$order->getSiteId()
        if(Option::get(Agents::MODULE, 'SALE_EVENT', 'N', $order->getSiteId())=='N')
            return;
        $appUtm = App::getInstance($order->getSiteId());
        if($appUtm->isEmpty()) return;
        $propertyCollection = $order->getPropertyCollection();
        /* @var \Bitrix\Sale\EntityPropertyValue $prop*/
        $isSet0 = false;
        $isSet1 = false;
        $isSet2 = false;
        $isSet3 = false;
        $isSet4 = false;
        $isSet5 = false;
        foreach($propertyCollection as $prop){
            if(!$isSet0 &&
                in_array(
                    $prop->getPropertyId(),
                    explode(',',Option::get(Agents::MODULE, 'SALE_UTM', '', $order->getSiteId()))
                )
            ){
                $isSet0 = true;
                $prop->setValue(str_replace("<br>","; ",$appUtm->getHtml()));
            }
            if(!$isSet1 &&
                in_array(
                    $prop->getPropertyId(),
                    explode(',',Option::get(Agents::MODULE, 'SALE_UTM_SOURCE', '', $order->getSiteId()))
                )
            ){
                $isSet1 = true;
                $prop->setValue($appUtm->get('SOURCE'));
            }
            if(!$isSet2 &&
                in_array(
                    $prop->getPropertyId(),
                    explode(',',Option::get(Agents::MODULE, 'SALE_UTM_MEDIUM', '', $order->getSiteId()))
                )
            ){
                $isSet2 = true;
                $prop->setValue($appUtm->get('MEDIUM'));
            }
            if(!$isSet3 &&
                in_array(
                    $prop->getPropertyId(),
                    explode(',',Option::get(Agents::MODULE, 'SALE_UTM_TERM', '', $order->getSiteId()))
                )
            ){
                $isSet3 = true;
                $prop->setValue($appUtm->get('TERM'));
            }
            if(!$isSet4 &&
                in_array(
                    $prop->getPropertyId(),
                    explode(',',Option::get(Agents::MODULE, 'SALE_UTM_CAMPAIGN', '', $order->getSiteId()))
                )
            ){
                $isSet4 = true;
                $prop->setValue($appUtm->get('CAMPAIGN'));
            }
            if(!$isSet5 &&
                in_array(
                    $prop->getPropertyId(),
                    explode(',',Option::get(Agents::MODULE, 'SALE_UTM_CONTENT', '', $order->getSiteId()))
                )
            ){
                $isSet5 = true;
                $prop->setValue($appUtm->get('CONTENT'));
            }
        }

        $event->addResult(
            new \Bitrix\Main\EventResult(
                \Bitrix\Main\EventResult::SUCCESS, $order
            )
        );

    }

}