<?php

namespace Devlat\CategoryProductPos\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResourceModel;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\Controller\Result\JsonFactory;

class Product extends Action
{
    private JsonFactory $resultJsonFactory;
    private CategoryRepository $categoryRepository;
    private CategoryResourceModel $categoryResourceModel;

    public function __construct(
        JsonFactory $resultJsonFactory,
        CategoryRepository $categoryRepository,
        CategoryResourceModel $categoryResourceModel,
        Context $context)
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->categoryRepository = $categoryRepository;
        $this->categoryResourceModel = $categoryResourceModel;
    }

    public function execute()
    {

        $params = $this->getRequest()->getParams();
        $categoryId = $params['category_id'];
        $catProducts = json_decode($params['cat_prods'], true);

        $categoryProducts = [];
        foreach ($catProducts as $catProduct) {
            $categoryProducts[$catProduct['productId']] = $catProduct['position'];
        }

        /** @var CategoryModel $category */
        $category = $this->categoryRepository->get($categoryId);
        $category->setData('posted_products', $categoryProducts);

        $this->categoryResourceModel->save($category);

        return $this->resultJsonFactory->create()->setData([
            'success' => true,
        ]);
    }
}
