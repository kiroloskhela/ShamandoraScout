<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * The site root is the public brand landing page (SEO / AEO entity).
     */
    public function test_the_application_landing_page_is_public(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Shamandora Scout', false);
    }
}
