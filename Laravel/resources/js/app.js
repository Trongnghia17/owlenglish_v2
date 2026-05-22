import './bootstrap';

// CKEditor 5 full free build for rich text editing in admin forms.
import ClassicEditor from '@blowstack/ckeditor5-full-free-build';

const adminEditorToolbarItems = [
	'undo',
	'redo',
	'removeFormat',
	'|',
	'bold',
	'italic',
	'underline',
	'strikeThrough',
	'|',
	'link',
	'highlight',
	'code',
	'codeBlock',
	'blockQuote',
	'superscript',
	'subscript',
	'specialCharacters',
	'|',
	'alignment',
	'indent',
	'outdent',
	'pageBreak',
	'|',
	'bulletedList',
	'numberedList',
	'todoList',
	'|',
	'imageUpload',
	'insertTable',
	'horizontalLine',
	'MathType',
	'ChemType'
];

const hiddenAdminEditorToolbarLabels = [
	'Choose heading',
	'Heading',
	'Font Size',
	'Font Family',
	'Font Color',
	'Font Background Color'
];

function hideAdminEditorToolbarItems(root = document) {
	if (typeof root.querySelectorAll !== 'function') return;

	root.querySelectorAll('.ck-toolbar [aria-label], .ck-toolbar [title]').forEach((element) => {
		const label = element.getAttribute('aria-label') || element.getAttribute('title') || '';

		if (!hiddenAdminEditorToolbarLabels.includes(label.trim())) return;

		const toolbarItem = element.closest('.ck-dropdown, .ck-button, .ck-toolbar__separator');
		if (toolbarItem) {
			toolbarItem.style.setProperty('display', 'none', 'important');
		}
	});
}

function adminEditorConfig(extra = {}) {
	const removePlugins = new Set([
		'Title',
		'MediaEmbed',
		'MediaEmbedToolbar',
		...(extra.removePlugins || [])
	]);

	return {
		...extra,
		removePlugins: Array.from(removePlugins),
		toolbar: {
			items: adminEditorToolbarItems,
			...(extra.toolbar || {})
		},
		title: {
			placeholder: '',
			...(extra.title || {})
		}
	};
}

function adminEditorFallbackConfig(extra = {}) {
	const removePlugins = new Set([
		'MediaEmbed',
		'MediaEmbedToolbar',
		...(extra.removePlugins || [])
	]);

	return {
		...extra,
		removePlugins: Array.from(removePlugins),
		toolbar: {
			items: adminEditorToolbarItems,
			...(extra.toolbar || {})
		},
		title: {
			placeholder: '',
			...(extra.title || {})
		}
	};
}

// Expose globally so inline scripts in Blade views can use window.ClassicEditor.
if (typeof window !== 'undefined') {
	window.ClassicEditor = ClassicEditor;
	window.adminEditorConfig = adminEditorConfig;
	window.adminEditorFallbackConfig = adminEditorFallbackConfig;
	window.adminEditorToolbarItems = adminEditorToolbarItems;
	window.hideAdminEditorToolbarItems = hideAdminEditorToolbarItems;
}

function initSkillDescriptionEditor() {
	if (typeof window === 'undefined' || !window.ClassicEditor) return;

	const descriptionField = document.querySelector('#description');
	if (!descriptionField || descriptionField.dataset.ckeditorInitialized === 'true') return;

	descriptionField.dataset.ckeditorInitialized = 'true';

	window.ClassicEditor
		.create(descriptionField, adminEditorConfig())
		.then((editor) => {
			hideAdminEditorToolbarItems(editor.ui.view.element || document);
		})
		.catch((error) => {
			console.error('CKEditor initialization error for #description:', error);

			return window.ClassicEditor
				.create(descriptionField, adminEditorFallbackConfig())
				.then((editor) => {
					hideAdminEditorToolbarItems(editor.ui.view.element || document);
				})
				.catch((fallbackError) => {
					descriptionField.dataset.ckeditorInitialized = 'false';
					console.error('CKEditor fallback initialization error for #description:', fallbackError);
				});
		});
}

if (typeof window !== 'undefined') {
	const startToolbarObserver = () => {
		hideAdminEditorToolbarItems(document);

		const observer = new MutationObserver(() => hideAdminEditorToolbarItems(document));
		observer.observe(document.body, { childList: true, subtree: true });
	};

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', startToolbarObserver);
	} else {
		startToolbarObserver();
	}
}

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', initSkillDescriptionEditor);
} else {
	initSkillDescriptionEditor();
}
