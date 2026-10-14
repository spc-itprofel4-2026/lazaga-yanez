<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;
use App\Models\WeatherLogModel;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class Weather extends BaseController
{
    public function refresh(): ResponseInterface
    {
        $client = Services::curlrequest(['timeout' => 10, 'http_errors' => false]);

        try {
            $response = $client->get('https://api.open-meteo.com/v1/forecast?latitude=8.2280&longitude=124.2452&current_weather=true');
        } catch (\Throwable $e) {
            return $this->errorResponse('upstream_error', 'Unable to fetch weather data from the source.', 502);
        }

        if ($response->getStatusCode() !== 200) {
            return $this->errorResponse('upstream_error', 'Weather source returned an unexpected response.', 502);
        }

        $data = json_decode($response->getBody(), true);
        $temperature = $data['current_weather']['temperature'] ?? null;

        if (! is_numeric($temperature)) {
            return $this->errorResponse('upstream_error', 'Temperature data is missing from the weather source.', 502);
        }

        $model = new WeatherLogModel();
        $id = $model->insert([
            'city'        => 'Iligan City',
            'temperature' => $temperature,
            'fetched_at'  => date('Y-m-d H:i:s'),
        ]);

        if ($id === false) {
            return $this->errorResponse('server_error', 'Unable to save the weather reading.', 500);
        }

        $row = $model->find($id);

        return $this->response->setStatusCode(201)->setJSON($this->formatRow($row));
    }

    public function logs(): ResponseInterface
    {
        $limitInput = $this->request->getGet('limit');

        if ($limitInput === null || $limitInput === '') {
            $limit = 10;
        } else {
            $validated = filter_var($limitInput, FILTER_VALIDATE_INT);

            if ($validated === false || $validated < 1 || $validated > 50) {
                return $this->errorResponse('invalid_limit', 'limit must be a whole number from 1 to 50.', 422);
            }

            $limit = $validated;
        }

        $model = new WeatherLogModel();
        $rows = $model->orderBy('id', 'DESC')->findAll($limit);

        $result = [];
        foreach ($rows as $row) {
            $result[] = $this->formatRow($row);
        }

        return $this->response->setStatusCode(200)->setJSON($result);
    }

    public function show($id = null): ResponseInterface
    {
        $model = new WeatherLogModel();
        $row = $model->find($id);

        if (empty($row)) {
            return $this->errorResponse('not_found', 'Reading not found.', 404);
        }

        return $this->response->setStatusCode(200)->setJSON($this->formatRow($row));
    }

    protected function errorResponse(string $code, string $message, int $statusCode): ResponseInterface
    {
        return $this->response->setStatusCode($statusCode)->setJSON([
            'status' => 'error',
            'error'  => [
                'code'    => $code,
                'message' => $message,
            ],
        ]);
    }

    protected function formatRow(array $row): array
    {
        return [
            'id'           => (int) $row['id'],
            'city'         => $row['city'],
            'temperatureC' => (float) $row['temperature'],
            'fetchedAt'    => $row['fetched_at'],
        ];
    }
}