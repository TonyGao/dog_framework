<?php

namespace App\Tests\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AccessControlTest extends WebTestCase
{
    public function testAnonymousUserCannotAccessAdminRoute(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/index');

        $this->assertSame(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('/login', (string) $client->getResponse()->headers->get('Location'));
    }

    public function testAnonymousUserCannotAccessAdminApiRoute(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/admin/org/company/list');

        $this->assertSame(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('/login', (string) $client->getResponse()->headers->get('Location'));
    }

    public function testAnonymousUserCannotUploadFile(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/storage/upload');

        $this->assertSame(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('/login', (string) $client->getResponse()->headers->get('Location'));
    }

    public function testAnonymousUserCannotUploadAvatar(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/employee/00000000-0000-0000-0000-000000000000/avatar');

        $this->assertSame(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('/login', (string) $client->getResponse()->headers->get('Location'));
    }
}
