<?php

namespace Devlat\CategoryProductPos\Model\Resolver;

use Devlat\CategoryProductPos\Model\Service\DataService;
use Devlat\CategoryProductPos\Model\Validator;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Validation\ValidationException;

class ProductPosition implements ResolverInterface
{

    /**
     * @var DataService
     */
    private DataService $dataService;
    private Validator $validator;

    public function __construct(
        DataService $dataService,
        Validator $validator
    )
    {
        $this->dataService = $dataService;
        $this->validator = $validator;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    )
    {
        // Input data validation.
        $inputs = $args['input'];
        $inputs['mode'] = !isset($inputs['mode']) ? 'ASC' : strtoupper($inputs['mode']);
        [$valid, $inputs] = $this->validator->checkInputs($inputs);
        if(!$valid) {
            throw new ValidationException(
                __("Category, Skus and Pos are required and Pos must be a numeric value, please check again.")
            );
        }

        // Validation of category.
        $category       =   $inputs['category'];
        $categoryId = $this->dataService->getCategoryId($category);
        if (is_null($categoryId)) {
            throw new ValidationException(
                __("There is no category found according to the category: {$category}")
            );
        }
        $skus               =   $inputs['skus'];
        [$productsNotMoved, $skuList] = $this->dataService->validProductInCategory($categoryId, $skus);

        $jumpPositions    =   $inputs['jump'];
        $productsMoved = $this->dataService->moveProductPosition($categoryId, $skuList, $jumpPositions);
        return [
            'category'  =>  $category,
            'moved'     =>  $productsMoved,
            'notMoved'  =>  $productsNotMoved
        ];

    }
}
