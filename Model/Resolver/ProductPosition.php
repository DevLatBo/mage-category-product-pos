<?php

namespace Devlat\CategoryProductPos\Model\Resolver;

use Devlat\CategoryProductPos\Model\Service\DataService;
use Devlat\CategoryProductPos\Model\Validator;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

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
        $inputs['mode'] = strtoupper($inputs['mode']) === 'DESC' ? 'DESC' : 'ASC';
        $inputs = $this->validator->checkInputs($inputs);

        // Validation of category.
        $category       =   $inputs['category'];
        $categoryId = $this->dataService->getCategoryId($category);

        $skus                           =   $inputs['skus'];
        [$productsNotMoved, $skuList]   =   $this->validator->checkProductInCategory($categoryId, $skus);

        $jumpPositions    =   $inputs['jump'];
        $productsMoved = $this->dataService->moveProductPosition($categoryId, $skuList, $jumpPositions);
        return [
            'category'  =>  $category,
            'moved'     =>  $productsMoved,
            'notMoved'  =>  $productsNotMoved
        ];

    }
}
