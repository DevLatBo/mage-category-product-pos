<?php

namespace Devlat\CategoryProductPos\Model\Resolver;

use Devlat\CategoryProductPos\Model\Service\DataService;
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

    public function __construct(
        DataService $dataService
    )
    {
        $this->dataService = $dataService;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    )
    {
        // TODO: Create a method helper for mapping input values.
        // Mapping input data and validation.
        $inputs = $args['input'];
        if(!isset($inputs['mode'])) {
            $inputs['mode'] = '';
        }
        $data = array (
            'options' => array(
                'category' => $inputs['category'],
                'skus'  =>  $inputs['skus'],
                'positions' => $inputs['positions']
            ),
            'arguments' => array(
                'mode'  =>  strtoupper($inputs['mode']) === 'DESC' ?? false
            )
        );
        [$valid , $inputs] = $this->dataService->checkInputs($data);
        if(!$valid) {
            throw new ValidationException(
                __("Category, Skus and Pos are required and Pos must be a numeric value, please check again.")
            );
        }

        // Validation of category.
        $category       =   $inputs['options']['category'];
        $categoryId = $this->dataService->getCategoryId($category);
        if (is_null($categoryId)) {
            throw new ValidationException(
                __("There is no category found according to the category: {$category}")
            );
        }

        $skus           =   $inputs['options']['skus'];
        $newPositions   =   $inputs['options']['positions'];
        [$notMoved, $skuList] = $this->dataService->validProductInCategory($categoryId, $skus);
        $productsPosition = $this->dataService->moveProductPosition($categoryId, $skuList, $newPositions);
        return [
            'category'  =>  $category,
            'moved'     =>  $productsPosition,
            'notMoved'  =>  $notMoved
        ];

    }
}
