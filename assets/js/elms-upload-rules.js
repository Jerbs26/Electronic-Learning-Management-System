/* Student upload rules (client-side check, the server checks again).
   Documents: PDF, DOCX, PPTX, XLSX  - max 20 MB
   Images:    JPG, PNG               - max 20 MB
   Video:     MP4                    - max 100 MB
   Usage: const msg = ElmsUpload.check(file);  // '' when OK, otherwise an error message */
(function (w) {
    var MB = 1024 * 1024;
    var RULES = { pdf: 20, docx: 20, pptx: 20, xlsx: 20, jpg: 20, jpeg: 20, png: 20, mp4: 100 };
    var HELP = 'Allowed: PDF, DOCX, PPTX, XLSX (max 20 MB) · JPG, PNG (max 20 MB) · MP4 (max 100 MB).';

    function check(file) {
        if (!file) return 'Please choose a file.';
        var name = String(file.name || '');
        var dot = name.lastIndexOf('.');
        var ext = dot >= 0 ? name.slice(dot + 1).toLowerCase() : '';
        if (!Object.prototype.hasOwnProperty.call(RULES, ext)) return 'File type not allowed. ' + HELP;
        if (file.size > RULES[ext] * MB) return 'This file type can be up to ' + RULES[ext] + ' MB only.';
        return '';
    }

    w.ElmsUpload = { check: check, rules: RULES, help: HELP };
})(window);
