<?php
declare(strict_types=1);

namespace Tada\Shopback\Test\Integration;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Tada\Shopback\Api\ShopbackStackRepositoryInterface;
use Tada\Shopback\Model\UpdateStackProcessor;

class UpdateStackProcessorTest extends TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var ShopbackStackRepositoryInterface
     */
    protected $shopbackStackRepository;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var UpdateStackProcessor
     */
    protected $updateStackProcessor;

    protected $cartManagement;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->cartManagement = $this->objectManager->create(CartManagementInterface::class);
        $this->shopbackStackRepository = $this->objectManager->create(ShopbackStackRepositoryInterface::class);
        $this->orderRepository = $this->objectManager->create(OrderRepositoryInterface::class);

        $servicePool = [];
        $servicePool['create'] = $this->objectManager
            ->create(\Tada\Shopback\Api\ShopbackCreateOrderInterface::class);
        $servicePool['validate'] = $this->objectManager
            ->create(\Tada\Shopback\Api\ShopbackValidateOrderInterface::class);

        $this->updateStackProcessor = new UpdateStackProcessor(
            $this->shopbackStackRepository,
            $this->orderRepository,
            $servicePool
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testExecute()
    {
        $quote = $this->_getQuote('test01');
        $quote = $this->_prepareQuote($quote);

        $orderId = (int)$this->cartManagement->placeOrder($quote->getId());

        $stackItem = $this->shopbackStackRepository->addToStackWithAction($orderId, 'validate');

        $this->assertEquals('pending', $stackItem->getStatus());

        //Execute Cron
        $this->updateStackProcessor->execute();

        $pendingList = $this->shopbackStackRepository->getPendingItems();

        $this->assertEquals(0, $pendingList->getTotalCount());
    }

    /**
     * Gets quote by reserved order ID.
     *
     * @param string $reservedOrderId
     * @return Quote
     */
    private function _getQuote(string $reservedOrderId): Quote
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', $reservedOrderId)
            ->create();

        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $items = $quoteRepository->getList($searchCriteria)
            ->getItems();

        return array_pop($items);
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _prepareQuote(\Magento\Quote\Api\Data\CartInterface $quote): Quote
    {
        $tracking = [
            'partner' => 'shopback',
            'partner_parameter' => 'happy10discount'
        ];

        $quote->getPayment()->setAdditionalInformation($tracking);

        $shippingAddress = $quote->getShippingAddress();

        /** @var $rate Rate */
        $rate = $this->objectManager->create(Rate::class);
        $rate->setCode('flatrate_flatrate');
        $rate->setPrice(5);

        $shippingAddress->setShippingMethod('flatrate_flatrate');
        $shippingAddress->addShippingRate($rate);

        $quote->setShippingAddress($shippingAddress);
        $quote->setCheckoutMethod('guest');
        $quoteRepository = $this->objectManager
            ->get(\Magento\Quote\Api\CartRepositoryInterface::class);
        $quoteRepository->save($quote);

        return $quote;
    }
}
