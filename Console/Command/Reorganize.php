<?php

namespace Devlat\CategoryProductPos\Console\Command;

use Composer\Console\Input\InputOption;
use Devlat\CategoryProductPos\Model\Service\Data;
use Devlat\CategoryProductPos\Model\Service\Validator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Reorganize extends Command
{
    /** @var string  */
    private CONST CATEGORY = 'category';

    /** @var string  */
    private CONST TYPE = 'type';

    /**
     * @var Validator
     */
    private Validator $validator;
    /**
     * @var Data
     */
    private Data $dataService;

    /**
     * Constructor.
     * @param Data $dataService
     * @param Validator $validator
     * @param string|null $name
     */
    public function __construct(
        Data $dataService,
        Validator $validator,
        ?string $name = null
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
    protected function configure(): void
    {
        $this->setName('devlat:category:reorganize');
        $this->setDescription('This command will reorganize all products based on their id, name or sku.');
        $this->setDefinition(
            [
                new InputOption(
                    self::CATEGORY,
                    'c',
                    InputOption::VALUE_REQUIRED,
                    'Category name'
                ),
                new InputOption(
                    self::TYPE,
                    't',
                    InputOption::VALUE_REQUIRED,
                    'Reorganization type (id, name or sku)'
                ),
        ]);
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputs = [
            'category' => $input->getOption(self::CATEGORY),
            'type' => $input->getOption(self::TYPE),
        ];

        $this->validator->validateReorganizeInputs($inputs);

        $category   =   $inputs['category'];
        $type       =   $inputs['type'];
        $categoryId =   $this->validator->getCategoryIdByName($category);

        $this->dataService->sortCategoryProducts($categoryId, $type);

        $output->writeln(__('<comment>Products in category "%1" have been reorganized by "%2".</comment>', $category, $type));

        return Command::SUCCESS;
    }
}
