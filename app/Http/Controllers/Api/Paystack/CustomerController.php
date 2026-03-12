<?php

namespace App\Http\Controllers\Api\Paystack;

use App\Http\Controllers\Controller;
use App\Models\PaystackCustomer;
use App\Services\PaystackResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
  public function __construct(protected PaystackResponseService $response) {}

  /**
   * POST /api/paystack/customer
   */
  public function store(Request $request): JsonResponse
  {
    $request->validate([
      'email'      => ['required', 'email'],
      'first_name' => ['nullable', 'string'],
      'last_name'  => ['nullable', 'string'],
      'phone'      => ['nullable', 'string'],
      'metadata'   => ['nullable', 'array'],
    ]);

    $customer = PaystackCustomer::firstOrCreate(
      [
        'project_id' => $request->_api_project->id,
        'email'      => $request->email,
      ],
      [
        'first_name'    => $request->first_name,
        'last_name'     => $request->last_name,
        'phone'         => $request->phone,
        'metadata'      => $request->metadata,
        'customer_code' => 'CUS_' . strtolower(Str::random(12)),
      ]
    );

    return response()->json([
      'status'  => true,
      'message' => 'Customer created',
      'data'    => $this->response->customerData($customer),
    ], 201);
  }

  /**
   * GET /api/paystack/customer/{email_or_code}
   */
  public function show(Request $request, string $emailOrCode): JsonResponse
  {
    $customer = PaystackCustomer::where('project_id', $request->_api_project->id)
      ->where(function ($q) use ($emailOrCode) {
        $q->where('email', $emailOrCode)
          ->orWhere('customer_code', $emailOrCode);
      })
      ->first();

    if (!$customer) {
      return $this->response->errorResponse('Customer not found', 404);
    }

    return response()->json($this->response->customerResponse($customer));
  }

  /**
   * GET /api/paystack/customer
   */
  public function index(Request $request): JsonResponse
  {
    $customers = PaystackCustomer::where('project_id', $request->_api_project->id)
      ->latest()
      ->paginate(50);

    return response()->json($this->response->customerListResponse($customers));
  }
}
