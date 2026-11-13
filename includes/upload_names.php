<?php
// ============================================================
//  Arandia College eLMS — Uploaded file names
//  File: includes/upload_names.php
//
//  Uploaded files are stored under a unique name so two students can upload
//  "Essay.pdf" without overwriting each other. The original name is kept in
//  the stored name, after "__":
//      uploads/submissions/sub_6ad2446b7e609__03_Handout_1(32).pdf
//  and elmsFileLabel() shows only the original part: "03_Handout_1(32).pdf".
//  Files uploaded before this change keep their old name (it can't be recovered).
// ============================================================

if (!function_exists('elmsUploadName')) {

    /** Unique stored name that keeps the original file name. $prefix like 'sub_' or 'qsub_12_5_'. */
    function elmsUploadName(string $prefix, string $originalName, string $ext): string
    {
        $base = pathinfo($originalName, PATHINFO_FILENAME);
        // keep letters (incl. ñ, é …), digits, spaces and . _ - ( ) [ ]; anything else (/ \ # ? % & …) becomes _
        $base = preg_replace('/[^\p{L}\p{N}\p{Pd} ._(),\[\]-]+/u', '_', (string) $base) ?? 'file';
        // never let a name carry a script extension (e.g. "evil.php.pdf" → "evil_php.pdf")
        $base = preg_replace('/\.(?=(php\d?|phtml|phar|pht|cgi|pl|py|asp|aspx|jsp|sh|exe|htaccess)\b)/i', '_', $base) ?? $base;
        $base = trim(preg_replace('/\s+/u', ' ', $base) ?? '', ' ._');
        if (function_exists('mb_substr')) $base = mb_substr($base, 0, 80); else $base = substr($base, 0, 80);
        if ($base === '') $base = 'file';
        $ext = strtolower(preg_replace('/[^a-z0-9]/i', '', $ext));
        return $prefix . uniqid() . '__' . $base . ($ext !== '' ? '.' . $ext : '');
    }

    /** The name to show for a stored file: the original name when it was kept, else the stored name. */
    function elmsFileLabel(?string $path): string
    {
        $b = basename((string) $path);
        if (preg_match('/^[a-z]+_(?:\d+_)*[0-9a-f]{13}__(.+)$/', $b, $m)) return $m[1];
        return $b;
    }
}

if (!function_exists('elmsCheckModuleFile')) {

    /**
     * Module attachments: PDF, JPG and PNG only, max 200 MB.
     * Checks the extension AND the real file content (MIME), so a renamed .exe/.php is refused.
     * Returns an error message, or null when the file is fine. $ext is returned lowercased via $extOut.
     */
    function elmsCheckModuleFile(array $file, ?string &$extOut = null): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return 'The file could not be uploaded. Try again.';
        if (($file['size'] ?? 0) > 200 * 1024 * 1024) return 'The file must be 200 MB or smaller.';
        $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        $map = ['pdf' => ['application/pdf'], 'jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'], 'png' => ['image/png']];
        if (!isset($map[$ext])) return 'Only PDF, JPG, or PNG files are allowed.';
        if (function_exists('finfo_open')) {
            $fi = finfo_open(FILEINFO_MIME_TYPE);
            $mime = $fi ? finfo_file($fi, $file['tmp_name']) : '';
            if ($fi) finfo_close($fi);
            if (!in_array($mime, $map[$ext], true)) return 'That file is not a real ' . strtoupper($ext) . '. Only PDF, JPG, or PNG files are allowed.';
        }
        $extOut = $ext;
        return null;
    }
}

if (!function_exists('elmsCheckTeacherFile')) {

    /**
     * Teacher attachments (assignment and quiz files): PDF, JPG, PNG only, max 20 MB.
     * Checks the extension AND the real file content, so a renamed .exe/.php/.docx is refused.
     * Returns an error message, or null when the file is fine ($extOut = lowercase extension).
     */
    function elmsCheckTeacherFile(array $file, ?string &$extOut = null): ?string
    {
        $e = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($e === UPLOAD_ERR_INI_SIZE || $e === UPLOAD_ERR_FORM_SIZE) return 'The file is too large. Max 20 MB.';
        if ($e !== UPLOAD_ERR_OK) return 'The file could not be uploaded. Try again.';
        $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        $map = ['pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'];
        if (!isset($map[$ext])) return 'Only PDF, JPG, or PNG files are allowed. Export Word/PowerPoint files as PDF first.';
        if ((int) ($file['size'] ?? 0) > 20 * 1024 * 1024) return 'The file must be 20 MB or smaller.';
        $tmp = (string) $file['tmp_name'];
        if (!is_uploaded_file($tmp)) return 'The file could not be uploaded. Try again.';
        $fh = @fopen($tmp, 'rb'); $head = $fh ? (string) fread($fh, 16) : ''; if ($fh) fclose($fh);
        if ($ext === 'pdf')                          $ok = strncmp($head, '%PDF', 4) === 0;
        elseif ($ext === 'jpg' || $ext === 'jpeg')   $ok = strncmp($head, "\xFF\xD8\xFF", 3) === 0;
        else                                         $ok = strncmp($head, "\x89PNG\r\n\x1A\n", 8) === 0;
        if ($ok && function_exists('finfo_open')) {
            $fi = finfo_open(FILEINFO_MIME_TYPE);
            $mime = $fi ? (string) finfo_file($fi, $tmp) : '';
            if ($fi) finfo_close($fi);
            if ($mime !== '' && $mime !== $map[$ext]) $ok = false;
        }
        if (!$ok) return 'That file is not a real ' . strtoupper($ext) . '. Only PDF, JPG, or PNG files are allowed.';
        $extOut = $ext;
        return null;
    }
}