<?php

namespace Devlat\CategoryProductPos\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CategoryPrepareSave implements ObserverInterface
{
    /**
     * Observer in which update the product positions.
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {

        $category = $observer->getEvent()->getCategory();

        $newProductPos = $category->getPostedProducts();
        asort($newProductPos);


        if (is_array($newProductPos) && !empty($newProductPos)) {
            $productsPosUpdated = $this->reindexPositions($newProductPos);
            $category->setData('posted_products',$productsPosUpdated);
        }
    }

    /**
     * Reindex all product positions.
     * @param array $productPositions
     * @return array
     */
    private function reindexPositions(array $productPositions): array
    {
        $orderedProducts = [];
        $index = 0;

        foreach( $productPositions as $productId => $position ) {
            $orderedProducts[$productId] = $index;
            $index++;
        }

        return $orderedProducts;
    }
}
