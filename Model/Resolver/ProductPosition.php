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

    /**
     * GraphQL query to set the product position in the category.
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array[]
     * @throws \Exception
     */
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
        $inputs = $this->validator->validatePositionInputs($inputs);

        $category   =   $inputs['category'];
        $sku        =   $inputs['sku'];
        $jump       =   intval($inputs['jump']);


        // Validation of category.
        $categoryId = $this->validator->getCategoryIdByName($category);

        $newPosition        =   null;
        $canChangePosition  =   $this->validator->checkProductInCategory($categoryId, $sku);
        if ($canChangePosition) {
            $productsMoved  =   $this->dataService->setProductPositions($categoryId, $sku, $jump);
            $newPosition    =   $productsMoved['pos'] ?? null;
        }

        return [
            'product' => [
                'category'      =>  $category,
                'sku'           =>  $sku,
                'newPosition'   =>  $newPosition,
            ],
        ];

    }
}
