<?php
/**
 * Created in PhpStorm.
 * by Edirect24
 * on 18.01.2021, 13:52, 2021
 * @author   biuro@edirect24.pl
 * @copyright Copyright (C) 2021 Edirect24
 **/

namespace Edirect\Erli\Console\Command;

use Edirect\Erli\Cron\CreateOrders as CronCreateOrders;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\Area as Area;

/**
 * Console Class CreateOrdersCronCommand
 */
class CreateOrdersCronCommand extends Command
{

    /**
     * @var CronCreateOrders
     */
    private $cronCreateOrders;

    /**
     * @var State
     */
    protected $state;

    /**
     * CronCreateOrders constructor.
     * @param CronCreateOrders $cronCreateOrders
     * @param State $state
     */
    public function __construct(
        CronCreateOrders $cronCreateOrders,
        State $state
    ) {
        parent::__construct();
        $this->cronCreateOrders = $cronCreateOrders;
        $this->state = $state;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("edirect:erli:create_orders")->setDescription('Run Create Orders Cron Command');
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //if (!$this->state->getAreaCode()) {
            $this->state->setAreaCode(Area::AREA_GLOBAL);
        //}

        $this->cronCreateOrders->execute();

        return Cli::RETURN_SUCCESS;
    }
}
