<?php

namespace Suphle\Console\Services;

use Suphle\Services\Decorators\BindsAsSingleton;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

#[BindsAsSingleton]
class RouteFormatterService
{
    public function formatRouteTable(OutputInterface $output, array $routes, string $title = "Route List"): void
    {
        $output->writeln("\n<info>{$title}</info>");
        $output->writeln(str_repeat("=", strlen($title)) . "\n");
        
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
                $this->formatMiddleware($route['middleware'] ?? []),
                $this->formatCanaryState($route['canary_state'] ?? null),
                $this->formatPlaceholders($route['placeholders'] ?? [])
            ]);
        }
        
        $table->render();
        $output->writeln(sprintf("\n<info>Total: %d routes</info>", count($routes)));
    }

    public function formatDetailedRouteTable(OutputInterface $output, array $routes): void
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
                $this->formatFlows($route['flows'] ?? []),
                $this->formatCanary($route['canary_state'] ?? null),
                $this->formatValidators($route['validation_rules'] ?? []),
                $this->formatBuilders($route['parameters'] ?? []),
                $this->formatResponse($route['response_shape'] ?? []),
                $route['summary'] ?? ''
            ]);
        }

        $table->render();
        $output->writeln(sprintf("\n<info>Total: %d routes</info>", count($routes)));
    }

    public function formatJson(OutputInterface $output, array $routes): void
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


