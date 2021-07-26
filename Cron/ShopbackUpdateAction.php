<?php
declare(strict_types=1);

namespace Tada\Shopback\Cron;

use GuzzleHttp\Psr7\Response;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Tada\Shopback\Api\Data\ShopbackStackInterface;
use Tada\Shopback\Api\ShopbackStackRepositoryInterface;
use Tada\Shopback\Api\ShopbackValidateOrderInterface;
use Tada\Shopback\Api\ShopbackCreateOrderInterface;
use Psr\Log\LoggerInterface;

class ShopbackUpdateAction
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var ShopbackStackRepositoryInterface
     */
    protected $shopbackStackRepository;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var ShopbackValidateOrderInterface
     */
    protected $validateService;

    /**
     * @var ShopbackCreateOrderInterface
     */
    protected $createService;

    /**
     * ShopbackUpdateAction constructor.
     * @param LoggerInterface $logger
     * @param ShopbackStackRepositoryInterface $shopbackStackRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param ShopbackValidateOrderInterface $validateService
     * @param ShopbackCreateOrderInterface $createService
     */
    public function __construct(
        LoggerInterface $logger,
        ShopbackStackRepositoryInterface $shopbackStackRepository,
        OrderRepositoryInterface $orderRepository,
        ShopbackValidateOrderInterface $validateService,
        ShopbackCreateOrderInterface $createService
    ) {
        $this->logger = $logger;
        $this->shopbackStackRepository = $shopbackStackRepository;
        $this->orderRepository = $orderRepository;
        $this->validateService = $validateService;
        $this->createService = $createService;
    }

    public function execute()
    {
        try {
            $pending = $this->shopbackStackRepository->getPendingItems();

            if ($pending->getTotalCount() == 0) {
                return false;
            }

            /** @var ShopbackStackInterface $item */
            foreach ($pending->getItems() as $item) {
                $orderId = (int)$item->getOrderId();

                /** @var OrderInterface $order */
                $order = $this->orderRepository->get($orderId);

                $service = $this->_getActionService($item->getAction());
                if (!$service) {
                    return false;
                }

                /** @var Response $response */
                $response = $service->execute($order);

                $status = $response->getStatusCode();
                $body = json_decode((string)$response->getBody(), true);

                if ($status == 200 && $body['success'] == true) {
                    $item->done();
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }

    /**
     * @param string $action
     * @return ShopbackCreateOrderInterface|ShopbackValidateOrderInterface|null
     */
    protected function _getActionService(string $action)
    {
        if ($action == 'create') {
            return $this->createService;
        }
        if ($action == 'validate') {
            return $this->validateService;
        }
        return null;
    }
}
