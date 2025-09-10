# API Documentation Component

This component provides automatic OpenAPI documentation generation for Suphle applications. It scans all routes and generates comprehensive API documentation with request/response schemas, validation rules, and authentication requirements.

## Features

- **Automatic Route Discovery**: Scans all registered routes and extracts metadata
- **OpenAPI 3.0 Specification**: Generates standards-compliant OpenAPI documentation
- **Response Schema Analysis**: Uses Psalm static analysis to infer response schemas
- **Validation Rule Integration**: Converts Laravel-style validation rules to OpenAPI schemas
- **Authentication Documentation**: Automatically detects and documents auth barriers
- **Renderer Interface Support**: Uses `OpenApiRenderer` interface for seamless renderer integration
- **Component Template**: Plug-and-play installation without manual configuration

## Installation

### As Component Template

```bash
# Install the component template
php suphle component:install ApiDocsComponent

# The component will be automatically registered and routes will be available at:
# - /api-docs (HTML documentation)
# - /api-docs-json (OpenAPI JSON specification)
```

### Manual Installation

1. Copy the component files to your module
2. Register the routes in your module's route collection
3. Configure the router with docs paths

## Configuration

The component can be configured in your router configuration:

```php
// In your router config
'docsPath' => '/api-docs',
'docsJsonPath' => '/api-docs-json',
```

## OpenApiRenderer Interface

Renderers can implement the `OpenApiRenderer` interface to provide automatic OpenAPI metadata:

```php
use Suphle\Contracts\Response\OpenApiRenderer;
use Suphle\Response\Traits\OpenApiRendererTrait;

class MyCustomRenderer implements OpenApiRenderer
{
    use OpenApiRendererTrait;

    public static function getContentType(): string
    {
        return 'application/json';
    }

    public static function getStatusCode(): int
    {
        return 200;
    }

    public static function getResponseSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'data' => ['type' => 'array'],
                'message' => ['type' => 'string']
            ]
        ];
    }

    public static function getDescription(): string
    {
        return 'Custom JSON response';
    }
}
```

### Auto-Discovery

The system automatically discovers renderers that implement the `OpenApiRenderer` interface:

- **Content Type**: Automatically extracted from `getContentType()`
- **Status Code**: Automatically extracted from `getStatusCode()`
- **Response Schema**: Automatically extracted from `getResponseSchema()`
- **Description**: Automatically extracted from `getDescription()`

### Fallback Support

For renderers that don't implement the interface, the system falls back to:
- Legacy mapping tables for known renderer types
- Psalm static analysis for response schemas
- Default values for content types and status codes

## Built-in Renderer Support

The following renderers are automatically supported:

- **Json**: JSON responses with object schemas
- **Markup**: HTML responses with string schemas
- **Redirect**: Redirect responses with Location headers
- **Reload**: Page reload responses
- **LocalFileDownload**: File download responses with binary schemas
- **BaseHotwireStream**: Turbo Stream responses
- **RedirectHotwireStream**: Turbo Stream redirects
- **ReloadHotwireStream**: Turbo Stream reloads

## Usage

### HTML Documentation

Visit `/api-docs` to view the interactive HTML documentation with:
- Route summaries and descriptions
- Request/response examples
- Try-it-out functionality
- Authentication information

### JSON Specification

Visit `/api-docs-json` to get the raw OpenAPI 3.0 specification for:
- Integration with external tools (Swagger UI, Postman, etc.)
- API client generation
- Documentation hosting

### Programmatic Access

```php
use Suphle\Routing\Documentation\OpenApiGeneratorService;

class MyService
{
    public function __construct(
        private OpenApiGeneratorService $openApiGenerator
    ) {}

    public function getApiSpec(): array
    {
        return $this->openApiGenerator->generateOpenApiSpec();
    }
}
```

## Integration with External Tools

### Swagger UI

```html
<!DOCTYPE html>
<html>
<head>
    <title>API Documentation</title>
    <script src="https://unpkg.com/swagger-ui-dist@5.0.0/swagger-ui-bundle.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5.0.0/swagger-ui.css">
</head>
<body>
    <div id="swagger-ui"></div>
    <script>
        window.onload = function() {
            SwaggerUIBundle({
                url: '/api-docs-json',
                dom_id: '#swagger-ui'
            });
        };
    </script>
</body>
</html>
```

### Postman

1. Import the OpenAPI specification from `/api-docs-json`
2. Postman will automatically generate a collection with all your endpoints
3. Authentication and request bodies will be pre-configured

## Testing

The component includes comprehensive tests:

```bash
# Run component tests
composer test -- --filter="ApiDocs"

# Run integration tests
composer test -- --filter="ApiDocsController"
```

## Customization

### Custom Response Schemas

Override the `getResponseSchema()` method in your renderer:

```php
public static function getResponseSchema(): array
{
    return [
        'type' => 'object',
        'properties' => [
            'id' => ['type' => 'integer'],
            'name' => ['type' => 'string'],
            'email' => ['type' => 'string', 'format' => 'email']
        ],
        'required' => ['id', 'name']
    ];
}
```

### Custom Error Responses

The system automatically includes:
- 422 Validation Error responses for routes with validation rules
- 401 Unauthorized responses for routes with authentication barriers
- 403 Forbidden responses for routes with authorization barriers

### Custom Headers

Include custom headers in your response schema:

```php
public static function getResponseSchema(): array
{
    return [
        'type' => 'string',
        'description' => 'File download',
        'headers' => [
            'Content-Disposition' => [
                'description' => 'File attachment header',
                'schema' => ['type' => 'string']
            ]
        ]
    ];
}
```

## Troubleshooting

### Missing Renderer Support

If a renderer isn't automatically detected:

1. Implement the `OpenApiRenderer` interface
2. Use the `OpenApiRendererTrait` for default implementations
3. Override methods as needed for custom behavior

### Schema Analysis Issues

If response schemas aren't being generated correctly:

1. Ensure Psalm is properly configured
2. Check that renderer classes are properly imported
3. Verify that the `OpenApiRenderer` interface is implemented

### Performance Considerations

- Schema analysis is cached during development
- Consider implementing caching for production environments
- Large applications may benefit from lazy loading of route details

## Contributing

When adding new renderers:

1. Implement the `OpenApiRenderer` interface
2. Add appropriate tests
3. Update documentation if needed
4. Consider adding to the built-in renderer list

The interface-based approach ensures that new renderers are automatically supported without requiring changes to the documentation system. 