<?php

namespace App\Http\Controllers;

use App\Constants\Api;
use App\Constants\Message;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
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

        list($rows, $paging, $err) = $this->transactionService->getList([
            'page' => $page,
            'size' => $size,
            'filters' => $filters
        ], TRUE);
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
}
