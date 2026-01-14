<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserNoteController extends Controller
{
    /**
     * Get all notes for a specific test
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'test_type' => 'required|string|in:exam,skill,section,test',
            'test_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $notes = UserNote::where('user_id', Auth::id())
            ->where('test_type', $request->test_type)
            ->where('test_id', $request->test_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notes
        ]);
    }

    /**
     * Store a newly created note
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'test_type' => 'required|string|in:exam,skill,section,test',
            'test_id' => 'required|integer',
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
            'selected_text' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $note = UserNote::create([
            'user_id' => Auth::id(),
            'test_type' => $request->test_type,
            'test_id' => $request->test_id,
            'title' => $request->title,
            'content' => $request->content,
            'selected_text' => $request->selected_text,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Note created successfully',
            'data' => $note
        ], 201);
    }

    /**
     * Display the specified note
     */
    public function show(string $id)
    {
        $note = UserNote::where('user_id', Auth::id())->find($id);

        if (!$note) {
            return response()->json([
                'success' => false,
                'message' => 'Note not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $note
        ]);
    }

    /**
     * Update the specified note
     */
    public function update(Request $request, string $id)
    {
        $note = UserNote::where('user_id', Auth::id())->find($id);

        if (!$note) {
            return response()->json([
                'success' => false,
                'message' => 'Note not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'content' => 'sometimes|required|string',
            'selected_text' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $note->update($request->only(['title', 'content', 'selected_text']));

        return response()->json([
            'success' => true,
            'message' => 'Note updated successfully',
            'data' => $note
        ]);
    }

    /**
     * Remove the specified note
     */
    public function destroy(string $id)
    {
        $note = UserNote::where('user_id', Auth::id())->find($id);

        if (!$note) {
            return response()->json([
                'success' => false,
                'message' => 'Note not found'
            ], 404);
        }

        $note->delete();

        return response()->json([
            'success' => true,
            'message' => 'Note deleted successfully'
        ]);
    }
}
