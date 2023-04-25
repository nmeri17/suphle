<?php

namespace Suphle\Bridge\Laravel\Cli;

use Suphle\Contracts\Bridge\{LaravelContainer, LaravelArtisan};

use Suphle\Console\BaseCliCommand;

use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\{InputInterface, InputArgument};

/**
 * All we want is for our ormBridge to run, hydrate and link our connection to the instance artisan works with
*/
class ArtisanCli extends BaseCliCommand
{
    final public const TO_FORWARD_ARGUMENT = "to_forward";

    protected static $defaultDescription = "Interface with artisan commands";

    protected function configure(): void
    {

        parent::configure();

        $this->addArgument(
            self::TO_FORWARD_ARGUMENT,
            InputArgument::REQUIRED,
            "Commands to forward to artisan"
        );
    }

    public static function commandSignature(): string
    {

        return "bridge:laravel";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $moduleInterface = $input->getOption(self::HYDRATOR_MODULE_OPTION);

        return $this->getExecutionContainer($moduleInterface)

        ->getClass(LaravelArtisan::class)

        ->invokeCommand(
            $input->getArgument(self::TO_FORWARD_ARGUMENT),
            $output
        ); // Command::SUCCESS/FAILURE/INVALID
    }
}
