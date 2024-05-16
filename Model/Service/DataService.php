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
use Magento\Framework\Exception\NoSuchEntityException;
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
            $newProductsPositions = $this->generateNewProductsPos($skuList, $category, $jump);
            /*foreach ($skuList as $sku) {
                $product = $this->productRepository->get($sku);
                $productsList[$product->getId()] = $this->getProductNewPos($product, $productsPositions, $numberOfProducts, $jump);
                $productsMoved[$product->getId()] = array(
                    'id'    =>  $product->getId(),
                    'sku'   =>  $product->getSku(),
                    'pos'   =>  $this->setProductPos($product, $category, $jump)
                );
            }*/
            $productsPosition = $category->getProductsPosition();
            // TODO: Verify the replacement of product position.
            /*$counter = 0;
            $limit = abs($jump);
            $step = ($jump < 0) ? 1 : -1;*/
            /*asort($productsPosition);
            if($jump < 0) {
                $productsPosition = array_reverse($productsPosition, true);
                $asc = true;
            }
            asort($productsPosition);*/
            print_r($productsPosition);
            print_r($newProductsPositions);
            foreach ($newProductsPositions as $productId => $position) {
                if (isset($productsPosition[$productId])) {
                    $productsPosition[$productId] = $position;
                }
            }
            //print_r($productsPosition);
            asort($productsPosition);
            if($jump < 0) {
                $productsPosition = array_reverse($productsPosition, true);
                $asc = true;
            }
            $flag = false;
            $counter = 0;
            $limit = abs($jump);
            $step = ($jump < 0) ? 1 : -1;
            /*foreach ($newProductsPositions as $prodId => $pos) {
                $flag = false;
                foreach ($productsPosition as $productId => $position) {
                    if(isset($newProductsPositions[$productId])) {
                        $flag = true;
                        continue;
                    }
                    if ($flag) {
                        $productsPosition[$productId] += $step;
                        $counter++;
                        if($counter === $limit) break;
                    }
                }
                //$productsPosition = $this->organizeProductsPositions($productsPosition, $jump);
            }*/
            print_r($productsPosition);die;
        } catch (Exception $e) {
            throw new Exception(__($e->getMessage()));
        }


        return $productsMoved;
    }

    /**
     * Returns the product positions list organized, based on jump value.
     * If jump value is negative, it will apply the reverse order,
     * otherwise it will be organized from the lowest to highest.
     * @param array $productsPositions
     * @param $jump
     * @return array
     */
    private function organizeProductsPositions(array $productsPositions, $jump): array
    {
        $productPosList = array();
        asort($productsPositions);
        $productPosList = $productsPositions;
        if($jump < 0) {
            $productPosList = array_reverse($productsPositions, true);
        }
        return $productPosList;
    }

    /**
     * This will generate an array of new product pos values,
     * if there is a position value repeated, it will increase or decrease its value based on jump value.
     *
     * @param array $skuList
     * @param CategoryModel $category
     * @param int $jump
     * @return array
     * @throws NoSuchEntityException
     */
    private function generateNewProductsPos(array $skuList, CategoryModel $category, int $jump): array
    {
        $productsPositions = $category->getProductsPosition();print_r($productsPositions);
        $numberOfProducts = $category->getProductCount();
        $posAux = -1;
        // si step es 1 es ASC, caso contrario es DESC (-1).
        $step = $jump < 0 ? 1 : -1;
        $funcAction = $step > 0 ? 'max':'min';
        $productsList = array();
        $asc = ($step > 0) ?? false;
        foreach ($skuList as $sku) {
            $product = $this->productRepository->get($sku);
            $productId = $product->getId();
            $productPos = $productsPositions[$productId] + $jump;
            if (!$asc) {
                $productPos = ($productPos >= $numberOfProducts) ? $numberOfProducts - 1 : $productPos;
            }
            if ($asc) {
                $productPos = ($productPos < 0) ? 0 : $productPos;
            }
            // TODO: Prepare new algorythm for new array product pos.
            if (in_array($productPos, $productsList)) {
                $elementVal = call_user_func_array($funcAction, [$productsList]);
                $productPos = $elementVal + $step;
            }
            $productsList[$productId] = $productPos;
        }
        print_r($productsList);
        die;
        return $productsList;
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
    private function setProductPosBACKUP(ProductInterface $product, CategoryModel $category, int $jump): int
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
