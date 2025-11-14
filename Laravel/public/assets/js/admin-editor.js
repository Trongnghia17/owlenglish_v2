// Quill Editor Configuration
const QuillEditor = {
    toolbar: [
        [{ 'header': [1, 2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ 'color': [] }, { 'background': [] }],
        [{ 'align': [] }],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        ['link', 'image'],
        ['clean']
    ],

    init(selector, placeholder = 'Nhập...') {
        // Register Image Resize Module
    

        const editor = new Quill(selector + '-editor', {
            theme: 'snow',
            modules: { 
                toolbar: {
                    container: this.toolbar,
                    handlers: {
                        image: this.imageHandler
                    }
                },
               
            },
            placeholder: placeholder
        });

        const input = document.querySelector(selector);
        editor.on('text-change', () => input.value = editor.root.innerHTML);
        if (input.value) editor.root.innerHTML = input.value;
        
        return editor;
    },

    imageHandler() {
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        input.click();

        input.onchange = async () => {
            const file = input.files[0];
            if (!file) return;

            // Show loading
            const range = this.quill.getSelection(true);
            this.quill.insertText(range.index, 'Đang tải ảnh...');
            
            // Upload to server
            const formData = new FormData();
            formData.append('image', file);

            try {
                const response = await fetch('/admin/upload-image', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();
                
                // Remove loading text
                this.quill.deleteText(range.index, 16);
                
                if (data.success) {
                    // Insert image
                    this.quill.insertEmbed(range.index, 'image', data.url);
                    this.quill.setSelection(range.index + 1);
                } else {
                    alert('Upload failed: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Upload error:', error);
                this.quill.deleteText(range.index, 16);
                alert('Lỗi khi upload ảnh!');
            }
        };
    }
};

// Image Preview Utilities
const ImagePreview = {
    show(fileInput, previewId = 'imagePreview') {
        const file = fileInput.files[0];
        if (!file) return;

        const reader = new FileReader();
        const preview = document.getElementById(previewId);
        const img = preview.querySelector('img');

        reader.onload = (e) => {
            img.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    },

    remove(inputId = 'image', previewId = 'imagePreview') {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        const img = preview.querySelector('img');

        input.value = '';
        preview.style.display = 'none';
        img.src = '';
    }
};

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Auto-init Quill editor if #description exists
    if (document.querySelector('#description')) {
        QuillEditor.init('#description');
    }
});
