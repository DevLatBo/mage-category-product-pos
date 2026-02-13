<?php

namespace Devlat\CategoryProductPos\Block\Adminhtml\Category;

use Magento\Backend\Block\Template;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Class ProductPos
 * @package Devlat\CategoryProductPos\Block\Adminhtml\Category
 */
class ProductPos extends Template
{
    /** @var string  */
    protected $_template = 'Devlat_CategoryProductPos::catalog/category/edit/product_position.phtml';

    /** @var string  */
    private CONST CATEGORY_PRODUCT_TABLE = 'catalog_category_product';

    private ?CategoryInterface $loadedCategory = null;
    /**
     * @var JsonHelper|null
     */
    private ?JsonHelper $jsonHelper;
    /**
     * @var DirectoryHelper|null
     */
    private ?DirectoryHelper $directoryHelper;
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;
    /**
     * @var CategoryRepositoryInterface
     */
    private CategoryRepositoryInterface $categoryRepository;
    /**
     * @var ProductCollectionFactory
     */
    private ProductCollectionFactory $productCollectionFactory;

    public function __construct(
        Template\Context $context,
        RequestInterface $request,
        CategoryRepositoryInterface $categoryRepository,
        ProductCollectionFactory $productCollectionFactory,
        array $data = [],
        ?JsonHelper $jsonHelper = null,
        ?DirectoryHelper $directoryHelper = null
    )
    {
        parent::__construct($context, $data, $jsonHelper, $directoryHelper);
        $this->jsonHelper = $jsonHelper;
        $this->directoryHelper = $directoryHelper;
        $this->request = $request;
        $this->categoryRepository = $categoryRepository;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    public function getCategoryId() {
        return (int)($this->request->getParam('id') ?? 0);
    }

    public function getCategory() {
        if ($this->loadedCategory !== null) {
            return $this->loadedCategory;
        }

        $categoryId = $this->getCategoryId();
        if (!$categoryId) {
            return null;
        }

        $storeId = (int)($this->request->getParam('store') ?? 0);

        try {
            $this->loadedCategory = $this->categoryRepository->get($categoryId, $storeId);
        } catch (NoSuchEntityException $e) {
            $this->loadedCategory = null;
        }

        return $this->loadedCategory;
    }

    public function getProductsCollection(): ?ProductCollection
    {
        $category = $this->getCategory();
        if (!$category) {
            return null;
        }

        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect(['name','sku','image', 'small_image', 'thumbnail']);
        $collection->getSelect()->joinLeft(
            ['ccp' => self::CATEGORY_PRODUCT_TABLE],
            'e.entity_id = ccp.product_id',
            ['position' => 'position']
        );
        $collection->getSelect()->where('ccp.category_id = ?', $category->getId());
        $collection->getSelect()->order('ccp.position ASC');

        return $collection;
    }

}
