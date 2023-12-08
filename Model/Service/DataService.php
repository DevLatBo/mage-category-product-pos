<?php

namespace Devlat\CategoryProductPos\Model\Service;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

class DataService
{
    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory
    )
    {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * @param array $inputs
     * @return bool
     */
    public function checkInputs(array $inputs): array
    {
        $flag = false;
        // Counts how many inputs are empty.
        $emptyCounter = array_sum(array_map(function($element) { return empty($element);}, $inputs['options']));
        if ($emptyCounter === 0) {
            $flag = true;
        }

        // Change the positions sign based on the mode value.
        if ($flag) {
            $positions = $inputs['options']['positions'];
            $isNum = is_numeric($positions) ?? false;
            if($isNum) {
                $positions = intval($positions);
                if (!$inputs['arguments']['mode']) {
                    $positions *= -1;
                }
            }
            $inputs['options']['positions'] = $positions;
            $flag = $isNum;
        }
        return [$flag, $inputs];
    }

    public function moveProductPosition(int $categoryId, string $skus, int $newPos, bool $mode): void
    {
        $skus = preg_replace('/\s+/', '', $skus);
        $skuList = explode(",", $skus);
        foreach ($skuList as $sku) {

        }
        print_r($skuList);
    }

    /**
     * @param string $name
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategoryId(string $name): ?int {
        $categoryId = null;
echo $name;
        $collection = $this->categoryCollectionFactory->create()
            ->addAttributeToFilter('name', $name)
            ->setPageSize(1)
            ->getFirstItem()
            ->getData();
        if (!empty($collection)) {
           $categoryId = $collection['entity_id'];
        }
        return $categoryId;
    }

}
