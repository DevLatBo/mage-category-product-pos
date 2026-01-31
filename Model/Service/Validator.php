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
    public function checkInputs(array $inputs): array
    {
        // Counts how many inputs are empty.
        $emptyCounter = array_sum(array_map(function($element) { return empty($element);}, $inputs));

        if ($emptyCounter) {
            throw new ValidationException(
                __("Category, Skus and Pos are required and the jump data has to be a numeric value or non-zero, please check again.")
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

    /**
     * Checks if the category with the given name exists and returns its ID.
     * @param string $name
     * @return int
     * @throws ValidationException
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
                __("There is no category found with the name: {$name}")
            );
        }
        return $categoryId;
    }
}
