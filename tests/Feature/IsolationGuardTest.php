<?php

namespace Tests\Feature;

use Tests\TestCase;

class IsolationGuardTest extends TestCase
{
    public function test_testing_database_is_isolated_in_memory_sqlite(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
    }
}
