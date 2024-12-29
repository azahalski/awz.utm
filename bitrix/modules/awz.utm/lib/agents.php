<?php
namespace Awz\Utm;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Type\DateTime;

class Agents {

    const MODULE = 'awz.utm';

    public static function deleteOld(){

        $days = Option::get(
            static::MODULE, 'EXPIRED_DAYS',
            0, ""
        );
        if($days>0){
            $timeMax = time() - $days*86400;
            $r = UtmTable::getList([
                'select'=>['ID'],
                'filter'=>['<DATE_ADD'=>DateTime::createFromTimestamp($timeMax)]
            ]);
            while($data = $r->fetch()){
                UtmTable::delete($data);
            }
        }

        return "\\Awz\\Utm\\Agents::deleteOld();";
    }

}