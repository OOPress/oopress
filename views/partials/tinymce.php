<!-- Self-hosted TinyMCE 8 - GDPR Compliant -->
<script src="/tinymce/tinymce.min.js"></script>

<script>
function initTinyMCE() {
    tinymce.init({
        selector: '#content-tinymce',
        license_key: 'gpl', // Required for TinyMCE 8 - confirms open-source use
        height: 500,
        menubar: true,
        branding: false,     // Removes "Powered by TinyMCE"
        promotion: false,    // Removes upgrade prompts
        elementpath: true,
        resize: true,
        
        // Core plugins
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
        
        // Toolbar configuration
        toolbar: 'undo redo | blocks | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | link image media | code help',
        toolbar_mode: 'floating',
        
        // Use local files (no external CDN calls)
        skin_url: '/tinymce/skins/ui/oxide',
        content_css: '/tinymce/skins/content/default/content.css',
        
        // Image handling
        image_title: true,
        automatic_uploads: true,
        file_picker_types: 'image',
        
        // GDPR compliant - use system fonts only
        font_formats: 'Arial=arial,helvetica,sans-serif; Courier New=courier new,courier,monospace; Georgia=georgia,serif; Tahoma=tahoma,arial,helvetica,sans-serif; Times New Roman=times new roman,times,serif; Verdana=verdana,geneva,sans-serif;',
        
        // Security
        allow_script_urls: false,
        allow_html_in_named_anchor: false,
        invalid_elements: 'script,iframe,object,embed',
        
        // File picker for images
        file_picker_callback: function(cb, value, meta) {
            var input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.onchange = function() {
                var file = this.files[0];
                var reader = new FileReader();
                reader.onload = function() {
                    var id = 'blobid' + (new Date()).getTime();
                    var blobCache = tinymce.activeEditor.editorUpload.blobCache;
                    var base64 = reader.result.split(',')[1];
                    var blobInfo = blobCache.create(id, file, base64);
                    blobCache.add(blobInfo);
                    cb(blobInfo.blobUri(), { title: file.name });
                };
                reader.readAsDataURL(file);
            };
            input.click();
        },
        
        // Disable telemetry
        telemetry: false
    });
}

function destroyTinyMCE() {
    if (typeof tinymce !== 'undefined' && tinymce.get('content-tinymce')) {
        tinymce.get('content-tinymce').remove();
    }
}
</script>