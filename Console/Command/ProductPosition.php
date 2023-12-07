<?php

namespace Devlat\CategoryProductPos\Console\Command;

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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $requiredInputs = [
            'category'  =>  $input->getOption(self::CATEGORY),
            'skus'      =>  $input->getOption(self::SKUS),
            'pos'       =>  $input->getOption(self::POS)
        ];
        $validInputs = $this->checkInputs($requiredInputs);
        if (!$validInputs) {
            throw new ValidationException(
                __("The data: category, skus and pos are required values, please check your inputs.")
            );
        }

        $category = $requiredInputs['category'];
        print_r($requiredInputs);
        echo $input->getArgument('mode');


        $output->writeln("<info>Setting products position in {$category} is done.</info>");
    }

    /**
     * @param array $inputs
     * @return bool
     */
    private function checkInputs(array $inputs): bool
    {
        $flag = false;
        $emptyCounter = array_sum(array_map(function($element) { return empty($element);}, $inputs));
        if ($emptyCounter === 0) {
            $flag = true;
        }
        return $flag;
    }
}
