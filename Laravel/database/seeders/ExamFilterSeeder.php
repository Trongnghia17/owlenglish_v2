<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamFilter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ExamFilterSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Helper
        |--------------------------------------------------------------------------
        */
        $create = function ($name, $type, $parentId = null, $order = 0) {
            return ExamFilter::create([
                'name'       => $name,
                'slug'       => Str::slug($name),
                'type'       => $type,
                'parent_id'  => $parentId,
                'sort_order' => $order,
                'is_active'  => true,
            ]);
        };

        /*
        |--------------------------------------------------------------------------
        | 1. LISTENING
        |--------------------------------------------------------------------------
        */
        $listening = $create('Listening', 'skill');

        // Theo dạng
        $listeningType = $create('Theo dạng', 'group', $listening->id);
        $listeningTypes = [
            'Multiple choice',
            'Matching',
            'Plan / map / diagram labelling',
            'Form completion',
            'Note completion',
            'Table completion',
            'Flow-chart completion',
            'Summary completion',
            'Sentence completion',
            'Short-answer questions',
        ];
        foreach ($listeningTypes as $i => $name) {
            $create($name, 'value', $listeningType->id, $i + 1);
        }

        // Theo phần
        $listeningSection = $create('Theo phần', 'group', $listening->id);
        foreach (['Section 1', 'Section 2', 'Section 3', 'Section 4'] as $i => $name) {
            $create($name, 'value', $listeningSection->id, $i + 1);
        }

        // Other
        $listeningOther = $create('Other', 'group', $listening->id);
        $create('Nghe chép chính tả', 'value', $listeningOther->id);

        /*
        |--------------------------------------------------------------------------
        | 2. READING
        |--------------------------------------------------------------------------
        */
        $reading = $create('Reading', 'skill');

        // Theo dạng
        $readingType = $create('Theo dạng', 'group', $reading->id);
        $readingTypes = [
            'Multiple choice',
            'True / False / Not Given',
            'Yes / No / Not Given',
            'Matching information',
            'Matching headings',
            'Matching features',
            'Matching sentence endings',
            'Sentence completion',
            'Summary completion',
            'Note completion',
            'Table completion',
            'Flow-chart completion',
            'Diagram label completion',
            'Short-answer questions',
        ];
        foreach ($readingTypes as $i => $name) {
            $create($name, 'value', $readingType->id, $i + 1);
        }

        // Theo phần
        $readingSection = $create('Theo phần', 'group', $reading->id);
        foreach (['Passage 1', 'Passage 2', 'Passage 3'] as $i => $name) {
            $create($name, 'value', $readingSection->id, $i + 1);
        }

        /*
        |--------------------------------------------------------------------------
        | 3. SPEAKING
        |--------------------------------------------------------------------------
        */
        $speaking = $create('Speaking', 'skill');

        // Theo phần
        $speakingPart = $create('Theo phần', 'group', $speaking->id);
        foreach (['Part 1', 'Part 2', 'Part 3'] as $i => $name) {
            $create($name, 'value', $speakingPart->id, $i + 1);
        }

        // Other
        $speakingOther = $create('Other', 'group', $speaking->id);
        $create('Lateral Practice', 'value', $speakingOther->id);

        /*
        |--------------------------------------------------------------------------
        | 4. WRITING
        |--------------------------------------------------------------------------
        */
        $writing = $create('Writing', 'skill');

        // Theo dạng (Task 1)
        $writingTask1 = $create('Theo dạng (Task 1)', 'group', $writing->id);
        $task1Types = [
            'Line Graph',
            'Bar Chart',
            'Pie Chart',
            'Table',
            'Mixed Graph',
            'Map',
            'Process',
        ];
        foreach ($task1Types as $i => $name) {
            $create($name, 'value', $writingTask1->id, $i + 1);
        }

        // Theo dạng (Task 2)
        $writingTask2 = $create('Theo dạng (Task 2)', 'group', $writing->id);
        $task2Types = [
            'Agree / Disagree',
            'Discussion',
            'Advantages and Disadvantages essay',
            'Problem and Solution essay',
            'Double question / Direct question',
            'Positive and negative',
        ];
        foreach ($task2Types as $i => $name) {
            $create($name, 'value', $writingTask2->id, $i + 1);
        }
    }
}
