<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ForbiddenPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('web')->get('/__test/forbidden', fn () => abort(403));
    }

    public function test_html_403_shows_branded_access_denied_page(): void
    {
        $this->get('/__test/forbidden')
            ->assertForbidden()
            ->assertSee(__('Access denied'), false)
            ->assertSee(__('You do not have permission to open this page.'), false)
            ->assertSee(__('Log in'), false)
            ->assertDontSee('THIS ACTION IS UNAUTHORIZED', false);
    }

    public function test_middleware_unauthorized_view_uses_the_same_branded_page(): void
    {
        $this->view('unauthorized')
            ->assertSee(__('Access denied'), false)
            ->assertSee(__('You do not have permission to open this page.'), false);
    }
}
