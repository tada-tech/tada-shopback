<?php
declare(strict_types=1);

namespace Tada\Shopback\Model;

use GuzzleHttp\ClientFactory;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\TransferStats;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Tada\Shopback\Api\Data\ShopbackStackInterface;
use Tada\Shopback\Api\ShopbackStackRepositoryInterface;
use Tada\Shopback\Api\ShopbackValidateOrderInterface;
use Psr\Log\LoggerInterface;
use Tada\Shopback\Helper\Data;
use Tada\Shopback\Model\ShopbackHttpAdapter;

class ShopbackValidateOrderService implements ShopbackValidateOrderInterface
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
     * @var ShopbackStackRepositoryInterface
     */
    protected $shopbackStackRepository;

    /**
     * @param \Tada\Shopback\Model\ShopbackHttpAdapter $httpAdapter
     * @param Data $configData
     * @param LoggerInterface $logger
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     * @param ShopbackStackRepositoryInterface $shopbackStackRepository
     */
    public function __construct(
        ShopbackHttpAdapter $httpAdapter,
        Data $configData,
        LoggerInterface $logger,
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory,
        ShopbackStackRepositoryInterface $shopbackStackRepository
    ) {
        $this->http = $httpAdapter;
        $this->configData = $configData;
        $this->logger = $logger;
        $this->shopbackStackRepository = $shopbackStackRepository;
    }

    /**
     * @param OrderItemInterface $orderItem
     */
    public function execute(OrderItemInterface $orderItem): Response
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $orderItem->getOrder();

        $params = [
            'query' => [
                'offer_id' => $this->configData->getValidateShopbackOfferId(), //offer_id -> SHOPBACK ISSUED,
                'aff_id' => $this->configData->getValidateShopbackAffiliateId(), // aff_id -> SHOPBACK ISSUED
                'amount' => $orderItem->getRowTotal(),
                'adv_sub' => $this->configData->toAdvSub($orderItem),
                'status' => $this->configData->getShopbackStatusByOrderState($order->getState()),
                'security_token' => $this->configData->getShopbackSecurityToken() //security_token -> SHOPBACK ISSUED
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

    /**
     * @param ShopbackStackInterface $shopbackStack
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isSkip(ShopbackStackInterface $shopbackStack): bool
    {
        $orderItemId = (int)$shopbackStack->getOrderItemId();
        return $this->shopbackStackRepository->isCreateActionDone($orderItemId) == false;
    }

    /**
     * @param Response $response
     * @param ShopbackStackInterface $shopbackStack
     */
    public function afterExecute(Response $response, ShopbackStackInterface $shopbackStack): void
    {
        $status = $response->getStatusCode();
        $body = $this->http->decode((string)$response->getBody());
        if ($status == 200 && isset($body['success'])) {
            $shopbackStack->done();
        }
    }

    /**
     * @param ShopbackStackInterface $shopbackStack
     */
    public function beforeExecute(ShopbackStackInterface $shopbackStack): void
    {
        // TODO: Implement beforeExecute() method.
    }
}
