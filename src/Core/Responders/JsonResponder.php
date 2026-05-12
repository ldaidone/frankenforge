<?php
/**
 * FrankenForge — FrankenForge\Core\Responders
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
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

    /**
     * Respond with JSON data and optional metadata.
     *
     * @param mixed $data The main response data (object, array, etc.)
     * @param int $status HTTP status code (default 200)
     * @param array $meta Optional metadata (e.g. pagination info)
     * @return Response
     * @throws \JsonException
     */
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

    /**
     * Respond with a JSON error message.
     *
     * @param string $message The error message to return
     * @param int $status HTTP status code (default 400)
     * @param array $errors Optional array of specific error details
     * @return Response
     * @throws \JsonException
     */
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

    /**
     * Respond with paginated JSON data.
     *
     * @param array $data The current page of data items
     * @param int $total The total number of items across all pages
     * @param int $page The current page number (1-based)
     * @param int $perPage The number of items per page
     * @return Response
     * @throws \JsonException
     */
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

    /**
     * Respond with a 201 Created status and the new resource ID.
     *
     * @param string $id The ID of the newly created resource
     * @param mixed|null $data Optional additional data to include in the response
     * @return Response
     * @throws \JsonException
     */
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

    /**
     * Respond with a 204 No Content status for successful deletions or updates.
     *
     * @return Response
     */
    public function noContent(): Response
    {
        return $this->response
            ->withStatus(204)
            ->withHeader('Content-Type', 'application/json')
            ->withBody('{}');
    }
}
