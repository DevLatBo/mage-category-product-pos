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

        $currentProducts = $category->getProductsPosition();
        $postedProducts = $category->getPostedProducts();

        // Detects removed products: Compares the products in DB with the ones removed.
        $removedProducts = array_diff_key($currentProducts ?? [], $postedProducts ?? []);

        // Detects added products: Products added recently, but not included in DB.
        $addedProducts = array_diff_key($postedProducts ?? [], $currentProducts ?? []);


        if (empty($removedProducts) && empty($addedProducts)) {
            return;
        }

        asort($postedProducts);

        if (!empty($postedProducts)) {
            $productsPosUpdated = $this->reindexPositions($postedProducts);
            $category->setData('posted_products', $productsPosUpdated);
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
