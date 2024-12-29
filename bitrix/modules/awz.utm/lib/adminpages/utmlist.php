<?php

namespace Awz\Utm\AdminPages;

use Bitrix\Main\Localization\Loc;
use Awz\Admin\IList;
use Awz\Admin\IParams;
use Awz\Admin\Helper;
use Awz\Utm\Access\AccessController;
use Awz\Utm\Access\Custom\ActionDictionary;

Loc::loadMessages(__FILE__);

class UtmList extends IList implements IParams {

    public function __construct($params){
        parent::__construct($params);
    }

    public function trigerGetRowListAdmin($row){
        if(!AccessController::can(0, ActionDictionary::ACTION_STAT_EDIT))
            return;
        Helper::editListField($row, 'SOURCE', ['type'=>'string'], $this);
        Helper::editListField($row, 'MEDIUM', ['type'=>'string'], $this);
        Helper::editListField($row, 'CAMPAIGN', ['type'=>'string'], $this);
        Helper::editListField($row, 'CONTENT', ['type'=>'string'], $this);
        Helper::editListField($row, 'TERM', ['type'=>'string'], $this);
        Helper::editListField($row, 'PARENT_ID', ['type'=>'int'], $this);
    }

    public function trigerInitFilter(){
    }

    public function trigerGetRowListActions(array $actions): array
    {
        return $actions;
    }

    public static function getTitle(): string
    {
        return Loc::getMessage('AWZ_UTM_UTM_LIST_TITLE');
    }

    public function checkActions($right){

        if(!AccessController::can(0, ActionDictionary::ACTION_STAT_EDIT))
            return;

        // обработка одиночных и групповых действий
        if(($arID = $this->getAdminList()->GroupAction()))
        {
            $arID = $this->defaultGetActionId($arID);
            $resActions = $this->defaultGetAction($arID);
        }

        // сохранение отредактированных элементов
        if($this->getAdminList()->EditAction())
        {
            global $FIELDS;

            $act = $this->getParam("CALLBACK_ACTIONS");

            // пройдем по списку переданных элементов
            foreach($FIELDS as $ID=>$arFields)
            {
                if(!$this->getAdminList()->IsUpdated($ID))
                    continue;

                $entity = $this->getParam("ENTITY");

                foreach($arFields as $key=>$value){
                    $obField = $entity::getEntity()->getField($key);
                    if($obField instanceof \Bitrix\Main\Entity\DatetimeField){
                        $arData[$key]=\Bitrix\Main\Type\DateTime::createFromUserTime($value);
                    }else{
                        $arData[$key]=$value;
                    }
                }

                if(isset($act["edit"])){
                    call_user_func($act["edit"], $ID, $arData);
                }else{
                    $entity::update(array($this->getParam("PRIMARY")=>$ID),$arData);
                }

            }
        }
    }

    public static function getParams(): array
    {
        $arParams = array(
            "PRIMARY" => "ID",
            "ENTITY" => "\\Awz\\Utm\\UtmTable",
            "BUTTON_CONTEXTS"=>array(),
            "ADD_GROUP_ACTIONS"=>array(),
            "ADD_LIST_ACTIONS"=>array(),
            "FIND"=>array(),
            "FIND_FROM_ENTITY"=>[
                'ID'=>[],
                'PARENT_ID'=>[],
                'SITE_ID'=>[],
                'IP_ADDR'=>[],
                'U_AGENT'=>[],
                'SOURCE'=>[],
                'MEDIUM'=>[],
                'CAMPAIGN'=>[],
                'CONTENT'=>[],
                'TERM'=>[],
                'DATE_ADD'=>[]
            ]
        );
        if(AccessController::can(0, ActionDictionary::ACTION_STAT_EDIT)){
            $arParams['ADD_GROUP_ACTIONS'] = ["delete","edit"];
            $arParams['ADD_LIST_ACTIONS'] = ["delete"];
        }
        return $arParams;
    }
}