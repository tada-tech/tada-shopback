<?php
declare(strict_types=1);

namespace Tada\Shopback\Model;

use GuzzleHttp\RequestOptions;
use Magento\Sales\Api\Data\OrderInterface;
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
    const API_REQUEST_URI = "http://mage.dev.local/";
    const API_REQUEST_ENDPOINT = "rest/V1/shopback/order";

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
     * @param Data $configData
     * @param \Tada\Shopback\Model\ShopbackHttpAdapter $httpAdapter
     * @param LoggerInterface $logger
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        Data $configData,
        ShopbackHttpAdapter $httpAdapter,
        LoggerInterface  $logger,
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory
    ) {
        $this->configData = $configData;
        $this->http = $httpAdapter;
        $this->logger = $logger;
    }

    /**
     * @param OrderInterface $order
     */
    public function execute(OrderInterface $order): Response
    {
        /** @var \Tada\CashbackTracking\Api\Data\CashbackTrackingInterface $partner */
        $partner = $order->getExtensionAttributes()->getPartnerTracking();

        $params = [
            'query' => [
                'offer_id' => $this->configData->getShopbackOfferId(), //OFFER_ID SHOPBACK ISSUED
                'amount' => $order->getGrandTotal(),
                'adv_sub' => $order->getEntityId(),
                'adv_sub5' => $order->getOrderCurrencyCode(),
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
}
