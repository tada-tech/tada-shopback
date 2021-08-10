<?php
declare(strict_types=1);

namespace Tada\Shopback\Model\Data;

use Tada\Shopback\Api\Data\FakeShopbackResponseInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

class FakeShopbackResponse extends AbstractExtensibleObject implements FakeShopbackResponseInterface
{
    /**
     * @return string
     */
    public function getErrMsg()
    {
        return $this->_get(self::ERR_MSG);
    }

    /**
     * @param string $errMsg
     * @return $this
     */
    public function setErrMsg($errMsg)
    {
        $this->setData(self::ERR_MSG, $errMsg);
        return $this;
    }

    /**
     * @return bool
     */
    public function getSuccess()
    {
        return $this->_get(self::SUCCESS);
    }

    /**
     * @param bool $bool
     * @return $this
     */
    public function setSuccess(bool $bool)
    {
        $this->setData(self::SUCCESS, $bool);
        return $this;
    }
}
