<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamFilter;
use Illuminate\Support\Str;

class ToeicExamFilterSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * Helper tạo filter
         */
        $create = function (
            string $name,
            string $type,
            ?int $parentId = null,
            int $sort = 0
        ) {
            return ExamFilter::create([
                'name'       => $name,
                'slug'       => Str::slug($name),
                'type'       => $type,
                'parent_id'  => $parentId,
                'exam_type'  => 'toeic',
                'sort_order' => $sort,
                'is_active'  => true,
            ]);
        };

        /**
         * =========================
         * 1. LISTENING
         * =========================
         */
        $listening = $create('Listening', 'skill', null, 1);

        $listeningPart = $create('Theo phần', 'group', $listening->id, 1);

        foreach (['Part 1', 'Part 2', 'Part 3', 'Part 4'] as $i => $part) {
            $create($part, 'value', $listeningPart->id, $i + 1);
        }

        /**
         * =========================
         * 2. READING
         * =========================
         */
        $reading = $create('Reading', 'skill', null, 2);

        $readingPart = $create('Theo phần', 'group', $reading->id, 1);

        foreach (['Part 5', 'Part 6', 'Part 7'] as $i => $part) {
            $create($part, 'value', $readingPart->id, $i + 1);
        }

        /**
         * =========================
         * 3. SPEAKING
         * =========================
         */
        $speaking = $create('Speaking', 'skill', null, 3);

        $speakingType = $create('Theo dạng', 'group', $speaking->id, 1);

        $speakingTypes = [
            'Read Aloud',
            'Describe a Picture',
            'Respond to Questions',
            'Respond using Given Information',
            'Suggest a Solution',
            'Express an Opinion',
        ];

        foreach ($speakingTypes as $i => $type) {
            $create($type, 'value', $speakingType->id, $i + 1);
        }

        /**
         * =========================
         * 4. WRITING
         * =========================
         */
        $writing = $create('Writing', 'skill', null, 4);

        // Theo dạng
        $writingType = $create('Theo dạng', 'group', $writing->id, 1);

        $writingTypes = [
            'Write a sentence based on a picture',
            'Respond to a written request',
            'Write an opinion essay',
        ];

        foreach ($writingTypes as $i => $type) {
            $create($type, 'value', $writingType->id, $i + 1);
        }

        // Theo phần
        $writingPart = $create('Theo phần', 'group', $writing->id, 2);

        foreach (['Task 1-3', 'Task 4', 'Task 5-6'] as $i => $task) {
            $create($task, 'value', $writingPart->id, $i + 1);
        }
    }
}
