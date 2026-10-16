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
            return $this->errorResponse(502, 'upstream_error', 'Unable to fetch weather data from the source.');
        }

        if ($response->getStatusCode() !== 200) {
            return $this->errorResponse(502, 'upstream_error', 'Weather source returned an unexpected response.');
        }

        $data = json_decode($response->getBody(), true);
        $temperature = $data['current_weather']['temperature'] ?? null;

        if (! is_numeric($temperature)) {
            return $this->errorResponse(502, 'upstream_error', 'Temperature data is missing from the weather source.');
        }

        $model = new WeatherLogModel();
        $id = $model->insert([
            'city'        => 'Iligan City',
            'temperature' => $temperature,
            'fetched_at'  => date('Y-m-d H:i:s'),
        ]);

        if ($id === false) {
            return $this->errorResponse(500, 'server_error', 'Unable to save the weather reading.');
        }

        $row = $model->find($id);

        return $this->response->setStatusCode(201)->setJSON([
            'status' => 201,
            'data'   => $this->formatRow($row),
        ]);
    }

    public function logs(): ResponseInterface
    {
        $limitInput = $this->request->getGet('limit');

        if ($limitInput === null || $limitInput === '') {
            $limit = 10;
        } else {
            $validated = filter_var($limitInput, FILTER_VALIDATE_INT);

            if ($validated === false || $validated < 1 || $validated > 50) {
                return $this->errorResponse(422, 'invalid_limit', 'limit must be 1 to 50.');
            }

            $limit = $validated;
        }

        $model = new WeatherLogModel();
        $rows = $model->orderBy('id', 'DESC')->findAll($limit);

        $result = [];
        foreach ($rows as $row) {
            $result[] = $this->formatRow($row);
        }

        return $this->response->setStatusCode(200)->setJSON([
            'status' => 200,
            'data'   => $result,
        ]);
    }

    public function show($id = null): ResponseInterface
    {
        $model = new WeatherLogModel();
        $row = $model->find($id);

        if (empty($row)) {
            return $this->errorResponse(404, 'not_found', 'Reading not found.');
        }

        return $this->response->setStatusCode(200)->setJSON([
            'status' => 200,
            'data'   => $this->formatRow($row),
        ]);
    }

    protected function errorResponse(int $status, string $code, string $message): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON([
            'status' => $status,
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