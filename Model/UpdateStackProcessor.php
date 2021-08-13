<?php
declare(strict_types=1);

namespace Tada\Shopback\Model;

use GuzzleHttp\Psr7\Response;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
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
     * @var OrderItemRepositoryInterface
     */
    protected $orderItemRepository;

    /**
     * @var ShopbackValidateOrderInterface
     */
    protected $validateService;

    /**
     * @var ShopbackCreateOrderInterface
     */
    protected $createService;

    /**
     * @param ShopbackStackRepositoryInterface $shopbackStackRepository
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param array $servicePool
     */
    public function __construct(
        ShopbackStackRepositoryInterface $shopbackStackRepository,
        OrderItemRepositoryInterface     $orderItemRepository,
        array                            $servicePool
    ) {
        $this->shopbackStackRepository = $shopbackStackRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->servicePool = $servicePool;
    }

    /**
     * @throws \Exception
     */
    public function execute(): void
    {
        $pending = $this->shopbackStackRepository->getPendingItems();

        $items = $pending->getItems();

        /** @var ShopbackStackInterface $item */
        foreach ($items as $item) {

            $action = $item->getAction();

            if (!isset($this->servicePool[$action])) {
                throw new \Exception(
                    "\"$action\" action in pool is not yet supported!"
                );
            }

            if ($this->servicePool[$action]->isSkip($item)) {
                continue;
            }

            $this->servicePool[$action]->beforeExecute($item);

            $orderItemId = (int)$item->getOrderItemId();
            /** @var OrderItemInterface $orderItem */
            $orderItem = $this->orderItemRepository->get($orderItemId);

            /** @var Response $response */
            $response = $this->servicePool[$action]->execute($orderItem);

            $this->servicePool[$action]->afterExecute($response, $item);
        }
    }
}
