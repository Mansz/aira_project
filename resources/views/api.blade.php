<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} API</title>
    @inertiaHead
</head>
<body>
    @inertia
    <script>
        // Override Inertia's default visit behavior for Android
        window.Inertia = {
            ...window.Inertia,
            visit: function(url, { method = 'get', data = {}, replace = false, preserveState = false, preserveScroll = false, only = [], headers = {} } = {}) {
                // Check if request is from Android app
                if (headers['X-Inertia-Android']) {
                    // Return JSON response instead of navigating
                    return fetch(url, {
                        method: method.toUpperCase(),
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Inertia': true,
                            'X-Inertia-Android': true,
                            ...headers
                        },
                        body: method.toLowerCase() !== 'get' ? JSON.stringify(data) : undefined
                    }).then(response => response.json());
                }
                
                // Default Inertia behavior for web requests
                return window.Inertia.visit(url, { method, data, replace, preserveState, preserveScroll, only, headers });
            }
        };
    </script>
</body>
</html>
