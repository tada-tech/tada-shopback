<?php
declare(strict_types=1);

namespace Tada\Shopback\Block;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Tada\Shopback\Helper\Data;
use Tada\Shopback\Model\ShopbackHttpAdapter;

class Simulator extends \Magento\Framework\View\Element\Template
{
    /**
     * @var ShopbackHttpAdapter
     */
    protected $http;

    /**
     * @var Data
     */
    protected $configData;

    /**
     * @param ShopbackHttpAdapter $http
     * @param Data $configData
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        ShopbackHttpAdapter $http,
        Data $configData,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->http = $http;
        $this->configData = $configData;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getMerchantUrl()
    {
        $params = [
            'query' => [
                'offer_id' => $this->configData->getCreateShopbackOfferId(),
                'aff_id' => $this->configData->getCreateShopbackAffiliateId()
            ],
            RequestOptions::ALLOW_REDIRECTS => false
        ];

        /** @var Response $response */
        $response = $this->http
            ->setBaseUri("https://shopback.go2cloud.org/")
            ->doRequest("aff_c", $params);

        if (empty($response->getHeader('Location'))) {
            return "";
        }

        $location = $response->getHeader('Location')[0];

        $baseUrl = rtrim($this->getBaseUrl(), "/");
        $merchantUrl = str_replace("https://www.petnme.co.th", $baseUrl, $location);

        return $merchantUrl;
    }

    /**
     * @return Data
     */
    public function getConfig()
    {
        return $this->configData;
    }
}
