<?php

namespace Devlat\CategoryProductPos\Console\Command;

use Devlat\CategoryProductPos\Model\Service\DataService;
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
    private const POS = 'pos';
    private const MODE = 'mode';
    /**
     * @var DataService
     */
    private DataService $dataService;

    /**
     * @param DataService $dataService
     * @param string|null $name
     */
    public function __construct(
        DataService $dataService,
        string $name = null
    )
    {
        parent::__construct($name);
        $this->dataService = $dataService;
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
                self::POS,
                'p',
                InputOption::VALUE_REQUIRED,
                __('Set the position of the product.')
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
        // TODO: Create a method helper for mapping the input values
        $inputs = [
            'options' => array(
                'category'  =>  $input->getOption(self::CATEGORY),
                'skus'      =>  $input->getOption(self::SKUS),
                'positions' =>  $input->getOption(self::POS),
            ),
            'arguments' => array(
                'mode'      =>  strtoupper($input->getArgument(self::MODE)) === 'DESC' ?? false
            ),
        ];
        [$valid, $inputs]   =   $this->dataService->checkInputs($inputs);
        if (!$valid) {
            throw new ValidationException(
                __("Category, Skus and Pos are required and Pos must be a numeric value, please check again.")
            );
        }

        $category       =   $inputs['options']['category'];
        $categoryId = $this->dataService->getCategoryId($category);
        if (is_null($categoryId)) {
            throw new ValidationException(
                __("There is no category found according to the category: {$category}")
            );
        }
        $skus           =   $inputs['options']['skus'];
        $newPositions   =   $inputs['options']['positions'];

        [$notValid, $skuList] = $this->dataService->validProductInCategory($categoryId, $skus);
        foreach($notValid as $sku) {
            $output->writeln("<comment>Sku: {$sku} was not found in {$category}</comment>");
        }
        $this->dataService->moveProductPosition($categoryId, $skuList, $newPositions);

        $output->writeln("<info>Setting products position in {$category} is done.</info>");
    }

}
