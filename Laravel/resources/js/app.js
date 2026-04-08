import './bootstrap';

// CKEditor 5 full free build for rich text editing in admin (skills create/edit, sections builder, etc.)
import ClassicEditor from '@blowstack/ckeditor5-full-free-build';

// Expose globally so inline scripts in Blade views can use window.ClassicEditor
if (typeof window !== 'undefined') {
	window.ClassicEditor = ClassicEditor;
}

// Initialize CKEditor for simple description textarea on skill create/edit pages
function initSkillDescriptionEditor() {
	if (typeof window === 'undefined' || !window.ClassicEditor) return;

	const descriptionField = document.querySelector('#description');
	if (!descriptionField) return;

	// Prevent double initialization
	if (descriptionField.dataset.ckeditorInitialized === 'true') return;
	descriptionField.dataset.ckeditorInitialized = 'true';

	window.ClassicEditor
		.create(descriptionField, {
			// Không dùng chức năng nhúng media, giúp giảm cảnh báo widget-toolbar-no-items
			removePlugins: ['MediaEmbed', 'MediaEmbedToolbar']
		})
		.catch((error) => {
			console.error('CKEditor initialization error for #description:', error);
		});
}

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', initSkillDescriptionEditor);
} else {
	initSkillDescriptionEditor();
}
