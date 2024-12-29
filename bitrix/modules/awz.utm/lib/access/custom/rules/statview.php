<?php
namespace Awz\Utm\Access\Custom\Rules;

use Bitrix\Main\Access\AccessibleItem;
use Awz\Utm\Access\Custom\PermissionDictionary;
use Awz\Utm\Access\Custom\Helper;

class Statview extends \Bitrix\Main\Access\Rule\AbstractRule
{

    public function execute(AccessibleItem $item = null, $params = null): bool
    {
        if ($this->user->isAdmin())
        {
            return true;
        }
        if ($this->user->getPermission(PermissionDictionary::STAT_VIEW))
        {
            return true;
        }
        return false;
    }

}