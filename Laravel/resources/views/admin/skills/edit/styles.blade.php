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
    </style>
