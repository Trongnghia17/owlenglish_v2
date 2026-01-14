<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamCollection;

class ExamCollectionsSeeder extends Seeder
{
    public function run(): void
    {
        $collections = [
            [
                'name'   => 'Real Test OWL',
                'type'   => 'ielts',
                'status' => 1,
            ],
            [
                'name'   => 'Cambridge',
                'type'   => 'ielts',
                'status' => 1,
            ],
            [
                'name'   => 'Ready for IELTS',
                'type'   => 'ielts',
                'status' => 1,
            ],
            [
                'name'   => 'Trainer',
                'type'   => 'ielts',
                'status' => 1,
            ],
            [
                'name'   => 'Actual',
                'type'   => 'ielts',
                'status' => 1,
            ],
            [
                'name'   => 'Homework',
                'type'   => 'ielts',
                'status' => 1,
            ],
            [
                'name'   => 'Other',
                'type'   => 'ielts',
                'status' => 1,
            ],

            // TOEIC collections
            [
                'name'   => 'Longman',
                'type'   => 'toeic',
                'status' => 1,
            ],
            [
                'name'   => 'Barron',
                'type'   => 'toeic',
                'status' => 1,
            ],
            [
                'name'   => 'Essay',
                'type'   => 'toeic',
                'status' => 1,
            ],
            [
                'name'   => 'Real TOEIC',
                'type'   => 'toeic',
                'status' => 1,
            ],
            [
                'name'   => 'Actual Test',
                'type'   => 'toeic',
                'status' => 1,
            ],
            [
                'name'   => 'Cambridge',
                'type'   => 'toeic',
                'status' => 1,
            ],
            [
                'name'   => 'Other',
                'type'   => 'toeic',
                'status' => 1,
            ],
        ];

        foreach ($collections as $collection) {
            ExamCollection::updateOrCreate(
                [
                    'name' => $collection['name'],
                    'type' => $collection['type'],
                ],
                [
                    'status' => $collection['status'],
                ]
            );
        }
    }
}
