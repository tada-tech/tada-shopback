<?php
declare(strict_types=1);

namespace Tada\Shopback\Model;

use Tada\Shopback\Api\Data\FakeShopbackResponseInterface;
use Tada\Shopback\Api\FakeShopbackRepositoryInterface;
use Tada\Shopback\Model\Data\FakeShopbackResponse;
use Tada\Shopback\Model\Data\FakeShopbackResponseFactory;
use Magento\Framework\Webapi\Rest\Request;

class FakeShopbackRepository implements FakeShopbackRepositoryInterface
{
    /**
     * @var FakeShopbackResponseFactory
     */
    protected $responseFactory;

    /**
     * @var Request
     */
    protected $request;

    /**
     * FakeShopbackRepository constructor.
     * @param FakeShopbackResponseFactory $fakeShopbackResponseFactory
     * @param Request $request
     */
    public function __construct(
        FakeShopbackResponseFactory $fakeShopbackResponseFactory,
        Request $request
    ) {
        $this->responseFactory = $fakeShopbackResponseFactory;
        $this->request = $request;
    }

    /**
     * GET - Create Order
     * @return FakeShopbackResponseInterface
     */
    public function createOrder()
    {
        /** @var FakeShopbackResponse $response */
        $response = $this->responseFactory->create();

        $params = $this->request->getParams();

        $response->setSuccess(true);
        return $response;
    }

    /**
     * GET - Validate Order
     * @return FakeShopbackResponseInterface
     */
    public function validateOrder()
    {
        /** @var FakeShopbackResponse $response */
        $response = $this->responseFactory->create();

        $params = $this->request->getParams();

        $response->setSuccess(false);
        return $response;
    }
}
