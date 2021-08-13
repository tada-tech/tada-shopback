<?php
declare(strict_types=1);

namespace Tada\Shopback\Test\Integration\Observer;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Rate;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Tada\CashbackTracking\Api\CashbackTrackingRepositoryInterface;
use Tada\CashbackTracking\Model\CashbackTrackingFactory;
use Tada\Shopback\Api\Data\ShopbackStackInterface;
use Tada\Shopback\Api\ShopbackStackRepositoryInterface;

class AfterCashbackTrackingSaveObserverTest extends TestCase
{
    /**
     * @var ShopbackStackRepositoryInterface
     */
    protected $shopbackStackRepository;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var CashbackTrackingFactory
     */
    protected $cashbackTrackingFactory;

    /**
     * @var CashbackTrackingRepositoryInterface
     */
    protected $cashbackTrackingRepository;

    /**
     * @var PublisherInterface
     */
    protected $publisher;

    /**
     * @var CartManagementInterface
     */
    protected $cartManagement;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->shopbackStackRepository = $this->objectManager->get(ShopbackStackRepositoryInterface::class);
        $this->cashbackTrackingFactory = $this->objectManager->create(CashbackTrackingFactory::class);
        $this->cashbackTrackingRepository = $this->objectManager->create(CashbackTrackingRepositoryInterface::class);
        $this->cartManagement = $this->objectManager->create(CartManagementInterface::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testExecute()
    {
        /** @var Quote $quote */
        $quote = $this->_getQuote('test01');
        $quote = $this->_prepareQuote($quote);
        $orderId = (int) $this->cartManagement->placeOrder($quote->getId());

        $cashbackTracking = $this->cashbackTrackingFactory->create();
        $cashbackTracking->setData(
            [
                'order_id' => $orderId,
                'partner' => 'Shopback',
                'partner_parameter' => 'happynewyear'
            ]
        );

        $cashbackTrackingEntity = $this->cashbackTrackingRepository->save($cashbackTracking);

        $this->assertNotEmpty($cashbackTracking->getExtensionAttributes()->getAction());
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
    private function _prepareQuote(CartInterface $quote): Quote
    {
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
