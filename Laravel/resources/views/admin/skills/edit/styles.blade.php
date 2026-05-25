    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
    <style>
        html {
            scroll-behavior: smooth;
        }

        .quill-container {
            height: auto;
        }

        .ql-editor {
            min-height: 100px;
        }

        .ck-editor__editable h1.ck-placeholder[data-placeholder="Type your title"],
        .ck-heading-dropdown,
        .ck-font-size-dropdown,
        .ck-font-family-dropdown,
        .ck-toolbar [aria-label="Choose heading"],
        .ck-toolbar [aria-label="Heading"],
        .ck-toolbar [aria-label="Font Size"],
        .ck-toolbar [aria-label="Font Family"],
        .ck-toolbar [aria-label="Font Color"],
        .ck-toolbar [aria-label="Font Background Color"],
        .ql-header {
            display: none !important;
        }

        .ck-toolbar .ck-dropdown:has([aria-label="Choose heading"]),
        .ck-toolbar .ck-dropdown:has([aria-label="Heading"]),
        .ck-toolbar .ck-dropdown:has([aria-label="Font Size"]),
        .ck-toolbar .ck-dropdown:has([aria-label="Font Family"]),
        .ck-toolbar .ck-dropdown:has([aria-label="Font Color"]),
        .ck-toolbar .ck-dropdown:has([aria-label="Font Background Color"]) {
            display: none !important;
        }

        .skill-specific-field {
            display: none;
        }

        .skill-type-listening .listening-field,
        .skill-type-reading .reading-field,
        .skill-type-writing .writing-field,
        .skill-type-speaking .speaking-field {
            display: block;
        }

        .skill-info-content>div {
            display: none;
        }

        .skill-info-content.skill-type-listening .listening-info,
        .skill-info-content.skill-type-reading .reading-info,
        .skill-info-content.skill-type-writing .writing-info,
        .skill-info-content.skill-type-speaking .speaking-info {
            display: block !important;
        }

        #main-wrapper {
            overflow-x: visible !important;
        }

        @supports (overflow: clip) {
            #main-wrapper {
                overflow-x: clip !important;
            }
        }

        .quiz-navigation-col {
            position: sticky;
            top: 90px;
            max-height: calc(100vh - 110px);
            align-self: flex-start;
            z-index: 10;
        }

        .quiz-navigation-card {
            max-height: inherit;
            display: flex;
            flex-direction: column;
        }

        .quiz-navigation-card .card-header {
            flex-shrink: 0;
        }

        .quiz-navigation-card .card-body {
            overflow-y: auto;
        }

        #sections-builder {
            height: calc(100vh - 110px);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        #sections-builder .sections-builder-sticky-header {
            flex-shrink: 0;
            z-index: 9;
        }

        .sections-builder-scroll-body {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            overscroll-behavior: contain;
        }

        #sectionsContainer .section-item,
        #sectionsContainer .question-group-item,
        #sectionsContainer .question-item,
        #sectionsContainer .direct-question-item {
            scroll-margin-top: 1rem;
        }

        .lazy-editor-placeholder {
            min-height: 96px;
            display: flex;
            align-items: center;
            padding: 0.75rem;
            color: #64748b;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 0.375rem;
            cursor: text;
        }

        .lazy-editor-placeholder::before {
            content: 'Bấm để tải editor';
        }

        @media (max-width: 991.98px) {
            .quiz-navigation-col {
                position: static;
                max-height: none;
            }

            #sections-builder {
                height: auto;
                overflow: visible;
            }

            .sections-builder-scroll-body {
                overflow-y: visible;
            }

            #sections-builder {
                height: auto;
                overflow: visible;
            }

            .sections-builder-scroll-body {
                overflow-y: visible;
            }

            .quiz-navigation-card {
                max-height: none;
                margin-bottom: 1rem;
            }

            .quiz-navigation-card .card-body {
                overflow-y: visible;
            }
        }
    </style>
