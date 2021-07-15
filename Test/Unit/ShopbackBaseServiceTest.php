<?php
declare(strict_types=1);

namespace Tada\Shopback\Test\Unit;

use GuzzleHttp\ClientFactory;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Setup\Model\ThemeDependencyCheckerFactory;
use PHPUnit\Framework\TestCase;
use Mockery;
use Tada\Shopback\Model\ShopbackBaseService;

class ShopbackBaseServiceTest extends TestCase
{
    /**
     * @var ShopbackBaseService|__anonymous@649
     */
    protected $baseService;

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
        $this->baseService = new class($this->clientFactory, $this->responseFactory) extends ShopbackBaseService {
            public function getThis()
            {
                return $this;
            }
        };
    }

    protected function tearDown()
    {
        Mockery::close();
    }

    public function testformatDateTime()
    {
        $now = (new \DateTime('now'))->format('Y-m-d H:i:s');
        $timeZone = new \DateTimeZone(ShopbackBaseService::BANGKOK_TIME_ZONE);
        $expect = (new \DateTime($now, $timeZone))->format('Y-m-d H:i:s');

        $actualResult = $this->baseService->getThis()
            ->formatDateTime($now);

        $this->assertEquals($expect, $actualResult);
    }
}
