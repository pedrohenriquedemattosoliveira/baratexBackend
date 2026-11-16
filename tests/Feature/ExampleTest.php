<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_health_check_responds(): void
    {
        $response = $this->get('/up');

        $response->assertStatus(200);
    }
}
