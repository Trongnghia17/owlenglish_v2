<?php

namespace App\Exports;

use App\Models\ExamSkill;
use App\Services\SkillExcelImportService;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\RichText\RichText;

class SkillImportTemplateExport implements WithMultipleSheets
{
    private const SECTION_CONTENT_BOLD_PHRASES = [
        'the circumstances leading to Charles II\'s escape',
        'examples of the fugitives\' behaviour',
        'He chose to celebrate what was essentially a defeat.',
        'He takes an unbiased approach',
        'Would Charles II have been a different king had these six weeks never happened?',
        'experiences no such consequences',
        'gene known as APoB',
        'about six months of fasting',
        'no evidence of significant loss of bone density',
        'problem-solving abilities',
        'expectations for intellectual performances that differ',
        'miss the point of what others are saying',
        'hospital fever',
        'glass, steel and air-conditioned skyscrapers',
        '24 air changes an hour',
        'relentlessly and aggressively',
        'does not require generated electricity',
        'water scarcity',
        'social mission',
        'return of thylacines to Tasmania',
        'forest disturbance',
        'biodiversity is being lost',
        'they tend to be linear',
        'such as overfeeding',
        'undistracted by temptations and undeterred by challenges',
        'transporting wool from Australia to Britain',
        'excellent navigator',
        'Badly damaged in a gale',
        'training ship',
        'suffered from fire',
        'human error as a contributory factor',
        'initiatives for car-sharing become much more viable',
        'reduce vehicle ownership by 43 percent',
        'average annual mileage double',
        'capacity for creativity',
        'follow rules and take turns',
        'now live in cities',
        'risk to do with traffic',
        'victims of crime',
        'greater competition in academic learning',
        'evidence to base policies on',
        'update the details they gave on a regular basis',
        'effect of each business on the environment',
        'rugby captain Tana Umaga',
        'locations chosen for blockbuster films',
        'according to the season',
        'links to accommodation',
        'submit a blog',
        'initial application online',
        'attend a Walk-In Day',
        'pass a swimming test',
        'Verbal references are then requested',
        'recruitment pool',
        'attend a full interview',
        'emergency procedures',
        'Two hydraulic steel gates',
        'A hydraulic clamp is removed',
        'rotate the central axle',
        'remain level',
        'passes straight onto the aqueduct',
        'under this wall via a tunnel',
        'pair of locks',
        'history of childhood',
        'miniature adults',
        'industrialisation created a new demand for child labour',
        'Factory Act of 1833',
        'play and education',
        'classroom',
    ];

    public function __construct(private readonly ExamSkill $skill)
    {
    }

    public function sheets(): array
    {
        if ($this->skill->isWriting() || $this->skill->isSpeaking()) {
            return [
                new SkillImportTemplateSheetExport('Direct Questions', $this->directQuestionRows()),
            ];
        }

        if ($this->skill->isReading()) {
            return [
                new SkillImportTemplateSheetExport('Multiple Choice', $this->readingMultipleChoiceRows()),
                new SkillImportTemplateSheetExport('True False Not Given', $this->readingTrueFalseNotGivenRows()),
                new SkillImportTemplateSheetExport('Yes No Not Given', $this->readingYesNoNotGivenRows()),
                new SkillImportTemplateSheetExport('Matching Information', $this->readingMatchingInformationRows()),
                new SkillImportTemplateSheetExport('Matching Headings', $this->readingMatchingHeadingsRows()),
                new SkillImportTemplateSheetExport('Matching Features', $this->readingMatchingFeaturesRows()),
                new SkillImportTemplateSheetExport('Matching Sentence Endings', $this->readingMatchingSentenceEndingsRows()),
                new SkillImportTemplateSheetExport('Sentence Completion', $this->readingSentenceCompletionRows()),
                new SkillImportTemplateSheetExport('Summary Completion', $this->readingSummaryCompletionRows()),
                new SkillImportTemplateSheetExport('Note Completion', $this->readingNoteCompletionRows()),
                new SkillImportTemplateSheetExport('Table Completion', $this->readingTableCompletionRows()),
                new SkillImportTemplateSheetExport('Flow-chart Completion', $this->readingFlowChartCompletionRows()),
                new SkillImportTemplateSheetExport('Diagram Label Completion', $this->readingDiagramLabelCompletionRows()),
                new SkillImportTemplateSheetExport('Short-answer Questions', $this->readingShortAnswerQuestionRows()),
            ];
        }

        return [
            new SkillImportTemplateSheetExport('Quiz Import', $this->listeningOrGenericRows()),
        ];
    }

    public function headings(): array
    {
        return SkillExcelImportService::HEADINGS;
    }

    public function array(): array
    {
        return array_merge(...array_map(
            fn(SkillImportTemplateSheetExport $sheet): array => $sheet->array(),
            $this->sheets()
        ));
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

    private function readingYesNoNotGivenRows(): array
    {
        $passage = <<<'TEXT'
The concept of intelligence

A
Looked at in one way, everyone knows what intelligence is; looked at in another way, no one does. In other words, people all have unconscious notions - known as 'implicit theories' - of intelligence, but no one knows for certain what it actually is. This chapter addresses how people conceptualize intelligence, whatever it may actually be.

But why should we even care what people think intelligence is, as opposed only to valuing whatever it actually is? There are at least four reasons people's conceptions of intelligence matter.

B
First, implicit theories of intelligence drive the way in which people perceive and evaluate their own intelligence and that of others. To better understand the judgments people make about their own and others' abilities, it is useful to learn about people's implicit theories. For example, parents' implicit theories of their children's language development will determine at what ages they will be willing to make various corrections in their children's speech. More generally, parents' implicit theories of intelligence will determine at what ages they believe their children are ready to perform various cognitive tasks. Job interviewers will make hiring decisions on the basis of their implicit theories of intelligence. People will decide who to be friends with on the basis of such theories. In sum, knowledge about implicit theories of intelligence is important because this knowledge is so often used by people to make judgments in the course of their everyday lives.

C
Second, the implicit theories of scientific investigators ultimately give rise to their explicit theories. Thus it is useful to find out what these implicit theories are. Implicit theories provide a framework that is useful in defining the general scope of a phenomenon - especially a not-well-understood phenomenon. These implicit theories can suggest what aspects of the phenomenon have been more or less attended to in previous investigations.

D
Third, implicit theories can be useful when an investigator suspects that existing explicit theories are wrong or misleading. If an investigation of implicit theories reveals little correspondence between the extant implicit and explicit theories, the implicit theories may be wrong. But the possibility also needs to be taken into account that the explicit theories are wrong and in need of correction or supplementation. For example, some implicit theories of intelligence suggest the need for expansion of some of our explicit theories of the construct.

E
Finally, understanding implicit theories of intelligence can help elucidate developmental and cross-cultural differences. As mentioned earlier, people have expectations for intellectual performances that differ for children of different ages. How these expectations differ is in part a function of culture. For example, expectations for children who participate in Western-style schooling are almost certain to be different from those for children who do not participate in such schooling.

F
I have suggested that there are three major implicit theories of how intelligence relates to society as a whole (Sternberg, 1997). These might be called Hamiltonian, Jeffersonian, and Jacksonian. These views are not based strictly, but rather, loosely, on the philosophies of Alexander Hamilton, Thomas Jefferson, and Andrew Jackson, three great statesmen in the history of the United States.

G
The Hamiltonian view, which is similar to the Platonic view, is that people are born with different levels of intelligence and that those who are less intelligent need the good offices of the more intelligent to keep them in line, whether they are called government officials or, in Plato's term, philosopher-kings. Herrnstein and Murray (1994) seem to have shared this belief when they wrote about the emergence of a cognitive (high-IQ) elite, which eventually would have to take responsibility for the largely irresponsible masses of non-elite (low-IQ) people who cannot take care of themselves. Left to themselves, the unintelligent would create, as they always have created, a kind of chaos.

H
The Jeffersonian view is that people should have equal opportunities, but they do not necessarily avail themselves equally of these opportunities and are not necessarily equally rewarded for their accomplishments. People are rewarded for what they accomplish, if given equal opportunity. Low achievers are not rewarded to the same extent as high achievers. In the Jeffersonian view, the goal of education is not to favor or foster an elite, as in the Hamiltonian tradition, but rather to allow children the opportunities to make full use of the skills they have. My own views are similar to these (Sternberg, 1997).

I
The Jacksonian view is that all people are equal, not only as human beings but in terms of their competencies - that one person would serve as well as another in government or on a jury or in almost any position of responsibility. In this view of democracy, people are essentially intersubstitutable except for specialized skills, all of which can be learned. In this view, we do not need or want any institutions that might lead to favoring one group over another.

J
Implicit theories of intelligence and of the relationship of intelligence to society perhaps need to be considered more carefully than they have been because they often serve as underlying presuppositions for explicit theories and even experimental designs that are then taken as scientific contributions. Until scholars are able to discuss their implicit theories and thus their assumptions, they are likely to miss the point of what others are saying when discussing their explicit theories and their data.
TEXT;

        $instructions = <<<'TEXT'
Questions 4-6
Do the following statements agree with the claims of the writer in Reading Passage 1?
In boxes 4-6 on your answer sheet, write

YES if the statement agrees with the claims of the writer
NO if the statement contradicts the claims of the writer
NOT GIVEN if it is impossible to say what the writer thinks about this
TEXT;

        $questions = [
            4 => [
                'content' => 'Slow language development in children is likely to prove disappointing to their parents.',
                'answer' => 'Not Given',
                'feedback' => 'Đáp án: NOT GIVEN. Phần thông tin về slow language development không xuất hiện và không được nhắc đến trong bài.',
            ],
            5 => [
                'content' => 'People\'s expectations of what children should gain from education are universal.',
                'answer' => 'No',
                'feedback' => 'Đáp án: NO. Đoạn E nói kỳ vọng về thành tích trí tuệ của trẻ khác nhau theo độ tuổi và một phần do văn hóa.',
            ],
            6 => [
                'content' => 'Scholars may discuss theories without fully understanding each other.',
                'answer' => 'Yes',
                'feedback' => 'Đáp án: YES. Đoạn J nói nếu học giả không thảo luận các giả định ngầm, họ có thể bỏ lỡ luận điểm của người khác.',
            ],
        ];

        $rows = [];

        foreach ($questions as $questionNo => $question) {
            $isFirstRow = $questionNo === 4;

            $rows[] = $this->row([
                'section_no' => 3,
                'section_title' => $isFirstRow ? 'Reading Passage 1 - The concept of intelligence' : '',
                'section_content' => $isFirstRow ? $passage : '',
                'group_no' => 1,
                'group_instructions' => $isFirstRow ? $instructions : '',
                'group_question_type' => 'yes_no_not_given',
                'question_no' => $questionNo,
                'question_content' => $question['content'],
                'question_type' => 'yes_no_not_given',
                'point' => 1,
                'answer_no' => 1,
                'answer_content' => $question['answer'],
                'answer_feedback' => $question['feedback'],
                'is_correct' => 'yes',
                'question_feedback' => $question['feedback'],
            ]);
        }

        return $rows;
    }

    private function readingTrueFalseNotGivenRows(): array
    {
        $passage = <<<'TEXT'
Why we need to protect polar bears

Polar bears are being increasingly threatened by the effects of climate change, but their disappearance could have far-reaching consequences. They are uniquely adapted to the extreme conditions of the Arctic Circle, where temperatures can reach -40°C. One reason for this is that they have up to 11 centimetres of fat underneath their skin. Humans with comparative levels of adipose tissue would be considered obese and would be likely to suffer from diabetes and heart disease. Yet the polar bear experiences no such consequences.

A 2014 study by Shi Ping Liu and colleagues sheds light on this mystery. They compared the genetic structure of polar bears with that of their closest relatives from a warmer climate, the brown bears. This allowed them to determine the genes that have allowed polar bears to survive in one of the toughest environments on Earth. Liu and his colleagues found the polar bears had a gene known as APoB, which reduces levels of low-density lipoproteins (LDLs) - a form of 'bad' cholesterol. In humans, mutations of this gene are associated with increased risk of heart disease. Polar bears may therefore be an important study model to understand heart disease in humans.

The genome of the polar bear may also provide the solution for another condition, one that particularly affects our older generation: osteoporosis. This is a disease where bones show reduced density, usually caused by insufficient exercise, reduced calcium intake or food starvation. Bone tissue is constantly being remodelled, meaning that bone is added or removed, depending on nutrient availability and the stress that the bone is under. Female polar bears, however, undergo extreme conditions during every pregnancy. Once autumn comes around, these females will dig maternity dens in the snow and will remain there throughout the winter, both before and after the birth of their cubs. This process results in about six months of fasting, where the female bears have to keep themselves and their cubs alive, depleting their own calcium and calorie reserves. Despite this, their bones remain strong and dense.

Physiologists Alanda Lennox and Allen Goodship found an explanation for this paradox in 2008. They discovered that pregnant bears were able to increase the density of their bones before they started to build their dens. In addition, six months later, when they finally emerged from the den with their cubs, there was no evidence of significant loss of bone density. Hibernating brown bears do not have this capacity and must therefore resort to major bone reformation in the following spring. If the mechanism of bone remodelling in polar bears can be understood, many bedridden humans, and even astronauts, could potentially benefit.

The medical benefits of the polar bear for humanity certainly have their importance in our conservation efforts, but these should not be the only factors taken into consideration. We tend to want to protect animals we think are intelligent and possess emotions, such as elephants and primates. Bears, on the other hand, seem to be perceived as stupid and in many cases violent. And yet anecdotal evidence from the field challenges those assumptions, suggesting for example that polar bears have good problem-solving abilities. A male bear called GoGo in Tennoji Zoo, Osaka, has even been observed making use of a tool to manipulate his environment. The bear used a tree branch on multiple occasions to dislodge a piece of meat hung out of his reach. Problem-solving ability has also been witnessed in wild polar bears, although not as obviously as with GoGo. A calculated move by a male bear involved running and jumping onto barrels in an attempt to get to a photographer standing on a platform four metres high.

In other studies, such as one by Alison Ames in 2008, polar bears showed deliberate and focused manipulation. For example, Ames observed bears putting objects in piles and then knocking them over in what appeared to be a game. The study demonstrates that bears are capable of agile and thought-out behaviours. These examples suggest bears have greater creativity and problem-solving abilities than previously thought.

As for emotions, while the evidence is once again anecdotal, many bears have been seen to hit out at ice and snow - seemingly out of frustration - when they have just missed out on a kill. Moreover, polar bears can form unusual relationships with other species, including playing with the dogs used to pull sleds in the Arctic. Remarkably, one hand-raised polar bear called Agee has formed a close relationship with her owner Mark Dumas to the point where they even swim together. This is even more astonishing since polar bears are known to actively hunt humans in the wild.

If climate change were to lead to their extinction, this would mean not only the loss of potential breakthroughs in human medicine, but more importantly, the disappearance of an intelligent, majestic animal.
TEXT;

        $instructions = <<<'TEXT'
Questions 1-7
Do the following statements agree with the information given in Reading Passage 1?
In boxes 1-7 on your answer sheet, write

TRUE if the statement agrees with the information
FALSE if the statement contradicts the information
NOT GIVEN if there is no information on this
TEXT;

        $questions = [
            1 => [
                'content' => 'Polar bears suffer from various health problems due to the build-up of fat under their skin.',
                'answer' => 'False',
                'feedback' => 'Đáp án: False. Bài đọc nói con người với lớp mỡ tương ứng có thể gặp bệnh, nhưng gấu Bắc Cực không chịu các hậu quả đó.',
            ],
            2 => [
                'content' => 'The study done by Liu and his colleagues compared different groups of polar bears.',
                'answer' => 'False',
                'feedback' => 'Đáp án: False. Nghiên cứu so sánh cấu trúc gen của gấu Bắc Cực với gấu nâu, không phải các nhóm gấu Bắc Cực khác nhau.',
            ],
            3 => [
                'content' => 'Liu and colleagues were the first researchers to compare polar bears and brown bears genetically.',
                'answer' => 'Not Given',
                'feedback' => 'Đáp án: Not Given. Bài chỉ nói họ so sánh gấu Bắc Cực và gấu nâu, không nói họ là những nhà nghiên cứu đầu tiên.',
            ],
            4 => [
                'content' => 'Polar bears are able to control their levels of "bad" cholesterol by genetic means.',
                'answer' => 'True',
                'feedback' => 'Đáp án: True. Gen APoB giúp giảm mức LDL, một dạng cholesterol xấu.',
            ],
            5 => [
                'content' => 'Female polar bears are able to survive for about six months without food.',
                'answer' => 'True',
                'feedback' => 'Đáp án: True. Bài đọc nói quá trình này dẫn đến khoảng sáu tháng nhịn ăn.',
            ],
            6 => [
                'content' => 'It was found that the bones of female polar bears were very weak when they came out of their dens in spring.',
                'answer' => 'False',
                'feedback' => 'Đáp án: False. Khi ra khỏi hang không có bằng chứng về mất mật độ xương đáng kể.',
            ],
            7 => [
                'content' => 'The polar bear\'s mechanism for increasing bone density could also be used by people one day.',
                'answer' => 'True',
                'feedback' => 'Đáp án: True. Nếu hiểu được cơ chế tái tạo xương, người nằm liệt giường và phi hành gia có thể hưởng lợi.',
            ],
        ];

        $rows = [];

        foreach ($questions as $questionNo => $question) {
            $isFirstRow = $questionNo === 1;

            $rows[] = $this->row([
                'section_no' => 2,
                'section_title' => $isFirstRow ? 'Reading Passage 1 - Why we need to protect polar bears' : '',
                'section_content' => $isFirstRow ? $passage : '',
                'group_no' => 1,
                'group_instructions' => $isFirstRow ? $instructions : '',
                'group_question_type' => 'true_false_not_given',
                'question_no' => $questionNo,
                'question_content' => $question['content'],
                'question_type' => 'true_false_not_given',
                'point' => 1,
                'answer_no' => 1,
                'answer_content' => $question['answer'],
                'answer_feedback' => $question['feedback'],
                'is_correct' => 'yes',
                'question_feedback' => $question['feedback'],
            ]);
        }

        return $rows;
    }

    private function readingMatchingInformationRows(): array
    {
        $passage = <<<'TEXT'
Back to the future of skyscraper design

Answers to the problem of excessive electricity use by skyscrapers and large public buildings can be found in ingenious but forgotten architectural designs of the 19th and early-20th centuries.

A
The Recovery of Natural Environments in Architecture by Professor Alan Short is the culmination of 30 years of research and award-winning green building design by Short and colleagues in Architecture, Engineering, Applied Maths and Earth Sciences at the University of Cambridge.

'The crisis in building design is already here,' said Short. 'Policy makers think you can solve energy and building problems with gadgets. You can't. As global temperatures continue to rise, we are going to continue to squander more and more energy on keeping our buildings mechanically cool until we have run out of capacity.'

B
Short is calling for a sweeping reinvention of how skyscrapers and major public buildings are designed - to end the reliance on sealed buildings which exist solely via the 'life support' system of vast air conditioning units.

Instead, he shows it is entirely possible to accommodate natural ventilation and cooling in large buildings by looking into the past, before the widespread introduction of air conditioning systems, which were 'relentlessly and aggressively marketed' by their inventors.

C
Short points out that to make most contemporary buildings habitable, they have to be sealed and air conditioned. The energy use and carbon emissions this generates is spectacular and largely unnecessary. Buildings in the West account for 40-50% of electricity usage, generating substantial carbon emissions, and the rest of the world is catching up at a frightening rate. Short regards glass, steel and air-conditioned skyscrapers as symbols of status, rather than practical ways of meeting our requirements.

D
Short's book highlights a developing and sophisticated art and science of ventilating buildings through the 19th and earlier-20th centuries, including the design of ingeniously ventilated hospitals. Of particular interest were those built to the designs of John Shaw Billings, including the first Johns Hopkins Hospital in the US city of Baltimore (1873-1889).

'We spent three years digitally modelling Billings' final designs,' says Short. 'We put pathogens* in the airstreams, modelled for someone with tuberculosis (TB) coughing in the wards and we found the ventilation systems in the room would have kept other patients safe from harm.'

* pathogens: microorganisms that can cause disease

E
'We discovered that 19th-century hospital wards could generate up to 24 air changes an hour - that's similar to the performance of a modern-day, computer-controlled operating theatre. We believe you could build wards based on these principles now.

Single rooms are not appropriate for all patients. Communal wards appropriate for certain patients - older people with dementia, for example - would work just as well in today's hospitals, at a fraction of the energy cost.'

Professor Short contends the mindset and skill-sets behind these designs have been completely lost, lamenting the disappearance of expertly designed theatres, opera houses, and other buildings where up to half the volume of the building was given over to ensuring everyone got fresh air.

F
Much of the ingenuity present in 19th-century hospital and building design was driven by a panicked public clamouring for buildings that could protect against what was thought to be the lethal threat of miasmas - toxic air that spread disease. Miasmas were feared as the principal agents of disease and epidemics for centuries, and were used to explain the spread of infection from the Middle Ages right through to the cholera outbreaks in London and Paris during the 1850s. Foul air, rather than germs, was believed to be the main driver of 'hospital fever', leading to disease and frequent death. The prosperous steered clear of hospitals.

While miasma theory has been long since disproved, Short has for the last 30 years advocated a return to some of the building design principles produced in its wake.

G
Today, huge amounts of a building's space and construction cost are given over to air conditioning. 'But I have designed and built a series of buildings over the past three decades which have tried to reinvent some of these ideas and then measure what happens.

'To go forward into our new low-energy, low-carbon future, we would be well advised to look back at design before our high-energy, high-carbon present appeared. What is surprising is what a rich legacy we have abandoned.'

H
Successful examples of Short's approach include the Queen's Building at De Montfort University in Leicester. Containing as many as 2,000 staff and students, the entire building is naturally ventilated, passively cooled and naturally lit, including the two largest auditoria, each seating more than 150 people. The award-winning building uses a fraction of the electricity of comparable buildings in the UK.

Short contends that glass skyscrapers in London and around the world will become a liability over the next 20 or 30 years if climate modelling predictions and energy price rises come to pass as expected.

I
He is convinced that sufficiently cooled skyscrapers using the natural environment can be produced in almost any climate. He and his team have worked on hybrid buildings in the harsh climates of Beijing and Chicago - built with natural ventilation assisted by back-up air conditioning - which, surprisingly perhaps, can be switched off more than half the time on milder days and during the spring and autumn.

Short looks at how we might reimagine the cities, offices and homes of the future. Maybe it's time we changed our outlook.
TEXT;

        $instructions = <<<'TEXT'
Questions 14-18
Reading Passage 2 has nine sections, A-I.
Which section contains the following information?
Write the correct letter, A-I, in boxes 14-18 on your answer sheet.
TEXT;

        $questions = [
            14 => [
                'content' => 'why some people avoided hospitals in the 19th century',
                'correct' => 6,
                'feedback' => 'Đáp án: F. Đoạn F nói không khí hôi bị cho là nguyên nhân chính của "hospital fever", dẫn đến bệnh tật và tử vong, nên người khá giả tránh xa bệnh viện.',
            ],
            15 => [
                'content' => 'a suggestion that the popularity of tall buildings is linked to prestige',
                'correct' => 3,
                'feedback' => 'Đáp án: C. Đoạn C nói Short xem glass, steel and air-conditioned skyscrapers là biểu tượng địa vị hơn là cách đáp ứng nhu cầu thực tế.',
            ],
            16 => [
                'content' => 'a comparison between the circulation of air in a 19th-century building and modern standards',
                'correct' => 5,
                'feedback' => 'Đáp án: E. Đoạn E so sánh 24 air changes an hour của wards thế kỷ 19 với hiệu suất của operating theatre hiện đại.',
            ],
            17 => [
                'content' => 'how Short tested the circulation of air in a 19th-century building',
                'correct' => 4,
                'feedback' => 'Đáp án: D. Đoạn D mô tả việc mô hình hóa thiết kế của Billings, đưa pathogens vào luồng khí và mô phỏng người bệnh TB ho trong ward.',
            ],
            18 => [
                'content' => 'an implication that advertising led to the large increase in the use of air conditioning',
                'correct' => 2,
                'feedback' => 'Đáp án: B. Đoạn B nhắc đến các hệ thống air conditioning được các nhà phát minh quảng bá relentlessly and aggressively.',
            ],
        ];

        $rows = [];
        $paragraphOptions = range('A', 'I');

        foreach ($questions as $questionNo => $question) {
            if ($questionNo === 14) {
                foreach ($paragraphOptions as $index => $letter) {
                    $answerNo = $index + 1;
                    $isCorrect = $answerNo === $question['correct'];

                    $rows[] = $this->row([
                        'section_no' => 4,
                        'section_title' => $index === 0 ? 'Reading Passage 2 - Back to the future of skyscraper design' : '',
                        'section_content' => $index === 0 ? $passage : '',
                        'group_no' => 1,
                        'group_instructions' => $index === 0 ? $instructions : '',
                        'group_question_type' => 'matching_information',
                        'question_no' => $questionNo,
                        'question_content' => $index === 0 ? $question['content'] : '',
                        'question_type' => 'matching_information',
                        'point' => 1,
                        'answer_no' => $answerNo,
                        'answer_content' => "Paragraph {$letter}",
                        'answer_feedback' => $isCorrect ? $question['feedback'] : '',
                        'is_correct' => $isCorrect ? 'yes' : 'no',
                        'question_feedback' => $isCorrect ? $question['feedback'] : '',
                    ]);
                }

                continue;
            }

            $correctLetter = chr(64 + $question['correct']);

            $rows[] = $this->row([
                'section_no' => 4,
                'group_no' => 1,
                'group_question_type' => 'matching_information',
                'question_no' => $questionNo,
                'question_content' => $question['content'],
                'question_type' => 'matching_information',
                'point' => 1,
                'answer_no' => $question['correct'],
                'answer_content' => "Paragraph {$correctLetter}",
                'answer_feedback' => $question['feedback'],
                'is_correct' => 'yes',
                'question_feedback' => $question['feedback'],
            ]);
        }

        return $rows;
    }

    private function readingMatchingHeadingsRows(): array
    {
        $passage = <<<'TEXT'
The Desolenator: producing clean water

A
Travelling around Thailand in the 1990s, William Janssen was impressed with the basic rooftop solar heating systems that were on many homes, where energy from the sun was absorbed by a plate and then used to heat water for domestic use. Two decades later Janssen developed that basic idea he saw in Southeast Asia into a portable device that uses the power from the sun to purify water.

B
The Desolenator operates as a mobile desalination unit that can take water from different places, such as the sea, rivers, boreholes and rain, and purify it for human consumption. It is particularly valuable in regions where natural groundwater reserves have been polluted, or where seawater is the only water source available.

Janssen saw that there was a need for a sustainable way to clean water in both developing and developed countries when he moved to the United Arab Emirates and saw large-scale water processing. 'I was confronted with the enormous carbon footprint that the Gulf nations have because of all of the desalination that they do,' he says.

C
The Desolenator can produce 15 litres of drinking water per day, enough to sustain a family for cooking and drinking. Its main selling point is that unlike standard desalination techniques, it does not require a generated power supply: just sunlight. It measures 120 cm by 90 cm, and it is easy to transport, thanks to its two wheels. Water enters through a pipe, and flows as a thin film between a sheet of double glazing and the surface of a solar panel, where it is heated by the sun. The warm water flows into a small boiler (heated by a solar-powered battery) where it is converted to steam. When the steam cools, it becomes distilled water. The device has a very simple filter to trap particles, and this can easily be shaken to remove them. There are two tubes for liquid coming out: one for the waste - salt from seawater, fluoride, etc. - and another for the distilled water. The performance of the unit is shown on an LCD screen and transmitted to the company which provides servicing when necessary.

D
A recent analysis found that at least two-thirds of the world's population lives with severe water scarcity for at least a month every year. Janssen says that by 2030 half of the world's population will be living with water stress - where the demand exceeds the supply over a certain period of time. 'It is really important that a sustainable solution is brought to the market that is able to help these people,' he says. Many countries 'do not have the money for desalination plants, which are very expensive to build. They do not have the money to operate them, they are very maintenance intensive, and they do not have the money to buy the diesel to run the desalination plants, so it is a really bad situation.'

E
The device is aimed at a wide variety of users - from homeowners in the developing world who do not have a constant supply of water to people living off the grid in rural parts of the US. The first commercial versions of the Desolenator are expected to be in operation in India early next year, after field tests are carried out. The market for the self-sufficient devices in developing countries is twofold - those who cannot afford the money for the device outright and pay through microfinance, and middle-income homes that can lease their own equipment. 'People in India do not pay for a fridge outright; they pay for it over six months. They would put the Desolenator on their roof and hook it up to their municipal supply and they would get very reliable drinking water on a daily basis,' Janssen says. In the developed world, it is aimed at niche markets where tap water is unavailable - for camping, on boats, or for the military, for instance.

F
Prices will vary according to where it is bought. In the developing world, the price will depend on what deal aid organisations can negotiate. In developed countries, it is likely to come in at $1,000 (GBP 685) a unit, said Janssen. 'We are a venture with a social mission. We are aware that the product we have envisioned is mainly finding application in the developing world and humanitarian sector and that this is the way we will proceed. We do realise, though, that to be a viable company there is a bottom line to keep in mind,' he says.

G
The company itself is based at Imperial College London, although Janssen, its chief executive, still lives in the UAE. It has raised GBP 340,000 in funding so far. Within two years, he says, the company aims to be selling 1,000 units a month, mainly in the humanitarian field. They are expected to be sold in areas such as Australia, northern Chile, Peru, Texas and California.
TEXT;

        $instructions = <<<'TEXT'
Questions 14-20
Reading Passage 2 has seven sections, A-G.
Choose the correct heading for each section from the list of headings below.
Write the correct number, i-x, in boxes 14-20 on your answer sheet.
TEXT;

        $headings = [
            1 => 'Getting the finance for production',
            2 => 'An unexpected benefit',
            3 => 'From initial inspiration to new product',
            4 => 'The range of potential customers for the device',
            5 => 'What makes the device different from alternatives',
            6 => 'Cleaning water from a range of sources',
            7 => 'Overcoming production difficulties',
            8 => 'Profit not the primary goal',
            9 => 'A warm welcome for the device',
            10 => 'The number of people affected by water shortages',
        ];

        $questions = [
            14 => [
                'content' => 'Section A',
                'correct' => 3,
                'feedback' => 'Đáp án: iii. Đoạn A nêu cảm hứng ban đầu từ hệ thống solar heating ở Thái Lan và việc Janssen phát triển ý tưởng đó thành thiết bị lọc nước.',
            ],
            15 => [
                'content' => 'Section B',
                'correct' => 6,
                'feedback' => 'Đáp án: vi. Đoạn B mô tả Desolenator có thể lấy nước từ biển, sông, boreholes và nước mưa rồi lọc để uống.',
            ],
            16 => [
                'content' => 'Section C',
                'correct' => 5,
                'feedback' => 'Đáp án: v. Đoạn C nhấn mạnh điểm khác biệt là thiết bị không cần nguồn điện phát sinh, chỉ cần ánh sáng mặt trời.',
            ],
            17 => [
                'content' => 'Section D',
                'correct' => 10,
                'feedback' => 'Đáp án: x. Đoạn D đưa số liệu về số người chịu water scarcity và dự báo water stress vào năm 2030.',
            ],
            18 => [
                'content' => 'Section E',
                'correct' => 4,
                'feedback' => 'Đáp án: iv. Đoạn E mở đầu bằng việc thiết bị nhắm tới nhiều nhóm người dùng khác nhau và liệt kê các nhóm khách hàng.',
            ],
            19 => [
                'content' => 'Section F',
                'correct' => 8,
                'feedback' => 'Đáp án: viii. Đoạn F nói doanh nghiệp có social mission, hướng vào developing world và humanitarian sector, dù vẫn cần bottom line.',
            ],
            20 => [
                'content' => 'Section G',
                'correct' => 1,
                'feedback' => 'Đáp án: i. Đoạn G nói công ty đã gọi vốn GBP 340,000 và mục tiêu bán hàng, liên quan trực tiếp đến finance.',
            ],
        ];

        $rows = [];

        foreach ($questions as $questionNo => $question) {
            if ($questionNo === 14) {
                foreach ($headings as $answerNo => $heading) {
                    $isCorrect = $answerNo === $question['correct'];

                    $rows[] = $this->row([
                        'section_no' => 5,
                        'section_title' => $answerNo === 1 ? 'Reading Passage 2 - The Desolenator: producing clean water' : '',
                        'section_content' => $answerNo === 1 ? $passage : '',
                        'group_no' => 1,
                        'group_instructions' => $answerNo === 1 ? $instructions : '',
                        'group_question_type' => 'matching_headings',
                        'question_no' => $questionNo,
                        'question_content' => $answerNo === 1 ? $question['content'] : '',
                        'question_type' => 'matching_headings',
                        'point' => 1,
                        'answer_no' => $answerNo,
                        'answer_content' => $heading,
                        'answer_feedback' => $isCorrect ? $question['feedback'] : '',
                        'is_correct' => $isCorrect ? 'yes' : 'no',
                        'question_feedback' => $isCorrect ? $question['feedback'] : '',
                    ]);
                }

                continue;
            }

            $rows[] = $this->row([
                'section_no' => 5,
                'group_no' => 1,
                'group_question_type' => 'matching_headings',
                'question_no' => $questionNo,
                'question_content' => $question['content'],
                'question_type' => 'matching_headings',
                'point' => 1,
                'answer_no' => $question['correct'],
                'answer_content' => $headings[$question['correct']],
                'answer_feedback' => $question['feedback'],
                'is_correct' => 'yes',
                'question_feedback' => $question['feedback'],
            ]);
        }

        return $rows;
    }

    private function readingMatchingFeaturesRows(): array
    {
        $passage = <<<'TEXT'
Should we try to bring extinct species back to life?

A
The passenger pigeon was a legendary species. Flying in vast numbers across North America, with potentially many millions within a single flock, their migration was once one of nature's great spectacles. Sadly, the passenger pigeon's existence came to an end on 1 September 1914, when the last living specimen died at Cincinnati Zoo. Geneticist Ben Novak is lead researcher on an ambitious project which now aims to bring the bird back to life through a process known as 'de-extinction'. The basic premise involves using cloning technology to turn the DNA of extinct animals into a fertilised embryo, which is carried by the nearest relative still in existence - in this case, the abundant band-tailed pigeon - before being born as a living, breathing animal. Passenger pigeons are one of the pioneering species in this field, but they are far from the only ones on which this cutting-edge technology is being trialled.

B
In Australia, the thylacine, more commonly known as the Tasmanian tiger, is another extinct creature which genetic scientists are striving to bring back to life. 'There is no carnivore now in Tasmania that fills the niche which thylacines once occupied,' explains Michael Archer of the University of New South Wales. He points out that in the decades since the thylacine went extinct, there has been a spread in a 'dangerously debilitating' facial tumour syndrome which threatens the existence of the Tasmanian devils, the island's other notorious resident. Thylacines would have prevented this spread because they would have killed significant numbers of Tasmanian devils. 'If that contagious cancer had popped up previously, it would have burned out in whatever region it started. The return of thylacines to Tasmania could help to ensure that devils are never again subjected to risks of this kind.'

C
If extinct species can be brought back to life, can humanity begin to correct the damage it has caused to the natural world over the past few millennia? 'The idea of de-extinction is that we can reverse this process, bringing species that no longer exist back to life,' says Beth Shapiro of University of California Santa Cruz's Genomics Institute. 'I do not think that we can do this. There is no way to bring back something that is 100 per cent identical to a species that went extinct a long time ago.' A more practical approach for long-extinct species is to take the DNA of existing species as a template, ready for the insertion of strands of extinct animal DNA to create something new; a hybrid, based on the living species, but which looks and/or acts like the animal which died out.

D
This complicated process and questionable outcome begs the question: what is the actual point of this technology? 'For us, the goal has always been replacing the extinct species with a suitable replacement,' explains Novak. 'When it comes to breeding, band-tailed pigeons scatter and make maybe one or two nests per hectare, whereas passenger pigeons were very social and would make 10,000 or more nests in one hectare.' Since the disappearance of this key species, ecosystems in the eastern US have suffered, as the lack of disturbance caused by thousands of passenger pigeons wrecking trees and branches means there has been minimal need for regrowth. This has left forests stagnant and therefore unwelcoming to the plants and animals which evolved to help regenerate the forest after a disturbance. According to Novak, a hybridized band-tailed pigeon, with the added nesting habits of a passenger pigeon, could, in theory, re-establish that forest disturbance, thereby creating a habitat necessary for a great many other native species to thrive.

E
Another popular candidate for this technology is the woolly mammoth. George Church, professor at Harvard Medical School and leader of the Woolly Mammoth Revival Project, has been focusing on cold resistance, the main way in which the extinct woolly mammoth and its nearest living relative, the Asian elephant, differ. By pinpointing which genetic traits made it possible for mammoths to survive the icy climate of the tundra, the project's goal is to return mammoths, or a mammoth-like species, to the area. 'My highest priority would be preserving the endangered Asian elephant,' says Church, 'expanding their range to the huge ecosystem of the tundra. Necessary adaptations would include smaller ears, thicker hair, and extra insulating fat, all for the purpose of reducing heat loss in the tundra, and all traits found in the now extinct woolly mammoth.' This repopulation of the tundra and boreal forests of Eurasia and North America with large mammals could also be a useful factor in reducing carbon emissions - elephants punch holes through snow and knock down trees, which encourages grass growth. This grass growth would reduce temperature, and mitigate emissions from melting permafrost.

F
While the prospect of bringing extinct animals back to life might capture imaginations, it is, of course, far easier to try to save an existing species which is merely threatened with extinction. 'Many of the technologies that people have in mind when they think about de-extinction can be used as a form of "genetic rescue",' explains Shapiro. She prefers to focus the debate on how this emerging technology could be used to fully understand why various species went extinct in the first place, and therefore how we could use it to make genetic modifications which could prevent mass extinctions in the future. 'I would also say there is an incredible moral hazard to not do anything at all,' she continues. 'We know that what we are doing today is not enough, and we have to be willing to take some calculated and measured risks.'
TEXT;

        $instructions = <<<'TEXT'
Questions 23-26
Look at the following statements (Questions 23-26) and the list of people below.
Match each statement with the correct person, A, B or C.
Write the correct letter, A, B or C, in boxes 23-26 on your answer sheet.
NB You may use any letter more than once.
TEXT;

        $people = [
            1 => 'Ben Novak',
            2 => 'Michael Archer',
            3 => 'Beth Shapiro',
        ];

        $questions = [
            23 => [
                'content' => 'Reintroducing an extinct species to its original habitat could improve the health of a particular species living there.',
                'correct' => 2,
                'feedback' => 'Đáp án: B. Michael Archer nói việc đưa thylacines trở lại Tasmania có thể giúp Tasmanian devils không còn chịu rủi ro từ hội chứng facial tumour.',
            ],
            24 => [
                'content' => 'It is important to concentrate on the causes of an animal\'s extinction.',
                'correct' => 3,
                'feedback' => 'Đáp án: C. Beth Shapiro muốn tập trung vào việc hiểu đầy đủ vì sao các loài khác nhau tuyệt chủng ngay từ đầu.',
            ],
            25 => [
                'content' => 'A species brought back from extinction could have an important beneficial impact on the vegetation of its habitat.',
                'correct' => 1,
                'feedback' => 'Đáp án: A. Ben Novak cho rằng passenger pigeon lai có thể tái thiết lập forest disturbance, giúp tạo môi trường cho nhiều loài bản địa phát triển.',
            ],
            26 => [
                'content' => 'Our current efforts at preserving biodiversity are insufficient.',
                'correct' => 3,
                'feedback' => 'Đáp án: C. Beth Shapiro nói những gì chúng ta đang làm hiện nay là chưa đủ.',
            ],
        ];

        $rows = [];

        foreach ($questions as $questionNo => $question) {
            if ($questionNo === 23) {
                foreach ($people as $answerNo => $person) {
                    $isCorrect = $answerNo === $question['correct'];

                    $rows[] = $this->row([
                        'section_no' => 6,
                        'section_title' => $answerNo === 1 ? 'Reading Passage 3 - Should we try to bring extinct species back to life?' : '',
                        'section_content' => $answerNo === 1 ? $passage : '',
                        'group_no' => 1,
                        'group_instructions' => $answerNo === 1 ? $instructions : '',
                        'group_question_type' => 'matching_features',
                        'question_no' => $questionNo,
                        'question_content' => $answerNo === 1 ? $question['content'] : '',
                        'question_type' => 'matching_features',
                        'point' => 1,
                        'answer_no' => $answerNo,
                        'answer_content' => $person,
                        'answer_feedback' => $isCorrect ? $question['feedback'] : '',
                        'is_correct' => $isCorrect ? 'yes' : 'no',
                        'question_feedback' => $isCorrect ? $question['feedback'] : '',
                    ]);
                }

                continue;
            }

            $rows[] = $this->row([
                'section_no' => 6,
                'group_no' => 1,
                'group_question_type' => 'matching_features',
                'question_no' => $questionNo,
                'question_content' => $question['content'],
                'question_type' => 'matching_features',
                'point' => 1,
                'answer_no' => $question['correct'],
                'answer_content' => $people[$question['correct']],
                'answer_feedback' => $question['feedback'],
                'is_correct' => 'yes',
                'question_feedback' => $question['feedback'],
            ]);
        }

        return $rows;
    }

    private function readingMatchingSentenceEndingsRows(): array
    {
        $passage = <<<'TEXT'
Great Migrations

Animal migration, however it is defined, is far more than just the movement of animals. It can loosely be described as travel that takes place at regular intervals - often in an annual cycle - that may involve many members of a species, and is rewarded only after a long journey. It suggests inherited instinct. The biologist Hugh Dingle has identified five characteristics that apply, in varying degrees and combinations, to all migrations. They are prolonged movements that carry animals outside familiar habitats; they tend to be linear, not zigzaggy; they involve special behaviours concerning preparation (such as overfeeding) and arrival; they demand special allocations of energy. And one more: migrating animals maintain an intense attentiveness to the greater mission, which keeps them undistracted by temptations and undeterred by challenges that would turn other animals aside.

An arctic tern, on its 20,000 km flight from the extreme south of South America to the Arctic circle, will take no notice of a nice smelly herring offered from a bird-watcher's boat along the way. While local gulls will dive voraciously for such handouts, the tern flies on. Why? The arctic tern resists distraction because it is driven at that moment by an instinctive sense of something we humans find admirable: larger purpose. In other words, it is determined to reach its destination. The bird senses that it can eat, rest and mate later. Right now it is totally focused on the journey; its undivided intent is arrival.

Reaching some gravelly coastline in the Arctic, upon which other arctic terns have converged, will serve its larger purpose as shaped by evolution: finding a place, a time, and a set of circumstances in which it can successfully hatch and rear offspring.

But migration is a complex issue, and biologists define it differently, depending in part on what sorts of animals they study. Joel Berger, of the University of Montana, who works on the American pronghorn and other large terrestrial mammals, prefers what he calls a simple, practical definition suited to his beasts: 'movements from a seasonal home area away to another home area and back again'. Generally the reason for such seasonal back-and-forth movement is to seek resources that are not available within a single area year-round.

But daily vertical movements by zooplankton in the ocean - upward by night to seek food, downward by day to escape predators - can also be considered migration. So can the movement of aphids when, having depleted the young leaves on one food plant, their offspring then fly onward to a different host plant, with no one aphid ever returning to where it started.

Dingle is an evolutionary biologist who studies insects. His definition is more intricate than Berger's, citing those five features that distinguish migration from other forms of movement. They allow for the fact that, for example, aphids will become sensitive to blue light (from the sky) when it is time for takeoff on their big journey, and sensitive to yellow light (reflected from tender young leaves) when it is appropriate to land. Birds will fatten themselves with heavy feeding in advance of a long migrational flight. The value of his definition, Dingle argues, is that it focuses attention on what the phenomenon of wildebeest migration shares with the phenomenon of the aphids, and therefore helps guide researchers towards understanding how evolution has produced them all.

Human behaviour, however, is having a detrimental impact on animal migration. The pronghorn, which resembles an antelope, though they are unrelated, is the fastest land mammal of the New World. One population, which spends the summer in the mountainous Grand Teton National Park of the western USA, follows a narrow route from its summer range in the mountains, across a river, and down onto the plains. Here they wait out the frozen months, feeding mainly on sagebrush blown clear of snow. These pronghorn are notable for the invariance of their migration route and the severity of its constriction at three bottlenecks. If they cannot pass through each of the three during their spring migration, they cannot reach their bounty of summer grazing; if they cannot pass through again in autumn, escaping south onto those windblown plains, they are likely to die trying to overwinter in the deep snow. Pronghorn, dependent on distance vision and speed to keep safe from predators, traverse high, open shoulders of land, where they can see and run. At one of the bottlenecks, forested hills rise to form a V, leaving a corridor of open ground only about 150 metres wide, filled with private homes. Increasing development is leading toward a crisis for the pronghorn, threatening to choke off their passageway.

Conservation scientists, along with some biologists and land managers within the USA's National Park Service and other agencies, are now working to preserve migrational behaviours, not just species and habitats. A National Forest has recognised the path of the pronghorn, much of which passes across its land, as a protected migration corridor. But neither the Forest Service nor the Park Service can control what happens on private land at a bottleneck. And with certain other migrating species, the challenge is complicated further - by vastly greater distances traversed, more jurisdictions, more borders, more dangers along the way. We will require wisdom and resoluteness to ensure that migrating species can continue their journeying a while longer.
TEXT;

        $instructions = <<<'TEXT'
Questions 19-22
Complete each sentence with the correct ending, A-G, below.
Write the correct letter, A-G, in boxes 19-22 on your answer sheet.
TEXT;

        $endings = [
            1 => 'be discouraged by difficulties.',
            2 => 'travel on open land where they can look out for predators.',
            3 => 'eat more than they need for immediate purposes.',
            4 => 'be repeated daily.',
            5 => 'ignore distractions.',
            6 => 'be governed by the availability of water.',
            7 => 'follow a straight line.',
        ];

        $questions = [
            19 => [
                'content' => 'According to Dingle, migratory routes are likely to',
                'correct' => 7,
                'feedback' => 'Đáp án: G. Đoạn 1 nói migratory movements "tend to be linear, not zigzaggy", tức là có xu hướng đi theo đường thẳng.',
            ],
            20 => [
                'content' => 'To prepare for migration, animals are likely to',
                'correct' => 3,
                'feedback' => 'Đáp án: C. Đoạn 6 nói birds will fatten themselves with heavy feeding before a long migrational flight.',
            ],
            21 => [
                'content' => 'During migration, animals are unlikely to',
                'correct' => 1,
                'feedback' => 'Đáp án: A. Đoạn 1 nói migrating animals are undeterred by challenges, tức là không bị nản lòng bởi khó khăn.',
            ],
            22 => [
                'content' => 'Arctic terns illustrate migrating animals\' ability to',
                'correct' => 5,
                'feedback' => 'Đáp án: E. Đoạn 2 nói arctic tern resists distraction và tiếp tục bay tới đích.',
            ],
        ];

        $rows = [];

        foreach ($questions as $questionNo => $question) {
            if ($questionNo === 19) {
                foreach ($endings as $answerNo => $ending) {
                    $isCorrect = $answerNo === $question['correct'];

                    $rows[] = $this->row([
                        'section_no' => 7,
                        'section_title' => $answerNo === 1 ? 'Reading Passage 1 - Great Migrations' : '',
                        'section_content' => $answerNo === 1 ? $passage : '',
                        'group_no' => 1,
                        'group_instructions' => $answerNo === 1 ? $instructions : '',
                        'group_question_type' => 'matching_sentence_endings',
                        'question_no' => $questionNo,
                        'question_content' => $answerNo === 1 ? $question['content'] : '',
                        'question_type' => 'matching_sentence_endings',
                        'point' => 1,
                        'answer_no' => $answerNo,
                        'answer_content' => $ending,
                        'answer_feedback' => $isCorrect ? $question['feedback'] : '',
                        'is_correct' => $isCorrect ? 'yes' : 'no',
                        'question_feedback' => $isCorrect ? $question['feedback'] : '',
                    ]);
                }

                continue;
            }

            $rows[] = $this->row([
                'section_no' => 7,
                'group_no' => 1,
                'group_question_type' => 'matching_sentence_endings',
                'question_no' => $questionNo,
                'question_content' => $question['content'],
                'question_type' => 'matching_sentence_endings',
                'point' => 1,
                'answer_no' => $question['correct'],
                'answer_content' => $endings[$question['correct']],
                'answer_feedback' => $question['feedback'],
                'is_correct' => 'yes',
                'question_feedback' => $question['feedback'],
            ]);
        }

        return $rows;
    }

    private function readingSentenceCompletionRows(): array
    {
        $passage = <<<'TEXT'
Cutty Sark: the fastest sailing ship of all time

The nineteenth century was a period of great technological development in Britain, and for shipping the major changes were from wind to steam power, and from wood to iron and steel.

The fastest commercial sailing vessels of all time were clippers, three-masted ships built to transport goods around the world, although some also took passengers. From the 1840s until 1869, when the Suez Canal opened and steam propulsion was replacing sail, clippers dominated world trade. Although many were built, only one has survived more or less intact: Cutty Sark, now on display in Greenwich, southeast London.

Cutty Sark's unusual name comes from the poem Tam O'Shanter by the Scottish poet Robert Burns. Tam, a farmer, is chased by a witch called Nannie, who is wearing a 'cutty sark' - an old Scottish name for a short nightdress. The witch is depicted in Cutty Sark's figurehead - the carving of a woman typically at the front of old sailing ships. In legend, and in Burns's poem, witches cannot cross water, so this was a rather strange choice of name for a ship.

Cutty Sark was built in Dumbarton, Scotland, in 1869, for a shipping company owned by John Willis. To carry out construction, Willis chose a new shipbuilding firm, Scott & Linton, and ensured that the contract with them put him in a very strong position. In the end, the firm was forced out of business, and the ship was finished by a competitor.

Willis's company was active in the tea trade between China and Britain, where speed could bring shipowners both profits and prestige, so Cutty Sark was designed to make the journey more quickly than any other ship. On her maiden voyage, in 1870, she set sail from London, carrying large amounts of goods to China. She returned laden with tea, making the journey back to London in four months. However, Cutty Sark never lived up to the high expectations of her owner, as a result of bad winds and various misfortunes. On one occasion, in 1872, the ship and a rival clipper, Thermopylae, left port in China on the same day. Crossing the Indian Ocean, Cutty Sark gained a lead of over 400 miles, but then her rudder was severely damaged in stormy seas, making her impossible to steer. The ship's crew had the daunting task of repairing the rudder at sea, and only succeeded at the second attempt. Cutty Sark reached London a week after Thermopylae.

Steam ships posed a growing threat to clippers, as their speed and cargo capacity increased. In addition, the opening of the Suez Canal in 1869, the same year that Cutty Sark was launched, had a serious impact. While steam ships could make use of the quick, direct route between the Mediterranean and the Red Sea, the canal was of no use to sailing ships, which needed the much stronger winds of the oceans, and so had to sail a far greater distance. Steam ships reduced the journey time between Britain and China by approximately two months.

By 1878, tea traders were not interested in Cutty Sark, and instead, she took on the much less prestigious work of carrying any cargo between any two ports in the world. In 1880, violence aboard the ship led ultimately to the replacement of the captain with an incompetent drunkard who stole the crew's wages. He was suspended from service, and a new captain appointed. This marked a turnaround and the beginning of the most successful period in Cutty Sark's working life, transporting wool from Australia to Britain. One such journey took just under 12 weeks, beating every other ship sailing that year by around a month.

The ship's next captain, Richard Woodget, was an excellent navigator, who got the best out of both his ship and his crew. As a sailing ship, Cutty Sark depended on the strong trade winds of the southern hemisphere, and Woodget took her further south than any previous captain, bringing her dangerously close to icebergs off the southern tip of South America. His gamble paid off, though, and the ship was the fastest vessel in the wool trade for ten years.

As competition from steam ships increased in the 1890s, and Cutty Sark approached the end of her life expectancy, she became less profitable. She was sold to a Portuguese firm, which renamed her Ferreira. For the next 25 years, she again carried miscellaneous cargoes around the world.

Badly damaged in a gale in 1922, she was put into Falmouth harbour in southwest England, for repairs. Wilfred Dowman, a retired sea captain who owned a training vessel, recognised her and tried to buy her, but without success. She returned to Portugal and was sold to another Portuguese company. Dowman was determined, however, and offered a high price: this was accepted, and the ship returned to Falmouth the following year and had her original name restored.

Dowman used Cutty Sark as a training ship, and she continued in this role after his death. When she was no longer required, in 1954, she was transferred to dry dock at Greenwich to go on public display. The ship suffered from fire in 2007, and again, less seriously, in 2014, but now Cutty Sark attracts a quarter of a million visitors a year.
TEXT;

        $instructions = <<<'TEXT'
Questions 9-13
Complete the sentences below.
Choose ONE WORD ONLY from the passage for each answer.
Write your answers in boxes 9-13 on your answer sheet.
TEXT;

        $groupContent = <<<'TEXT'
9 After 1880, Cutty Sark carried {{9}} as its main cargo during its most successful time.
10 As a captain and {{10}}, Woodget was very skilled.
11 Ferreira went to Falmouth to repair damage that a {{11}} had caused.
12 Between 1923 and 1954, Cutty Sark was used for {{12}}.
13 Cutty Sark has twice been damaged by {{13}} in the 21st century.
TEXT;

        $questions = [
            9 => [
                'content' => 'After 1880, Cutty Sark carried as its main cargo during its most successful time.',
                'answer' => 'wool',
                'feedback' => 'Đáp án: wool. Bài đọc nói giai đoạn thành công nhất là transporting wool from Australia to Britain.',
            ],
            10 => [
                'content' => 'As a captain and, Woodget was very skilled.',
                'answer' => 'navigator',
                'feedback' => 'Đáp án: navigator. Bài đọc nói Richard Woodget was an excellent navigator.',
            ],
            11 => [
                'content' => 'Ferreira went to Falmouth to repair damage that a had caused.',
                'answer' => 'gale',
                'feedback' => 'Đáp án: gale. Bài đọc nói Badly damaged in a gale in 1922.',
            ],
            12 => [
                'content' => 'Between 1923 and 1954, Cutty Sark was used for.',
                'answer' => 'training',
                'feedback' => 'Đáp án: training. Dowman used Cutty Sark as a training ship và con tàu tiếp tục vai trò này tới năm 1954.',
            ],
            13 => [
                'content' => 'Cutty Sark has twice been damaged by in the 21st century.',
                'answer' => 'fire',
                'feedback' => 'Đáp án: fire. Bài đọc nói The ship suffered from fire in 2007, and again in 2014.',
            ],
        ];

        $rows = [];

        foreach ($questions as $questionNo => $question) {
            $isFirstRow = $questionNo === 9;

            $rows[] = $this->row([
                'section_no' => 8,
                'section_title' => $isFirstRow ? 'Reading Passage 1 - Cutty Sark: the fastest sailing ship of all time' : '',
                'section_content' => $isFirstRow ? $passage : '',
                'group_no' => 1,
                'group_content' => $isFirstRow ? $groupContent : '',
                'group_instructions' => $isFirstRow ? $instructions : '',
                'group_question_type' => 'sentence_completion',
                'question_no' => $questionNo,
                'question_content' => $question['content'],
                'question_type' => 'sentence_completion',
                'point' => 1,
                'answer_no' => 1,
                'answer_content' => $question['answer'],
                'answer_feedback' => $question['feedback'],
                'is_correct' => 'yes',
                'question_feedback' => $question['feedback'],
            ]);
        }

        return $rows;
    }

    private function readingSummaryCompletionRows(): array
    {
        $passage = <<<'TEXT'
Driverless cars

A
The automotive sector is well used to adapting to automation in manufacturing. The implementation of robotic car manufacture from the 1970s onwards led to significant cost savings and improvements in the reliability and flexibility of vehicle mass production. A new challenge to vehicle production is now on the horizon and, again, it comes from automation. However, this time it is not to do with the manufacturing process, but with the vehicles themselves.

Research projects on vehicle automation are not new. Vehicles with limited self-driving capabilities have been around for more than 50 years, resulting in significant contributions towards driver assistance systems. But since Google announced in 2010 that it had been trialling self-driving cars on the streets of California, progress in this field has quickly gathered pace.

B
There are many reasons why technology is advancing so fast. One frequently cited motive is safety; indeed, research at the UK's Transport Research Laboratory has demonstrated that more than 90 percent of road collisions involve human error as a contributory factor, and it is the primary cause in the vast majority. Automation may help to reduce the incidence of this.

Another aim is to free the time people spend driving for other purposes. If the vehicle can do some or all of the driving, it may be possible to be productive, to socialise or simply to relax while automation systems have responsibility for safe control of the vehicle. If the vehicle can do the driving, those who are challenged by existing mobility models - such as older or disabled travellers - may be able to enjoy significantly greater travel autonomy.

C
Beyond these direct benefits, we can consider the wider implications for transport and society, and how manufacturing processes might need to respond as a result. At present, the average car spends more than 90 percent of its life parked. Automation means that initiatives for car-sharing become much more viable, particularly in urban areas with significant travel demand. If a significant proportion of the population choose to use shared automated vehicles, mobility demand can be met by far fewer vehicles.

D
The Massachusetts Institute of Technology investigated automated mobility in Singapore, finding that fewer than 30 percent of the vehicles currently used would be required if fully automated car sharing could be implemented. If this is the case, it might mean that we need to manufacture far fewer vehicles to meet demand. However, the number of trips being taken would probably increase, partly because empty vehicles would have to be moved from one customer to the next.

Modelling work by the University of Michigan Transportation Research Institute suggests automated vehicles might reduce vehicle ownership by 43 percent, but that vehicles' average annual mileage double as a result. As a consequence, each vehicle would be used more intensively, and might need replacing sooner. This faster rate of turnover may mean that vehicle production will not necessarily decrease.

E
Automation may prompt other changes in vehicle manufacture. If we move to a model where consumers are tending not to own a single vehicle but to purchase access to a range of vehicles through a mobility provider, drivers will have the freedom to select one that best suits their needs for a particular journey, rather than making a compromise across all their requirements.

Since, for most of the time, most of the seats in most cars are unoccupied, this may boost production of a smaller, more efficient range of vehicles that suit the needs of individuals. Specialised vehicles may then be available for exceptional journeys, such as going on a family camping trip or helping a son or daughter move to university.

F
There are a number of hurdles to overcome in delivering automated vehicles to our roads. These include the technical difficulties in ensuring that the vehicle works reliably in the infinite range of traffic, weather and road situations it might encounter; the regulatory challenges in understanding how liability and enforcement might change when drivers are no longer essential for vehicle operation; and the societal changes that may be required for communities to trust and accept automated vehicles as being a valuable part of the mobility landscape.

G
It is clear that there are many challenges that need to be addressed but, through robust and targeted research, these can most probably be conquered within the next 10 years. Mobility will change in such potentially significant ways and in association with so many other technological developments, such as telepresence and virtual reality, that it is hard to make concrete predictions about the future. However, one thing is certain: change is coming, and the need to be flexible in response to this will be vital for those involved in manufacturing the vehicles that will deliver future mobility.
TEXT;

        $instructions = <<<'TEXT'
Questions 19-22
Complete the summary below.
Choose NO MORE THAN TWO WORDS from the passage for each answer.
Write your answers in boxes 19-22 on your answer sheet.
TEXT;

        $groupContent = <<<'TEXT'
The impact of driverless cars

Figures from the Transport Research Laboratory indicate that most motor accidents are partly due to {{19}} so the introduction of driverless vehicles will result in greater safety. In addition to the direct benefits of automation, it may bring other advantages. For example, schemes for {{20}} will be more workable, especially in towns and cities, resulting in fewer cars on the road.

According to the University of Michigan Transportation Research Institute, there could be a 43 percent drop in {{21}} of cars. However, this would mean that the yearly {{22}} of each car would, on average, be twice as high as it currently is. This would lead to a higher turnover of vehicles, and therefore no reduction in automotive manufacturing.
TEXT;

        $questions = [
            19 => [
                'content' => 'Figures from the Transport Research Laboratory indicate that most motor accidents are partly due to.',
                'answer' => 'human error',
                'feedback' => 'Đáp án: human error. Section B nói hơn 90 percent of road collisions involve human error as a contributory factor.',
            ],
            20 => [
                'content' => 'Schemes for will be more workable, especially in towns and cities.',
                'answer' => 'car-sharing',
                'feedback' => 'Đáp án: car-sharing. Section C nói automation means initiatives for car-sharing become much more viable in urban areas.',
            ],
            21 => [
                'content' => 'There could be a 43 percent drop in of cars.',
                'answer' => 'ownership',
                'feedback' => 'Đáp án: ownership. Section D nói automated vehicles might reduce vehicle ownership by 43 percent.',
            ],
            22 => [
                'content' => 'The yearly of each car would be twice as high as it currently is.',
                'answer' => 'mileage',
                'feedback' => 'Đáp án: mileage. Section D nói vehicles\' average annual mileage double as a result.',
            ],
        ];

        $rows = [];

        foreach ($questions as $questionNo => $question) {
            $isFirstRow = $questionNo === 19;

            $rows[] = $this->row([
                'section_no' => 9,
                'section_title' => $isFirstRow ? 'Reading Passage 2 - Driverless cars' : '',
                'section_content' => $isFirstRow ? $passage : '',
                'group_no' => 1,
                'group_content' => $isFirstRow ? $groupContent : '',
                'group_instructions' => $isFirstRow ? $instructions : '',
                'group_question_type' => 'summary_completion',
                'question_no' => $questionNo,
                'question_content' => $question['content'],
                'question_type' => 'summary_completion',
                'point' => 1,
                'answer_no' => 1,
                'answer_content' => $question['answer'],
                'answer_feedback' => $question['feedback'],
                'is_correct' => 'yes',
                'question_feedback' => $question['feedback'],
            ]);
        }

        return $rows;
    }

    private function readingNoteCompletionRows(): array
    {
        $passage = <<<'TEXT'
THE IMPORTANCE OF CHILDREN'S PLAY

Brick by brick, six-year-old Alice is building a magical kingdom. Imagining fairy-tale turrets and fire-breathing dragons, wicked witches and gallant heroes, she's creating an enchanting world. Although she isn't aware of it, this fantasy is helping her take her first steps towards her capacity for creativity and so it will have important repercussions in her adult life.

Minutes later, Alice has abandoned the kingdom in favour of playing schools with her younger brother. When she bosses him around as his 'teacher', she's practising how to regulate her emotions through pretence. Later on, when they tire of this and settle down with a board game, she's learning about the need to follow rules and take turns with a partner.

'Play in all its rich variety is one of the highest achievements of the human species,' says Dr David Whitebread from the Faculty of Education at the University of Cambridge, UK. 'It underpins how we develop as intellectual, problem-solving adults and is crucial to our success as a highly adaptable species.'

Recognizing the importance of play is not new: over two millennia ago, the Greek philosopher Plato extolled its virtues as a means of developing skills for adult life, and ideas about play-based learning have been developing since the 19th century.

But we live in changing times, and Whitebread is mindful of a worldwide decline in play, pointing out that over half the people in the world now live in cities. 'The opportunities for free play, which I experienced almost every day of my childhood, are becoming increasingly scarce,' he says. Outdoor play is curtailed by perceptions of risk to do with traffic, as well as parents' increased wish to protect their children from being the victims of crime, and by the emphasis on 'earlier is better' which is leading to greater competition in academic learning and schools.

International bodies like the United Nations and the European Union have begun to develop policies concerned with children's right to play, and to consider implications for leisure facilities and educational programmes. But what they often lack is the evidence to base policies on.

'The type of play we are interested in is child-initiated, spontaneous and unpredictable - but, as soon as you ask a five-year-old "to play", then you as the researcher have intervened,' explains Dr Sara Baker. 'And we want to know what the long-term impact of play is. It's a real challenge.'

Dr Jenny Gibson agrees, pointing out that although some of the steps in the puzzle of how and why play is important have been looked at, there is very little data on the impact it has on the child's later life.

Now, thanks to the university's new Centre for Research on Play in Education, Development and Learning (PEDAL), Whitebread, Baker, Gibson and a team of researchers hope to provide evidence on the role played by play in how a child develops.

'A strong possibility is that play supports the early development of children's self-control,' explains Baker. 'This is our ability to develop awareness of our own thinking progresses - it influences how effectively we go about undertaking challenging activities.'

In a study carried out by Baker with toddlers and young pre-schoolers, she found that children with greater self-control solved problems more quickly when exploring an unfamiliar set-up requiring scientific reasoning. 'This sort of evidence makes us think that giving children the chance to play will make them more successful problem-solvers in the long run.'

If playful experiences do facilitate this aspect of development, say the researchers, it could be extremely significant for educational practices, because the ability to self-regulate has been shown to be a key predictor of academic performance.

Gibson adds: 'Playful behavior is also an important indicator of healthy social and emotional development. In my previous research, I investigated how observing children at play can give us important clues about their well-being and can even be useful in the diagnosis of neurodevelopmental disorders like autism.'

Whitebread's recent research has involved developing a play-based approach to supporting children's writing. 'Many primary school children find writing difficult, but we showed in a previous study that a playful stimulus was far more effective than an instructional one.' Children wrote longer and better-structured stories when they first played with dolls representing characters in the story. In the latest study, children first created their story with Lego*, with similar results. 'Many teachers commented that they had always previously had children saying they didn't know what to write about. With the Lego building, however, not a single child said this through the whole year of the project.'

Whitebread, who directs PEDAL, trained as a primary school teacher in the early 1970s, when, as he describes, 'the teaching of young children was largely a quiet backwater, untroubled by any serious intellectual debate or controversy.' Now, the landscape is very different, with hotly debated topics such as school starting age.

'Somehow the importance of play has been lost in recent decades. It's regarded as something trivial, or even as something negative that contrasts with "work". Let's not lose sight of its benefits, and the fundamental contributions it makes to human achievements in the arts, sciences and technology. Let's make sure children have a rich diet of play experiences.'

* Lego: coloured plastic building blocks and other pieces that can be joined together
TEXT;

        $instructions = <<<'TEXT'
Questions 1-8
Complete the notes below.
Choose ONE WORD ONLY from the passage for each answer.
Write your answers in boxes 1-8 on your answer sheet.
TEXT;

        $groupContent = <<<'TEXT'
Children's play

Uses of children's play
- building a 'magical kingdom' may help develop {{1}}
- board games involve {{2}} and turn-taking

Recent changes affecting children's play
- population of {{3}} have grown
- opportunities for free play are limited due to
  - fear of {{4}}
  - fear of {{5}}
  - increased {{6}} in schools

International policies on children's play
- it is difficult to find {{7}} to support new policies
- research needs to study the impact of play on the rest of the child's {{8}}
TEXT;

        $questions = [
            1 => [
                'content' => 'building a magical kingdom may help develop',
                'answer' => 'creativity',
                'feedback' => 'Đáp án: creativity. Đoạn 1 nói fantasy này giúp Alice phát triển capacity for creativity.',
            ],
            2 => [
                'content' => 'board games involve and turn-taking',
                'answer' => 'rules',
                'feedback' => 'Đáp án: rules. Đoạn 2 nói board game giúp trẻ học the need to follow rules and take turns.',
            ],
            3 => [
                'content' => 'population of have grown',
                'answer' => 'cities',
                'feedback' => 'Đáp án: cities. Đoạn 5 nói over half the people in the world now live in cities.',
            ],
            4 => [
                'content' => 'fear of',
                'answer' => 'traffic',
                'feedback' => 'Đáp án: traffic. Đoạn 5 nói outdoor play bị hạn chế bởi risk to do with traffic.',
            ],
            5 => [
                'content' => 'fear of',
                'answer' => 'crime',
                'feedback' => 'Đáp án: crime. Đoạn 5 nói phụ huynh muốn bảo vệ trẻ khỏi being the victims of crime.',
            ],
            6 => [
                'content' => 'increased in schools',
                'answer' => 'competition',
                'feedback' => 'Đáp án: competition. Đoạn 5 nói greater competition in academic learning and schools.',
            ],
            7 => [
                'content' => 'it is difficult to find to support new policies',
                'answer' => 'evidence',
                'feedback' => 'Đáp án: evidence. Đoạn 6 nói các chính sách thường thiếu evidence to base policies on.',
            ],
            8 => [
                'content' => "impact of play on the rest of the child's",
                'answer' => 'life',
                'feedback' => "Đáp án: life. Đoạn 8 nói có rất ít dữ liệu về impact it has on the child's later life.",
            ],
        ];

        $rows = [];

        foreach ($questions as $questionNo => $question) {
            $isFirstRow = $questionNo === 1;

            $rows[] = $this->row([
                'section_no' => 10,
                'section_title' => $isFirstRow ? "Reading Passage 1 - The importance of children's play" : '',
                'section_content' => $isFirstRow ? $passage : '',
                'group_no' => 1,
                'group_content' => $isFirstRow ? $groupContent : '',
                'group_instructions' => $isFirstRow ? $instructions : '',
                'group_question_type' => 'note_completion',
                'question_no' => $questionNo,
                'question_content' => $question['content'],
                'question_type' => 'note_completion',
                'point' => 1,
                'answer_no' => 1,
                'answer_content' => $question['answer'],
                'answer_feedback' => $question['feedback'],
                'is_correct' => 'yes',
                'question_feedback' => $question['feedback'],
            ]);
        }

        return $rows;
    }

    private function readingTableCompletionRows(): array
    {
        $passage = <<<'TEXT'
Case Study: Tourism New Zealand website

New Zealand is a small country of four million inhabitants, a long-haul flight from all the major tourist-generating markets of the world. Tourism currently makes up 9% of the country's gross domestic product, and is the country's largest export sector. Unlike other export sectors, which make products and then sell them overseas, tourism brings its customers to New Zealand. The product is the country itself - the people, the places and the experiences. In 1999, Tourism New Zealand launched a campaign to communicate a new brand position to the world. The campaign focused on New Zealand's scenic beauty, exhilarating outdoor activities and authentic Maori culture, and it made New Zealand one of the strongest national brands in the world.

A key feature of the campaign was the website www.newzealand.com, which provided potential visitors to New Zealand with a single gateway to everything the destination had to offer. The heart of the website was a database of tourism services operators, both those based in New Zealand and those based abroad which offered tourism service to the country. Any tourism-related business could be listed by filling in a simple form. This meant that even the smallest bed and breakfast address or specialist activity provider could gain a web presence with access to an audience of long-haul visitors. In addition, because participating businesses were able to update the details they gave on a regular basis, the information provided remained accurate. And to maintain and improve standards, Tourism New Zealand organised a scheme whereby organisations appearing on the website underwent an independent evaluation against a set of agreed national standards of quality. As part of this, the effect of each business on the environment was considered.

To communicate the New Zealand experience, the site also carried features relating to famous people and places. One of the most popular was an interview with former New Zealand All Blacks rugby captain Tana Umaga. Another feature that attracted a lot of attention was an interactive journey through a number of the locations chosen for blockbuster films which had made use of New Zealand's stunning scenery as a backdrop. As the site developed, additional features were added to help independent travelers devise their own customised itineraries. To make it easier to plan motoring holidays, the site catalogued the most popular driving routes in the country, highlighting different routes according to the season and indicating distances and times.

Later, a Travel Planner feature was added, which allowed visitors to click and 'bookmark' places or attractions they were interested in, and then view the results on a map. The Travel Planner offered suggested routes and public transport options between the chosen locations. There were also links to accommodation in the area. By registering with the website, users could save their Travel Plan and return to it later, or print it out to take on the visit. The website also had a 'Your Words' section where anyone could submit a blog of their New Zealand travels for possible inclusion on the website.

The Tourism New Zealand website won two Webby awards for online achievement and innovation. More importantly perhaps, the growth of tourism to New Zealand was impressive. Overall tourism expenditure increased by an average of 6.9% per year between 1999 and 2004. From Britain, visits to New Zealand grew at an average annual rate of 13% between 2002 and 2006, compared to a rate of 4% overall for British visits abroad.

The website was set up to allow both individuals and travel organisations to create itineraries and travel packages to suit their own needs and interests. On the website, visitors can search for activities not solely by geographical location, but also by the particular nature of the activity. This is important as research shows that activities are the key driver of visitor satisfaction, contributing 74% to visitor satisfaction, while transport and accommodation account for the remaining 26%. The more activities that visitors undertake, the more satisfied they will be. It has also been found that visitors enjoy cultural activities most when they are interactive, such as visiting a marae (meeting ground) to learn about traditional Maori life. Many long-haul travelers enjoy such learning experiences, which provide them with stories to take home to their friends and family. In addition, it appears that visitors to New Zealand don't want to be 'one of the crowd' and find activities that involve only a few people more special and meaningful.

It could be argued that New Zealand is not a typical destination. New Zealand is a small country with a visitor economy composed mainly of small businesses. It is generally perceived as a safe English-speaking country with a reliable transport infrastructure. Because of the long-haul flight, most visitors stay for longer (average 20 days) and want to see as much of the country as possible on what is often seen as a once-in-a-lifetime visit. However, the underlying lessons apply anywhere - the effectiveness of a strong brand, a strategy based on unique experiences and a comprehensive and user-friendly website.
TEXT;

        $instructions = <<<'TEXT'
Questions 1-7
Complete the table below.
Choose ONE WORD ONLY from the passage for each answer.
Write your answers in boxes 1-7 on your answer sheet.
TEXT;

        $groupContent = <<<'HTML'
<table>
    <thead>
        <tr>
            <th>Section of website</th>
            <th>Comments</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Database of tourism services</td>
            <td>easy for tourism-related businesses to get on the list<br>allowed businesses to {{1}} information regularly<br>provided a country-wide evaluation of businesses, including their impact on the {{2}}</td>
        </tr>
        <tr>
            <td>Special features on local topics</td>
            <td>e.g. an interview with a former sports {{3}}, and an interactive tour of various locations used in {{4}}</td>
        </tr>
        <tr>
            <td>Information on driving routes</td>
            <td>varied depending on the {{5}}</td>
        </tr>
        <tr>
            <td>Travel Planner</td>
            <td>included a map showing selected places, details of public transport and local {{6}}</td>
        </tr>
        <tr>
            <td>Your Words</td>
            <td>travellers could send a link to their {{7}}</td>
        </tr>
    </tbody>
</table>
HTML;

        $questions = [
            1 => [
                'content' => 'allowed businesses to information regularly',
                'answer' => 'update',
                'feedback' => 'Đáp án: update. Đoạn 2 nói participating businesses were able to update the details they gave on a regular basis.',
            ],
            2 => [
                'content' => 'including their impact on the',
                'answer' => 'environment',
                'feedback' => 'Đáp án: environment. Đoạn 2 nói effect of each business on the environment was considered.',
            ],
            3 => [
                'content' => 'an interview with a former sports',
                'answer' => 'captain',
                'feedback' => 'Đáp án: captain. Đoạn 3 nhắc former New Zealand All Blacks rugby captain Tana Umaga.',
            ],
            4 => [
                'content' => 'locations used in',
                'answer' => 'films',
                'feedback' => 'Đáp án: films. Đoạn 3 nói locations chosen for blockbuster films.',
            ],
            5 => [
                'content' => 'varied depending on the',
                'answer' => 'season',
                'feedback' => 'Đáp án: season. Đoạn 3 nói driving routes được highlight according to the season.',
            ],
            6 => [
                'content' => 'public transport and local',
                'answer' => 'accommodation',
                'feedback' => 'Đáp án: accommodation. Đoạn 4 nói there were also links to accommodation in the area.',
            ],
            7 => [
                'content' => 'travellers could send a link to their',
                'answer' => 'blog',
                'feedback' => 'Đáp án: blog. Đoạn 4 nói Your Words cho phép submit a blog of their New Zealand travels.',
            ],
        ];

        $rows = [];

        foreach ($questions as $questionNo => $question) {
            $isFirstRow = $questionNo === 1;

            $rows[] = $this->row([
                'section_no' => 11,
                'section_title' => $isFirstRow ? 'Reading Passage 1 - Tourism New Zealand website' : '',
                'section_content' => $isFirstRow ? $passage : '',
                'group_no' => 1,
                'group_content' => $isFirstRow ? $groupContent : '',
                'group_instructions' => $isFirstRow ? $instructions : '',
                'group_question_type' => 'table_completion',
                'question_no' => $questionNo,
                'question_content' => $question['content'],
                'question_type' => 'table_completion',
                'point' => 1,
                'answer_no' => 1,
                'answer_content' => $question['answer'],
                'answer_feedback' => $question['feedback'],
                'is_correct' => 'yes',
                'question_feedback' => $question['feedback'],
            ]);
        }

        return $rows;
    }

    private function readingFlowChartCompletionRows(): array
    {
        $passage = <<<'TEXT'
Careers with Kiwi Air

Flight Attendants - Recruitment and Training Process - Recruitment

The position of Flight Attendant is one of prestige and immense responsibility. Recruitment is conducted according to operational demands and there can be periods of up to 12 months where no new intake is required. However, applications are always welcomed.

After you submit your initial application online, the Kiwi Air HR Services Team review the details you have provided. Candidates whose details closely match the requirements of the position are then contacted via email advising that their application has progressed to the next stage of the recruitment process. Potential candidates are then asked to attend a Walk-In Day. This could occur several weeks or months after the original application has been submitted depending on current needs.

The Walk-In Day consists of a brief presentation about the role and a short interview. Candidates who are successful on the Walk-In Day are notified within 10 days and invited to attend an Assessment Centre. Please note that candidates are required to pass a swimming test before attending the Assessment Centre. At the Assessment Centre, candidates attend an interview as well as participating in a number of assessments. Verbal references are then requested, and candidates attend a medical check.

At times, there may not be a need to recruit for Flight Attendant positions. However, the company continuously maintains a 'recruitment pool' of those who have completed the Assessment Centre stage. These candidates are contacted when a need for Flight Attendants is established and attend a full interview before a decision is made on whether to extend an offer of employment.

Due to the volume of applications received, Kiwi Air is not able to offer verbal feedback to candidates at any stage of the recruitment process. Unsuccessful candidates may reapply at any time after 12 months from the date at which their applications are declined.

Training

Upon being offered a role as a trainee Flight Attendant, a 5-week training course is undertaken at our Inflight Services Training Centre in Auckland. This covers emergency procedures, customer care and service delivery, and equipment knowledge. To successfully complete the course, high standards must be attained and maintained in all subjects.
TEXT;

        $instructions = <<<'TEXT'
Questions 21-27
Complete the flow-chart below.
Choose NO MORE THAN TWO WORDS from the text for each answer.
Write your answers in boxes 21-27 on your answer sheet.
TEXT;

        $groupContent = <<<'HTML'
<h3>Flight attendants of Kiwi Air - Recruitment and Training Process</h3>
<p>Candidates go online to complete their {{21}}.</p>
<p>&darr;</p>
<p>Suitable candidates are then invited to come to a {{22}}.</p>
<p>&darr;</p>
<p>After having satisfactorily completed a {{23}}, successful candidates will then go to an Assessment Centre.</p>
<p>&darr;</p>
<p>Kiwi Air then asks for {{24}} and candidates are required to undergo a medical check.</p>
<p>&darr;</p>
<p>If there is no immediate need for flight attendants, successful candidates are put into a {{25}}.</p>
<p>&darr;</p>
<p>When the need arises, these candidates will then be given a {{26}}, after which they may be offered a job.</p>
<p>&darr;</p>
<p>On starting the job, a 5-week training programme is given which includes how to look after passengers and what to do in an {{27}}.</p>
HTML;

        $questions = [
            21 => [
                'content' => 'Candidates go online to complete their.',
                'answer' => 'application',
                'feedback' => 'Đáp án: application. Bài đọc nói After you submit your initial application online.',
            ],
            22 => [
                'content' => 'Suitable candidates are then invited to come to a.',
                'answer' => 'Walk-In Day',
                'feedback' => 'Đáp án: Walk-In Day. Bài đọc nói potential candidates are then asked to attend a Walk-In Day.',
            ],
            23 => [
                'content' => 'After having satisfactorily completed a, successful candidates will then go to an Assessment Centre.',
                'answer' => 'swimming test',
                'feedback' => 'Đáp án: swimming test. Candidates are required to pass a swimming test before attending the Assessment Centre.',
            ],
            24 => [
                'content' => 'Kiwi Air then asks for and candidates are required to undergo a medical check.',
                'answer' => 'verbal references',
                'feedback' => 'Đáp án: verbal references. Bài đọc nói Verbal references are then requested.',
            ],
            25 => [
                'content' => 'If there is no immediate need for flight attendants, successful candidates are put into a.',
                'answer' => 'recruitment pool',
                'feedback' => "Đáp án: recruitment pool. Công ty duy trì một 'recruitment pool' cho ứng viên đã hoàn thành Assessment Centre.",
            ],
            26 => [
                'content' => 'When the need arises, these candidates will then be given a, after which they may be offered a job.',
                'answer' => 'full interview',
                'feedback' => 'Đáp án: full interview. Candidates attend a full interview before a decision is made.',
            ],
            27 => [
                'content' => 'Training includes how to look after passengers and what to do in an.',
                'answer' => 'emergency',
                'feedback' => 'Đáp án: emergency. Training covers emergency procedures, customer care and service delivery.',
            ],
        ];

        $rows = [];

        foreach ($questions as $questionNo => $question) {
            $isFirstRow = $questionNo === 21;

            $rows[] = $this->row([
                'section_no' => 12,
                'section_title' => $isFirstRow ? 'Reading Passage 1 - Careers with Kiwi Air' : '',
                'section_content' => $isFirstRow ? $passage : '',
                'group_no' => 1,
                'group_content' => $isFirstRow ? $groupContent : '',
                'group_instructions' => $isFirstRow ? $instructions : '',
                'group_question_type' => 'flow_chart_completion',
                'question_no' => $questionNo,
                'question_content' => $question['content'],
                'question_type' => 'flow_chart_completion',
                'point' => 1,
                'answer_no' => 1,
                'answer_content' => $question['answer'],
                'answer_feedback' => $question['feedback'],
                'is_correct' => 'yes',
                'question_feedback' => $question['feedback'],
            ]);
        }

        return $rows;
    }

    private function readingDiagramLabelCompletionRows(): array
    {
        $passage = <<<'TEXT'
The Falkirk Wheel

A unique engineering achievement

The Falkirk Wheel in Scotland is the world's first and only rotating boat lift. Opened in 2002, it is central to the ambitious 84.5m Millennium Link project to restore navigability across Scotland by reconnecting the historic waterways of the Forth & Clyde and Union Canals.

The major challenge of the project lay in the fact that the Forth & Clyde Canal is situated 35 metres below the level of the Union Canal. Historically, the two canals had been joined near the town of Falkirk by a sequence of 11 locks - enclosed sections of canal in which the water level could be raised or lowered - that stepped down across a distance of 1.5 km. This had been dismantled in 1933, thereby breaking the link. When the project was launched in 1994, the British Waterways authority were keen to create a dramatic twenty-first-century landmark which would not only be a fitting commemoration of the Millennium, but also a lasting symbol of the economic regeneration of the region.

Numerous ideas were submitted for the project, including concepts ranging from rolling eggs to tilting tanks, from giant seesaws to overhead monorails. The eventual winner was a plan for the huge rotating steel boat lift which was to become The Falkirk Wheel. The unique shape of the structure is claimed to have been inspired by various sources, both manmade and natural, most notably a Celtic double headed axe, but also the vast turning propeller of a ship, the ribcage of a whale or the spine of a fish.

The various parts of The Falkirk Wheel were all constructed and assembled, like one giant toy building set, at Butterley Engineering's Steelworks in Derbyshire, some 400 km from Falkirk. A team there carefully assembled the 1,200 tonnes of steel, painstakingly fitting the pieces together to an accuracy of just 10 mm to ensure a perfect final fit. In the summer of 2001, the structure was then dismantled and transported on 35 lorries to Falkirk, before all being bolted back together again on the ground, and finally lifted into position in five large sections by crane. The Wheel would need to withstand immense and constantly changing stresses as it rotated, so to make the structure more robust, the steel sections were bolted rather than welded together. Over 45,000 bolt holes were matched with their bolts, and each bolt was hand-tightened.

The Wheel consists of two sets of opposing axe-shaped arms, attached about 25 metres apart to a fixed central spine. Two diametrically opposed water-filled 'gondolas', each with a capacity of 360,000 litres, are fitted between the ends of the arms. These gondolas always weigh the same, whether or not they are carrying boats. This is because, according to Archimedes' principle of displacement, floating objects displace their own weight in water. So when a boat enters a gondola, the amount of water leaving the gondola weighs exactly the same as the boat. This keeps the Wheel balanced and so, despite its enormous mass, it rotates through 180 degrees in five and a half minutes while using very little power. It takes just 1.5 kilowatt-hours (5.4 MJ) of energy to rotate the Wheel - roughly the same as boiling eight small domestic kettles of water.

Boats needing to be lifted up enter the canal basin at the level of the Forth & Clyde Canal and then enter the lower gondola of the Wheel. Two hydraulic steel gates are raised, so as to seal the gondola off from the water in the canal basin. The water between the gates is then pumped out. A hydraulic clamp, which prevents the arms of the Wheel moving while the gondola is docked, is removed, allowing the Wheel to turn. In the central machine room an array of ten hydraulic motors then begins to rotate the central axle. The axle connects to the outer arms of the Wheel, which begin to rotate at a speed of 1/8 of a revolution per minute. As the wheel rotates, the gondolas are kept in the upright position by a simple gearing system. Two eight-metre-wide cogs orbit a fixed inner cog of the same width, connected by two smaller cogs travelling in the opposite direction to the outer cogs - so ensuring that the gondolas always remain level. When the gondola reaches the top, the boat passes straight onto the aqueduct situated 24 metres above the canal basin.

The remaining 11 metres of lift needed to reach the Union Canal is achieved by means of a pair of locks. The Wheel could not be constructed to elevate boats over the full 35-metre difference between the two canals, owing to the presence of the historically important Antonine Wall, which was built by the Romans in the second century AD. Boats travel under this wall via a tunnel, then through the locks, and finally on to the Union Canal.
TEXT;

        $instructions = <<<'TEXT'
Questions 20-26
Label the diagram below.
Choose ONE WORD from the passage for each answer.
Write your answers in boxes 20-26 on your answer sheet.
TEXT;

        $groupContent = <<<'HTML'
<h3>How a boat is lifted on the Falkirk Wheel</h3>
<p>A pair of {{20}} are lifted in order to shut out water from the canal basin.</p>
<p>A {{21}} is taken out, enabling the Wheel to rotate.</p>
<p>Hydraulic motors drive the central {{22}}.</p>
<p>A range of different-sized {{23}} ensures the boat keeps upright.</p>
<p>The boat reaches the top of the Wheel, then moves directly onto the {{24}}.</p>
<p>The boat travels through a tunnel beneath the Roman {{25}}.</p>
<p>{{26}} raise the boat 11m to the level of the Union Canal.</p>
HTML;

        $questions = [
            20 => [
                'content' => 'A pair of are lifted in order to shut out water from the canal basin.',
                'answer' => 'gates',
                'feedback' => 'Đáp án: gates. Đoạn 6 nói Two hydraulic steel gates are raised to seal the gondola off from the water.',
            ],
            21 => [
                'content' => 'A is taken out, enabling the Wheel to rotate.',
                'answer' => 'clamp',
                'feedback' => 'Đáp án: clamp. Đoạn 6 nói A hydraulic clamp is removed, allowing the Wheel to turn.',
            ],
            22 => [
                'content' => 'Hydraulic motors drive the central.',
                'answer' => 'axle',
                'feedback' => 'Đáp án: axle. Đoạn 6 nói ten hydraulic motors begin to rotate the central axle.',
            ],
            23 => [
                'content' => 'A range of different-sized ensures the boat keeps upright.',
                'answer' => 'cogs',
                'feedback' => 'Đáp án: cogs. Đoạn 6 mô tả gearing system gồm các cogs để gondolas remain level.',
            ],
            24 => [
                'content' => 'The boat reaches the top of the Wheel, then moves directly onto the.',
                'answer' => 'aqueduct',
                'feedback' => 'Đáp án: aqueduct. Đoạn 6 nói the boat passes straight onto the aqueduct.',
            ],
            25 => [
                'content' => 'The boat travels through a tunnel beneath the Roman.',
                'answer' => 'wall',
                'feedback' => 'Đáp án: wall. Đoạn cuối nói boats travel under this wall via a tunnel.',
            ],
            26 => [
                'content' => 'raise the boat 11m to the level of the Union Canal.',
                'answer' => 'locks',
                'feedback' => 'Đáp án: locks. Đoạn cuối nói remaining 11 metres of lift is achieved by means of a pair of locks.',
            ],
        ];

        $rows = [];

        foreach ($questions as $questionNo => $question) {
            $isFirstRow = $questionNo === 20;

            $rows[] = $this->row([
                'section_no' => 13,
                'section_title' => $isFirstRow ? 'Reading Passage 1 - The Falkirk Wheel' : '',
                'section_content' => $isFirstRow ? $passage : '',
                'group_no' => 1,
                'group_content' => $isFirstRow ? $groupContent : '',
                'group_instructions' => $isFirstRow ? $instructions : '',
                'group_question_type' => 'diagram_label_completion',
                'question_no' => $questionNo,
                'question_content' => $question['content'],
                'question_type' => 'diagram_label_completion',
                'point' => 1,
                'answer_no' => 1,
                'answer_content' => $question['answer'],
                'answer_feedback' => $question['feedback'],
                'is_correct' => 'yes',
                'question_feedback' => $question['feedback'],
            ]);
        }

        return $rows;
    }

    private function readingShortAnswerQuestionRows(): array
    {
        $passage = <<<'TEXT'
The Concept of Childhood in Western Countries

The history of childhood has been a heated topic in social history since the highly influential book Centuries of Childhood, written by French historian Philippe Aries, emerged in 1960. He claimed that 'childhood' is a concept created by modern society.

A
Whether childhood is itself a recent invention has been one of the most intensely debated issues in the history of childhood. Historian Philippe Aries asserted that children were regarded as miniature adults, with all the intellect and personality that this implies, in Western Europe during the Middle Ages (up to about the end of the 15th century). After scrutinizing medieval pictures and diaries, he concluded that there was no distinction between children and adults for they shared similar leisure activities and work. However, this does not mean children were neglected, forsaken or despised, he argued. The idea of childhood corresponds to awareness about the peculiar nature of childhood, which distinguishes the child from adults, even the young adults. Therefore, the concept of childhood is not to be confused with affection for children.

B
Traditionally, children played a functional role in contributing to the family income in history. Under this circumstance, children were considered to be useful. Back in the Middle Ages, children of 5 or 6 years old did necessary chores for their parents. During the 16th century, children of 9 or 10 years old were often encouraged or even forced to leave their family to work as servants for wealthier families or apprentices for a trade.

C
In the 18th and 19th centuries, industrialisation created a new demand for child labour; thus many children were forced to work for a long time in mines, workshops and factories. The issue of whether long hours of laboring would interfere with children's growing bodies began to perplex social reformers. Some of them started to realize the potential of systematic studies to monitor how far these early deprivations might be influencing children's development.

D
The concerns of reformers gradually had some impact on the working condition of children. For example, in Britain, the Factory Act of 1833 signified the emergence of the legal protection of children from exploitation and was also associated with the rise of schools for factory children. Due partly to factory reform, the worst forms of child exploitation were eliminated gradually. The influence of trade unions and economic changes also contributed to the evolution by leaving some forms of child labour redundant during the 19th century. Initiating children into work as 'useful' children was no longer a priority, and childhood was deemed to be a time for play and education for all children instead of a privileged minority. Childhood was increasingly understood as a more extended phase of dependency, development and learning with the delay of the age for starting full-time work. Even so, work continued to play a significant, if less essential, role in children's lives in the later 19th and 20th centuries. Finally, the 'useful child' has become a controversial concept during the first decade of the 21st century, especially in the context of global concern about large numbers of children engaged in child labour.

E
The half-time schools established upon the Factory Act of 1833 allowed children to work and attend school. However, a significant proportion of children never attended school in the 1840s, and even if they did, they dropped out by the age of 10 or 11. By the end of the 19th century in Britain, the situation changed dramatically, and schools became the core to the concept of a 'normal' childhood.

F
It is no longer a privilege for children to attend school and all children are expected to spend a significant part of their day in a classroom. Once in school, children's lives could be separated from domestic life and the adult world of work. In this way, school turns into an institution dedicated to shaping the minds, behavior and morals of the young. Besides, education dominated the management of children's waking hours through the hours spent in the classroom, homework, the growth of after school activities, and the importance attached to parental involvement.

G
Industrialisation, urbanization and mass schooling pose new challenges for those who are responsible for protecting children's welfare, as well as promoting their learning. An increasing number of children are being treated as a group with unique needs, and are organized into groups in the light of their age. For instance, teachers need to know some information about what to expect of children in their classrooms, what kinds of instruction are appropriate for different age groups, and what is the best way to assess children's progress. Also, they want tools enabling them to sort and select children according to their abilities and potential.
TEXT;

        $instructions = <<<'TEXT'
Questions 8-13
Answer the questions below.
Choose NO MORE THAN THREE WORDS from the passage for each answer.
Write your answers in boxes 8-13 on your answer sheet.
TEXT;

        $questions = [
            8 => [
                'content' => "What has not become a hot topic until the French historian Philippe Aries' book caused great attention?",
                'answer' => 'history of childhood',
                'feedback' => 'Đáp án: history of childhood. Mở bài nói The history of childhood has been a heated topic since Centuries of Childhood appeared in 1960.',
            ],
            9 => [
                'content' => 'What image did Aries believe children are supposed to be like in Western Europe during the Middle Ages?',
                'answer' => 'miniature adults',
                'feedback' => 'Đáp án: miniature adults. Đoạn A nói children were regarded as miniature adults in Western Europe during the Middle Ages.',
            ],
            10 => [
                'content' => 'What historical event generated the need for a large number of children to work for a long time in the 18th and 19th centuries?',
                'answer' => 'industrialisation',
                'feedback' => 'Đáp án: industrialisation. Đoạn C nói industrialisation created a new demand for child labour.',
            ],
            11 => [
                'content' => 'What bill was enacted to protect children from exploitation in Britain in the 1800s?',
                'answer' => 'the Factory Act',
                'feedback' => 'Đáp án: the Factory Act. Đoạn D nói the Factory Act of 1833 signified legal protection of children from exploitation.',
            ],
            12 => [
                'content' => 'What activities were more and more regarded as preferable to almost all children in the 19th century?',
                'answer' => 'play and education',
                'feedback' => 'Đáp án: play and education. Đoạn D nói childhood was deemed to be a time for play and education for all children.',
            ],
            13 => [
                'content' => 'In what place did children spend the majority of time during their day in school?',
                'answer' => 'classroom',
                'feedback' => 'Đáp án: classroom. Đoạn F nói all children are expected to spend a significant part of their day in a classroom.',
            ],
        ];

        $rows = [];

        foreach ($questions as $questionNo => $question) {
            $isFirstRow = $questionNo === 8;

            $rows[] = $this->row([
                'section_no' => 14,
                'section_title' => $isFirstRow ? 'Reading Passage 1 - The Concept of Childhood in Western Countries' : '',
                'section_content' => $isFirstRow ? $passage : '',
                'group_no' => 1,
                'group_instructions' => $isFirstRow ? $instructions : '',
                'group_question_type' => 'short_answer_questions',
                'question_no' => $questionNo,
                'question_content' => $question['content'],
                'question_type' => 'short_answer_questions',
                'point' => 1,
                'answer_no' => 1,
                'answer_content' => $question['answer'],
                'answer_feedback' => $question['feedback'],
                'is_correct' => 'yes',
                'question_feedback' => $question['feedback'],
            ]);
        }

        return $rows;
    }

    private function row(array $values): array
    {
        if (isset($values['section_content']) && is_string($values['section_content'])) {
            $values['section_content'] = $this->sectionContentForTemplate($values['section_content']);
        }

        return array_map(
            fn(string $heading): mixed => $values[$heading] ?? '',
            SkillExcelImportService::HEADINGS
        );
    }

    private function sectionContentForTemplate(string $content): string|RichText
    {
        $content = trim($content);

        if ($content === '') {
            return '';
        }

        return $this->richTextWithBoldPhrases($content, self::SECTION_CONTENT_BOLD_PHRASES);
    }

    private function richTextWithBoldPhrases(string $text, array $phrases): string|RichText
    {
        $matches = [];

        foreach (array_unique(array_filter($phrases)) as $phrase) {
            $offset = 0;

            while (($position = stripos($text, $phrase, $offset)) !== false) {
                $matches[] = [
                    'start' => $position,
                    'length' => strlen($phrase),
                ];

                $offset = $position + strlen($phrase);
            }
        }

        if (!$matches) {
            return $text;
        }

        usort($matches, function (array $left, array $right): int {
            return $left['start'] <=> $right['start']
                ?: $right['length'] <=> $left['length'];
        });

        $filteredMatches = [];
        $lastEnd = -1;

        foreach ($matches as $match) {
            if ($match['start'] < $lastEnd) {
                continue;
            }

            $filteredMatches[] = $match;
            $lastEnd = $match['start'] + $match['length'];
        }

        $richText = new RichText();
        $cursor = 0;

        foreach ($filteredMatches as $match) {
            if ($match['start'] > $cursor) {
                $richText->createText(substr($text, $cursor, $match['start'] - $cursor));
            }

            $run = $richText->createTextRun(substr($text, $match['start'], $match['length']));
            $run->getFont()?->setBold(true);

            $cursor = $match['start'] + $match['length'];
        }

        if ($cursor < strlen($text)) {
            $richText->createText(substr($text, $cursor));
        }

        return $richText;
    }
}
