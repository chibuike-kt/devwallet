<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class ApiController extends Controller
{
  /**
   * 200 success with data payload.
   */
  protected function success(mixed $data, string $message = 'ok', int $status = 200)
  {
    return response()->json([
      'status'  => 'success',
      'message' => $message,
      'data'    => $data,
    ], $status);
  }

  /**
   * 201 created.
   */
  protected function created(mixed $data, string $message = 'created')
  {
    return $this->success($data, $message, 201);
  }

  /**
   * Error response.
   */
  protected function error(string $message, int $status = 400, mixed $errors = null)
  {
    $body = [
      'status'  => 'error',
      'message' => $message,
    ];

    if ($errors !== null) {
      $body['errors'] = $errors;
    }

    return response()->json($body, $status);
  }

  /**
   * 404 not found.
   */
  protected function notFound(string $message = 'Resource not found.')
  {
    return $this->error($message, 404);
  }
}
