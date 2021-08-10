<?php
declare(strict_types=1);

namespace Tada\Shopback\Model;

use GuzzleHttp\Psr7\Response;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Tada\Shopback\Api\Data\ShopbackStackInterface;
use Tada\Shopback\Api\ShopbackCreateOrderInterface;
use Tada\Shopback\Api\ShopbackServiceInterface;
use Tada\Shopback\Api\ShopbackStackRepositoryInterface;
use Tada\Shopback\Api\ShopbackValidateOrderInterface;
use Tada\Shopback\Api\UpdateStackProcessorInterface;

class UpdateStackProcessor implements UpdateStackProcessorInterface
{
    /**
     * @var ShopbackServiceInterface[]
     */
    private $servicePool;

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
     * UpdateStackProcessor constructor.
     * @param ShopbackStackRepositoryInterface $shopbackStackRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param ShopbackServiceInterface[] $servicePool
     */
    public function __construct(
        ShopbackStackRepositoryInterface $shopbackStackRepository,
        OrderRepositoryInterface $orderRepository,
        array $servicePool
    ) {
        $this->shopbackStackRepository = $shopbackStackRepository;
        $this->orderRepository = $orderRepository;
        $this->servicePool = $servicePool;
    }

    /**
     * @throws \Exception
     */
    public function execute(): void
    {
        $pending = $this->shopbackStackRepository->getPendingItems();

        /** @var ShopbackStackInterface $item */
        foreach ($pending->getItems() as $item) {
            $orderId = (int)$item->getOrderId();

            /** @var OrderInterface $order */
            $order = $this->orderRepository->get($orderId);
            $action = $item->getAction();

            if (!isset($this->servicePool[$action])) {
                throw new \Exception(
                    "\"$action\" action in pool is not yet supported!"
                );
            }

            /** @var Response $response */
            $response = $this->servicePool[$action]->execute($order);

            $status = $response->getStatusCode();
            $body = json_decode((string)$response->getBody(), true);

            if ($status == 200 && $body['success'] == true) {
                $item->done();
            }
        }
    }
}
