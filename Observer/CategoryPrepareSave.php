<?php

namespace Devlat\CategoryProductPos\Observer;

use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResourceModel;

class CategoryPrepareSave implements ObserverInterface
{
    /**
     * @var CategoryFactory
     */
    private CategoryFactory $categoryFactory;
    /**
     * @var CategoryRepository
     */
    private CategoryRepository $categoryRepository;
    /**
     * @var CategoryResourceModel
     */
    private CategoryResourceModel $categoryResourceModel;


    /**
     * Constructor.
     * @param CategoryFactory $categoryFactory
     * @param CategoryRepository $categoryRepository
     * @param CategoryResourceModel $categoryResourceModel
     */
    public function __construct(
        CategoryFactory $categoryFactory,
        CategoryRepository $categoryRepository,
        CategoryResourceModel $categoryResourceModel
    )
    {
        $this->categoryFactory = $categoryFactory;
        $this->categoryRepository = $categoryRepository;
        $this->categoryResourceModel = $categoryResourceModel;
    }

    public function execute(Observer $observer)
    {

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/oscar.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $category = $observer->getEvent()->getCategory();
        $request = $observer->getEvent()->getRequest();

        $categoryId = (int)$category->getId();
        $actualCategory = $this->categoryFactory->create()->load($categoryId);
        $actualProductsPos = $actualCategory->getProductsPosition() ?? [];
        asort($actualProductsPos);

        $logger->info("old product positions");
        $logger->info(print_r($actualProductsPos, true));

        $newProductPos = $category->getPostedProducts();
        asort($newProductPos);
        $logger->info("new product positions");
        $logger->info(print_r($newProductPos, true));

        if (is_array($newProductPos) && !empty($newProductPos)) {
            $logger->info("entro aca: ".$categoryId);
            $productsPosUpdated = $this->fixSequenceOrder($newProductPos);
            $logger->info(print_r($productsPosUpdated, true));

            /** @var CategoryModel $categoryMod */
            $categoryMod = $this->categoryRepository->get($categoryId);
            $categoryMod->setPostedProducts($productsPosUpdated);
            $this->categoryResourceModel->save($categoryMod);
        }

    }

    private function fixSequenceOrder(array $productPositions): array
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
