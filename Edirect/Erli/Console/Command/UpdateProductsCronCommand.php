<?php
/**
 * Created in PhpStorm.
 * by Edirect24
 * on 18.01.2021, 14:19, 2021
 * @author   biuro@edirect24.pl
 * @copyright Copyright (C) 2021 Edirect24
 **/


namespace Edirect\Erli\Console\Command;

use Edirect\Erli\Cron\UpdateProducts as CronUpdateProducts;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Console Class CreateProductsCronCommand
 */
class UpdateProductsCronCommand extends Command
{

    /**
     * @var CronUpdateProducts
     */
    private $cronUpdateProducts;

    /**
     * CronUpdateProducts constructor.
     * @param CronUpdateProducts $cronUpdateProducts
     */
    public function __construct(
        CronUpdateProducts $cronUpdateProducts
    ) {
        parent::__construct();
        $this->cronUpdateProducts = $cronUpdateProducts;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("edirect:erli:update_products")->setDescription('Run Update Product Cron Command');
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->cronUpdateProducts->execute();

        return Cli::RETURN_SUCCESS;
    }
}
