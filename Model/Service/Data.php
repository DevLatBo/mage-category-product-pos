<?php

namespace Devlat\CategoryProductPos\Model\Service;

use Exception;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Validation\ValidationException;

class Data
{
    /**
     * @var CategoryCollectionFactory
     */
    private CategoryCollectionFactory $categoryCollectionFactory;
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
     * @param ProductRepository $productRepository
     * @param CategoryRepository $categoryRepository
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param Category $category
     */
    public function __construct(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        CategoryCollectionFactory $categoryCollectionFactory,
        Category $category
    )
    {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->category = $category;
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
            $newProductsPositions = $this->organizingProductPositions($sku, $category, $jump);

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
     * @param CategoryModel $category
     * @param int $jump
     * @return array
     * @throws NoSuchEntityException
     */
    private function organizingProductPositions(string $sku, CategoryModel $category, int $jump): array
    {
        $productsPositions = $category->getProductsPosition();
        $numberOfProducts = $category->getProductCount();

        $product = $this->productRepository->get($sku);
        $productId = $product->getId();

        $oldPos = $productsPositions[$productId];
        $newPos = $oldPos + $jump;

        if ($newPos >= $numberOfProducts) {
            $newPos = $numberOfProducts - 1;
        } elseif ($newPos < 0) {
            $newPos = 0;
        }
        asort($productsPositions);

        foreach ($productsPositions as $prodId => $pos) {
            if ($prodId === $productId) continue;

            if ($jump > 0 && $pos > $oldPos && $pos <= $newPos) {
                $productsPositions[$prodId] = $pos - 1;

            } else if ($jump < 0 && $pos < $oldPos && $pos >= $newPos) {
                $productsPositions[$prodId] = $pos + 1;
            }
        }
        $productsPositions[$productId] = $newPos;

        return $productsPositions;
    }

    /**
     * @param string $name
     * @return int
     * @throws LocalizedException
     */
    public function getCategoryId(string $name): int {
        $category = $this->categoryCollectionFactory->create()
            ->addAttributeToFilter('name', $name)
            ->setPageSize(1)
            ->getFirstItem()
            ->getData();

        $categoryId = intval($category['entity_id'] ?? 0);

        if (!$categoryId) {
            throw new ValidationException(
                __("There is no category found according to the category: {$name}")
            );
        }
        return $categoryId;
    }
}
