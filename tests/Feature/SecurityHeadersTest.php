<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_success_response_has_security_headers(): void
    {
        $response = $this->getJson('/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertHeader('Cross-Origin-Resource-Policy', 'same-origin')
            ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin')
            ->assertHeader('Permissions-Policy')
            ->assertHeaderMissing('X-Powered-By');

        $this->assertStringContainsString(
            "default-src 'none'",
            $response->headers->get('Content-Security-Policy')
        );
    }

    public function test_error_response_also_has_security_headers(): void
    {
        // 401 : route protégée sans token. Les erreurs doivent aussi être protégées.
        $this->getJson('/api/me')
            ->assertUnauthorized()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY');
    }

    public function test_hsts_is_sent_only_over_https(): void
    {
        $this->getJson('https://localhost/')
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000');

        $this->getJson('http://localhost/')
            ->assertHeaderMissing('Strict-Transport-Security');
    }
}
