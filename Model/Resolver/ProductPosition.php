<?php

namespace Devlat\CategoryProductPos\Model\Resolver;

use Devlat\CategoryProductPos\Model\Service\Data;
use Devlat\CategoryProductPos\Model\Service\Validator;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class ProductPosition implements ResolverInterface
{
    /**
     * @var Data
     */
    private Data $dataService;
    /**
     * @var Validator
     */
    private Validator $validator;

    /**
     * Constructor.
     * @param Data $dataService
     * @param Validator $validator
     */
    public function __construct(
        Data      $dataService,
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
        $inputs = $this->validator->checkInputs($inputs);

        // Validation of category.
        $category       =   $inputs['category'];
        $categoryId = $this->validator->getCategoryId($category);

        $sku                =   $inputs['sku'];
        $jump               =   $inputs['jump'];
        $canChangePosition  =   $this->validator->checkProductInCategory($categoryId, $sku);

        if ($canChangePosition) {
            $productsMoved = $this->dataService->setProductPositions($categoryId, $sku, $jump);
        }
        return [
            'product' => [
                'category'      =>  $category,
                'sku'           =>  $sku,
                'newPosition'   =>  $productsMoved['pos']
            ],
        ];

    }
}
