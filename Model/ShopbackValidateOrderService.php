<?php
declare(strict_types=1);

namespace Tada\Shopback\Model;

use GuzzleHttp\ClientFactory;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\TransferStats;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Tada\Shopback\Api\ShopbackValidateOrderInterface;
use Psr\Log\LoggerInterface;

class ShopbackValidateOrderService extends ShopbackBaseService implements ShopbackValidateOrderInterface
{
    const API_REQUEST_URI = "http://mage.dev.local/";
    const API_REQUEST_ENDPOINT = "rest/V1/shopback/validate";

    const STATUS_MAP = [
        Order::STATE_CANCELED => 'rejected',
        Order::STATE_COMPLETE => 'approved'
    ];

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * ShopbackValidateOrderService constructor.
     * @param LoggerInterface $logger
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        LoggerInterface $logger,
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
        $params = [
            'query' => [
                'offer_id' => 142, //OFFER_ID SHOPBACK ISSUED,
                'aff_id' => 345, // aff_id SHOPBACK ISS
                'amount' => $order->getGrandTotal(),
                'adv_sub' => $order->getEntityId(),
                'adv_sub5' => $order->getOrderCurrencyCode(),
                'status' => self::STATUS_MAP[$order->getState()],
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
