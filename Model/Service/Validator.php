<?php

namespace Devlat\CategoryProductPos\Model\Service;

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
        // Counts how many inputs are empty.
        $emptyCounter = array_sum(array_map(function($element) { return empty($element);}, $inputs));

        if ($emptyCounter) {
            throw new ValidationException(
                __("Category, Skus and Pos are required and the jump data has to be a numeric value, please check again.")
            );
        }

        if (!is_numeric($inputs['jump'])) {
            throw new ValidationException(
                __("Jump requires to be a numeric value, please check again.")
            );
        }

        $inputs['jump'] = intval($inputs['jump']);

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
