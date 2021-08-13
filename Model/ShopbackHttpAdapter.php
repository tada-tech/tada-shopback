<?php
declare(strict_types=1);

namespace Tada\Shopback\Model;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Webapi\Rest\Request;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;

class ShopbackHttpAdapter
{
    const BANGKOK_TIME_ZONE = "Asia/Bangkok";

    protected $baseUri = "";

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
     * @param string $uri
     * @return $this
     */
    public function setBaseUri(string $uri)
    {
        $this->baseUri = $uri;
        return $this;
    }

    /**
     * @param $uriEndpoint
     * @param array $params
     * @param string $requestMethod
     * @return Response
     */
    public function doRequest($uriEndpoint, $params = [], $requestMethod = Request::HTTP_METHOD_GET): Response
    {
        /** @var Client $client */
        $client = $this->clientFactory->create(['config' => [
            'base_uri' => $this->baseUri
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

    public function decode(string $body): array
    {
        $arr = explode(";", $body);
        $result = [];

        foreach ($arr as $item) {
            if (!$item) {
                continue;
            }
            list($key, $value) = explode("=", $item);
            $result[$key] = $value;
        }
        return $result;
    }
}
