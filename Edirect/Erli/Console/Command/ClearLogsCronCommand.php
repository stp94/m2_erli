<?php
/**
 * Created in PhpStorm.
 * by Edirect24
 * on 09.02.2021, 12:26, 2021
 * @author   biuro@edirect24.pl
 * @copyright Copyright (C) 2021 Edirect24
 **/

namespace Edirect\Erli\Console\Command;

use Edirect\Erli\Cron\ClearLogs as CronClearLogs;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Console Class ClearLogsCronCommand
 */
class ClearLogsCronCommand extends Command
{

    /**
     * @var CronClearLogs
     */
    private $cronClearLogs;

    /**
     * CronClearLogs constructor.
     * @param CronClearLogs $cronClearLogs
     */
    public function __construct(
        CronClearLogs $cronClearLogs
    ) {
        parent::__construct();
        $this->cronClearLogs = $cronClearLogs;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("edirect:erli:clear_logs")->setDescription('Run Clear Logs Cron Command');
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->cronClearLogs->execute();

        return Cli::RETURN_SUCCESS;
    }
}
