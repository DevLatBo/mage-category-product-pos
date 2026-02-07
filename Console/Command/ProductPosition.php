<?php

namespace Devlat\CategoryProductPos\Console\Command;

use Devlat\CategoryProductPos\Model\Service\Data;
use Devlat\CategoryProductPos\Model\Service\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validation\ValidationException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProductPosition extends Command
{
    /** @var string  */
    private const SKU = 'sku';
    /** @var string  */
    private const CATEGORY = 'category';
    /** @var string  */
    private const JUMP = 'jump';
    /**
     * @var Data
     */
    private Data $dataService;
    private Validator $validator;

    /**
     * @param Data $dataService
     * @param string|null $name
     */
    public function __construct(
        Data      $dataService,
        Validator $validator,
        string    $name = null
    )
    {
        parent::__construct($name);
        $this->dataService = $dataService;
        $this->validator = $validator;
    }

    /**
     * Configure the command with its name, description and options.
     * @return void
     */
    protected function configure()
    {
        $this->setName('devlat:category:position');
        $this->setDescription('The command will help you to organize your product(s) in a specific category.');
        $this->setDefinition([
            new InputOption(
                self::CATEGORY,
                'c',
                InputOption::VALUE_REQUIRED,
                __('Category')
            ),
            new InputOption(
                self::SKU,
                null,
                InputOption::VALUE_REQUIRED,
                __('Product(s) in order to change the position.')
            ),
            new InputOption(
                self::JUMP,
                null,
                InputOption::VALUE_REQUIRED,
                __('Set the position of the product by jumping.')
            ),
        ]);

        parent::configure();
    }

    /**
     * Execute the command to change the product position in the category.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws ValidationException
     * @throws LocalizedException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Input data validation.
        $inputs = array(
            'category'  =>  $input->getOption(self::CATEGORY),
            'sku'      =>  $input->getOption(self::SKU),
            'jump'      =>  $input->getOption(self::JUMP),
        );

        $inputs = $this->validator->validatePositionInputs($inputs);

        // Validation of category.
        $category           =   $inputs['category'];
        $categoryId         =   $this->validator->getCategoryIdByName($category);

        $sku                =   $inputs['sku'];
        $jump               =   intval($inputs['jump']);
        $canChangePosition  =   $this->validator->checkProductInCategory($categoryId, $sku);

        if ($canChangePosition) {
            $productMoved = $this->dataService->setProductPositions($categoryId, $sku, $jump);
            $output->writeln(
                "<comment>The product with SKU: {$productMoved['sku']}, ID: {$productMoved['id']} is now in position {$productMoved['pos']}</comment>");
            return Command::SUCCESS;
        }

        $output->writeln("<comment>The product position with SKU: {$sku} in category: {$category} was not updated.</comment>");
        return Command::INVALID;

    }
}
