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
     * Constructor.
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
     * Checks if a product with the given SKU exists in the specified category.
     * @param int $categoryId
     * @param string $sku
     * @return bool
     * @throws NoSuchEntityException
     */
    public function checkProductInCategory(int $categoryId, string $sku): bool
    {
        try {
            $product = $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException(__($e->getMessage()));
        }
        return in_array($categoryId, $product->getCategoryIds());
    }
}
