<?php

namespace Devlat\CategoryProductPos\Console\Command;

use Devlat\CategoryProductPos\Model\Service\DataService;
use Devlat\CategoryProductPos\Model\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validation\ValidationException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProductPosition extends Command
{
    private const SKUS = 'skus';
    private const CATEGORY = 'category';
    private const JUMP = 'jump';
    private const MODE = 'mode';
    /**
     * @var DataService
     */
    private DataService $dataService;
    private Validator $validator;

    /**
     * @param DataService $dataService
     * @param string|null $name
     */
    public function __construct(
        DataService $dataService,
        Validator $validator,
        string $name = null
    )
    {
        parent::__construct($name);
        $this->dataService = $dataService;
        $this->validator = $validator;
    }

    /**
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
                self::SKUS,
                null,
                InputOption::VALUE_REQUIRED,
                __('Product(s) in order to change the position.')
            ),
            new InputOption(
                self::JUMP,
                'j',
                InputOption::VALUE_REQUIRED,
                __('Set the position of the product by jumping.')
            ),
            new InputArgument(
                self::MODE,
                InputArgument::OPTIONAL,
                __('Define if it is going to change the position based on ASC or DESC')
            )
        ]);

        parent::configure();
    }

    /**
     * @throws ValidationException
     * @throws LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Input data validation.
        $inputs = array(
            'category'  =>  $input->getOption(self::CATEGORY),
            'skus'      =>  $input->getOption(self::SKUS),
            'jump'      =>  $input->getOption(self::JUMP),
            'mode'      =>  strtoupper($input->getArgument(self::MODE)) === 'DESC' ? 'DESC' : 'ASC'
        );
        [$valid, $inputs] = $this->validator->checkInputs($inputs);
        if (!$valid) {
            throw new ValidationException(
                __("Category, Skus and Pos are required and Pos must be a numeric value, please check again.")
            );
        }

        // Validation of category.
        $category       =   $inputs['category'];
        $categoryId = $this->dataService->getCategoryId($category);
        if (is_null($categoryId)) {
            throw new ValidationException(
                __("There is no category found according to the category: {$category}")
            );
        }

        $skus           =   $inputs['skus'];
        $newPositions   =   $inputs['jump'];
        [$productsNotMoved, $skuList] = $this->dataService->validProductInCategory($categoryId, $skus);
        foreach($productsNotMoved as $product => $data) {
            $output->writeln("<comment>Sku: {$data['sku']} with ID: {$data['id']} was not found in {$category}</comment>");
        }
        $productsMoved = $this->dataService->moveProductPosition($categoryId, $skuList, $newPositions);
        foreach($productsMoved as $product => $data) {
            $output->writeln("<comment>The product with SKU: {$data['sku']} with ID: {$data['id']} is in position {$data['pos']}</comment>");
        }

        $output->writeln("<info>Setting products position(s) in {$category} is done.</info>");
    }

}
