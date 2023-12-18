<?php

namespace Devlat\CategoryProductPos\Model\Service;

use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class DataService
{
    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;
    /**
     * @var Category
     */
    private $category;

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
     * @param array $inputs
     * @return bool
     */
    public function checkInputs(array $inputs): array
    {
        $flag = false;
        // Counts how many inputs are empty.
        $emptyCounter = array_sum(array_map(function($element) { return empty($element);}, $inputs['options']));
        if ($emptyCounter === 0) {
            $flag = true;
        }

        // Change the positions sign based on the mode value.
        if ($flag) {
            $positions = $inputs['options']['positions'];
            $isNum = is_numeric($positions) ?? false;
            if($isNum) {
                $positions = intval($positions);
                if (!$inputs['arguments']['mode']) {
                    $positions *= -1;
                }
            }
            $inputs['options']['positions'] = $positions;
            $flag = $isNum;
        }
        return [$flag, $inputs];
    }

    public function moveProductPosition(int $categoryId, string $skus, int $newPos): void
    {
        $skus = preg_replace('/\s+/', '', $skus);
        $skuList = explode(",", $skus);
        foreach ($skuList as $sku) {
            try {
                $product = $this->productRepository->get($sku);
                $productId = $product->getId();
                /** @var CategoryModel $category */
                $category = $this->categoryRepository->get($categoryId);

                $productsCategory = $product->getCategoryIds();
                if (!in_array($categoryId, $productsCategory)) {
                    continue;
                }
                $this->setProductPosition($productId, $category, $newPos);

            } catch (NoSuchEntityException $e) {
                throw new NoSuchEntityException(__($e->getMessage()));
            }

        }
    }

    /**
     * @param string $name
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategoryId(string $name): ?int {
        $categoryId = null;
        $collection = $this->categoryCollectionFactory->create()
            ->addAttributeToFilter('name', $name)
            ->setPageSize(1)
            ->getFirstItem()
            ->getData();
        if (!empty($collection)) {
           $categoryId = $collection['entity_id'];
        }
        return $categoryId;
    }

    private function setProductPosition(int $productId, CategoryModel $category, int $newPos) {
        $productsPositions = $category->getProductsPosition();
        asort($productsPositions);
        $flag = false;
        foreach ($productsPositions as $prodId => $position) {
            if ($prodId === $productId) {
                $productsPositions[$productId] += $newPos;
                $flag = true;
            }
            if ($flag) {
                continue;
            }
        }
        try{
            $category->setData('posted_products', $productsPositions);
            $this->category->save($category);
        } catch (\Exception $e) {
            throw new \Exception(__("Product Position not updated in cateogry: {$category->getName()}"));
        }
    }
}
