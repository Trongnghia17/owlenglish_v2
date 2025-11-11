<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\ExamTest;
use App\Models\ExamSkill;
use App\Models\ExamSection;
use App\Models\ExamQuestionGroup;
use App\Models\ExamQuestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExamSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // ==================== IELTS ACADEMIC ====================
            $ieltsExam = Exam::create([
                'name' => 'IELTS Academic',
                'type' => 'ielts',
                'description' => 'Bài thi IELTS Academic dành cho mục đích học tập và nghiên cứu',
                'is_active' => true,
            ]);

            // Test 1
            $test1 = $ieltsExam->tests()->create([
                'name' => 'IELTS Academic Test 1',
                'description' => 'Đề thi mẫu số 1 - Full test',
                'order' => 1,
                'is_active' => true,
            ]);

            // ===== READING SKILL =====
            $reading = $test1->skills()->create([
                'skill_type' => 'reading',
                'name' => 'Reading',
                'description' => 'Academic Reading Test - 3 passages',
                'time_limit' => 60,
                'order' => 1,
                'is_active' => true,
            ]);

            // Reading - Passage 1
            $readingSection1 = $reading->sections()->create([
                'title' => 'Passage 1: Climate Change and Global Warming',
                'content' => 'Climate change refers to long-term shifts in temperatures and weather patterns. These shifts may be natural, but since the 1800s, human activities have been the main driver of climate change, primarily due to the burning of fossil fuels like coal, oil, and gas.

Burning fossil fuels generates greenhouse gas emissions that act like a blanket wrapped around the Earth, trapping the sun\'s heat and raising temperatures. The main greenhouse gases that are causing climate change include carbon dioxide and methane. These come from using gasoline for driving a car or coal for heating a building, for example.

Clearing land and cutting down forests can also release carbon dioxide. Agriculture, oil and gas operations are major sources of methane emissions. Energy, industry, transport, buildings, agriculture and land use are among the main sectors causing greenhouse gases.',
                'feedback' => 'Đọc kỹ toàn bộ đoạn văn trước khi trả lời câu hỏi',
                'content_format' => 'text',
                'order' => 1,
                'is_active' => true,
            ]);

            // Question Group 1: Multiple Choice
            $qGroup1 = $readingSection1->questionGroups()->create([
                'content' => null,
                'question_type' => 'multiple_choice',
                'answer_layout' => 'standard',
                'instructions' => 'Choose the correct letter A, B, C or D.',
                'order' => 1,
                'is_active' => true,
            ]);

            $qGroup1->questions()->create([
                'content' => 'What is the main cause of climate change since the 1800s?',
                'answer_content' => 'A. Natural weather patterns',
                'is_correct' => false,
                'point' => 1.0,
                'order' => 1,
            ]);

            $qGroup1->questions()->create([
                'content' => 'What is the main cause of climate change since the 1800s?',
                'answer_content' => 'B. Human activities and fossil fuel burning',
                'is_correct' => true,
                'point' => 1.0,
                'feedback' => 'Correct! The passage states that human activities have been the main driver since the 1800s.',
                'order' => 2,
            ]);

            $qGroup1->questions()->create([
                'content' => 'What is the main cause of climate change since the 1800s?',
                'answer_content' => 'C. Deforestation only',
                'is_correct' => false,
                'point' => 1.0,
                'order' => 3,
            ]);

            $qGroup1->questions()->create([
                'content' => 'What is the main cause of climate change since the 1800s?',
                'answer_content' => 'D. Agricultural practices',
                'is_correct' => false,
                'point' => 1.0,
                'order' => 4,
            ]);

            // Question Group 2: True/False/Not Given
            $qGroup2 = $readingSection1->questionGroups()->create([
                'content' => null,
                'question_type' => 'true_false_not_given',
                'answer_layout' => 'standard',
                'instructions' => 'Do the following statements agree with the information given in the passage? Write TRUE, FALSE or NOT GIVEN.',
                'order' => 2,
                'is_active' => true,
            ]);

            $qGroup2->questions()->create([
                'content' => 'Greenhouse gases trap heat from the sun.',
                'answer_content' => 'TRUE',
                'is_correct' => true,
                'point' => 1.0,
                'order' => 1,
            ]);

            $qGroup2->questions()->create([
                'content' => 'Carbon dioxide is the only greenhouse gas.',
                'answer_content' => 'FALSE',
                'is_correct' => true,
                'point' => 1.0,
                'feedback' => 'The passage mentions both CO2 and methane.',
                'order' => 2,
            ]);

            $qGroup2->questions()->create([
                'content' => 'Solar panels can reduce greenhouse gas emissions.',
                'answer_content' => 'NOT GIVEN',
                'is_correct' => true,
                'point' => 1.0,
                'feedback' => 'This information is not mentioned in the passage.',
                'order' => 3,
            ]);

            // ===== LISTENING SKILL =====
            $listening = $test1->skills()->create([
                'skill_type' => 'listening',
                'name' => 'Listening',
                'description' => 'Academic Listening Test - 4 sections',
                'time_limit' => 40,
                'order' => 2,
                'is_active' => true,
            ]);

            // Listening - Section 1
            $listeningSection1 = $listening->sections()->create([
                'title' => 'Section 1: Telephone Conversation',
                'content' => 'You will hear a telephone conversation about booking a hotel room.',
                'feedback' => 'Lắng nghe cẩn thận các chi tiết về tên, số điện thoại và địa chỉ',
                'content_format' => 'audio',
                'audio_file' => 'exam-audio/ielts-test1-section1.mp3', // Placeholder
                'order' => 1,
                'is_active' => true,
            ]);

            // Question Group: Form Completion
            $listeningQGroup1 = $listeningSection1->questionGroups()->create([
                'content' => 'Complete the form below. Write NO MORE THAN TWO WORDS AND/OR A NUMBER for each answer.',
                'question_type' => 'fill_in_blank',
                'answer_layout' => 'inline',
                'instructions' => 'Listen and complete the booking form.',
                'order' => 1,
                'is_active' => true,
            ]);

            $listeningQGroup1->questions()->create([
                'content' => 'Name: John _____',
                'answer_content' => 'Smith',
                'is_correct' => true,
                'point' => 1.0,
                'order' => 1,
            ]);

            $listeningQGroup1->questions()->create([
                'content' => 'Phone: _____',
                'answer_content' => '0412345678',
                'is_correct' => true,
                'point' => 1.0,
                'order' => 2,
            ]);

            $listeningQGroup1->questions()->create([
                'content' => 'Arrival date: _____ May',
                'answer_content' => '15',
                'is_correct' => true,
                'point' => 1.0,
                'order' => 3,
            ]);

            // ===== WRITING SKILL =====
            $writing = $test1->skills()->create([
                'skill_type' => 'writing',
                'name' => 'Writing',
                'description' => 'Academic Writing - 2 tasks',
                'time_limit' => 60,
                'order' => 3,
                'is_active' => true,
            ]);

            // Writing Task 1
            $writingSection1 = $writing->sections()->create([
                'title' => 'Writing Task 1',
                'content' => 'You should spend about 20 minutes on this task.',
                'feedback' => 'Write at least 150 words. Describe the main features and make comparisons.',
                'content_format' => 'text',
                'order' => 1,
                'is_active' => true,
            ]);

            $writingQGroup1 = $writingSection1->questionGroups()->create([
                'content' => 'The chart below shows the percentage of households in different income groups in a country from 2000 to 2020.

Summarise the information by selecting and reporting the main features, and make comparisons where relevant.',
                'question_type' => 'essay',
                'answer_layout' => 'standard',
                'instructions' => 'Write at least 150 words.',
                'order' => 1,
                'is_active' => true,
            ]);

            $writingQGroup1->questions()->create([
                'content' => 'Your answer for Task 1',
                'answer_content' => null,
                'is_correct' => true,
                'point' => 33.33,
                'feedback' => 'Ensure you describe trends, make comparisons, and write in academic style.',
                'order' => 1,
            ]);

            // Writing Task 2
            $writingSection2 = $writing->sections()->create([
                'title' => 'Writing Task 2',
                'content' => 'You should spend about 40 minutes on this task.',
                'feedback' => 'Write at least 250 words. Give your opinion and support it with examples.',
                'content_format' => 'text',
                'order' => 2,
                'is_active' => true,
            ]);

            $writingQGroup2 = $writingSection2->questionGroups()->create([
                'content' => 'Some people think that universities should provide graduates with the knowledge and skills needed in the workplace. Others think that the true function of a university should be to give access to knowledge for its own sake, regardless of whether the course is useful to an employer.

What, in your opinion, should be the main function of a university?',
                'question_type' => 'essay',
                'answer_layout' => 'standard',
                'instructions' => 'Give reasons for your answer and include any relevant examples from your own knowledge or experience. Write at least 250 words.',
                'order' => 1,
                'is_active' => true,
            ]);

            $writingQGroup2->questions()->create([
                'content' => 'Your answer for Task 2',
                'answer_content' => null,
                'is_correct' => true,
                'point' => 66.67,
                'feedback' => 'Present a clear position, develop your arguments, and use appropriate examples.',
                'order' => 1,
            ]);

            // ===== SPEAKING SKILL =====
            $speaking = $test1->skills()->create([
                'skill_type' => 'speaking',
                'name' => 'Speaking',
                'description' => 'Speaking Test - 3 parts',
                'time_limit' => 15,
                'order' => 4,
                'is_active' => true,
            ]);

            // Speaking Part 1
            $speakingSection1 = $speaking->sections()->create([
                'title' => 'Part 1: Introduction and Interview',
                'content' => 'The examiner will ask you general questions about yourself and familiar topics.',
                'feedback' => 'Answer naturally and give extended responses.',
                'content_format' => 'text',
                'order' => 1,
                'is_active' => true,
            ]);

            $speakingQGroup1 = $speakingSection1->questionGroups()->create([
                'content' => null,
                'question_type' => 'speaking',
                'answer_layout' => 'standard',
                'instructions' => 'Answer the following questions (4-5 minutes)',
                'order' => 1,
                'is_active' => true,
            ]);

            $speakingQGroup1->questions()->createMany([
                [
                    'content' => 'Can you tell me about your hometown?',
                    'point' => 10.0,
                    'order' => 1,
                ],
                [
                    'content' => 'What do you like most about living there?',
                    'point' => 10.0,
                    'order' => 2,
                ],
                [
                    'content' => 'Do you work or are you a student?',
                    'point' => 10.0,
                    'order' => 3,
                ],
                [
                    'content' => 'What do you enjoy about your job/studies?',
                    'point' => 10.0,
                    'order' => 4,
                ],
            ]);

            // ==================== TOEIC ====================
            $toeicExam = Exam::create([
                'name' => 'TOEIC Listening & Reading',
                'type' => 'toeic',
                'description' => 'TOEIC Listening and Reading Test',
                'is_active' => true,
            ]);

            $toeicTest1 = $toeicExam->tests()->create([
                'name' => 'TOEIC Practice Test 1',
                'description' => 'Full TOEIC L&R practice test',
                'order' => 1,
                'is_active' => true,
            ]);

            // TOEIC Listening
            $toeicListening = $toeicTest1->skills()->create([
                'skill_type' => 'listening',
                'name' => 'Listening',
                'description' => 'TOEIC Listening - 100 questions',
                'time_limit' => 45,
                'order' => 1,
                'is_active' => true,
            ]);

            $toeicListeningPart1 = $toeicListening->sections()->create([
                'title' => 'Part 1: Photographs',
                'content' => 'For each question, you will see a photograph and hear four statements. Choose the statement that best describes what you see.',
                'content_format' => 'audio',
                'audio_file' => 'exam-audio/toeic-part1.mp3',
                'order' => 1,
                'is_active' => true,
            ]);

            // TOEIC Reading
            $toeicReading = $toeicTest1->skills()->create([
                'skill_type' => 'reading',
                'name' => 'Reading',
                'description' => 'TOEIC Reading - 100 questions',
                'time_limit' => 75,
                'order' => 2,
                'is_active' => true,
            ]);

            $toeicReadingPart5 = $toeicReading->sections()->create([
                'title' => 'Part 5: Incomplete Sentences',
                'content' => 'Choose the best answer to complete each sentence.',
                'content_format' => 'text',
                'order' => 1,
                'is_active' => true,
            ]);

            $toeicQGroup = $toeicReadingPart5->questionGroups()->create([
                'content' => null,
                'question_type' => 'multiple_choice',
                'answer_layout' => 'standard',
                'instructions' => 'Select the correct answer (A, B, C, or D)',
                'order' => 1,
                'is_active' => true,
            ]);

            $toeicQGroup->questions()->create([
                'content' => 'The meeting has been _____ until next week.',
                'answer_content' => 'A. postponed',
                'is_correct' => true,
                'point' => 1.0,
                'order' => 1,
            ]);

            $toeicQGroup->questions()->create([
                'content' => 'The meeting has been _____ until next week.',
                'answer_content' => 'B. postpone',
                'is_correct' => false,
                'point' => 1.0,
                'order' => 2,
            ]);

            $toeicQGroup->questions()->create([
                'content' => 'The meeting has been _____ until next week.',
                'answer_content' => 'C. postponing',
                'is_correct' => false,
                'point' => 1.0,
                'order' => 3,
            ]);

            $toeicQGroup->questions()->create([
                'content' => 'The meeting has been _____ until next week.',
                'answer_content' => 'D. postpones',
                'is_correct' => false,
                'point' => 1.0,
                'order' => 4,
            ]);

            echo "✅ Exam seeder completed!\n";
            echo "   - Created " . Exam::count() . " exams\n";
            echo "   - Created " . ExamTest::count() . " tests\n";
            echo "   - Created " . ExamSkill::count() . " skills\n";
            echo "   - Created " . ExamSection::count() . " sections\n";
            echo "   - Created " . ExamQuestionGroup::count() . " question groups\n";
            echo "   - Created " . ExamQuestion::count() . " questions\n";
        });
    }
}
