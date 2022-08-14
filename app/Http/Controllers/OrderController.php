<?php

namespace App\Http\Controllers;

use App\Constants\Api;
use App\Constants\Message;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => ['required', 'numeric'],
            'amount' => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => Message::ERR_SHOPBE_WRONG_INFORMATION,
                'errors' => $validator->errors()
            ], 422);
        }

        $err = $this->orderService->doCreate($request->only(['product_id', 'amount', 'description']));
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
        if($request->has('filters')){
            $validator = Validator::make($request->all(), [
                'filters' => ['required', 'array'],
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => FALSE,
                    'message' => Message::ERR_SHOPBE_WRONG_INFORMATION,
                    'errors' => $validator->errors()
                ], 422);
            }
        }

        $page = $request->has('page') ? $request->page : NULL;
        $size = $request->get('size', Api::LIST_DEFAULT_PAGING_SIZE);
        $filters = $request->get('filters', []);

        list($rows, $paging, $err) = $this->orderService->getList([
            'page' => $page,
            'size' => $size,
            'filters' => $filters
        ]);
        if ($err instanceof \Exception){
            return response()->json([
                'success' => FALSE,
                'data' => [
                    'paging' => $paging,
                    'rows' => []
                ],
                'message' => $err->getMessage()
            ]);
        }

        return response()->json([
            'success' => TRUE,
            'data' => [
                'paging' => $paging,
                'rows' => $rows
            ],
            'message' => ''
        ]);
    }

    public function update(Request $request, int $id)
    {
        $err = $this->orderService->doUpdate($id, array_filter($request->only(['product_id', 'amount', 'description'])));
        if ($err instanceof \Exception){
            return response()->json([
                'success' => FALSE,
                'message' => $err->getMessage()
            ]);
        }

        return response()->json([
            'success' => TRUE,
            'message' => Message::MSG_SHOPBE_UPDATE_SUCCESS
        ]);
    }

    public function delete(string $id, Request $request)
    {
        $forceDelete = $request->get('force', '0');
        $err = $this->orderService->doDelete($id, $forceDelete === '0');
        if ($err instanceof \Exception){
            return response()->json([
                'success' => FALSE,
                'message' => $err->getMessage()
            ]);
        }

        return response()->json([
            'success' => TRUE,
            'message' => Message::MSG_SHOPBE_DELETE_SUCCESS
        ]);
    }
}
