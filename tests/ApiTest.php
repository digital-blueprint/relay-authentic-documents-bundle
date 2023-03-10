<?php

declare(strict_types=1);

namespace DBP\API\AuthenticDocumentBundle\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class ApiTest extends ApiTestCase
{
    public function testNotAuth()
    {
        $endpoints = [
            ['POST', '/authentic-document-requests', 401],
            ['GET', '/authentic-document-types', 401],
            ['GET', '/authentic-document-types/42', 401],
            ['GET', '/authentic-documents/42', 401],
        ];

        foreach ($endpoints as $ep) {
            [$method, $path, $status] = $ep;
            $client = self::createClient();
            $response = $client->request($method, $path);
            $this->assertEquals($status, $response->getStatusCode(), $path);
        }
    }
}
