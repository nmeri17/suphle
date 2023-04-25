<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Commands;

use Suphle\Console\BaseCliCommand;

use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\BCounter;

use Symfony\Component\Console\{Output\OutputInterface, Command\Command};

use Symfony\Component\Console\Input\{InputInterface, InputArgument};

class AltersConcreteCommand extends BaseCliCommand
{
    final public const NEW_VALUE_ARGUMENT = "new_value";

    protected function configure(): void
    {

        parent::configure();

        $this->addArgument(
            self::NEW_VALUE_ARGUMENT,
            InputArgument::REQUIRED,
            "Value to update concrete to"
        );
    }

    public static function commandSignature(): string
    {

        return "test:alters_concrete";
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {

        $moduleInterface = $input->getOption(self::HYDRATOR_MODULE_OPTION);

        $this->getExecutionContainer($moduleInterface)->getClass(BCounter::class)

        ->setCount($input->getArgument(self::NEW_VALUE_ARGUMENT));

        $output->writeln("Operation completed successfully");

        return Command::SUCCESS; // Command::SUCCESS/FAILURE/INVALID
    }
}
