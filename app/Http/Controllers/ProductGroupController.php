<?php

namespace App\Http\Controllers;

use App\Constants\Api;
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
        $page = $request->has('page') ? $request->page : NULL;
        $size = $request->get('size', Api::LIST_DEFAULT_PAGING_SIZE);

        list($rows, $paging, $err) = $this->productGroupService->getList([
            'page' => $page,
            'size' => $size
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
        $err = $this->productGroupService->doUpdate($id, $request->only(['name', 'order']));
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
        $err = $this->productGroupService->doDelete($id, $forceDelete === '0');
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
