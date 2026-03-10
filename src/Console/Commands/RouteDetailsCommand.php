<?php

namespace Suphle\Console\Commands;

use Suphle\Console\BaseCliCommand;
use Suphle\Routing\RouteDetailsService;
use Suphle\Console\Services\RouteFormatterService;
use Symfony\Component\Console\{Output\OutputInterface, Command\Command};
use Symfony\Component\Console\Input\{InputInterface, InputOption};

class RouteDetailsCommand extends BaseCliCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this->addOption(
            'module',
            'm',
            InputOption::VALUE_OPTIONAL,
            'Filter routes by module name'
        )
        ->addOption(
            'method',
            null,
            InputOption::VALUE_OPTIONAL,
            'Filter routes by HTTP method'
        )
        ->addOption(
            'path',
            'p',
            InputOption::VALUE_OPTIONAL,
            'Filter routes by path pattern'
        )
        ->addOption(
            'json',
            'j',
            InputOption::VALUE_NONE,
            'Output in JSON format'
        );
    }

    public static function commandSignature(): string
    {
        return "route:details";
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $moduleInterface = $input->getOption(self::HYDRATOR_MODULE_OPTION);
        $container = $this->getExecutionContainer($moduleInterface);
        $detailsService = $container->getClass(RouteDetailsService::class);
        $formatterService = $container->getClass(RouteFormatterService::class);

        $routes = $detailsService->getAllDetailedRoutes();

        // Apply filters
        $filters = array_filter([
            'module' => $input->getOption('module'),
            'method' => $input->getOption('method'),
            'path' => $input->getOption('path')
        ]);
        if (!empty($filters)) {
            $routes = array_filter($routes, function ($route) use ($filters) {
                if (isset($filters['module']) && !str_contains($route['coordinator'], $filters['module'])) {
                    return false;
                }
                if (isset($filters['method']) && strtoupper($route['method']) !== strtoupper($filters['method'])) {
                    return false;
                }
                if (isset($filters['path']) && !str_contains($route['path'], $filters['path'])) {
                    return false;
                }
                return true;
            });
        }

        if (empty($routes)) {
            $output->writeln("<info>No routes found matching the specified criteria.</info>");
            return Command::SUCCESS;
        }

        if ($input->getOption('json')) {
            $formatterService->formatJson($output, $routes);
        } else {
            $formatterService->formatDetailedRouteTable($output, $routes);
        }

        return Command::SUCCESS;
    }

} 