<?php
declare(strict_types=1);

namespace Tada\Shopback\Api\Data;

interface FakeShopbackResponseInterface
{
    const SUCCESS = 'success';
    const ERR_MSG = 'err_msg';

    /**
     * @return bool
     */
    public function getSuccess();

    /**
     * @return string
     */
    public function getErrMsg();
}
