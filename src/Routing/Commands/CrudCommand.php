<?php

namespace Suphle\Routing\Commands;

use Suphle\Console\BaseCliCommand;

use Suphle\Routing\Crud\ResourceBootstrapper;

use Symfony\Component\Console\{Output\OutputInterface, Command\Command};

use Symfony\Component\Console\Input\{InputInterface, InputArgument, InputOption};

use Throwable;

class CrudCommand extends BaseCliCommand
{
    public const RESOURCE_NAME_ARGUMENT = "resource_name",

    IS_API_OPTION = "is_api";

    protected function configure(): void
    {

        parent::configure();

        $this->addArgument(
            self::RESOURCE_NAME_ARGUMENT,
            InputArgument::REQUIRED,
            "Name of resource e.g. Post"
        );

        $this->addOption(
            self::IS_API_OPTION,
            "i",
            InputOption::VALUE_NONE,
            "Dictates Coordinator type whether view files will be outputted"
        );
    }

    public static function commandSignature(): string
    {

        return "route:crud";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $resourceName = $input->getArgument(self::RESOURCE_NAME_ARGUMENT);

        $bootstrapperService = $this->getExecutionContainer(
            $input->getOption(self::HYDRATOR_MODULE_OPTION)
        )->getClass(ResourceBootstrapper::class);

        try {

            if (!$bootstrapperService->outputResourceTemplates(
                $resourceName,
                $input->getOption(self::IS_API_OPTION)
            )
            ) {
                return Command::FAILURE;
            }

            $output->writeln("Elements for Resource '$resourceName' outputted successfully");

            return Command::SUCCESS;
        } catch (Throwable $exception) {

            $exceptionOutput = "Unable to output elements for resource '$resourceName':\n". $exception;

            echo($exceptionOutput); // leaving this in since writeln doesn't work in tests

            $output->writeln($exceptionOutput);

            return Command::INVALID;
        }
    }
}
