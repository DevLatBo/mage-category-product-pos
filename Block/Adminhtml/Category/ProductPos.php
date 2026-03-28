<?php

namespace Devlat\CategoryProductPos\Block\Adminhtml\Category;

use Exception;
use Devlat\CategoryProductPos\Model\Service\Validator as ServiceValidator;
use Magento\Backend\Block\Template;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
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

    /** @var string  */
    private CONST IMAGE_TYPE = 'product_page_image_small';

    /**
     * @var CategoryInterface|null
     */
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
    /**
     * @var ImageHelper
     */
    private ImageHelper $imageHelper;
    /**
     * @var ServiceValidator
     */
    private ServiceValidator $serviceValidator;

    /**
     * Constructor.
     * @param Template\Context $context
     * @param RequestInterface $request
     * @param CategoryRepositoryInterface $categoryRepository
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ImageHelper $imageHelper
     * @param array $data
     * @param JsonHelper|null $jsonHelper
     * @param DirectoryHelper|null $directoryHelper
     */
    public function __construct(
        Template\Context $context,
        RequestInterface $request,
        CategoryRepositoryInterface $categoryRepository,
        ProductCollectionFactory $productCollectionFactory,
        ImageHelper $imageHelper,
        ServiceValidator $serviceValidator,
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
        $this->imageHelper = $imageHelper;
        $this->serviceValidator = $serviceValidator;
    }

    /**
     * Returns the category ID from the request parameters.
     * @return int
     */
    public function getCategoryId():int
    {
        return (int)($this->request->getParam('id') ?? 0);
    }

    /**
     * Returns the category instance based on the category ID from the request parameters.
     * @return CategoryInterface|null
     */
    public function getCategory(): ?CategoryInterface
    {
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

    /**
     * Returns product collection ordered by position for the current category.
     * @return ProductCollection|null
     */
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

    /**
     * Returns the product image url, in case the product doesn't have an image, it returns the placeholder image url.
     * @param Product $product
     * @return string
     */
    public function getProductImageUrl(Product $product): string
    {
        $productImage = (string)$product->getSmallImage();

        if ($productImage === '' || $productImage === 'no_selection') {
            return $this->imageHelper->getDefaultPlaceholderUrl('small_image');
        }
        $imageHelper = $this->imageHelper
            ->init($product, self::IMAGE_TYPE)
            ->setImageFile($productImage)
            ->resize(300);
        return $imageHelper->getUrl();
    }

    /**
     * Validates if category reorganizer is available.
     * @param int $categoryId
     * @return string|null
     * @throws Exception
     */
    public function isAvailable(int $categoryId): ?string
    {
        if (!$this->serviceValidator->containProducts($categoryId)) {
            return (string)__("The category does not contain any products.");
        }

        if (!$this->serviceValidator->hasSequenceOrder($categoryId)) {
            return (string)__("The category products do not have a sequential position order.");
        }

        return null;
    }

}
