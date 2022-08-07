<?php

namespace App\Http\Controllers;

use App\Constants\Api;
use App\Constants\Message;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
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

        $err = $this->productService->doCreate($request->only(['name', 'base_price', 'price', 'order', 'status']));
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

        $page = $request->get('page', Api::LIST_DEFAULT_PAGING_PAGE);
        $size = $request->get('size', Api::LIST_DEFAULT_PAGING_SIZE);
        $filters = $request->get('filters', []);

        list($rows, $paging, $err) = $this->productService->getList([
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

    public function view(string $id)
    {
        list($data, $err) = $this->productService->getView($id);
        if ($err instanceof \Exception){
            return response()->json([
                'success' => FALSE,
                'data' => [],
                'message' => $err->getMessage()
            ]);
        }

        return response()->json([
            'success' => TRUE,
            'data' => $data,
            'message' => ''
        ]);
    }

    public function update(Request $request, int $id)
    {
        $err = $this->productService->doUpdate($id, $request->only(['name', 'base_price', 'price', 'status', 'order']));
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

    public function delete(string $id)
    {
        $err = $this->productService->doDelete($id);
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
