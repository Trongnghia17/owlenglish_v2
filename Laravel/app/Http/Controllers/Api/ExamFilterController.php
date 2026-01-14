<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamFilterController extends Controller
{
    public function index(Request $request)
    {
        $examType = $request->get('type');

        $filters = ExamFilter::query()
            ->where('type', 'skill')
            ->where('is_active', true)
            ->when($examType, fn ($q) =>
                $q->where('exam_type', $examType)
            )
            ->with([
                'children' => function ($q) {
                    $q->where('type', 'group')
                      ->with([
                          'children' => function ($q2) {
                              $q2->where('type', 'value');
                          }
                      ]);
                }
            ])
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $filters
        ]);
    }
}
