<?php

namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\State;

class AddSimpleProduct implements \Magento\Framework\Setup\Patch\DataPatchInterface
{

    public function __construct(
        private ProductInterfaceFactory $productFactory,
        private ProductRepositoryInterface $productRepository,
        private State $state
    ) {}

    public function apply()
    {
        $this->state->setAreaCode('adminhtml');

        $product = $this->productFactory->create();
        $product->setSku('test-product')
            ->setName('Test Product')
            ->setPrice(49.99)
            ->setTypeId('simple')
            ->setAttributeSetId(4)
            ->setStatus(1)
            ->setVisibility(4)
            ->setWebsiteIds([1])
            ->setCategoryIds([2])
            ->setStockData([
                'use_config_manage_stock' => 1,
                'qty' => 100,
                'is_in_stock' => 1
            ]);

        $this->productRepository->save($product);
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
