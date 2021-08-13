<?php
declare(strict_types=1);

namespace Tada\Shopback\Model;

use GuzzleHttp\RequestOptions;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Tada\Shopback\Api\Data\ShopbackStackInterface;
use Tada\Shopback\Api\ShopbackCreateOrderInterface;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use GuzzleHttp\TransferStats;
use Psr\Log\LoggerInterface;
use Tada\Shopback\Helper\Data;
use Tada\Shopback\Model\ShopbackHttpAdapter;

class ShopbackCreateOrderService implements ShopbackCreateOrderInterface
{
    /**
     * @var Data
     */
    protected $configData;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ShopbackHttpAdapter
     */
    protected $http;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @param Data $configData
     * @param \Tada\Shopback\Model\ShopbackHttpAdapter $httpAdapter
     * @param LoggerInterface $logger
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Data $configData,
        ShopbackHttpAdapter $httpAdapter,
        LoggerInterface  $logger,
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->configData = $configData;
        $this->http = $httpAdapter;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param OrderItemInterface $orderItem
     */
    public function execute(OrderItemInterface $orderItem): Response
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderRepository->get((int)$orderItem->getOrderId());

        /** @var \Tada\CashbackTracking\Api\Data\CashbackTrackingInterface $partner */
        $partner = $order->getExtensionAttributes()->getPartnerTracking();

        $params = [
            'query' => [
                'offer_id' => $this->configData->getCreateShopbackOfferId(), //OFFER_ID SHOPBACK ISSUED
                'amount' => $orderItem->getRowTotal(),
                'adv_sub' => $this->configData->toAdvSub($orderItem),
                'transaction_id' => $partner->getPartnerParameter(),
                'datetime' => $this->http->formatDateTime($order->getCreatedAt()), //need format GMT +7 Asia/Bangkok
                'timezone' => ShopbackHttpAdapter::BANGKOK_TIME_ZONE,
                'security_token' => $this->configData->getShopbackSecurityToken()
            ],
            RequestOptions::ON_STATS => function (TransferStats $stats) use (&$fullRequestUrl) {
                $fullRequestUrl = $stats->getEffectiveUri();
            }
        ];

        /** @var Response $response */
        $response = $this->http
            ->setBaseUri(self::API_REQUEST_URI)
            ->doRequest(self::API_REQUEST_ENDPOINT, $params);

        //START LOGGING
        $this->logger->info('Request: ' . $fullRequestUrl);

        $this->logger->info('Response: StatusCode: ' . $response->getStatusCode()
            . ', ResponseBody: ' . $response->getBody());

        return $response;
    }

    public function isSkip(ShopbackStackInterface $shopbackStack): bool
    {
        return false;
    }

    public function afterExecute(Response $response, ShopbackStackInterface $shopbackStack): void
    {
        $status = $response->getStatusCode();
        $body = $this->http->decode((string)$response->getBody());

        if ($status == 200 && $body['success'] == true) {
            $shopbackStack->done();
        }
    }

    public function beforeExecute(ShopbackStackInterface $shopbackStack): void
    {
        // TODO: Implement beforeExecute() method.
    }
}
