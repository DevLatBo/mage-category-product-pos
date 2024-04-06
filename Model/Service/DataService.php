<?php

namespace Devlat\CategoryProductPos\Model\Service;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validation\ValidationException;

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
     * @param int $categoryId
     * @param array $skuList
     * @param int $jump
     * @return array
     * @throws Exception
     */
    public function moveProductPosition(int $categoryId, array $skuList, int $jump): array
    {
        $productsMoved = array();
        try {
            /** @var CategoryModel $category */
            $category = $this->categoryRepository->get($categoryId);
            foreach ($skuList as $sku) {
                    $product = $this->productRepository->get($sku);
                    $productsMoved[$product->getId()] = array(
                        'id'    =>  $product->getId(),
                        'sku'   =>  $product->getSku(),
                        'pos'   =>  $this->setProductPos($product, $category, $jump)
                    );
            }
        } catch (Exception $e) {
            throw new Exception(__($e->getMessage()));
        }


        return $productsMoved;
    }

    /**
     * @param string $name
     * @return int
     * @throws LocalizedException
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
        if (is_null($categoryId)) {
            throw new ValidationException(
                __("There is no category found according to the category: {$name}")
            );
        }
        return $categoryId;
    }

    /**
     * @param ProductInterface $product
     * @param CategoryModel $category
     * @param int $jump
     * @return int
     * @throws Exception
     */
    private function setProductPos(ProductInterface $product, CategoryModel $category, int $jump): int
    {
        $productId = intval($product->getId());
        $productsPositions = $category->getProductsPosition();
        $numberOfProducts = $category->getProductCount();
        $asc = ($jump < 0) ?? false;
        $flag = false;
        asort($productsPositions);
        $auxPos = $productsPositions[$productId] + $jump;
        if (!$asc) {
            $productsPositions[$productId] = ($auxPos >= $numberOfProducts) ? $numberOfProducts - 1 : $auxPos;
        }
        if ($asc) {
            $productsPositions[$productId] = ($auxPos < 0) ? 0 : $auxPos;
        }
        // This will organize the other products positions.
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
        } catch (Exception $e) {
            throw new Exception(__("Product Position not updated in category: {$category->getName()}"));
        }

        return $productsPositions[$productId];
    }
}
