<?php

namespace Suphle\Console\Commands;

use Suphle\Console\BaseCliCommand;
use Suphle\Routing\RouteDetectorService;
use Symfony\Component\Console\{Output\OutputInterface, Command\Command};
use Symfony\Component\Console\Input\{InputInterface, InputOption};
use Symfony\Component\Console\Helper\Table;

class RouteListCommand extends BaseCliCommand
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
        return "route:list";
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $moduleInterface = $input->getOption(self::HYDRATOR_MODULE_OPTION);
        $container = $this->getExecutionContainer($moduleInterface);
        
        $detectorService = $container->getClass(RouteDetectorService::class);
        
        $routes = $detectorService->getAllRoutes();
        
        // Apply filters
        $filters = array_filter([
            'module' => $input->getOption('module'),
            'method' => $input->getOption('method'),
            'path' => $input->getOption('path')
        ]);
        
        if (!empty($filters)) {
            $routes = $detectorService->filterRoutes($routes, $filters);
        }
        
        if (empty($routes)) {
            $output->writeln("<info>No routes found matching the specified criteria.</info>");
            return Command::SUCCESS;
        }
        
        if ($input->getOption('json')) {
            $this->outputJson($output, $routes);
        } else {
            $this->outputTable($output, $routes);
        }
        
        return Command::SUCCESS;
    }

    protected function outputTable(OutputInterface $output, array $routes): void
    {
        $output->writeln("\n<info>Route List</info>");
        $output->writeln("==========\n");
        
        $table = new Table($output);
        $table->setHeaders([
            'Method',
            'Path',
            'Handler',
            'Renderer',
            'Middleware',
            'Canary State',
            'Placeholders'
        ]);
        
        foreach ($routes as $route) {
            $table->addRow([
                $this->formatMethod($route['method']),
                $route['path'],
                $this->formatHandler($route['handler'], $route['coordinator']),
                $this->formatRenderer($route['renderer']),
                $this->formatMiddleware($route['middleware']),
                $this->formatCanaryState($route['canary_state']),
                $this->formatPlaceholders($route['placeholders'])
            ]);
        }
        
        $table->render();
        
        $output->writeln(sprintf("\n<info>Total: %d routes</info>", count($routes)));
    }

    protected function outputJson(OutputInterface $output, array $routes): void
    {
        $output->writeln(json_encode($routes, JSON_PRETTY_PRINT));
    }

    protected function formatMethod(string $method): string
    {
        $colors = [
            'GET' => 'green',
            'POST' => 'yellow',
            'PUT' => 'blue',
            'DELETE' => 'red',
            'PATCH' => 'cyan'
        ];
        
        $color = $colors[strtoupper($method)] ?? 'white';
        return "<fg={$color}>{$method}</>";
    }

    protected function formatHandler(string $handler, string $coordinator): string
    {
        $shortCoordinator = basename(str_replace('\\', '/', $coordinator));
        return "{$shortCoordinator}::{$handler}";
    }

    protected function formatRenderer(?string $renderer): string
    {
        if (!$renderer) {
            return '<fg=gray>mixed</>';
        }
        
        $shortRenderer = basename(str_replace('\\', '/', $renderer));
        return "<fg=cyan>{$shortRenderer}</>";
    }

    protected function formatMiddleware(array $middleware): string
    {
        if (empty($middleware)) {
            return '<fg=gray>none</>';
        }
        
        $shortMiddleware = array_map(function ($mw) {
            return basename(str_replace('\\', '/', $mw));
        }, $middleware);
        
        return implode(', ', $shortMiddleware);
    }

    protected function formatCanaryState(?array $canaryState): string
    {
        if (!$canaryState) {
            return '<fg=gray>none</>';
        }
        
        $shortCanaries = array_map(function ($canary) {
            return basename(str_replace('\\', '/', $canary));
        }, $canaryState);
        
        return "<fg=magenta>" . implode(', ', $shortCanaries) . "</>";
    }

    protected function formatPlaceholders(array $placeholders): string
    {
        if (empty($placeholders)) {
            return '<fg=gray>none</>';
        }
        
        return "<fg=yellow>" . implode(', ', $placeholders) . "</>";
    }
} 