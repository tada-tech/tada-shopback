<?php
declare(strict_types=1);

namespace Tada\Shopback\Model;

use GuzzleHttp\ClientFactory;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\TransferStats;
use Magento\Sales\Api\Data\OrderInterface;
use Tada\Shopback\Api\ShopbackValidateOrderInterface;
use Psr\Log\LoggerInterface;
use Tada\Shopback\Helper\Data;
use Tada\Shopback\Model\ShopbackHttpAdapter;

class ShopbackValidateOrderService implements ShopbackValidateOrderInterface
{
    const API_REQUEST_URI = "http://mage.dev.local/";
    const API_REQUEST_ENDPOINT = "rest/V1/shopback/validate";

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
     * ShopbackValidateOrderService constructor.
     * @param ShopbackHttpAdapter $httpAdapter
     * @param Data $configData
     * @param LoggerInterface $logger
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        ShopbackHttpAdapter $httpAdapter,
        Data $configData,
        LoggerInterface $logger,
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory
    ) {
        $this->http = $httpAdapter;
        $this->configData = $configData;
        $this->logger = $logger;
    }

    /**
     * @param OrderInterface $order
     */
    public function execute(OrderInterface $order): Response
    {
        $params = [
            'query' => [
                'offer_id' => 142, //OFFER_ID SHOPBACK ISSUED,
                'aff_id' => 345, // aff_id SHOPBACK ISS
                'amount' => $order->getGrandTotal(),
                'adv_sub' => $order->getEntityId(),
                'adv_sub5' => $order->getOrderCurrencyCode(),
                'status' => $this->configData->getShopbackStatusByOrderState($order->getState()),
                'security_token' => "MERCHANT TOKEN ISSUED"
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
