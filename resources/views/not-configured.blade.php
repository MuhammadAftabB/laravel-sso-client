<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>SSO Not Configured</title>
    <style>
        body {
            font-family: system-ui, sans-serif;
            max-width: 600px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        h1 {
            color: #c00;
        }

        code {
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <h1>SSO is not configured</h1>
    <p>This application uses Single Sign-On. Configure it by running:</p>
    <pre><code>php artisan sso:install --token=YOUR_TOKEN --hub=https://your-hub.example.com</code></pre>
    <p>Get an install token from your Hub admin (SSO Install Tokens page).</p>
</body>

</html>
