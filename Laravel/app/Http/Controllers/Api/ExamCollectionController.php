<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamCollection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExamCollectionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ExamCollection::query()->where('status', 1);;
        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        $examCollections = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $examCollections,
        ]);
    }
}
