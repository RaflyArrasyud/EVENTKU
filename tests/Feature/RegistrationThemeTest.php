<?php

namespace Tests\Feature;

use Tests\TestCase;

class RegistrationThemeTest extends TestCase
{
    public function test_registration_fields_have_explicit_theme_colors(): void
    {
        $html = $this->get('/register')->assertOk()->getContent();

        foreach (['name', 'email', 'password', 'password_confirmation'] as $field) {
            preg_match('/<input\b[^>]*id="'.preg_quote($field, '/').'"[^>]*>/s', $html, $matches);
            $this->assertNotEmpty($matches, $field);
            foreach (['bg-white', 'dark:bg-deep', 'text-slate-900', 'dark:text-paper', 'placeholder-slate-500', 'dark:placeholder-muted'] as $class) {
                $this->assertStringContainsString($class, $matches[0], $field);
            }
        }
    }
}
