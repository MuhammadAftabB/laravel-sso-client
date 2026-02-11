<?php

namespace Liqwiz\LaravelSsoClient\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SsoInstallCommand extends Command
{
    protected $signature = 'sso:install
                            {--token= : One-time install token from the Hub}
                            {--hub= : Hub base URL (default: SSO_HUB_URL from .env)}';

    protected $description = 'Register this app with the SSO Hub and write .env + config';

    public function handle(): int
    {
        $token = $this->option('token');
        $hub = $this->option('hub') ?: config('sso-client.hub_url') ?: env('SSO_HUB_URL');

        if (! $token) {
            $this->error('Install token is required. Use --token=YOUR_TOKEN');
            return self::FAILURE;
        }
        if (! $hub) {
            $this->error('Hub URL is required. Use --hub=https://hub.example.com or set SSO_HUB_URL in .env');
            return self::FAILURE;
        }

        $hubUrl = rtrim(preg_replace('#/$#', '', $hub), '/');
        $appUrl = rtrim(config('app.url'), '/');
        $prefix = config('sso-client.routes.prefix', 'sso');
        $redirectUri = $appUrl.'/'.trim($prefix, '/').'/callback';

        $this->info('Registering with Hub at '.$hubUrl.' ...');

        $response = \Illuminate\Support\Facades\Http::asJson()
            ->timeout(15)
            ->post($hubUrl.'/api/sso/register-client', [
                'install_token' => $token,
                'name' => config('app.name'),
                'app_url' => $appUrl,
                'redirect_uri' => $redirectUri,
            ]);

        if (! $response->successful()) {
            $body = $response->json();
            $msg = $body['message'] ?? $response->body();
            $this->error('Registration failed: '.$msg);
            return self::FAILURE;
        }

        $data = $response->json();
        $clientId = $data['client_id'] ?? '';
        $clientSecret = $data['client_secret'] ?? '';

        if (! $clientId || ! $clientSecret) {
            $this->error('Hub did not return client_id or client_secret.');
            return self::FAILURE;
        }

        $this->writeEnv($hubUrl, $clientId, $clientSecret, $redirectUri);
        $this->call('config:clear');
        $this->publishConfig();

        $this->info('SSO client installed successfully.');
        $this->line('Add this to your login page: <a href="'.url('/sso/login').'">Login with Hub</a>');

        return self::SUCCESS;
    }

    protected function writeEnv(string $hubUrl, string $clientId, string $clientSecret, string $redirectUri): void
    {
        $path = base_path('.env');
        if (! File::exists($path)) {
            $this->warn('.env not found; create it and add the following:');
            $this->line('SSO_HUB_URL='.$hubUrl);
            $this->line('SSO_CLIENT_ID='.$clientId);
            $this->line('SSO_CLIENT_SECRET='.$clientSecret);
            $this->line('SSO_REDIRECT_URI='.$redirectUri);
            return;
        }

        $content = File::get($path);
        $lines = [
            'SSO_HUB_URL='.$hubUrl,
            'SSO_CLIENT_ID='.$clientId,
            'SSO_CLIENT_SECRET='.$clientSecret,
            'SSO_REDIRECT_URI='.$redirectUri,
        ];

        foreach ($lines as $line) {
            $key = Str::before($line, '=');
            if (Str::contains($content, $key.'=')) {
                $content = preg_replace('/'.preg_quote($key.'=', '/').'.*$/m', $line, $content, 1);
            } else {
                $content .= "\n".$line;
            }
        }

        File::put($path, $content);
    }

    protected function publishConfig(): void
    {
        if (! File::exists(config_path('sso-client.php'))) {
            $this->call('vendor:publish', ['--tag' => 'sso-client-config']);
        }
    }
}
