<?php
declare(strict_types=1);

namespace Tada\Shopback\Test\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use PHPUnit\Framework\TestCase;
use Mockery;
use Tada\Shopback\Model\ShopbackHttpAdapter;

class ShopbackHttpAdapterTest extends TestCase
{
    /**
     * @var ShopbackHttpAdapter|__anonymous@649
     */
    protected $adapter;

    /**
     * @var Mockery\MockInterface
     */
    protected $clientFactory;

    /**
     * @var Mockery\MockInterface
     */
    protected $responseFactory;

    protected function setUp()
    {
        $this->clientFactory = Mockery::mock(ClientFactory::class);
        $this->responseFactory = Mockery::mock(ResponseFactory::class);
        $this->adapter = new ShopbackHttpAdapter(
            $this->clientFactory,
            $this->responseFactory
        );
    }

    protected function tearDown()
    {
        Mockery::close();
    }

    public function testDoRequest()
    {
        $baseUri = '';
        $client = Mockery::mock(Client::class);

        $this->clientFactory
            ->shouldReceive('create')
            ->with(['config' => [
                'base_uri' => $baseUri]
            ])
            ->andReturn($client);

        $response = Mockery::mock(Response::class);

        $params = [
            'query' => [
                'param_1' => '13',
                'param_2' => 34
            ]
        ];

        $client->shouldReceive('request')
            ->with('GET', '/rest/V1/shopback/order', $params)
            ->andReturn($response);

        $actualResult = $this->adapter->doRequest('/rest/V1/shopback/order', $params);

        $this->assertSame($response, $actualResult);
    }

    public function testformatDateTime()
    {
        $now = (new \DateTime('now'))->format('Y-m-d H:i:s');
        $timeZone = new \DateTimeZone(ShopbackHttpAdapter::BANGKOK_TIME_ZONE);
        $expect = (new \DateTime($now, $timeZone))->format('Y-m-d H:i:s');

        $actualResult = $this->adapter->formatDateTime($now);

        $this->assertEquals($expect, $actualResult);
    }
}
