<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suphle API Documentation</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
            border-radius: 8px;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }
        
        .stat-label {
            color: #666;
            margin-top: 5px;
        }
        
        .filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .filter-group {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-group label {
            font-weight: 500;
            min-width: 80px;
        }
        
        .filter-group select,
        .filter-group input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .routes-table {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .table-header h2 {
            margin: 0;
            color: #495057;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        
        .method {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .method.get { background: #d4edda; color: #155724; }
        .method.post { background: #d1ecf1; color: #0c5460; }
        .method.put { background: #fff3cd; color: #856404; }
        .method.delete { background: #f8d7da; color: #721c24; }
        .method.patch { background: #e2e3e5; color: #383d41; }
        
        .path {
            font-family: 'Monaco', 'Menlo', monospace;
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 13px;
        }
        
        .tag {
            display: inline-block;
            background: #e9ecef;
            color: #495057;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            margin: 2px;
        }
        
        .validation-rules {
            font-size: 12px;
            color: #666;
        }
        
        .validation-rule {
            display: inline-block;
            background: #f8f9fa;
            padding: 1px 4px;
            border-radius: 2px;
            margin: 1px;
        }
        
        .no-routes {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .json-link {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
            transition: background 0.3s;
        }
        
        .json-link:hover {
            background: #5a6fd8;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .filter-group {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-group label {
                min-width: auto;
            }
            
            table {
                font-size: 14px;
            }
            
            th, td {
                padding: 8px 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Suphle API Documentation</h1>
            <p>Auto-generated API documentation from your Suphle routes</p>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number">{{ count($routes) }}</div>
                <div class="stat-label">Total Routes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ count(array_unique(array_column($routes, 'method'))) }}</div>
                <div class="stat-label">HTTP Methods</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ count(array_unique(array_column($routes, 'coordinator'))) }}</div>
                <div class="stat-label">Coordinators</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ count(array_filter($routes, fn($r) => !empty($r['validation_rules']))) }}</div>
                <div class="stat-label">Validated Routes</div>
            </div>
        </div>
        
        <div class="filters">
            <div class="filter-group">
                <label for="method-filter">Method:</label>
                <select id="method-filter">
                    <option value="">All Methods</option>
                    @foreach(array_unique(array_column($routes, 'method')) as $method)
                        <option value="{{ $method }}">{{ $method }}</option>
                    @endforeach
                </select>
                
                <label for="module-filter">Module:</label>
                <select id="module-filter">
                    <option value="">All Modules</option>
                    @foreach(array_unique(array_map(fn($r) => explode('\\', $r['coordinator'])[1] ?? 'default', $routes)) as $module)
                        <option value="{{ $module }}">{{ $module }}</option>
                    @endforeach
                </select>
                
                <label for="path-filter">Path:</label>
                <input type="text" id="path-filter" placeholder="Filter by path...">
            </div>
        </div>
        
        <div class="routes-table">
            <div class="table-header">
                <h2>Route Details</h2>
            </div>
            
            @if(empty($routes))
                <div class="no-routes">
                    <p>No routes found. Make sure your coordinators are properly configured.</p>
                </div>
            @else
                <table id="routes-table">
                    <thead>
                        <tr>
                            <th>Method</th>
                            <th>Path</th>
                            <th>Handler</th>
                            <th>Module</th>
                            <th>Validation</th>
                            <th>Response</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($routes as $route)
                            <tr class="route-row" 
                                data-method="{{ $route['method'] }}"
                                data-module="{{ explode('\\', $route['coordinator'])[1] ?? 'default' }}"
                                data-path="{{ $route['path'] }}">
                                <td>
                                    <span class="method {{ strtolower($route['method']) }}">
                                        {{ $route['method'] }}
                                    </span>
                                </td>
                                <td>
                                    <span class="path">{{ $route['path'] }}</span>
                                </td>
                                <td>{{ $route['handler'] }}</td>
                                <td>
                                    @foreach(explode('\\', $route['coordinator']) as $part)
                                        @if($loop->index > 0)
                                            <span class="tag">{{ $part }}</span>
                                        @endif
                                    @endforeach
                                </td>
                                <td>
                                    @if(!empty($route['validation_rules']))
                                        <div class="validation-rules">
                                            @foreach($route['validation_rules'] as $field => $rules)
                                                <div class="validation-rule">
                                                    <strong>{{ $field }}:</strong> {{ $rules }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span style="color: #999;">None</span>
                                    @endif
                                </td>
                                <td>
                                    @if(isset($route['response_shape']['renderer_type']))
                                        <span class="tag">{{ $route['response_shape']['renderer_type'] }}</span>
                                    @else
                                        <span style="color: #999;">Unknown</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="/api-docs/json" class="json-link" target="_blank">
                View OpenAPI JSON Specification
            </a>
        </div>
    </div>
    
    <script>
        // Simple filtering functionality
        document.addEventListener('DOMContentLoaded', function() {
            const methodFilter = document.getElementById('method-filter');
            const moduleFilter = document.getElementById('module-filter');
            const pathFilter = document.getElementById('path-filter');
            const routeRows = document.querySelectorAll('.route-row');
            
            function filterRoutes() {
                const methodValue = methodFilter.value.toLowerCase();
                const moduleValue = moduleFilter.value.toLowerCase();
                const pathValue = pathFilter.value.toLowerCase();
                
                routeRows.forEach(row => {
                    const method = row.dataset.method.toLowerCase();
                    const module = row.dataset.module.toLowerCase();
                    const path = row.dataset.path.toLowerCase();
                    
                    const methodMatch = !methodValue || method === methodValue;
                    const moduleMatch = !moduleValue || module === moduleValue;
                    const pathMatch = !pathValue || path.includes(pathValue);
                    
                    if (methodMatch && moduleMatch && pathMatch) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
            
            methodFilter.addEventListener('change', filterRoutes);
            moduleFilter.addEventListener('change', filterRoutes);
            pathFilter.addEventListener('input', filterRoutes);
        });
    </script>
</body>
</html> 