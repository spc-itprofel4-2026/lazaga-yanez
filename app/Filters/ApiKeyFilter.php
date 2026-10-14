<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class ApiKeyFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): ?ResponseInterface
    {
        $expected = env('API_KEY');
        $expected = is_string($expected) ? trim($expected) : '';

        if ($expected === '') {
            return Services::response()->setStatusCode(401)->setJSON([
                'status' => 'error',
                'error'  => [
                    'code'    => 'unauthorized',
                    'message' => 'API key is not configured.',
                ],
            ]);
        }

        $provided = trim((string) $request->getHeaderLine('X-API-Key'));

        if ($provided === '' || ! hash_equals($expected, $provided)) {
            return Services::response()->setStatusCode(401)->setJSON([
                'status' => 'error',
                'error'  => [
                    'code'    => 'unauthorized',
                    'message' => 'A valid API key is required.',
                ],
            ]);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): ?ResponseInterface
    {
        return null;
    }
}