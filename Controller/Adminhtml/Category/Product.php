<?php

namespace Devlat\CategoryProductPos\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Product extends Action
{
    private JsonFactory $resultJsonFactory;

    public function __construct(
        JsonFactory $resultJsonFactory,
        Context $context)
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/oscar.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('TEST MESSAGE');

        return $this->resultJsonFactory->create()->setData([
            'success' => true,
        ]);
    }
}
