<?php

namespace Tests\Feature;

use Tests\TestCase;

class RobotsTest extends TestCase
{
    public function test_robots_txt_memakai_sitemap_url_absolut(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

        $content = $response->getContent();

        $this->assertStringContainsString('User-agent: *', $content);
        $this->assertStringContainsString('Disallow:', $content);
        $this->assertMatchesRegularExpression('/^Sitemap: https?:\/\/\S+$/m', $content);
        $this->assertStringNotContainsString('Sitemap: /sitemap.xml', $content);
    }
}
