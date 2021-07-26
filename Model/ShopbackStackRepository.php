<?php
declare(strict_types=1);

namespace Tada\Shopback\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Tada\Shopback\Api\Data\ShopbackStackInterface;
use Tada\Shopback\Api\Data\ShopbackStackSearchResultInterface;
use Tada\Shopback\Api\Data\ShopbackStackSearchResultInterfaceFactory;
use Tada\Shopback\Api\ShopbackStackRepositoryInterface;
use Tada\Shopback\Model\ResourceModel\ShopbackStack as ResourceModel;
use Tada\Shopback\Model\ResourceModel\ShopbackStack\Collection;
use Tada\Shopback\Model\ResourceModel\ShopbackStack\CollectionFactory;
use Tada\Shopback\Model\ShopbackStackFactory as ModelFactory;
use Tada\Shopback\Model\ShopbackStack as Model;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

class ShopbackStackRepository implements ShopbackStackRepositoryInterface
{
    /**
     * @var ResourceModel
     */
    protected $resourceModel;

    /**
     * @var ShopbackStackFactory
     */
    protected $modelFactory;

    /**
     * @var JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ShopbackStackSearchResultInterfaceFactory
     */
    protected $searchResultFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $criteriaBuilder;

    /**
     * ShopbackStackRepository constructor.
     * @param ResourceModel $resourceModel
     * @param ShopbackStackFactory $modelFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CollectionFactory $collectionFactory
     * @param ShopbackStackSearchResultInterfaceFactory $searchResultFactory
     * @param SearchCriteriaBuilder $criteriaBuilder
     */
    public function __construct(
        ResourceModel $resourceModel,
        ModelFactory $modelFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        CollectionProcessorInterface $collectionProcessor,
        CollectionFactory $collectionFactory,
        ShopbackStackSearchResultInterfaceFactory $searchResultFactory,
        SearchCriteriaBuilder $criteriaBuilder
    ) {
        $this->resourceModel = $resourceModel;
        $this->modelFactory = $modelFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultFactory = $searchResultFactory;
        $this->criteriaBuilder = $criteriaBuilder;
    }

    /**
     * @param int $entityId
     * @return ShopbackStackInterface
     * @throws NoSuchEntityException
     */
    public function get(int $entityId): ShopbackStackInterface
    {
        /** @var Model $model */
        $model = $this->modelFactory->create();
        $this->resourceModel->load($model, $entityId);

        if (!$model->getEntityId()) {
            throw NoSuchEntityException::singleField('entityId', $entityId);
        }

        return $model;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return ShopbackStackSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();

        $this->extensionAttributesJoinProcessor->process($collection);

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var ShopbackStackSearchResultInterface $searchResult */
        $searchResult = $this->searchResultFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * @param ShopbackStackInterface $object
     * @return ShopbackStackInterface
     * @throws CouldNotSaveException
     */
    public function save(ShopbackStackInterface $object): ShopbackStackInterface
    {
        try {
            $this->resourceModel->save($object);
        } catch (\Exception $e) {
            if ($object->getEntityId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save $object with ID %1. Error: %2',
                        [$object->getEntityId(), $e->getMessage()]
                    )
                );
            }

            throw new CouldNotSaveException(
                __('Unable to save new $object. Error: %1', $e->getMessage())
            );
        }
        return $object;
    }

    /**
     * @param ShopbackStackInterface $object
     * @return ShopbackStackInterface
     * @throws \Exception
     */
    public function delete(ShopbackStackInterface $object): ShopbackStackInterface
    {
        $this->resourceModel->delete($object);
        return $object;
    }

    /**
     * @return ShopbackStackSearchResultInterface
     */
    public function getPendingItems()
    {
        $criteria = $this->criteriaBuilder
            ->addFilter(ShopbackStackInterface::STATUS, 'pending')
            ->create();

        $pendingItems = $this->getList($criteria);

        return $pendingItems;
    }

    /**
     * @return ShopbackStackSearchResultInterface
     */
    public function getDoneItems()
    {
        $criteria = $this->criteriaBuilder
            ->addFilter(ShopbackStackInterface::STATUS, 'done')
            ->create();

        $pendingItems = $this->getList($criteria);

        return $pendingItems;
    }

    /**
     * @param int $orderId
     * @param string $action
     * @return false|ShopbackStackInterface
     * @throws CouldNotSaveException
     */
    public function addToStackWithAction(int $orderId, string $action)
    {
        if (!in_array($action, ShopbackStackInterface::ALLOW_ACTION)) {
            return false;
        }

        $model = $this->modelFactory->create();
        $model->setOrderId($orderId);
        $model->setAction($action);
        $model->setStatus('pending');

        return $this->save($model);
    }

    /**
     * @param ShopbackStackInterface $object
     * @return ShopbackStackInterface
     * @throws CouldNotSaveException
     */
    public function makeDone(ShopbackStackInterface $object)
    {
        $object->setStatus('done');
        return $this->save($object);
    }
}
