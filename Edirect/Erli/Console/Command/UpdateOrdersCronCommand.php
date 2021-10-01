<?php
/**
 * Created in PhpStorm.
 * by Edirect24
 * on 18.01.2021, 14:19, 2021
 * @author   biuro@edirect24.pl
 * @copyright Copyright (C) 2021 Edirect24
 **/


namespace Edirect\Erli\Console\Command;

use Edirect\Erli\Cron\UpdateOrders as CronUpdateOrders;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\Area as Area;

/**
 * Console Class UpdateOrdersCronCommand
 */
class UpdateOrdersCronCommand extends Command
{

    /**
     * @var CronUpdateOrders
     */
    private $cronUpdateOrders;

    /**
     * @var State
     */
    protected $state;

    /**
     * CronUpdateProducts constructor.
     * @param CronUpdateOrders $cronUpdateOrders
     * @param State $state
     */
    public function __construct(
        CronUpdateOrders $cronUpdateOrders,
        State $state
    ) {
        parent::__construct();
        $this->cronUpdateOrders = $cronUpdateOrders;
        $this->state = $state;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("edirect:erli:update_orders")->setDescription('Run Update Orders Cron Command');
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

        $this->cronUpdateOrders->execute();

        return Cli::RETURN_SUCCESS;
    }
}
