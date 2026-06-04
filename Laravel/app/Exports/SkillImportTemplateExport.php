<?php

namespace App\Exports;

use App\Models\ExamSkill;
use App\Services\SkillExcelImportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SkillImportTemplateExport implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    public function __construct(private readonly ExamSkill $skill)
    {
    }

    public function headings(): array
    {
        return SkillExcelImportService::HEADINGS;
    }

    public function array(): array
    {
        if ($this->skill->isWriting() || $this->skill->isSpeaking()) {
            return $this->directQuestionRows();
        }

        if ($this->skill->isReading()) {
            return $this->readingMultipleChoiceRows();
        }

        return $this->listeningOrGenericRows();
    }

    public function title(): string
    {
        return 'quiz_import';
    }

    private function directQuestionRows(): array
    {
        return [
            $this->row([
                'section_no' => 1,
                'section_title' => 'Section 1',
                'question_content' => 'Describe the chart below.',
                'point' => 1,
                'direct_question_no' => 1,
                'sample_answer' => 'Sample answer or marking criteria',
                'question_feedback' => 'Question feedback',
                'hint' => 'Optional hint',
            ]),
        ];
    }

    private function listeningOrGenericRows(): array
    {
        return [
            $this->row([
                'section_no' => 1,
                'section_title' => 'Section 1',
                'section_content' => 'Reading passage or listening context',
                'group_no' => 1,
                'group_instructions' => 'Questions 1 - 2',
                'group_question_type' => 'multiple_choice',
                'question_no' => 1,
                'question_content' => 'Question 1 content',
                'question_type' => 'multiple_choice',
                'point' => 1,
                'answer_no' => 1,
                'answer_content' => 'Answer A',
                'is_correct' => 'yes',
            ]),
            $this->row([
                'section_no' => 1,
                'group_no' => 1,
                'question_no' => 1,
                'point' => 1,
                'answer_no' => 2,
                'answer_content' => 'Answer B',
                'is_correct' => 'no',
            ]),
        ];
    }

    private function readingMultipleChoiceRows(): array
    {
        $passage = <<<'TEXT'
READING PASSAGE 3

To catch a king

Anna Keay reviews Charles Spencer's book about the hunt for King Charles II during the English Civil War of the seventeenth century

Charles Spencer's latest book, To Catch a King, tells us the story of the hunt for King Charles II in the six weeks after his resounding defeat at the Battle of Worcester in September 1651. And what a story it is. After his father was executed by the Parliamentarians in 1649, the young Charles II sacrificed one of the very principles his father had died for and did a deal with Scots, thereby accepting Presbyterianism* as the national religion in return for being crowned King of Scots. His arrival in Edinburgh prompted the English Parliamentary army to invade Scotland in a pre-emptive strike. This was followed by a Scottish invasion of England. The two sides finally faced one another at Worcester in the west of England in 1651. After being comprehensively defeated on the meadows outside the city by the Parliamentarian army, the 21-year-old king found himself the subject of a national manhunt, with a huge sum offered for his capture, through a series of heart-poundingly close escapes, to evade the Parliamentarians before seeking refuge in France. For the next nine years, the penniless and defeated Charles wandered around Europe with only a small group of loyal supporters.

Years later, after his restoration as king, the 50-year-old Charles II requested a meeting with the writer and diarist Samuel Pepys. His intention when asking Pepys to commit his story to paper was to ensure that this most extraordinary episode was never forgotten. Over two three-hour sittings, the king related to him in great detail his personal recollections of the six weeks he had spent as a fugitive. As the king and secretary settled down (a scene that is surely a gift for a future scriptwriter), Charles commenced his story: 'After the battle was so absolutely lost as to be beyond hope of recovery, I began to think of the best way of saving myself.'

One of the joys of Spencer's book, a result not least of its use of Charles II's own narrative as well as those of his supporters, is just how close the reader gets to the action. The day-by-day retelling of the fugitives' doings provides delicious details: the cutting of the king's long hair with agricultural shears, the use of walnut leaves to dye his pale skin, and the day Charles spent lying on a branch of the great oak tree in Boscobel Wood as the Parliamentary soldiers scoured the forest floor below. Spencer draws out both the humour - such as the preposterous refusal of Charles's friend Henry Wilmot to adopt disguise on the grounds that it was beneath his dignity - and the emotional tension when the secret of the king's presence was cautiously revealed to his supporters.

Charles's adventures after losing the Battle of Worcester hide the uncomfortable truth that whilst almost everyone in England had been appalled by the execution of his father, they had not welcomed the arrival of his son with the Scots army, but had instead firmly bolted their doors. This was partly because he rode at the head of what looked like a foreign invasion force and partly because, after almost a decade of civil war, people were desperate to avoid it beginning again. This makes it all the more interesting that Charles II himself loved the story so much ever after. As well as retelling it to anyone who would listen, causing eye-rolling among courtiers, he set in train a series of initiatives to memorialise it. There was to be a new order of chivalry, the Knights of the Royal Oak. A series of enormous oil paintings depicting the episode were produced, including a two-metre-wide canvas of Boscobel Wood and a set of six similarly enormous paintings of the king on the run. In 1660, Charles II commissioned the artist John Michael Wright to paint a flying squadron of cherubs* carrying an oak tree to the heavens on the ceiling of his bedchamber. It is hard to imagine many other kings marking the lowest point in their life so enthusiastically, or indeed pulling off such an escape in the first place.

Charles Spencer is the perfect person to pass the story on to a new generation. His pacey, readable prose steers deftly clear of modern idioms and elegantly brings to life the details of the great tale. He has even-handed sympathy for both the fugitive king and the fierce republican regime that hunted him, and he succeeds in his desire to explore far more of the background of the story than previous books on the subject have done. Indeed, the opening third of the book is about how Charles II found himself at Worcester in the first place, which for some will be reason alone to read To Catch a King.

The tantalizing question left, in the end, is that of what it all meant. Would Charles II have been a different king had these six weeks never happened? The days and nights spent in hiding must have affected him in some way. Did the need to assume disguises, to survive on wit and charm alone, to use trickery and subterfuge to escape from tight corners help form him? This is the one area where the book doesn't quite hit the mark. Instead its depiction of Charles II in his final years as an ineffective, pleasure-loving monarch doesn't do justice to the man (neither is it accurate), or to the complexity of his character. But this one niggle aside, To Catch a King is an excellent read, and those who come to it knowing little of the famous tale will find they have a treat in store.

* Presbyterianism: part of the reformed Protestant religion
* cherub: an image of angelic children used in paintings
TEXT;

        $instructions = <<<'TEXT'
Questions 36-40
Choose the correct letter, A, B, C, or D.
Write the correct letter in boxes 36-40 on your answer sheet.
TEXT;

        $questions = [
            36 => [
                'content' => 'What is the reviewer\'s main purpose in the first paragraph?',
                'correct' => 2,
                'feedback' => 'Đáp án: B. Đoạn 1 giới thiệu các hoàn cảnh và mốc sự kiện dẫn đến việc Charles II phải trốn chạy sau thất bại tại Worcester.',
                'answers' => [
                    'to describe what happened during the Battle of Worcester',
                    'to give an account of the circumstances leading to Charles II\'s escape',
                    'to provide details of the Parliamentarians\' political views',
                    'to compare Charles II\'s beliefs with those of his father',
                ],
            ],
            37 => [
                'content' => 'Why does the reviewer include examples of the fugitives\' behaviour in the third paragraph?',
                'correct' => 3,
                'feedback' => 'Đáp án: C. Các ví dụ trong đoạn 3 minh họa cách những sự kiện trong sáu tuần được kể lại sống động bằng nhiều chi tiết cụ thể.',
                'answers' => [
                    'to explain how close Charles II came to losing his life',
                    'to suggest that Charles II\'s supporters were badly prepared',
                    'to illustrate how the events of the six weeks are brought to life',
                    'to argue that certain aspects are not as well known as they should be',
                ],
            ],
            38 => [
                'content' => 'What point does the reviewer make about Charles II in the fourth paragraph?',
                'correct' => 1,
                'feedback' => 'Đáp án: A. Đoạn 4 nhấn mạnh Charles II lại rất thích và còn tưởng niệm một câu chuyện vốn gắn với thất bại và thời điểm thấp nhất của ông.',
                'answers' => [
                    'He chose to celebrate what was essentially a defeat.',
                    'He misunderstood the motives of his opponents.',
                    'He aimed to restore people\'s faith in the monarchy.',
                    'He was driven by a desire to be popular.',
                ],
            ],
            39 => [
                'content' => 'What does the reviewer say about Charles Spencer in the fifth paragraph?',
                'correct' => 2,
                'feedback' => 'Đáp án: B. Đoạn 5 nói Spencer có sự cảm thông công bằng với cả vị vua đang trốn chạy và chế độ cộng hòa đang truy lùng ông.',
                'answers' => [
                    'His decision to write the book comes as a surprise.',
                    'He takes an unbiased approach to the subject matter.',
                    'His descriptions of events would be better if they included more detail.',
                    'He chooses language that is suitable for a twenty-first-century audience.',
                ],
            ],
            40 => [
                'content' => 'When the reviewer says the book "doesn\'t quite hit the mark", she is making the point that',
                'correct' => 4,
                'feedback' => 'Đáp án: D. Đoạn cuối đặt câu hỏi liệu sáu tuần trốn chạy có ảnh hưởng lâu dài đến Charles II hay không, nhưng sách chưa phân tích thỏa đáng điểm này.',
                'answers' => [
                    'it overlooks the impact of events on ordinary people.',
                    'it lacks an analysis of prevalent views on monarchy.',
                    'it omits any references to the deceit practised by Charles II during his time in hiding.',
                    'it fails to address whether Charles II\'s experiences had a lasting influence on him.',
                ],
            ],
        ];

        $rows = [];

        foreach ($questions as $questionNo => $question) {
            foreach ($question['answers'] as $answerIndex => $answerContent) {
                $isFirstRow = $questionNo === 36 && $answerIndex === 0;
                $isCorrect = ($answerIndex + 1) === $question['correct'];

                $rows[] = $this->row([
                    'section_no' => 1,
                    'section_title' => $isFirstRow ? 'Reading Passage 3 - To catch a king' : '',
                    'section_content' => $isFirstRow ? $passage : '',
                    'group_no' => 1,
                    'group_instructions' => $isFirstRow ? $instructions : '',
                    'group_question_type' => 'multiple_choice',
                    'question_no' => $questionNo,
                    'question_content' => $answerIndex === 0 ? $question['content'] : '',
                    'question_type' => 'multiple_choice',
                    'point' => 1,
                    'answer_no' => $answerIndex + 1,
                    'answer_content' => $answerContent,
                    'answer_feedback' => $isCorrect ? $question['feedback'] : '',
                    'is_correct' => $isCorrect ? 'yes' : 'no',
                    'question_feedback' => $isCorrect ? $question['feedback'] : '',
                ]);
            }
        }

        return $rows;
    }

    private function row(array $values): array
    {
        return array_map(
            fn(string $heading): mixed => $values[$heading] ?? '',
            SkillExcelImportService::HEADINGS
        );
    }
}
