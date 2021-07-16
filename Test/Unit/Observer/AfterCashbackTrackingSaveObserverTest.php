<?php
declare(strict_types=1);

namespace Tada\Shopback\Test\Unit\Observer;

use PHPUnit\Framework\TestCase;
use Mockery;
use Magento\Framework\Event\Observer;
use Magento\Framework\MessageQueue\PublisherInterface;
use Psr\Log\LoggerInterface;
use Tada\CashbackTracking\Model\CashbackTracking;
use Tada\Shopback\Observer\AfterCashbackTrackingSaveObserver;

class AfterCashbackTrackingSaveObserverTest extends TestCase
{
    /**
     * @var Mockery\MockInterface
     */
    protected $logger;

    /**
     * @var Mockery\MockInterface
     */
    protected $publisher;

    /**
     * @var AfterCashbackTrackingSaveObserver
     */
    protected $afterCashbackTrackingSaveObserver;

    protected function setUp()
    {
        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->publisher = Mockery::mock(PublisherInterface::class);
        $this->afterCashbackTrackingSaveObserver = new AfterCashbackTrackingSaveObserver(
            $this->publisher,
            $this->logger
        );
    }

    protected function tearDown()
    {
        Mockery::close();
    }

    public function testExecute()
    {
        /** @var CashbackTracking $cashbackEntity */
        $cashbackEntity = Mockery::mock(CashbackTracking::class);
        $observer = Mockery::mock(Observer::class);
        $observer->shouldReceive('getData')
            ->with('data_object')
            ->andReturn($cashbackEntity);

        $this->publisher
            ->shouldReceive('publish')
            ->with(AfterCashbackTrackingSaveObserver::TOPIC_NAME, $cashbackEntity);

        $this->assertNull($this->afterCashbackTrackingSaveObserver->execute($observer));
    }

    public function testExecuteAndThrowException()
    {
        $observer = Mockery::mock(Observer::class);
        $observer->shouldReceive('getData')
            ->with('data_object')
            ->andReturn(123);

        $exception = Mockery::mock(\Exception::class);
        $this->publisher
            ->shouldReceive('publish')
            ->with(AfterCashbackTrackingSaveObserver::TOPIC_NAME, 123)
            ->andThrow($exception);

        $this->logger
            ->shouldReceive('error')
            ->with($exception);

        $this->assertNull($this->afterCashbackTrackingSaveObserver->execute($observer));
    }
}
