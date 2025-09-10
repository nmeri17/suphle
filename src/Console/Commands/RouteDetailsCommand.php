<?php

namespace Suphle\Console\Commands;

use Suphle\Console\BaseCliCommand;
use Suphle\Routing\RouteDetailsService;
use Symfony\Component\Console\{Output\OutputInterface, Command\Command};
use Symfony\Component\Console\Input\{InputInterface, InputOption};
use Symfony\Component\Console\Helper\Table;

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
            $this->outputJson($output, $routes);
        } else {
            $this->outputTable($output, $routes);
        }

        return Command::SUCCESS;
    }

    protected function outputTable(OutputInterface $output, array $routes): void
    {
        $output->writeln("\n<info>Route Details</info>");
        $output->writeln("==============\n");

        $table = new Table($output);
        $table->setHeaders([
            'Method',
            'Path',
            'Handler',
            'Renderer',
            'Flows',
            'Canary',
            'Validators',
            'Builders',
            'Response',
            'Summary'
        ]);

        foreach ($routes as $route) {
            $table->addRow([
                $route['method'],
                $route['path'],
                $route['coordinator'] . '::' . $route['handler'],
                $route['renderer'] ?? '-',
                $this->formatFlows($route['flows']),
                $this->formatCanary($route['canary_state']),
                $this->formatValidators($route['validation_rules']),
                $this->formatBuilders($route['parameters']),
                $this->formatResponse($route['response_shape']),
                $route['summary'] ?? ''
            ]);
        }

        $table->render();
        $output->writeln(sprintf("\n<info>Total: %d routes</info>", count($routes)));
    }

    protected function outputJson(OutputInterface $output, array $routes): void
    {
        $output->writeln(json_encode(array_values($routes), JSON_PRETTY_PRINT));
    }

    protected function formatFlows($flows): string
    {
        if (empty($flows)) return '-';
        return implode(", ", array_map(fn($f) => $f['type'], $flows));
    }

    protected function formatCanary($canary): string
    {
        if (empty($canary)) return '-';
        if (is_array($canary)) return implode(", ", array_map('basename', $canary));
        return (string)$canary;
    }

    protected function formatValidators($rules): string
    {
        if (empty($rules)) return '-';
        return implode(", ", array_map(fn($k, $v) => "$k: $v", array_keys($rules), $rules));
    }

    protected function formatBuilders($params): string
    {
        $builders = array_filter($params, fn($p) => !empty($p['is_payload_reader']));
        if (empty($builders)) return '-';
        return implode(", ", array_map(fn($b) => $b['payload_class'], $builders));
    }

    protected function formatResponse($response): string
    {
        if (empty($response)) return '-';
        if (isset($response['renderer_type'])) return $response['renderer_type'];
        return $response['type'] ?? '-';
    }
} 