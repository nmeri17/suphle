<?php

namespace Suphle\Routing\Commands;

use Suphle\Console\BaseCliCommand;
use Suphle\Routing\Analysis\RouteListingService;
use Symfony\Component\Console\{Output\OutputInterface, Command\Command, Helper\Table};
use Symfony\Component\Console\Input\{InputInterface, InputOption};

class RouteListCommand extends BaseCliCommand
{
    public static function commandSignature(): string
    {
        return "route:list";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $moduleFilter = $input->getOption(self::HYDRATOR_MODULE_OPTION);

        $listingService = $this->getExecutionContainer($moduleFilter)
            ->getClass(RouteListingService::class);

        $output->writeln("\n<info>Suphle Route Analysis</info>");
        $output->writeln("======================\n");

        $rows = $listingService->getFormattedRows($moduleFilter);

        (new Table($output))
            ->setHeaders(["Method", "URI", "Handler", "Response (View)", "Flows", "Validators", "Mirror?", "Module"])
            ->setRows($rows)
            ->render();

        return Command::SUCCESS;
    }
}