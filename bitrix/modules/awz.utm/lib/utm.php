<?php
namespace Awz\Utm;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM;

class UtmTable extends ORM\Data\DataManager {

    public static function getFilePath()
    {
        return __FILE__;
    }

    public static function getTableName()
    {
        return 'b_awz_utm';
    }

    public static function getMap()
    {
        return [
            (new ORM\Fields\IntegerField('ID'))
                ->configureTitle(Loc::getMessage('AWZ_UTM_UTM_ENTITY_ID'))
                ->configureAutocomplete()->configurePrimary(true),
            (new ORM\Fields\IntegerField('PARENT_ID'))
                ->configureTitle(Loc::getMessage('AWZ_UTM_UTM_ENTITY_PARENT_ID')),
            (new ORM\Fields\StringField('SITE_ID'))
                ->configureTitle(Loc::getMessage('AWZ_UTM_UTM_ENTITY_SITE_ID')),
            (new ORM\Fields\StringField('IP_ADDR'))
                ->configureTitle(Loc::getMessage('AWZ_UTM_UTM_ENTITY_IP_ADDR')),
            (new ORM\Fields\StringField('U_AGENT'))
                ->configureTitle(Loc::getMessage('AWZ_UTM_UTM_ENTITY_U_AGENT')),
            (new ORM\Fields\StringField('AWZ_UTM_UTM_ENTITY_PAGE'))
                ->configureTitle(Loc::getMessage('AWZ_UTM_UTM_ENTITY_PAGE')),
            (new ORM\Fields\StringField('REFERER'))
                ->configureTitle(Loc::getMessage('AWZ_UTM_UTM_ENTITY_REFERER')),
            (new ORM\Fields\StringField('SOURCE'))
                ->configureTitle(Loc::getMessage('AWZ_UTM_UTM_ENTITY_SOURCE')),
            (new ORM\Fields\StringField('MEDIUM'))
                ->configureTitle(Loc::getMessage('AWZ_UTM_UTM_ENTITY_MEDIUM')),
            (new ORM\Fields\StringField('CAMPAIGN'))
                ->configureTitle(Loc::getMessage('AWZ_UTM_UTM_ENTITY_CAMPAIGN')),
            (new ORM\Fields\StringField('CONTENT'))
                ->configureTitle(Loc::getMessage('AWZ_UTM_UTM_ENTITY_CONTENT')),
            (new ORM\Fields\StringField('TERM'))
                ->configureTitle(Loc::getMessage('AWZ_UTM_UTM_ENTITY_TERM')),
            (new ORM\Fields\DatetimeField('DATE_ADD'))
                ->configureTitle(Loc::getMessage('AWZ_UTM_UTM_ENTITY_DATE_ADD'))
        ];
    }

}