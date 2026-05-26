        <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
        <script src="{{ asset('assets/js/admin-editor.js') }}"></script>
        <script>
            (function () {
                'use strict';

                let sectionIndex = {{ count($skill->sections) }};
                const exams = @json($exams);
                const currentTestId = {{ $skill->exam_test_id }};
                const skillType = '{{ $skill->skill_type }}'; // Get current skill type
                const questionTypeOptions = @json($questionTypeOptions);
                const escapeHtml = (value) => String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
                const buildQuestionTypeOptionsHtml = (selectedValue = '') => Object.entries(questionTypeOptions)
                    .map(([value, label]) => `<option value="${escapeHtml(value)}" ${value === selectedValue ? 'selected' : ''}>${escapeHtml(label)}</option>`)
                    .join('');
                const questionTypeOptionsHtml = buildQuestionTypeOptionsHtml();

                // Event delegation for all button clicks
                document.addEventListener('click', function (e) {
                    const btn = e.target.closest('[data-action]');
                    if (!btn) return;

                    const action = btn.dataset.action;
                    const actions = {
                        'add-section': () => addSection(),
                        'delete-section': () => deleteItem(btn, '.section-item', 'section'),
                        'add-group': () => addQuestionGroup(btn),
                        'delete-group': () => deleteItem(btn, '.question-group-item', 'group'),
                        'add-question': () => addQuestion(btn),
                        'delete-question': () => deleteItem(btn, '.question-item', 'question'),
                        'add-direct-question': () => addDirectQuestion(btn),
                        'delete-direct-question': () => deleteItem(btn, '.direct-question-item', 'direct question'),
                        'delete-answer': () => deleteItem(btn, '.answer-item', 'answer'),
                        'move-answer-up': () => moveAnswer(btn, -1),
                        'move-answer-down': () => moveAnswer(btn, 1),
                        'add-multiple-answers': () => addMultipleAnswers(btn)
                    };

                    if (actions[action]) {
                        e.preventDefault();
                        actions[action]();
                    }
                });

                // Handle Bootstrap collapse icon rotation
                document.addEventListener('shown.bs.collapse', e => {
                    const btn = document.querySelector(`[data-bs-target="#${e.target.id}"] i`);
                    if (btn) btn.className = 'bi bi-chevron-down';
                });

                document.addEventListener('hidden.bs.collapse', e => {
                    const btn = document.querySelector(`[data-bs-target="#${e.target.id}"] i`);
                    if (btn) btn.className = 'bi bi-chevron-right';
                });

                // Question type change handler
                document.addEventListener('change', function (e) {
                    // Handle individual question type select
                    if (e.target.matches('.question-type-select')) {
                        const questionItem = e.target.closest('.question-item');
                        const optionsSection = questionItem?.querySelector('.question-options-section');
                        if (optionsSection) {
                            optionsSection.style.display = e.target.value === 'multiple_choice' ? 'block' : 'none';
                        }
                    }
                    
                    // Handle group question type select (for Table Selection)
                    if (e.target.matches('.group-question-type-select')) {
                        const groupItem = e.target.closest('.question-group-item');
                        const tableSelectionOptions = groupItem?.querySelector('.table-selection-options');
                        if (tableSelectionOptions) {
                            tableSelectionOptions.style.display = e.target.value === 'table_selection' ? 'block' : 'none';
                        }
                    }
                });

                // Initialize on DOM ready
                document.addEventListener('DOMContentLoaded', function () {
                    initExamSelect();
                    initSkillTypeSelect();
                    initializeAllEditors();
                    initImagePreview();
                    initBuilderNavigationScroll();
                });

                // Initialize image preview
                function initImagePreview() {
                    const imageInput = document.getElementById('image');
                    const imagePreview = document.getElementById('imagePreview');
                    const currentImage = document.getElementById('currentImage');

                    if (imageInput) {
                        imageInput.addEventListener('change', function(e) {
                            const file = e.target.files[0];
                            if (file) {
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    imagePreview.querySelector('img').src = e.target.result;
                                    imagePreview.style.display = 'block';
                                    if (currentImage) {
                                        currentImage.style.display = 'none';
                                    }
                                };
                                reader.readAsDataURL(file);
                            } else {
                                imagePreview.style.display = 'none';
                                if (currentImage) {
                                    currentImage.style.display = 'block';
                                }
                            }
                        });
                    }
                }

                function initBuilderNavigationScroll() {
                    const navContainer = document.getElementById('sectionNavigation');
                    const scrollBody = document.querySelector('.sections-builder-scroll-body');

                    if (!navContainer || !scrollBody) return;

                    navContainer.addEventListener('click', function (e) {
                        const link = e.target.closest('a[href^="#"]');
                        if (!link) return;

                        const targetId = link.getAttribute('href').slice(1);
                        const target = document.getElementById(targetId);
                        if (!target || !scrollBody.contains(target)) return;

                        e.preventDefault();

                        const bodyRect = scrollBody.getBoundingClientRect();
                        const targetRect = target.getBoundingClientRect();
                        const top = scrollBody.scrollTop + targetRect.top - bodyRect.top - 8;

                        scrollBody.scrollTo({
                            top,
                            behavior: 'smooth'
                        });

                        history.replaceState(null, '', `#${targetId}`);
                    });
                }

                // Initialize exam select
                function initExamSelect() {
                    const examSelect = document.getElementById('exam_id');
                    const testSelect = document.getElementById('exam_test_id');

                    examSelect.addEventListener('change', function () {
                        testSelect.innerHTML = '<option value="">-- Đang tải... --</option>';
                        testSelect.disabled = true;

                        if (!this.value) {
                            testSelect.innerHTML = '<option value="">-- Vui lòng chọn Quiz Collection trước --</option>';
                            return;
                        }

                        const selectedExam = exams.find(exam => exam.id == this.value);
                        if (selectedExam?.tests) {
                            testSelect.innerHTML = '<option value="">Chọn Quiz Group</option>';
                            selectedExam.tests.forEach(test => {
                                const opt = new Option(test.name, test.id, false, test.id == currentTestId);
                                testSelect.add(opt);
                            });
                            testSelect.disabled = false;
                        } else {
                            testSelect.innerHTML = '<option value="">-- Không có test nào --</option>';
                        }
                    });

                    examSelect.dispatchEvent(new Event('change'));
                }

                // Initialize skill type classes (select is disabled in edit mode)
                function initSkillTypeSelect() {
                    const skillTypeSelect = document.getElementById('skill_type');
                    const type = skillTypeSelect.value;
                    const container = document.getElementById('sectionsContainer');

                    // Just set container class once, no need for event listener since select is disabled
                    ['listening', 'reading', 'writing', 'speaking'].forEach(skill => {
                        container?.classList.toggle(`skill-type-${skill}`, type === skill);
                    });
                }

                // Generic delete item function
                function deleteItem(btn, selector, type) {
                    if (!confirm(`Are you sure you want to delete this ${type}?`)) return;

                    const item = btn.closest(selector);
                    const parent = item.parentElement;
                    item.remove();

                    // Update numbering based on type
                    if (selector === '.section-item') {
                        updateSectionNumbers();
                    } else if (selector === '.question-group-item') {
                        updateGroupNumbers(parent.closest('.section-item'));
                    } else if (selector === '.question-item') {
                        updateQuestionNumbers(parent.closest('.question-group-item'));
                    } else if (selector === '.direct-question-item') {
                        updateDirectQuestionNumbers(parent.closest('.section-item'));
                    }

                    updateNavigation();
                }

                // Move answer up or down
                function moveAnswer(btn, direction) {
                    const item = btn.closest('.answer-item');
                    const sibling = direction < 0 ? item.previousElementSibling : item.nextElementSibling;

                    if (sibling?.classList.contains('answer-item')) {
                        direction < 0 ?
                            item.parentNode.insertBefore(item, sibling) :
                            item.parentNode.insertBefore(sibling, item);
                    }
                }

                // Add answer (single or multiple)
                function addMultipleAnswers(btn) {
                    const { section, group, question } = btn.dataset;
                    const input = btn.closest('.answers-list-section')?.querySelector('.answer-count-input');
                    const count = input ? parseInt(input.value) || 1 : 1;

                    for (let i = 0; i < count; i++) {
                        addAnswer(btn, section, group, question);
                    }

                    if (input) input.value = 1;
                }

                // Add single answer
                function addAnswer(btn, sectionIdx, groupIdx, questionIdx) {
                    const answersList = btn.closest('.answers-list-section').querySelector('.answers-list');
                    const answerIndex = answersList.querySelectorAll('.answer-item').length;

                    // Detect question type to decide between dropdown or rich text for answer content
                    const questionItem = btn.closest('.question-item');
                    const qType = questionItem?.querySelector('.question-type-select')?.value || '';
                    const isYN = qType === 'yes_no_not_given';
                    const isTF = qType === 'true_false_not_given';

                    const dropdownHtml = () => {
                        const opts = isYN ? ['Yes','No','Not Given'] : ['True','False','Not Given'];
                        const optionsHTML = opts.map(opt => `<option value="${opt}" ${opt === (isYN ? 'Yes' : 'True') ? 'selected' : ''}>${opt}</option>`).join('');
                        return `
                            <select class="form-select form-select-sm" name="sections[${sectionIdx}][groups][${groupIdx}][questions][${questionIdx}][answers][${answerIndex}][content]">
                                ${optionsHTML}
                            </select>
                        `;
                    };

                    const answerContentHtml = (isYN || isTF)
                        ? dropdownHtml()
                        : `
                            <input type="hidden" id="answer-content-new-${sectionIdx}-${groupIdx}-${questionIdx}-${answerIndex}"
                                   name="sections[${sectionIdx}][groups][${groupIdx}][questions][${questionIdx}][answers][${answerIndex}][content]">
                            <div id="answer-content-new-${sectionIdx}-${groupIdx}-${questionIdx}-${answerIndex}-editor" style="background: white;"></div>
                        `;

                    answersList.insertAdjacentHTML('beforeend', `
                    <div class="answer-item mb-3 p-3" style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 0.375rem; position: relative;">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Answer Content</label>
                            ${answerContentHtml}
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Feedback</label>
                            <input type="hidden" id="answer-feedback-new-${sectionIdx}-${groupIdx}-${questionIdx}-${answerIndex}"
                                   name="sections[${sectionIdx}][groups][${groupIdx}][questions][${questionIdx}][answers][${answerIndex}][feedback]">
                            <div id="answer-feedback-new-${sectionIdx}-${groupIdx}-${questionIdx}-${answerIndex}-editor" style="background: white;"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="sections[${sectionIdx}][groups][${groupIdx}][questions][${questionIdx}][answers][${answerIndex}][is_correct]" value="1">
                                <label class="form-check-label">Is correct</label>
                            </div>
                            <button type="button" class="btn btn-sm text-danger" data-action="delete-answer">Delete Answer</button>
                        </div>
                        <div class="position-absolute" style="top: 10px; left: -25px; display: flex; flex-direction: column; gap: 5px;">
                            <button type="button" class="btn btn-sm btn-light border" data-action="move-answer-up"><i class="bi bi-chevron-up"></i></button>
                            <button type="button" class="btn btn-sm btn-light border" data-action="move-answer-down"><i class="bi bi-chevron-down"></i></button>
                        </div>
                    </div>
                `);

                    // Initialize editors only when using rich text mode
                    if (!(isYN || isTF)) {
                        queueEditorById(`answer-content-new-${sectionIdx}-${groupIdx}-${questionIdx}-${answerIndex}`);
                    }
                    queueEditorById(`answer-feedback-new-${sectionIdx}-${groupIdx}-${questionIdx}-${answerIndex}`);
                }

                // Add section
                function addSection() {
                    const container = document.getElementById('sectionsContainer');
                    const isSpeakingOrWriting = (skillType === 'speaking111' || skillType === 'writing222');
                    // Determine which questions container to show
                    const questionsContainerHTML = isSpeakingOrWriting ? `
                    
                        <div class="direct-questions-container mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6>Questions</h6>
                                <button type="button" class="btn btn-sm btn-outline-success" data-action="add-direct-question">
                                    <i class="bi bi-plus me-1"></i>Add Question
                                </button>
                            </div>
                            <div class="direct-questions-list"></div>
                        </div>
                    ` : `
                        <div class="question-groups-container mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6>Question Groups</h6>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-action="add-group">
                                    <i class="bi bi-plus me-1"></i>Add Question Group
                                </button>
                            </div>
                            <div class="groups-list"></div>
                        </div>
                    `;

                    container.insertAdjacentHTML('beforeend', `
                    <div class="section-item card mb-3" data-section-index="${sectionIndex}">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-sm btn-link text-dark p-0" data-bs-toggle="collapse" data-bs-target="#section-new-${sectionIndex}">
                                        <i class="bi bi-chevron-down"></i>
                                    </button>
                                    <strong>Section ${sectionIndex + 1}</strong>
                                </div>
                                <button type="button" class="btn btn-sm btn-link text-danger" data-action="delete-section">Delete Section</button>
                            </div>
                        </div>
                        <div class="card-body section-content collapse show" id="section-new-${sectionIndex}">

                                    <div class="exam-filter-sidebar d-none" data-skill="{{ $skill->skill_type ?? '' }}">
                            @foreach($skillFilters as $skillSlug => $filterItem)
                        <div class="filter-skill {{ $skillSlug }} {{ $skillSlug != ($skill->skill_type ?? '') ? 'd-none' : '' }}"
                            data-skill="{{ $skill->skill_type  }}">
                                @foreach($filterItem->children as $group)
                                <div class="filter-group mb-3"
                                 data-group-type="{{ Str::slug($group->name) }}"> <strong class="d-block mb-2">{{ $group->name }}</strong>
                                    @foreach($group->children as $value)
                                        <div class="form-check">
                                            <input
                                            class="form-check-input exam-filter-input"
                                            type="checkbox"
                                            data-skill="{{ $skillSlug }}"
                                            data-group="new_${sectionIndex}{{ $group->id }}"
                                            value="{{ $value->id }}"
                                            name="sections[${sectionIndex}][exam_filters][]"
                                            >
                                        <label class="form-check-label">
                                        {{ $value->name }}
                                        </label>
                                        </div>
                                    @endforeach
                                </div>
                             @endforeach

                             </div>
                                @endforeach
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Section Title</label>
                                <input type="text" class="form-control" name="sections[${sectionIndex}][title]" placeholder="Enter section title">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Section Content</label>
                                <input type="hidden" id="section-content-new-${sectionIndex}" name="sections[${sectionIndex}][content]">
                                <div id="section-content-new-${sectionIndex}-editor"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Section Feedback</label>
                                <input type="hidden" id="section-feedback-new-${sectionIndex}" name="sections[${sectionIndex}][feedback]">
                                <div id="section-feedback-new-${sectionIndex}-editor"></div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="hidden" name="sections[${sectionIndex}][answer_inputs_inside_content]" value="0">
                                    <input class="form-check-input" type="checkbox" name="sections[${sectionIndex}][answer_inputs_inside_content]" value="1">
                                    <label class="form-check-label">Answer inputs inside content</label>
                                </div>
                            </div>
                            ${questionsContainerHTML}
                        </div>
                    </div>
                `);

                    queueEditorById(`section-content-new-${sectionIndex}`);
                    queueEditorById(`section-feedback-new-${sectionIndex}`);
                    sectionIndex++;
                    updateSectionNumbers();
                    updateNavigation();
                }

                function updateSectionNumbers() {
                    document.querySelectorAll('.section-item').forEach((section, newIndex) => {
                        const oldIndex = section.dataset.sectionIndex;

                        // Update section index
                        section.dataset.sectionIndex = newIndex;
                        section.id = `section-${newIndex}`;

                        // Update all name attributes within this section
                        section.querySelectorAll('[name]').forEach(input => {
                            const name = input.getAttribute('name');
                            if (name && name.startsWith(`sections[${oldIndex}]`)) {
                                input.setAttribute('name', name.replace(`sections[${oldIndex}]`, `sections[${newIndex}]`));
                            }
                        });

                        // Update all id attributes for Quill editors
                        section.querySelectorAll('[id]').forEach(element => {
                            const id = element.getAttribute('id');
                            if (id && id.includes(`-${oldIndex}-`)) {
                                element.setAttribute('id', id.replace(`-${oldIndex}-`, `-${newIndex}-`));
                            }
                        });

                        // Update header text
                        const headerText = section.querySelector('.card-header strong');
                        if (headerText) {
                            headerText.textContent = `Section ${newIndex + 1}`;
                        }
                    });
                }

                // Add question group
                function addQuestionGroup(btn) {
                    const sectionItem = btn.closest('.section-item');
                    const sectionIdx = sectionItem.dataset.sectionIndex;
                    const groupsList = sectionItem.querySelector('.groups-list');
                    const groupIndex = groupsList.querySelectorAll('.question-group-item').length;

                    groupsList.insertAdjacentHTML('beforeend', `
                    <div class="question-group-item border rounded p-3 mb-3" data-group-index="${groupIndex}">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-link text-dark p-0" data-bs-toggle="collapse" data-bs-target="#group-new-${sectionIdx}-${groupIndex}">
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                                <strong>Question Group ${groupIndex + 1}</strong>
                            </div>
                            <button type="button" class="btn btn-sm btn-link text-danger" data-action="delete-group">Delete Question Group</button>
                        </div>
                        <div class="group-content collapse show" id="group-new-${sectionIdx}-${groupIndex}">
                            <div class="mb-3">
                                <label class="form-label">Question Group Content</label>
                                <input type="hidden" id="group-content-new-${sectionIdx}-${groupIndex}" name="sections[${sectionIdx}][groups][${groupIndex}][content]">
                                <div id="group-content-new-${sectionIdx}-${groupIndex}-editor"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Question Group Instructions</label>
                                <input type="hidden" id="group-instructions-new-${sectionIdx}-${groupIndex}" name="sections[${sectionIdx}][groups][${groupIndex}][instructions]">
                                <div id="group-instructions-new-${sectionIdx}-${groupIndex}-editor"></div>
                                <small class="form-text text-muted">Example: Questions 7 - 10, Complete the notes below, Write ONE WORD ONLY...</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Question Type cha</label>
                                <select class="form-select group-question-type-select" name="sections[${sectionIdx}][groups][${groupIndex}][question_type]">
                                    ${questionTypeOptionsHtml}
                                </select>
                            </div>
                            <div class="mb-3 table-selection-options" style="display: none;">
                                <label class="form-label">Number of Options</label>
                                <input type="number" class="form-control" name="sections[${sectionIdx}][groups][${groupIndex}][number_of_options]" value="4" min="2" max="10" placeholder="Enter number of options (e.g., 4)">
                                <small class="form-text text-muted">Number of dropdown options for table selection questions</small>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input type="hidden" name="sections[${sectionIdx}][groups][${groupIndex}][answer_inputs_inside_content]" value="0">
                                        <input class="form-check-input" type="checkbox" name="sections[${sectionIdx}][groups][${groupIndex}][answer_inputs_inside_content]" value="1">
                                        <label class="form-check-label">Answer inputs inside content</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input type="hidden" name="sections[${sectionIdx}][groups][${groupIndex}][split_questions_side_by_side]" value="0">
                                        <input class="form-check-input" type="checkbox" name="sections[${sectionIdx}][groups][${groupIndex}][split_questions_side_by_side]" value="1">
                                        <label class="form-check-label">Split content and questions side by side</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input type="hidden" name="sections[${sectionIdx}][groups][${groupIndex}][allow_drag_drop]" value="0">
                                        <input class="form-check-input" type="checkbox" name="sections[${sectionIdx}][groups][${groupIndex}][allow_drag_drop]" value="1">
                                        <label class="form-check-label">Allow drag and drop answers</label>
                                    </div>
                                </div>
                            </div>
                            <div class="questions-container mt-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">Questions</h6>
                                    <button type="button" class="btn btn-sm btn-outline-success" data-action="add-question">
                                        <i class="bi bi-plus me-1"></i>Add Question
                                    </button>
                                </div>
                                <div class="questions-list"></div>
                            </div>
                        </div>
                    </div>
                `);

                    queueEditorById(`group-content-new-${sectionIdx}-${groupIndex}`);
                    queueEditorById(`group-instructions-new-${sectionIdx}-${groupIndex}`);
                    updateGroupNumbers(sectionItem);
                    updateNavigation();
                    syncQuestionTypeWithFilters(sectionItem);
                }

                function updateGroupNumbers(sectionItem) {
                    sectionItem.querySelectorAll('.question-group-item').forEach((group, index) => {
                        group.querySelector('strong').textContent = `Question Group ${index + 1}`;
                    });
                }

                // Add question
                function addQuestion(btn) {
                    const groupItem = btn.closest('.question-group-item');
                    const sectionItem = btn.closest('.section-item');
                    const sectionIdx = sectionItem.dataset.sectionIndex;
                    const groupIdx = groupItem.dataset.groupIndex;
                    const questionsList = groupItem.querySelector('.questions-list');
                    const questionIndex = questionsList.querySelectorAll('.question-item').length;
                    const selectedGroupType = groupItem.querySelector('.group-question-type-select')?.value || '';

                    questionsList.insertAdjacentHTML('beforeend', `
                    <div class="question-item bg-light p-3 rounded mb-2" data-question-index="${questionIndex}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-link text-dark p-0" data-bs-toggle="collapse" data-bs-target="#question-new-${sectionIdx}-${groupIdx}-${questionIndex}">
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                                <strong>Question ${questionIndex + 1}</strong>
                            </div>
                            <button type="button" class="btn btn-sm btn-link text-danger" data-action="delete-question">Delete Question</button>
                        </div>
                        <div class="question-content collapse show" id="question-new-${sectionIdx}-${groupIdx}-${questionIndex}">
                            <div class="mb-2">
                                <label class="form-label small">Question Content</label>
                                <input type="hidden" id="question-content-new-${sectionIdx}-${groupIdx}-${questionIndex}" name="sections[${sectionIdx}][groups][${groupIdx}][questions][${questionIndex}][content]">
                                <div id="question-content-new-${sectionIdx}-${groupIdx}-${questionIndex}-editor"></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <label class="form-label small">Points</label>
                                    <input type="number" step="0.01" class="form-control form-control-sm" name="sections[${sectionIdx}][groups][${groupIdx}][questions][${questionIndex}][point]" value="1">
                                </div>
                            </div>


                            <div class="mb-3 answers-list-section">
                                <div class="answers-list" data-question-id="${sectionIdx}-${groupIdx}-${questionIndex}">
                                    <!-- Answers will be added here -->
                                </div>

                                <!-- Answer to create Section -->
                                <div class="mt-3 p-3" style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 0.375rem;">
                                    <label class="form-label small fw-semibold">Answer to create</label>
                                    <div class="d-flex gap-2 align-items-center">
                                        <input type="number" class="form-control form-control-sm answer-count-input" min="1" max="10" value="1" placeholder="Enter number of answer to create" style="max-width: 300px;">
                                        <button type="button" class="btn btn-sm btn-primary" data-action="add-multiple-answers" data-section="${sectionIdx}" data-group="${groupIdx}" data-question="${questionIndex}">
                                            <i class="bi bi-plus-lg me-1"></i>Add Answer
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-2 question-options-section" style="display: none;">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="sections[${sectionIdx}][groups][${groupIdx}][questions][${questionIndex}][allow_multiple_selection]" value="1">
                                    <label class="form-check-label small">Allow multiple selection</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="sections[${sectionIdx}][groups][${groupIdx}][questions][${questionIndex}][count_each_correct]" value="1">
                                    <label class="form-check-label small">Count each correct answer as separate question</label>
                                </div>
                            </div>

                        </div>
                    </div>
                `);

                    queueEditorById(`question-content-new-${sectionIdx}-${groupIdx}-${questionIndex}`);
                    updateQuestionNumbers(groupItem);
                    updateNavigation();
                }

                function updateQuestionNumbers(groupItem) {
                    groupItem.querySelectorAll('.question-item').forEach((question, index) => {
                        question.querySelector('strong').textContent = `Question ${index + 1}`;
                    });
                }

                // Add direct question (for speaking/writing)
                function addDirectQuestion(btn) {
                    const sectionItem = btn.closest('.section-item');
                    const sectionIdx = sectionItem.dataset.sectionIndex;
                    const questionsList = sectionItem.querySelector('.direct-questions-list');
                    const questionIndex = questionsList.querySelectorAll('.direct-question-item').length;

                    questionsList.insertAdjacentHTML('beforeend', `
                    <div class="direct-question-item bg-light p-3 rounded mb-3" data-question-index="${questionIndex}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-link text-dark p-0" data-bs-toggle="collapse" data-bs-target="#direct-question-new-${sectionIdx}-${questionIndex}">
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                                <strong>Question ${questionIndex + 1}</strong>
                            </div>
                            <button type="button" class="btn btn-sm btn-link text-danger" data-action="delete-direct-question">Delete Question</button>
                        </div>
                        <div class="collapse show" id="direct-question-new-${sectionIdx}-${questionIndex}">
                            <div class="mb-3">
                                <label class="form-label">Question Content</label>
                                <input type="hidden" id="direct-question-content-new-${sectionIdx}-${questionIndex}" name="sections[${sectionIdx}][direct_questions][${questionIndex}][content]">
                                <div id="direct-question-content-new-${sectionIdx}-${questionIndex}-editor"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Points</label>
                                <input type="number" step="0.01" class="form-control" name="sections[${sectionIdx}][direct_questions][${questionIndex}][point]" value="1">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Sample Answer / Marking Criteria</label>
                                <input type="hidden" id="direct-answer-new-${sectionIdx}-${questionIndex}" name="sections[${sectionIdx}][direct_questions][${questionIndex}][answer_content]">
                                <div id="direct-answer-new-${sectionIdx}-${questionIndex}-editor"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Feedback</label>
                                <input type="hidden" id="direct-feedback-new-${sectionIdx}-${questionIndex}" name="sections[${sectionIdx}][direct_questions][${questionIndex}][feedback]">
                                <div id="direct-feedback-new-${sectionIdx}-${questionIndex}-editor"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Hint (Optional)</label>
                                <input type="text" class="form-control" name="sections[${sectionIdx}][direct_questions][${questionIndex}][hint]" placeholder="Enter hint for this question">
                            </div>
                        </div>
                    </div>
                `);

                    queueEditorById(`direct-question-content-new-${sectionIdx}-${questionIndex}`);
                    queueEditorById(`direct-answer-new-${sectionIdx}-${questionIndex}`);
                    queueEditorById(`direct-feedback-new-${sectionIdx}-${questionIndex}`);
                    updateDirectQuestionNumbers(sectionItem);
                    updateNavigation();
                }

                function updateDirectQuestionNumbers(sectionItem) {
                    sectionItem.querySelectorAll('.direct-question-item').forEach((question, index) => {
                        question.querySelector('strong').textContent = `Question ${index + 1}`;
                    });
                }

                // Update navigation
                function updateNavigation() {
                    const navContainer = document.getElementById('sectionNavigation');
                    const sections = document.querySelectorAll('.section-item');

                    navContainer.innerHTML = Array.from(sections).map((section, sIndex) => {
                        section.id = `section-${sIndex}`;

                        // Check if section has direct questions (Speaking/Writing)
                        const directQuestions = section.querySelectorAll('.direct-question-item');
                        if (directQuestions.length > 0) {
                            const directQuestionsHtml = `<div class="ps-3">${Array.from(directQuestions).map((question, qIndex) => {
                                question.id = `direct-question-${sIndex}-${qIndex}`;
                                return `<a href="#direct-question-${sIndex}-${qIndex}" class="list-group-item list-group-item-action small text-muted py-1">
                                    <i class="bi bi-question-circle me-2"></i>Question ${qIndex + 1}
                                </a>`;
                            }).join('')}</div>`;

                            return `<div class="section-nav-item">
                                <a href="#section-${sIndex}" class="list-group-item list-group-item-action">
                                    <i class="bi bi-file-text me-2"></i>Section ${sIndex + 1}
                                </a>${directQuestionsHtml}
                            </div>`;
                        }
                        

                        // Otherwise check for question groups (Listening/Reading)
                        const groups = section.querySelectorAll('.question-group-item');
                        const groupsHtml = groups.length > 0 ? `<div class="ps-3">${Array.from(groups).map((group, gIndex) => {
                            group.id = `group-${sIndex}-${gIndex}`;
                            const questions = group.querySelectorAll('.question-item');

                            const questionsHtml = questions.length > 0 ? `<div class="ps-3">${Array.from(questions).map((question, qIndex) => {
                                question.id = `question-${sIndex}-${gIndex}-${qIndex}`;
                                return `<a href="#question-${sIndex}-${gIndex}-${qIndex}" class="list-group-item list-group-item-action small text-muted py-1">
                                <i class="bi bi-question-circle me-2"></i>Question ${qIndex + 1}
                            </a>`;
                            }).join('')}</div>` : '';

                            return `<div>
                            <a href="#group-${sIndex}-${gIndex}" class="list-group-item list-group-item-action small">
                                <i class="bi bi-diagram-3 me-2"></i>Group ${gIndex + 1}
                            </a>${questionsHtml}
                        </div>`;
                        }).join('')}</div>` : '';

                        return `<div class="section-nav-item">
                        <a href="#section-${sIndex}" class="list-group-item list-group-item-action">
                            <i class="bi bi-file-text me-2"></i>Section ${sIndex + 1}
                        </a>${groupsHtml}
                    </div>`;
                    }).join('');
                    autoCheckSectionFilters();
                }

                const richTextEditorPrefixes = [
                    'section-content-',
                    'section-feedback-',
                    'group-content-',
                    'group-instructions-',
                    'question-content-',
                    'answer-content-',
                    'answer-feedback-',
                    'direct-question-content-',
                    'direct-answer-',
                    'direct-feedback-'
                ];
                const lazyEditorQueue = [];
                const lazyEditorQueued = new Set();
                let activeLazyEditors = 0;
                let lazyEditorObserver = null;
                const maxConcurrentLazyEditors = 2;

                // Register editors lazily. Creating every CKEditor at once makes long tests very slow.
                function initializeAllEditors() {
                    const scrollBody = document.querySelector('.sections-builder-scroll-body');

                    if ('IntersectionObserver' in window) {
                        lazyEditorObserver = new IntersectionObserver(entries => {
                            entries.forEach(entry => {
                                if (!entry.isIntersecting) return;

                                lazyEditorObserver.unobserve(entry.target);
                                queueLazyEditor(entry.target);
                            });
                        }, {
                            root: scrollBody || null,
                            rootMargin: '600px 0px',
                            threshold: 0
                        });
                    }

                    registerLazyEditors(document);

                    document.addEventListener('pointerdown', function (e) {
                        const editorDiv = e.target.closest('[id$="-editor"]');
                        if (!editorDiv || !isRichTextEditorElement(editorDiv)) return;

                        queueLazyEditor(editorDiv, true);
                    });
                }

                function registerLazyEditors(root) {
                    root.querySelectorAll('[id$="-editor"]').forEach(editorDiv => {
                        if (!isRichTextEditorElement(editorDiv) || isEditorInitialized(editorDiv)) return;

                        editorDiv.classList.add('lazy-editor-placeholder');

                        if (lazyEditorObserver) {
                            lazyEditorObserver.observe(editorDiv);
                        } else {
                            queueLazyEditor(editorDiv);
                        }
                    });
                }

                function isRichTextEditorElement(editorDiv) {
                    const baseId = editorDiv.id.replace(/-editor$/, '');

                    return richTextEditorPrefixes.some(prefix => baseId.startsWith(prefix));
                }

                function isEditorInitialized(editorDiv) {
                    return editorDiv.dataset.ckeditorInitialized === 'true'
                        || editorDiv.dataset.quillInitialized === 'true'
                        || editorDiv.classList.contains('ql-container');
                }

                function queueLazyEditor(editorDiv, prioritize = false) {
                    if (!editorDiv || !editorDiv.isConnected || isEditorInitialized(editorDiv) || lazyEditorQueued.has(editorDiv)) {
                        return;
                    }

                    lazyEditorQueued.add(editorDiv);
                    prioritize ? lazyEditorQueue.unshift(editorDiv) : lazyEditorQueue.push(editorDiv);
                    processLazyEditorQueue();
                }

                function queueEditorById(elementId, prioritize = true) {
                    const editorDiv = document.getElementById(elementId + '-editor');
                    if (!editorDiv) return;

                    editorDiv.classList.add('lazy-editor-placeholder');
                    queueLazyEditor(editorDiv, prioritize);
                }

                function processLazyEditorQueue() {
                    while (activeLazyEditors < maxConcurrentLazyEditors && lazyEditorQueue.length > 0) {
                        const editorDiv = lazyEditorQueue.shift();
                        lazyEditorQueued.delete(editorDiv);

                        if (!editorDiv.isConnected || isEditorInitialized(editorDiv)) {
                            continue;
                        }

                        activeLazyEditors++;
                        editorDiv.classList.remove('lazy-editor-placeholder');

                        const result = initQuillEditor(editorDiv.id.replace('-editor', ''));
                        Promise.resolve(result).finally(() => {
                            activeLazyEditors--;
                            scheduleLazyEditorQueue();
                        });
                    }
                }

                function scheduleLazyEditorQueue() {
                    if ('requestIdleCallback' in window) {
                        window.requestIdleCallback(processLazyEditorQueue, { timeout: 500 });
                    } else {
                        setTimeout(processLazyEditorQueue, 50);
                    }
                }

                function initQuillEditor(elementId, forceQuill = false) {
                    const hiddenInput = document.getElementById(elementId);
                    const editorDiv = document.getElementById(elementId + '-editor');

                    if (!editorDiv) return;

                    editorDiv.classList.remove('lazy-editor-placeholder');

                    // If CKEditor (ClassicEditor) is available, prefer it
                    if (!forceQuill && window.ClassicEditor) {
                        // Avoid double initialization
                        if (editorDiv.dataset.ckeditorInitialized === 'true') {
                            return Promise.resolve();
                        }
                        editorDiv.dataset.ckeditorInitialized = 'true';

                        // Prepare initial data from the hidden input (decode HTML entities if needed)
                        let initialData = hiddenInput ? hiddenInput.value || '' : '';
                        if (initialData && (initialData.includes('&lt;') || initialData.includes('&gt;'))) {
                            const tempDiv = document.createElement('textarea');
                            tempDiv.innerHTML = initialData;
                            initialData = tempDiv.value;
                        }

                        return window.ClassicEditor
                            .create(editorDiv, window.adminEditorConfig ? window.adminEditorConfig() : {
                                removePlugins: ['Title', 'MediaEmbed', 'MediaEmbedToolbar'],
                                toolbar: {
                                    items: [
                                        'undo', 'redo', 'removeFormat', '|',
                                        'bold', 'italic', 'underline', 'strikeThrough',
                                        '|',
                                        'link', 'highlight', 'code', 'codeBlock', 'blockQuote',
                                        'superscript', 'subscript', 'specialCharacters', '|',
                                        'alignment', 'indent', 'outdent', 'pageBreak', '|',
                                        'bulletedList', 'numberedList', 'todoList', '|',
                                        'imageUpload', 'insertTable', 'horizontalLine', 'MathType', 'ChemType'
                                    ]
                                },
                                title: { placeholder: '' }
                            })
                            .then(editor => {
                                if (window.hideAdminEditorToolbarItems) {
                                    window.hideAdminEditorToolbarItems(editor.ui.view.element || document);
                                }

                                if (initialData) {
                                    editor.setData(initialData);
                                }

                                if (hiddenInput) {
                                    editor.model.document.on('change:data', () => {
                                        hiddenInput.value = editor.getData();
                                    });
                                }
                            })
                            .catch(error => {
                                editorDiv.dataset.ckeditorInitialized = 'false';
                                console.error('CKEditor initialization error for', elementId, error);
                                return initQuillEditor(elementId, true);
                            });
                    }

                    // Fallback to Quill if CKEditor is not available
                    if (!window.Quill) {
                        console.error('No editor library available for', elementId);
                        return;
                    }

                    // Prevent double initialization with Quill
                    if (editorDiv.dataset.quillInitialized === 'true' || editorDiv.classList.contains('ql-container')) return;

                    const quill = new Quill(`#${elementId}-editor`, {
                        theme: 'snow',
                        modules: {
                            toolbar: [
                                ['bold', 'italic', 'underline', 'strike'],
                                [{ 'color': [] }, { 'background': [] }],
                                [{ 'align': [] }],
                                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                                ['link', 'image'],
                                ['clean']
                            ]
                        },
                        placeholder: 'Nhập nội dung...'
                    });

                    if (hiddenInput && hiddenInput.value) {
                        // Decode HTML entities if the value contains escaped HTML
                        let decodedHtml = hiddenInput.value;
                        if (decodedHtml.includes('&lt;') || decodedHtml.includes('&gt;')) {
                            const tempDiv = document.createElement('textarea');
                            tempDiv.innerHTML = decodedHtml;
                            decodedHtml = tempDiv.value;
                        }

                        // Set content with a small delay to prevent browser lag
                        setTimeout(() => {
                            quill.root.innerHTML = decodedHtml;
                        }, 10);
                    }

                    quill.on('text-change', () => {
                        if (hiddenInput) hiddenInput.value = quill.root.innerHTML;
                    });

                    editorDiv.dataset.quillInitialized = 'true';

                    return quill;
                }

            })();

           function autoCheckSectionFilters() {

    document.querySelectorAll('.section-item').forEach((section, sIndex) => {

        const sectionNumber = sIndex + 1;

        const sectionCheckboxes = section.querySelectorAll(`
            .filter-group[data-group-type="theo-phan"] .exam-filter-input
        `);

        sectionCheckboxes.forEach(checkbox => {

            const label = normalize(
                checkbox.closest('.form-check')
                    ?.querySelector('.form-check-label')
                    ?.textContent
            );

            checkbox.checked = false;

            const patterns = [
                `part ${sectionNumber}`,
                `section ${sectionNumber}`,
                `passage ${sectionNumber}`
            ];

            if (patterns.includes(label)) {
                checkbox.checked = true;
            }
        });
    });
}

// làm phần dạng

function normalize(str) {
    return str
        ?.replace(/[^a-z0-9]/gi, ' ')
        ?.replace(/\s+/g, ' ')
        ?.trim()
        ?.toLowerCase();
}

function syncQuestionTypeWithFilters(section) {

    const sidebar = section.querySelector('.exam-filter-sidebar');
    if (!sidebar) return;

    // chỉ checkbox theo-dang
    const typeCheckboxes = sidebar.querySelectorAll(`
        .filter-group[data-group-type="theo-dang"] .exam-filter-input
    `);

    const selects = section.querySelectorAll('.group-question-type-select');

    if (!typeCheckboxes.length || !selects.length) return;

    // reset ONLY theo-dang
    typeCheckboxes.forEach(cb => cb.checked = false);

    selects.forEach(select => {

        const selectedValue = normalize(select.value);

        typeCheckboxes.forEach(checkbox => {

            const label = normalize(
                checkbox.closest('.form-check')
                    ?.querySelector('.form-check-label')
                    ?.textContent
            );

            if (label === selectedValue) {
                checkbox.checked = true;
            }
        });
    });
}

$(document).on('change', '.group-question-type-select', function () {

    const section = this.closest('.section-item');

    syncQuestionTypeWithFilters(section);

    // gọi lại để giữ section checkbox
    autoCheckSectionFilters();
});
        </script>
