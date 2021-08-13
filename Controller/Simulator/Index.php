<?php
declare(strict_types=1);

namespace Tada\Shopback\Controller\Simulator;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\ForwardFactory;
use Tada\Shopback\Helper\Data;

class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var ForwardFactory
     */
    protected $forwardFactory;

    /**
     * @var Data
     */
    protected $configData;

    /**
     * @param ForwardFactory $forwardFactory
     * @param Data $configData
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param MessageManager $messageManager
     */
    public function __construct(
        ForwardFactory $forwardFactory,
        Data $configData,
        Context $context,
        PageFactory $resultPageFactory,
        MessageManager $messageManager
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->forwardFactory = $forwardFactory;
        $this->configData = $configData;
    }

    public function execute()
    {
        $testingEnabled = $this->configData->getTestingEnabled();
        if (!$testingEnabled) {
            /** @var Forward $forward */
            $forward = $this->forwardFactory->create();
            return $forward->forward('noroute');
        }

        $page = $this->resultPageFactory->create();
        return $page;
    }
}
