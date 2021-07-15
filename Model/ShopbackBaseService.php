<?php
declare(strict_types=1);

namespace Tada\Shopback\Model;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Webapi\Rest\Request;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;

abstract class ShopbackBaseService
{
    const API_REQUEST_URI = "";
    const API_REQUEST_ENDPOINT = "";
    const BANGKOK_TIME_ZONE = "Asia/Bangkok";

    /**
     * @var ClientFactory
     */
    protected $clientFactory;

    /**
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * ShopbackBaseService constructor.
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory
    ) {
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param $uriEndpoint
     * @param array $params
     * @param string $requestMethod
     * @return Response
     */
    protected function doRequest($uriEndpoint, $params = [], $requestMethod = Request::HTTP_METHOD_GET): Response
    {
        /** @var Client $client */
        $client = $this->clientFactory->create(['config' => [
            'base_uri' => static::API_REQUEST_URI
        ]]);

        try {
            $response = $client->request(
                $requestMethod,
                $uriEndpoint,
                $params
            );
        } catch (GuzzleException $exception) {
            /** @var Response $response */
            $response = $this->responseFactory->create([
                'status' => $exception->getCode(),
                'reason' => $exception->getMessage()
            ]);
        }

        return $response;
    }

    /**
     * @param string $dateTime
     * @param string $timeZone
     * @param string $format
     * @return string
     * @throws \Exception
     */
    public function formatDateTime(
        string $dateTime,
        string $timeZone = self::BANGKOK_TIME_ZONE,
        string $format = "Y-m-d H:i:s"
    ) {
        $timeZone = new \DateTimeZone($timeZone);
        $dateTime = new \DateTime($dateTime, $timeZone);
        return $dateTime->format($format);
    }
}
