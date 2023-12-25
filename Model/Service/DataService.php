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
     * @param array $inputs
     * @return array
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

    /**
     * @param int $categoryId
     * @param string $skus
     * @param int $newPos
     * @return void
     * @throws NoSuchEntityException
     */
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

    /**
     * @param int $productId
     * @param CategoryModel $category
     * @param int $newPos
     * @return void
     * @throws \Exception
     */
    private function setProductPosition(int $productId, CategoryModel $category, int $newPos) {
        $productsPositions = $category->getProductsPosition();
        $numberOfProducts = $category->getProductCount();
        $asc = ($newPos < 0) ?? false;
        $flag = false;
        asort($productsPositions);

        $auxPos = $productsPositions[$productId] + $newPos;
        if (!$asc) {
            $productsPositions[$productId] = ($auxPos >= $numberOfProducts) ? $numberOfProducts - 1 : $auxPos;
        }
        if ($asc) {
            $productsPositions[$productId] = ($auxPos < 0) ? 0 : $auxPos;
        }

        foreach ($productsPositions as $prodId => $position) {
            if ($asc) {
                if ($prodId === $productId) {
                    break;
                }
                if ($position == $productsPositions[$productId]) {
                    $flag = true;
                }
                if ($flag) {
                    $productsPositions[$prodId]++;
                }
            }
            else {
                if($prodId === $productId) {
                    $flag = true;
                    continue;
                }
                if ($flag) {
                    if($position == $productsPositions[$productId]) {
                        $flag = false;
                    }
                    $productsPositions[$prodId]--;
                }
            }
        }

        try{
            $category->setData('posted_products', $productsPositions);
            $this->category->save($category);
        } catch (\Exception $e) {
            throw new \Exception(__("Product Position not updated in category: {$category->getName()}"));
        }
    }
}
