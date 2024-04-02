<?php

namespace Devlat\CategoryProductPos\Model;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Validation\ValidationException;

class Validator
{
    /**
     * @var ProductRepository
     */
    private ProductRepository $productRepository;

    /**
     * @param ProductRepository $productRepository
     */
    public function __construct(
        ProductRepository $productRepository
    )
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Validates the input data.
     * @param array $inputs
     * @return array
     * @throws ValidationException
     */
    public function checkInputs(array $inputs): array
    {
        $flag = false;
        // Counts how many inputs are empty.
        $emptyCounter = array_sum(array_map(function($element) { return empty($element);}, $inputs));
        if ($emptyCounter === 0) {
            $flag = true;
        }

        // Change the positions sign based on the mode value.
        if ($flag) {
            $positions = $inputs['jump'];
            $isNum = is_numeric($positions) ?? false;
            if($isNum) {
                $positions = intval($positions);
                if ($inputs['mode'] === 'ASC') {
                    $positions *= -1;
                }
            }
            $inputs['jump'] = $positions;
            $flag = $isNum;
        }
        if (!$flag) {
            throw new ValidationException(
                __("Category, Skus and Pos are required and the jump data has to be a numeric value, please check again.")
            );
        }
        return $inputs;
    }

    /**
     * Verifies if product is assigned to this category.
     * @param int $categoryId
     * @param string $skus
     * @return array
     * @throws NoSuchEntityException
     */
    public function checkProductInCategory(int $categoryId, string $skus): array
    {
        $productsNotMoved = [];
        $skus = preg_replace('/\s+/', '', $skus);
        $skuList = explode(",", $skus);
        foreach ($skuList as $key => $sku) {
            try {
                $product = $this->productRepository->get($sku);
                $productsCategory = $product->getCategoryIds();
                if(!in_array($categoryId, $productsCategory)) {
                    $productsNotMoved[] = array(
                        'id'    =>  $product->getId(),
                        'sku'   =>  $sku
                    );
                    unset($skuList[$key]);
                }
            } catch (NoSuchEntityException $e) {
                throw new NoSuchEntityException(__($e->getMessage()));
            }
        }
        return [$productsNotMoved, $skuList];
    }
}
