<?php

namespace App\Tests\Security;

use App\Service\Security\EmailHtmlSanitizer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EmailHtmlSanitizerTest extends KernelTestCase
{
    public function testSanitizeRemovesExecutableMarkup(): void
    {
        self::bootKernel();
        $sanitizer = static::getContainer()->get(EmailHtmlSanitizer::class);

        $sanitized = $sanitizer->sanitize('<p onclick="alert(1)">hello</p><script>alert(1)</script><img src="x" onerror="alert(1)"><a href="javascript:alert(1)">link</a>');

        $this->assertStringNotContainsString('<script', $sanitized);
        $this->assertStringNotContainsString('onclick=', $sanitized);
        $this->assertStringNotContainsString('onerror=', $sanitized);
        $this->assertStringNotContainsString('javascript:', $sanitized);
        $this->assertStringContainsString('<p>hello</p>', $sanitized);
    }

    public function testSanitizeKeepsSafeEmailMarkup(): void
    {
        self::bootKernel();
        $sanitizer = static::getContainer()->get(EmailHtmlSanitizer::class);

        $sanitized = $sanitizer->sanitize('<table><tr><td style="color:#111827;">content</td></tr></table><a href="https://example.com">open</a>');

        $this->assertStringContainsString('<table>', $sanitized);
        $this->assertStringContainsString('style="color:#111827;"', $sanitized);
        $this->assertStringContainsString('href="https://example.com"', $sanitized);
    }
}
