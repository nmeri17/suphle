<?php

namespace Suphle\ComponentTemplates\Commands;

use Suphle\Console\BaseCliCommand;

use Suphle\ComponentTemplates\ComponentEjector;

use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\{InputInterface, InputArgument, InputOption};

use Symfony\Component\Console\Command\Command;

class InstallComponentCommand extends BaseCliCommand
{
    public const OVERWRITE_OPTION = "overwrite",

    COMPONENT_ARGS_OPTION = "misc_args";

    protected static $defaultDescription = "Extract templates registered for given module";

    protected bool $withModuleOption = false;

    public static function commandSignature(): string
    {

        return "templates:install";
    }

    protected function configure(): void
    {

        parent::configure();

        $this->addArgument(
            self::HYDRATOR_MODULE_OPTION,
            InputArgument::REQUIRED,
            "Relative name of the Module interface where templates are to be ejected"
        );

        $this->addOption(
            self::OVERWRITE_OPTION,
            "o",
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            "List of entries to override or empty to overwrite all",
            [] // default value. Means option wasn't passed. When value = [null], option is present but has no value aka "all"
        );

        $this->addOption(
            self::COMPONENT_ARGS_OPTION,
            "p",
            InputOption::VALUE_REQUIRED,
            "Arguments to pass to the components: foo=value uju=bar"
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $moduleInterface = $input->getArgument(self::HYDRATOR_MODULE_OPTION);

        $result = $this->getExecutionContainer($moduleInterface)

        ->getClass(ComponentEjector::class)

        ->depositFiles(
            $this->getOverwriteOption($input),
            $this->getMiscArguments($input)
        );

        if ($result) {

            $output->writeln("Templates ejected successfully");

            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }

    /**
     * @see option definition for legend
    */
    protected function getOverwriteOption(InputInterface $input): ?array
    {

        $givenValue = $input->getOption(self::OVERWRITE_OPTION);

        if (is_array($givenValue)) {

            if (empty($givenValue)) {
                return null;
            }

            return array_filter($givenValue); // empty string or no value will populate this with nulls
        }

        return $givenValue; // will never get here since option is declared as an array
    }

    protected function getMiscArguments(InputInterface $input): array
    {

        $argumentList = $input->getOption(self::COMPONENT_ARGS_OPTION);

        if (is_null($argumentList)) {
            return [];
        }

        $valueRows = explode(" ", $argumentList);

        $keyValue = [];

        foreach ($valueRows as $row) {

            $keyValuePair = explode("=", $row);

            $keyValue[$keyValuePair[0]] = $keyValuePair[1];
        }

        return $keyValue;
    }
}
