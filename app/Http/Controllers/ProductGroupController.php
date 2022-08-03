<?php

namespace App\Http\Controllers;

use App\Constants\Message;
use App\Services\ProductGroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductGroupController extends Controller
{
    protected $productGroupService;

    public function __construct(ProductGroupService $productGroupService)
    {
        $this->productGroupService = $productGroupService;
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => Message::ERR_SHOPBE_WRONG_INFORMATION,
                'errors' => $validator->errors()
            ], 422);
        }

        $err = $this->productGroupService->doCreate($request->only(['name', 'parent_id', 'order']));
        if ($err instanceof \Exception){
            return response()->json([
                'success' => FALSE,
                'message' => $err->getMessage()
            ]);
        }

        return response()->json([
            'success' => TRUE,
            'message' => Message::MSG_SHOPBE_CREATE_SUCCESS
        ]);
    }

    public function list(Request $request)
    {
        return response()->json([
            'success' => TRUE,
            'message' => ''
        ]);
    }

    public function view(Request $request, string $id)
    {
        return response()->json([
            'success' => TRUE,
            'message' => ''
        ]);
    }

    public function update(Request $request)
    {
        return response()->json([
            'success' => TRUE,
            'message' => ''
        ]);
    }

    public function delete(Request $request)
    {
        return response()->json([
            'success' => TRUE,
            'message' => ''
        ]);
    }
}
