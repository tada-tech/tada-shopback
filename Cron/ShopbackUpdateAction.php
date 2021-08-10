<?php
declare(strict_types=1);

namespace Tada\Shopback\Cron;

use Psr\Log\LoggerInterface;
use Tada\Shopback\Api\UpdateStackProcessorInterface;

class ShopbackUpdateAction
{
    /**
     * @var UpdateStackProcessorInterface
     */
    protected $updateStackProcessor;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        UpdateStackProcessorInterface $updateStackProcessor,
        LoggerInterface $logger
    ) {
        $this->updateStackProcessor = $updateStackProcessor;
        $this->logger = $logger;
    }

    public function execute():void
    {
        try {
            $this->updateStackProcessor->execute();
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}
