<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suphle API Documentation</title>
    <script src="https://unpkg.com/swagger-ui-dist@5.0.0/swagger-ui-bundle.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5.0.0/swagger-ui.css">
</head>
<body>
    <div id="swagger-ui"></div>
    <script>
        window.onload = function () {
            SwaggerUIBundle({
                url: '/api-docs/json',
                dom_id: '#swagger-ui',

                deepLinking: true,
                displayRequestDuration: true,
                filter: true,

                tryItOutEnabled: true,

                docExpansion: "list", // or "full" if you prefer expanded
                defaultModelsExpandDepth: 2,
                defaultModelExpandDepth: 2,

                presets: [
                    SwaggerUIBundle.presets.apis
                ],

                layout: "BaseLayout"
            });
        };
    </script>
</body>
</html>