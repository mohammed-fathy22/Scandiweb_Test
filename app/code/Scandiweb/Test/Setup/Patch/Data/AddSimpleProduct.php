<?php

namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\State;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Create Test product class
 */
class AddSimpleProduct implements DataPatchInterface
{
    /**
     * @var array
     */
    protected array $sourceItems = [];

    /**
     * Test patch constructor.
     *
     * @param ProductFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param SourceItemInterface $sourceItemFactory
     * @param SourceItemsSaveInterface $sourceItemsSaveInterface
     * @param State $appState
     * @param StoreManagerInterface $storeManager
     * @param EavSetup $eavSetup
     */
    public function __construct(
        protected ProductFactory $productFactory,
        protected ProductRepositoryInterface $productRepository,
        protected State $appState,
        protected StoreManagerInterface $storeManager,
        protected EavSetup $eavSetup,
        protected SourceItemInterface $sourceItemFactory,
        protected SourceItemsSaveInterface $sourceItemsSaveInterface
    ) {}

    /**
     * Add new product
     */
    public function apply(): void
    {
        $this->appState->emulateAreaCode('adminhtml', [$this, 'execute']);
    }

    /**
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws ValidationException
     */
    public function execute(): void
    {
        $product = $this->productFactory->create();

        if ($product->getIdBySku('test-product')) {
            return;
        }

        $attributeSetId = $this->eavSetup->getAttributeSetId(Product::ENTITY, 'Default');
        $websiteIDs = [$this->storeManager->getStore()->getWebsiteId()];
        $product->setTypeId(Type::TYPE_SIMPLE)
            ->setWebsiteIds($websiteIDs)
            ->setAttributeSetId($attributeSetId)
            ->setName('Test Product')
            ->setUrlKey('testproduct')
            ->setSku('test-product')
            ->setPrice(49.99)
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStatus(Status::STATUS_ENABLED);
        $product = $this->productRepository->save($product);

        $sourceItem = $this->sourceItemFactory;
        $sourceItem->setSourceCode('default');
        $sourceItem->setQuantity(100);
        $sourceItem->setSku($product->getSku());
        $sourceItem->setStatus(SourceItemInterface::STATUS_IN_STOCK);
        $this->sourceItems[] = $sourceItem;

        $this->sourceItemsSaveInterface->execute($this->sourceItems);
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}
