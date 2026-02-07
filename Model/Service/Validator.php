<?php

namespace Devlat\CategoryProductPos\Model\Service;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Validation\ValidationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

class Validator
{
    /**
     * @var ProductRepository
     */
    private ProductRepository $productRepository;
    /**
     * @var CategoryCollectionFactory
     */
    private CategoryCollectionFactory $categoryCollectionFactory;

    /**
     * Constructor.
     * @param ProductRepository $productRepository
     * @param CategoryCollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        ProductRepository $productRepository,
        CategoryCollectionFactory $categoryCollectionFactory
    )
    {
        $this->productRepository = $productRepository;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * Validates the input data.
     * @param array $inputs
     * @return array
     * @throws ValidationException
     */
    public function validatePositionInputs(array $inputs): array
    {
        $this->validateRequiredInputs($inputs, ['sku', 'category', 'jump']);

        if (!is_numeric($inputs['jump']) || (int)$inputs['jump'] === 0) {
            throw new ValidationException(
                __("Jump requires to be a numeric value, please check again.")
            );
        }

        return $inputs;
    }

    /**
     * Validates the input data for products reorganization in category.
     * @param array $inputs
     * @return void
     * @throws ValidationException
     */
    public function validateReorganizeInputs(array $inputs): void
    {
        $this->validateRequiredInputs($inputs, ['category', 'type']);

        $validTypes = ['id', 'name', 'sku'];
        if (!in_array(strtolower($inputs['type']), $validTypes)) {
            throw new ValidationException(
                __("Type must be one of the following: " . implode(', ', $validTypes) . ". Please check again.")
            );
        }
    }

    /**
     * Validates that the required fields are present and not empty.
     * @param array $inputs
     * @param array $requiredFields
     * @return void
     * @throws ValidationException
     */
    private function validateRequiredInputs(array $inputs, array $requiredFields): void
    {
        foreach ($requiredFields as $field) {
            if (!isset($inputs[$field]) || $inputs[$field] === '') {
                throw new ValidationException(
                    __("The field '{$field}' is required and cannot be empty. Please check again.")
                );
            }
        }
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

    /**
     * Checks if the category with the given name exists and returns its ID.
     * @param string $name
     * @return int
     * @throws ValidationException
     * @throws LocalizedException
     */
    public function getCategoryIdByName(string $name): int {
        $category = $this->categoryCollectionFactory->create()
            ->addAttributeToFilter('name', $name)
            ->setPageSize(1)
            ->getFirstItem()
            ->getData();

        $categoryId = intval($category['entity_id'] ?? 0);

        if (!$categoryId) {
            throw new ValidationException(
                __("There is no category found with the name: {$name}")
            );
        }
        return $categoryId;
    }
}
