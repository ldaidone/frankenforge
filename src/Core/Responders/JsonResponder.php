<?php

declare(strict_types=1);

namespace FrankenForge\Core\Responders;

use FrankenForge\Core\Http\Request;
use FrankenForge\Core\Http\Response;

/**
 * Dedicated JSON responder for API endpoints.
 *
 * Handles pagination, errors, and collection responses.
 */
final readonly class JsonResponder
{
    public function __construct(
        private Response $response,
    ) {}

    public function respond(mixed $data, int $status = 200, array $meta = []): Response
    {
        $body = ['data' => $data];

        if ($meta !== []) {
            $body['meta'] = $meta;
        }

        return $this->response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json')
            ->withBody(json_encode($body, JSON_THROW_ON_ERROR));
    }

    public function error(string $message, int $status = 400, array $errors = []): Response
    {
        $body = [
            'error' => [
                'message' => $message,
                'status' => $status,
            ],
        ];

        if ($errors !== []) {
            $body['error']['errors'] = $errors;
        }

        return $this->response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json')
            ->withBody(json_encode($body, JSON_THROW_ON_ERROR));
    }

    public function paginated(
        array $data,
        int $total,
        int $page,
        int $perPage,
    ): Response {
        $lastPage = (int) ceil($total / $perPage);

        return $this->respond($data, 200, [
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => $lastPage,
                'from' => ($page - 1) * $perPage + 1,
                'to' => min($page * $perPage, $total),
            ],
        ]);
    }

    public function created(string $id, mixed $data = null): Response
    {
        $body = ['id' => $id];
        if ($data !== null) {
            $body['data'] = $data;
        }

        return $this->response
            ->withStatus(201)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Location', '/api/' . $id)
            ->withBody(json_encode($body, JSON_THROW_ON_ERROR));
    }

    public function noContent(): Response
    {
        return $this->response
            ->withStatus(204)
            ->withHeader('Content-Type', 'application/json')
            ->withBody('{}');
    }
}