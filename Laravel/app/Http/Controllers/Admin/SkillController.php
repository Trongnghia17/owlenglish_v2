<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamSkill;
use App\Models\Exam;
use App\Models\ExamFilter;
use App\Models\ExamSection;
use App\Models\ExamTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SkillController extends Controller
{
    /**
     * Display a listing of skills.
     */
    public function index(Request $request)
    {
        $query = ExamSkill::with(['examTest.exam']);

        // Filter by skill type
        if ($request->filled('skill_type')) {
            $query->where('skill_type', $request->skill_type);
        }

        // Filter by exam
        if ($request->filled('exam_id')) {
            $query->whereHas('examTest', function ($q) use ($request) {
                $q->where('exam_id', $request->exam_id);
            });
        }

        // Filter by test
        if ($request->filled('test_id')) {
            $query->where('exam_test_id', $request->test_id);
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $skills = $query->latest()->paginate(15);

        // Get all exams and tests for filter dropdowns
        $exams = Exam::orderBy('name')->get();
        $tests = ExamTest::orderBy('name')->get();

        return view('admin.skills.index', compact('skills', 'exams', 'tests'));
    }

    /**
     * Show the form for creating a new skill.
     */
    public function create()
    {

        $exams = Exam::with('tests')->orderBy('name')->get();

        return view('admin.skills.create', compact('exams'));
    }

    /**
     * Store a newly created skill.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'exam_test_id' => 'required|exists:exam_tests,id',
            'skill_type' => 'required|in:reading,writing,listening,speaking',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'time_limit' => 'required|integer|min:1',
            'is_online' => 'nullable|boolean',
        ]);

        // Verify that exam_test_id belongs to the selected exam_id
        $examTest = ExamTest::where('id', $validated['exam_test_id'])
            ->where('exam_id', $validated['exam_id'])
            ->firstOrFail();

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store('skills', 'public');
            $validated['image'] = $imagePath;
        }

        // Remove exam_id from validated data as it's not in the database table
        unset($validated['exam_id']);

        // Ensure is_active is always boolean (true or false)
        $validated['is_active'] = $request->has('is_active') ? true : false;

        // Ensure is_online is boolean
        $validated['is_online'] = $request->has('is_online') ? true : false;

        $skill = ExamSkill::create($validated);

        return redirect()->route('admin.skills.index')
            ->with('success', 'Skill đã được tạo thành công!');
    }

    /**
     * Display the specified skill.
     */
    public function show(ExamSkill $skill)
    {
        $skill->load(['examTest.exam', 'sections.questionGroups.questions']);

        return view('admin.skills.show', compact('skill'));
    }

    /**
     * Show the form for editing the specified skill.
     */
    public function edit(ExamSkill $skill)
    {
        $exams = Exam::with('tests')->orderBy('name')->get();

        // mục đích để lấy ra type của exam
        $exam_id = ExamTest::where('id', $skill->exam_test_id)->value('exam_id');
        $examFirst = $exams->firstWhere('id', $exam_id);
        $skill->load([
            'sections.questionGroups.questions',
            'sections.questions',
            'examTest.exam',
            'sections.filters'
        ]);

        $skillFilters = ExamFilter::where('type', 'skill')
        ->where('exam_type', $examFirst->type)
        ->whereNull('parent_id')
        ->with([
            'children' => function ($q) {
                $q->where('type', 'group')
                  ->with(['children' => function ($qq) {
                      $qq->where('type', 'value');
                  }]);
            }
        ])
        ->get()
        ->keyBy(fn ($item) => Str::slug($item->name));

        return view('admin.skills.edit', compact('skill', 'exams', 'skillFilters'));
    }

    /**
     * Update the specified skill.
     */
    public function update(Request $request, ExamSkill $skill)
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'exam_test_id' => 'required|exists:exam_tests,id',
            'skill_type' => 'required|in:reading,writing,listening,speaking',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'time_limit' => 'required|integer|min:1',
            'is_online' => 'nullable|boolean',
            'sections' => 'nullable|array',
            'sections.*.id' => 'nullable|integer',
            'sections.*.title' => 'nullable|string|max:255',
            'sections.*.content' => 'nullable|string',
            'sections.*.feedback' => 'nullable|string',
            'sections.*.content_format' => 'nullable|in:text,audio,video',
            'sections.*.answer_inputs_inside_content' => 'nullable|string',
            'sections.*.audio_file' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:102400', // 100MB
            'sections.*.ui_layer' => 'nullable|in:1,2',
            'sections.*.groups' => 'nullable|array',
            'sections.*.groups.*.id' => 'nullable|integer',
            'sections.*.groups.*.content' => 'nullable|string',
            'sections.*.groups.*.question_type' => 'nullable|in:multiple_choice,yes_no_not_given,true_false_not_given,short_text,fill_in_blank,matching,table_selection,essay,speaking',
            'sections.*.groups.*.number_of_options' => 'nullable|integer|min:2|max:10',
            'sections.*.groups.*.answer_inputs_inside_content' => 'nullable|string',
            'sections.*.groups.*.split_questions_side_by_side' => 'nullable|string',
            'sections.*.groups.*.allow_drag_drop' => 'nullable|string',
            'sections.*.groups.*.questions' => 'nullable|array',
            'sections.*.groups.*.questions.*.id' => 'nullable|integer',
            'sections.*.groups.*.questions.*.content' => 'nullable|string',
            'sections.*.groups.*.questions.*.answer_content' => 'nullable|string',
            'sections.*.groups.*.questions.*.answer_label' => 'nullable|string',
            'sections.*.groups.*.questions.*.feedback' => 'nullable|string',
            'sections.*.groups.*.questions.*.point' => 'nullable|numeric|min:0',
            'sections.*.groups.*.questions.*.question_type' => 'nullable|string',
            // Answers for questions
            'sections.*.groups.*.questions.*.answers' => 'nullable|array',
            'sections.*.groups.*.questions.*.answers.*.content' => 'nullable|string',
            'sections.*.groups.*.questions.*.answers.*.feedback' => 'nullable|string',
            'sections.*.groups.*.questions.*.answers.*.is_correct' => 'nullable|boolean',
            // Direct questions (for speaking/writing)
            'sections.*.direct_questions' => 'nullable|array',
            'sections.*.direct_questions.*.id' => 'nullable|integer',
            'sections.*.direct_questions.*.content' => 'nullable|string',
            'sections.*.direct_questions.*.answer_content' => 'nullable|string',
            'sections.*.direct_questions.*.point' => 'nullable|numeric|min:0',
            'sections.*.direct_questions.*.feedback' => 'nullable|string',
            'sections.*.direct_questions.*.hint' => 'nullable|string',

            // dữ liệu filter theo kỹ năng
            'sections.*.exam_filters' => 'nullable|array',
            'sections.*.exam_filters.*' => 'nullable',
        ]);
        // Log validated data
        Log::info('Validated Data:', $validated);
        // Verify that exam_test_id belongs to the selected exam_id
        $examTest = ExamTest::where('id', $validated['exam_test_id'])
            ->where('exam_id', $validated['exam_id'])
            ->firstOrFail();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($skill->image && Storage::disk('public')->exists($skill->image)) {
                Storage::disk('public')->delete($skill->image);
            }

            $image = $request->file('image');
            $imagePath = $image->store('skills', 'public');
            $validated['image'] = $imagePath;
        }

        // Remove exam_id from validated data as it's not in the database table
        unset($validated['exam_id']);

        // Ensure is_active is always boolean (true or false)
        $validated['is_active'] = $request->has('is_active') ? true : false;

        // Ensure is_online is boolean
        $validated['is_online'] = $request->has('is_online') ? true : false;

        // Extract sections data before updating skill
        $sectionsData = $validated['sections'] ?? [];
        unset($validated['sections']);
        // Update skill basic info
        $skill->update($validated);

        // Process sections
        $this->processSections($skill, $sectionsData, $request);

        return redirect()->route('admin.skills.index')
            ->with('success', 'Skill đã được cập nhật thành công!');
    }

    /**
     * Process sections, question groups, and questions
     */
    private function processSections(ExamSkill $skill, array $sectionsData, Request $request)
    {

        $existingSectionIds = [];

        foreach ($sectionsData as $index => $sectionData) {
            $sectionId = $sectionData['id'] ?? null;

            // Get existing section to preserve old files if needed
            $existingSection = $sectionId ? $skill->sections()->find($sectionId) : null;
            $metadata = [
                'answer_inputs_inside_content' => ($sectionData['answer_inputs_inside_content'] ?? '0') === '1',
            ];

            // Handle audio file upload
            if ($request->hasFile("sections.{$index}.audio_file")) {
                $audioFile = $request->file("sections.{$index}.audio_file");

                // Delete old audio file if exists
                if ($existingSection && isset($existingSection->metadata['audio_file'])) {
                    Storage::disk('public')->delete($existingSection->metadata['audio_file']);
                }

                $audioPath = $audioFile->store('sections/audio', 'public');
                $metadata['audio_file'] = $audioPath;
            } else {
                // Keep existing audio file
                if ($existingSection && isset($existingSection->metadata['audio_file'])) {
                    $metadata['audio_file'] = $existingSection->metadata['audio_file'];
                }
            }

            // Prepare section data
            $sectionInfo = [
                'exam_skill_id' => $skill->id,
                'title' => $sectionData['title'] ?? '',
                'content' => $sectionData['content'] ?? '',
                'feedback' => $sectionData['feedback'] ?? '',
                'content_format' => $sectionData['content_format'] ?? 'text',
                'ui_layer' => isset($sectionData['ui_layer']) && in_array($sectionData['ui_layer'], ['1', '2'])
                    ? $sectionData['ui_layer']
                    : null,
                'metadata' => $metadata,
                'is_active' => true,
            ];
            // dd(2);
            // Update or create section
            if ($sectionId) {
                $section = $skill->sections()->findOrFail($sectionId);
                $section->update($sectionInfo);
                $existingSectionIds[] = $sectionId;
            } else {
                $section = $skill->sections()->create($sectionInfo);
                $existingSectionIds[] = $section->id;
            }

            $existingSectionIds[] = $section->id;
            if (array_key_exists('exam_filters', $sectionData)) {
                $this->syncSectionFilters(
                    $section,
                    $sectionData['exam_filters'] ?? []
                );
            }

            // Process question groups
            $groupsData = $sectionData['groups'] ?? [];
            $this->processQuestionGroups($section, $groupsData);

            // Process direct questions (for speaking/writing)
            $directQuestionsData = $sectionData['direct_questions'] ?? [];
            $this->processDirectQuestions($section, $directQuestionsData);
        }

        // Delete sections that are not in the request
        $skill->sections()->whereNotIn('id', $existingSectionIds)->delete();
    }

    /**
     * Process question groups
     */
    private function processQuestionGroups($section, array $groupsData)
    {
        $existingGroupIds = [];

        foreach ($groupsData as $index => $groupData) {
            $groupId = $groupData['id'] ?? null;

            $options = [
                'answer_inputs_inside_content' => ($groupData['answer_inputs_inside_content'] ?? '0') === '1',
                'split_questions_side_by_side' => ($groupData['split_questions_side_by_side'] ?? '0') === '1',
                'allow_drag_drop' => ($groupData['allow_drag_drop'] ?? '0') === '1',
            ];

            // Add number_of_options for table_selection type
            if (isset($groupData['number_of_options'])) {
                $options['number_of_options'] = (int) $groupData['number_of_options'];
            }

            $groupInfo = [
                'exam_section_id' => $section->id,
                'content' => $groupData['content'] ?? '',
                'question_type' => $groupData['question_type'] ?? 'multiple_choice',
                'options' => $options,
                'is_active' => true,
            ];

            if ($groupId) {
                $group = $section->questionGroups()->findOrFail($groupId);
                $group->update($groupInfo);
                $existingGroupIds[] = $groupId;
            } else {
                $group = $section->questionGroups()->create($groupInfo);
                $existingGroupIds[] = $group->id;
            }

            // Process questions
            $questionsData = $groupData['questions'] ?? [];
            $this->processQuestions($group, $questionsData);
        }

        // Delete groups that are not in the request
        $section->questionGroups()->whereNotIn('id', $existingGroupIds)->delete();
    }

    /**
     * Process questions
     */
    private function processQuestions($group, array $questionsData)
    {
        $existingQuestionIds = [];

        foreach ($questionsData as $questionData) {
            $questionId = $questionData['id'] ?? null;

            // Prepare metadata with answers array and other question-specific data
            $metadata = [
                'question_type' => $questionData['question_type'] ?? 'multiple_choice',
                'answer_label' => $questionData['answer_label'] ?? null,
            ];

            // Store answers in metadata if provided
            if (isset($questionData['answers']) && is_array($questionData['answers'])) {
                $metadata['answers'] = $questionData['answers'];
            }

            // Build question payload conservatively to avoid overwriting with empty values when keys are missing
            $questionInfo = [
                'exam_question_group_id' => $group->id,
                'exam_section_id' => null, // Belongs to group, not section
                'metadata' => $metadata,
                'is_active' => true,
            ];

            // Only set fields if they are actually present in request payload
            if (array_key_exists('content', $questionData)) {
                $questionInfo['content'] = $questionData['content'] ?? '';
            }
            if (array_key_exists('answer_content', $questionData)) {
                $questionInfo['answer_content'] = $questionData['answer_content']; // Backward compatibility
            }
            if (array_key_exists('feedback', $questionData)) {
                $questionInfo['feedback'] = $questionData['feedback'];
            }
            if (array_key_exists('point', $questionData)) {
                $questionInfo['point'] = $questionData['point'];
            }

            if ($questionId) {
                $question = $group->questions()->findOrFail($questionId);
                $question->update($questionInfo);
                $existingQuestionIds[] = $questionId;
            } else {
                // For new questions, ensure content is set
                if (!isset($questionInfo['content'])) {
                    $questionInfo['content'] = '';
                }
                $question = $group->questions()->create($questionInfo);
                $existingQuestionIds[] = $question->id;
            }
        }

        // Delete questions that are not in the request
        $group->questions()->whereNotIn('id', $existingQuestionIds)->delete();
    }

    /**
     * Process direct questions (for speaking/writing)
     */
    private function processDirectQuestions($section, array $questionsData)
    {
        $existingQuestionIds = [];

        foreach ($questionsData as $questionData) {
            $questionId = $questionData['id'] ?? null;

            // Build conservatively to avoid wiping existing values when inputs are missing
            $questionInfo = [
                'exam_section_id' => $section->id,
                'exam_question_group_id' => null, // Direct to section, not group
                'metadata' => [],
                'is_active' => true,
            ];

            if (array_key_exists('content', $questionData)) {
                $questionInfo['content'] = $questionData['content'] ?? '';
            }
            if (array_key_exists('answer_content', $questionData)) {
                $questionInfo['answer_content'] = $questionData['answer_content'];
            }
            if (array_key_exists('point', $questionData)) {
                $questionInfo['point'] = $questionData['point'];
            }
            if (array_key_exists('feedback', $questionData)) {
                $questionInfo['feedback'] = $questionData['feedback'];
            }
            if (array_key_exists('hint', $questionData)) {
                $questionInfo['hint'] = $questionData['hint'];
            }

            if ($questionId) {
                // Find only direct questions (not belonging to any group)
                $question = $section->questions()->whereNull('exam_question_group_id')->findOrFail($questionId);
                $question->update($questionInfo);
                $existingQuestionIds[] = $questionId;
            } else {
                // For new questions, ensure content is set
                if (!isset($questionInfo['content'])) {
                    $questionInfo['content'] = '';
                }
                $question = $section->questions()->create($questionInfo);
                $existingQuestionIds[] = $question->id;
            }
        }

        // Delete direct questions that are not in the request (only direct questions, not group questions)
        $section->questions()->whereNull('exam_question_group_id')->whereNotIn('id', $existingQuestionIds)->delete();
    }

    private function syncSectionFilters(ExamSection $section, array $filters)
    {
        $filterIds = [];

        foreach ($filters as $filterValue) {
            if (is_array($filterValue)) {
                foreach ($filterValue as $id) {
                    $filterIds[] = $id;
                }
            } else {
                $filterIds[] = $filterValue;
            }
        }

        // xoá trùng + sync
        $section->filters()->sync(array_unique($filterIds));
    }


    /**
     * Remove the specified skill.
     */
    public function destroy(ExamSkill $skill)
    {
        $skill->delete();

        return redirect()->route('admin.skills.index')
            ->with('success', 'Skill đã được xóa thành công!');
    }

    /**
     * Toggle skill active status.
     */
    public function toggleActive(ExamSkill $skill)
    {
        $skill->update([
            'is_active' => !$skill->is_active
        ]);

        return back()->with('success', 'Trạng thái skill đã được cập nhật!');
    }

    /**
     * Get tests by exam (AJAX)
     */
    public function getTestsByExam(Request $request)
    {
        $examId = $request->get('exam_id');

        $tests = ExamTest::where('exam_id', $examId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($tests);
    }
}
