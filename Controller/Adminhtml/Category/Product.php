<?php

namespace Devlat\CategoryProductPos\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResourceModel;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

class Product extends Action
{
    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;
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
     * @param JsonFactory $resultJsonFactory
     * @param CategoryRepository $categoryRepository
     * @param CategoryResourceModel $categoryResourceModel
     * @param Context $context
     */
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

    /**
     * Updates category products positions and refresh the page.
     * @return Json
     */
    public function execute(): Json
    {

        $params = $this->getRequest()->getParams();
        $categoryId = $params['category_id'];
        $catProducts = json_decode($params['cat_prods'], true);

        $categoryProducts = [];
        foreach ($catProducts as $catProduct) {
            $categoryProducts[$catProduct['productId']] = $catProduct['position'];
        }

        try {
            /** @var CategoryModel $category */
            $category = $this->categoryRepository->get($categoryId);
            $category->setData('posted_products', $categoryProducts);

            $this->categoryResourceModel->save($category);
        } catch (\Exception $e) {

        }

        return $this->resultJsonFactory->create()->setData([
            'success'       =>  true,
            'redirect_url'   =>  $this->_url->getUrl(
                'catalog/category/edit',
                ['id' => $categoryId])
        ]);
    }
}
