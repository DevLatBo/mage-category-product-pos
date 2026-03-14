<?php

namespace Devlat\CategoryProductPos\Model\Service;

use Exception;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\Exception\NoSuchEntityException;

class Data
{
    /**
     * @var ProductRepository
     */
    private ProductRepository $productRepository;
    /**
     * @var CategoryRepository
     */
    private CategoryRepository $categoryRepository;
    /**
     * @var Category
     */
    private Category $category;
    /**
     * @var ProductCollectionFactory
     */
    private ProductCollectionFactory $productCollectionFactory;

    public function __construct(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        Category $category,
        ProductCollectionFactory $productCollectionFactory
    )
    {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->category = $category;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Set the products positions in the category.
     * @param int $categoryId
     * @param string $sku
     * @param int $jump
     * @return array
     * @throws Exception
     */
    public function setProductPositions(int $categoryId, string $sku, int $jump): array
    {
        $productPositioned = array();
        try {
            /** @var CategoryModel $category */
            $category = $this->categoryRepository->get($categoryId);
            $productsPositions = $category->getProductsPosition();
            $newProductsPositions = $this->organizingProductPositions($sku, $productsPositions, $jump);

            $category->setData('posted_products', $newProductsPositions);
            $this->category->save($category);

            $product = $this->productRepository->get($sku);
            $productPositioned = array(
                'id'    => $product->getId(),
                'sku'   => $product->getSku(),
                'pos'   => $newProductsPositions[$product->getId()]
            );
        } catch (Exception $e) {
            throw new Exception(__($e->getMessage()));
        }

        return $productPositioned;
    }

    /**
     *  Method in charge of generating the new position of the product and reordering the other products accordingly.
     * @param string $sku
     * @param array $productsPos
     * @param int $jump
     * @return array
     * @throws NoSuchEntityException
     */
    private function organizingProductPositions(string $sku, array $productsPos, int $jump): array
    {
        $numberOfProducts = count($productsPos);

        $product = $this->productRepository->get($sku);
        $productId = $product->getId();

        $oldPos = $productsPos[$productId];
        $newPos = $oldPos + $jump;

        if ($newPos >= $numberOfProducts) {
            $newPos = $numberOfProducts - 1;
        } elseif ($newPos < 0) {
            $newPos = 0;
        }
        asort($productsPos);

        foreach ($productsPos as $prodId => $pos) {
            if ($prodId === $productId) continue;

            if ($jump > 0 && $pos > $oldPos && $pos <= $newPos) {
                $productsPos[$prodId] = $pos - 1;

            } else if ($jump < 0 && $pos < $oldPos && $pos >= $newPos) {
                $productsPos[$prodId] = $pos + 1;
            }
        }
        $productsPos[$productId] = $newPos;

        return $productsPos;
    }

    /**
     * @param int $categoryId
     * @param string $type
     * @return ProductCollection
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function sortCategoryProducts(int $categoryId, string $type): void
    {
        $category = $this->categoryRepository->get($categoryId);

        $collection = $this->productCollectionFactory->create();
        $collection->addCategoryFilter($category)
            ->addAttributeToSelect(['entity_id', 'name', 'sku']);

        if ($type === 'id') $type = 'entity_id';

        $collection->setOrder($type, 'ASC');

        $pos = 0;
        $productsOrdered = [];
        foreach ($collection->getData() as $product) {
            $productsOrdered[$product['entity_id']] = $pos;
            $pos++;
        }

        try {
            /** @var CategoryModel $category */
            $category = $this->categoryRepository->get($categoryId);
            $category->setData('posted_products', $productsOrdered);
            $this->category->save($category);
        } catch (Exception $e) {
            throw new Exception(__($e->getMessage()));
        }


    }
}
