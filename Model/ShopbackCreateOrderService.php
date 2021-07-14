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

class ShopbackCreateOrderService extends ShopbackBaseService implements ShopbackCreateOrderInterface
{
    const API_REQUEST_URI = "http://mage.dev.local/";
    const API_REQUEST_ENDPOINT = "rest/V1/shopback/order";

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * ShopbackCreateOrderService constructor.
     * @param LoggerInterface $logger
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        LoggerInterface  $logger,
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory
    ) {
        $this->logger = $logger;
        parent::__construct($clientFactory, $responseFactory);
    }

    /**
     * @param OrderInterface $order
     */
    public function execute(OrderInterface $order): void
    {
        /** @var \Tada\CashbackTracking\Api\Data\CashbackTrackingInterface $partner */
        $partner = $order->getExtensionAttributes()->getPartnerTracking();

        $params = [
            'query' => [
                'offer_id' => 142, //OFFER_ID SHOPBACK ISSUED
                'amount' => $order->getGrandTotal(),
                'adv_sub' => $order->getEntityId(),
                'adv_sub5' => $order->getOrderCurrencyCode(),
                'transaction_id' => $partner->getPartnerParameter(),
                'datetime' => $this->formatDateTime($order->getCreatedAt()), //need format GMT +7 Asia/Bangkok
                'timezone' => self::BANGKOK_TIME_ZONE,
                'security_token' => "MERCHANT TOKEN ISSUED"
            ],
            RequestOptions::ON_STATS => function (TransferStats $stats) use (&$fullRequestUrl) {
                $fullRequestUrl = $stats->getEffectiveUri();
            }
        ];

        /** @var Response $response */
        $response = $this->doRequest(self::API_REQUEST_ENDPOINT, $params);

        //START LOGGING
        $this->logger->info('Request: ' . $fullRequestUrl);

        $this->logger->info('Response: StatusCode: ' . $response->getStatusCode()
            . ', ResponseBody: ' . $response->getBody()->getContents());
    }
}
