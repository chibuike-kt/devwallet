<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends ApiController
{
  /**
   * GET /api/v1/project
   * Returns metadata about the project the API key belongs to.
   */
  public function show(Request $request): JsonResponse
  {
    $project = $request->_api_project->load('wallets');

    return $this->success([
      'id'           => $project->id,
      'name'         => $project->name,
      'slug'         => $project->slug,
      'environment'  => $project->environment,
      'status'       => $project->status,
      'wallet_count' => $project->wallets->count(),
      'currencies'   => $project->wallets->pluck('currency')->unique()->values(),
      'created_at'   => $project->created_at->toIso8601String(),
    ]);
  }
}
