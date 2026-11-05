<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class DeploymentProxyTest extends TestCase
{
    public function test_railway_proxy_opt_in_uses_https_for_generated_urls(): void
    {
        config(['app.trust_proxy_headers' => true]);
        $host = parse_url(config('app.url'), PHP_URL_HOST);
        $port = parse_url(config('app.url'), PHP_URL_PORT);
        $expected = 'https://'.$host.($port ? ':'.$port : '').'/login';
        Route::get('/_deployment-proxy-test', fn (Request $request) => [
            'secure' => $request->isSecure(),
            'url' => url('/login'),
        ]);

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.2'])
            ->withHeaders(['X-Forwarded-Proto' => 'https'])
            ->get('/_deployment-proxy-test')
            ->assertOk()
            ->assertJson(['secure' => true, 'url' => $expected]);
    }

    public function test_local_default_does_not_trust_arbitrary_proxy_headers(): void
    {
        config(['app.trust_proxy_headers' => false]);
        Route::get('/_deployment-proxy-test', fn (Request $request) => [
            'secure' => $request->isSecure(),
        ]);

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->withHeaders(['X-Forwarded-Proto' => 'https'])
            ->get('/_deployment-proxy-test')
            ->assertOk()
            ->assertJson(['secure' => false]);
    }
}
