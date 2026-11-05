<?php

namespace Tests\Feature;

use Tests\TestCase;

class DemoLoginViewTest extends TestCase
{
    public function test_hosted_demo_does_not_fill_local_or_private_password(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        config(['demo.password' => 'private-hosting-secret-do-not-render']);

        $this->get('/login')->assertOk()
            ->assertSee('klik untuk mengisi email')
            ->assertSee('Password lokal tidak berlaku')
            ->assertDontSee('private-hosting-secret-do-not-render')
            ->assertDontSee("password = 'password'", false);
    }

    public function test_local_demo_keeps_password_autofill(): void
    {
        $this->app->detectEnvironment(fn () => 'local');

        $this->get('/login')->assertOk()
            ->assertSee('Akun demo, klik untuk mengisi')
            ->assertDontSee('Password lokal tidak berlaku')
            ->assertSee("password = 'password'", false);
    }
}
