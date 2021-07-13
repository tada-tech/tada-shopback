<?php
declare(strict_types=1);

namespace Tada\Shopback\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    const SHOPBACK_GENERAL_COOKIE_TIMELIFE = 'shopback/general/cookie_timelife';

    /**
     * @return int
     */
    public function getCookieTimeLife()
    {
        return (int)$this->scopeConfig->getValue(self::SHOPBACK_GENERAL_COOKIE_TIMELIFE);
    }
}
