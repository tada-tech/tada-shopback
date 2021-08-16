<?php
declare(strict_types=1);

namespace Tada\Shopback\Api;

use Magento\Sales\Api\Data\OrderInterface;

interface AddStackProcessorInterface
{
    /**
     * @param OrderInterface $order
     * @param string $action
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(OrderInterface $order, string $action): void;
}
