<?php
/**
 * Created in PhpStorm.
 * by Edirect24
 * on 18.01.2021, 13:52, 2021
 * @author   biuro@edirect24.pl
 * @copyright Copyright (C) 2021 Edirect24
 **/

namespace Edirect\Erli\Console\Command;

use Edirect\Erli\Cron\CreateProducts as CronCreateProducts;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Console Class CreateProductsCronCommand
 */
class CreateProductsCronCommand extends Command
{

    /**
     * @var CronCreateProducts
     */
    private $cronCreateProducts;

    /**
     * CronCreateProducts constructor.
     * @param CronCreateProducts $cronCreateProducts
     */
    public function __construct(
        CronCreateProducts $cronCreateProducts
    ) {
        parent::__construct();
        $this->cronCreateProducts = $cronCreateProducts;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("edirect:erli:create_products")->setDescription('Run Create Product Cron Command');
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->cronCreateProducts->execute();

        return Cli::RETURN_SUCCESS;
    }
}
