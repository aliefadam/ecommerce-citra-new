<?php

namespace Tests\Feature;

use Tests\TestCase;

class MobileHelpApiTest extends TestCase
{
    public function test_faqs_endpoint_returns_same_data_as_website_help_center(): void
    {
        config(['mobile-spa.enabled' => true]);

        $response = $this->getJson('/api/mobile/v1/help/faqs')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'title',
                    'intro',
                    'categories' => [['key', 'label']],
                    'faqs' => [['id', 'category', 'question', 'answer', 'platforms']],
                    'help_center_url',
                ],
            ]);

        $this->assertSame(
            collect(trans('help.faqs'))->pluck('question')->all(),
            collect($response->json('data.faqs'))->pluck('question')->all(),
        );
    }
}
