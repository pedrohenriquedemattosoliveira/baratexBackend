<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>BARATEX API — Documentação</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui.css">
    <style>body { margin: 0; }</style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script>
        window.onload = () => {
            window.ui = SwaggerUIBundle({
                url: '/openapi.yaml',
                dom_id: '#swagger-ui',
                presets: [SwaggerUIBundle.presets.apis],
            });
        };
    </script>
</body>
</html>
