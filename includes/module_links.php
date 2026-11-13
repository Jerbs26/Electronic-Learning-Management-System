<?php
// ============================================================
//  Arandia College eLMS — Assignments and quizzes under a module
//  File: includes/module_links.php
//
//  Every assignment and quiz can belong to one module of its subject
//  (column module_id on assignments and quizzes, added here the first
//  time it is needed — see database/migration_module_links.sql).
//
//  When a teacher creates work without picking a module, it is placed
//  automatically under the module the class is on now: the first
//  module (in order) that fewer than half of the teacher's students
//  have finished. The teacher can change it in the form.
//
//  Used by: api/assignments.php, api/quizzes.php, api/module_links.php,
//  teacher/classes.php (Modules tab), student/modules.php.
// ============================================================

if (!function_exists('mlEnsure')) {

    /** Adds module_id to assignments + quizzes once. Returns false if it cannot (then nothing is linked). */
    function mlEnsure(mysqli $conn): bool
    {
        static $ok = null;
        if ($ok !== null) return $ok;
        try {
            foreach (['assignments', 'quizzes'] as $t) {
                if (!$conn->query("SHOW TABLES LIKE '$t'")->num_rows) { $ok = false; return $ok; }
                if (!$conn->query("SHOW COLUMNS FROM `$t` LIKE 'module_id'")->num_rows) {
                    $conn->query("ALTER TABLE `$t` ADD COLUMN module_id INT UNSIGNED NULL DEFAULT NULL, ADD KEY idx_{$t}_module (module_id)");
                }
            }
            $ok = true;
            // First time only: place the work that already exists under a module too.
            // Remembered in app_settings, so it also runs once on databases where the
            // column was added earlier (e.g. by database/migration_module_links.sql).
            $conn->query("CREATE TABLE IF NOT EXISTS app_settings (
                            name VARCHAR(64) NOT NULL PRIMARY KEY,
                            value VARCHAR(255) NULL,
                            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $conn->query("INSERT IGNORE INTO app_settings (name, value) VALUES ('module_links_backfill', 'running')");
            if ($conn->affected_rows > 0) {
                foreach (['assignments', 'quizzes'] as $t) mlBackfill($conn, $t);
                $conn->query("UPDATE app_settings SET value = 'done' WHERE name = 'module_links_backfill'");
            }
        } catch (Throwable $e) {
            error_log('[eLMS module links] ' . $e->getMessage());
            $ok = false;
        }
        return $ok;
    }

    /**
     * Work posted before modules were linked: put each item under the module whose
     * title it names (e.g. "Essay on Panitikang Asyano" → "Panitikang Asyano"),
     * otherwise under the module its class is on now. Runs once, when the column is added.
     */
    function mlBackfill(mysqli $conn, string $table): void
    {
        try {
            $res = $conn->query("SELECT id, course_id, title, " . ($conn->query("SHOW COLUMNS FROM `$table` LIKE 'teacher_id'")->num_rows ? 'teacher_id' : '0 AS teacher_id') . "
                                 FROM `$table` WHERE module_id IS NULL");
            $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
            $modsOf = $autoOf = [];
            $st = $conn->prepare("UPDATE `$table` SET module_id = ? WHERE id = ? AND module_id IS NULL");
            foreach ($rows as $r) {
                $cid = (int) $r['course_id'];
                $modsOf[$cid] ??= mlModules($conn, $cid);
                if (!$modsOf[$cid]) continue;
                $pick = null; $best = 0;
                $title = mb_strtolower((string) $r['title']);
                foreach ($modsOf[$cid] as $id => $m) {
                    $mt = mb_strtolower(trim((string) $m['title']));
                    if (mb_strlen($mt) >= 4 && mb_strlen($mt) > $best && str_contains($title, $mt)) { $pick = $id; $best = mb_strlen($mt); }
                }
                if ($pick === null) {
                    $key = $cid . ':' . (int) $r['teacher_id'];
                    $autoOf[$key] ??= mlAutoModule($conn, $cid, (int) $r['teacher_id']);
                    $pick = $autoOf[$key];
                }
                if ($pick === null) continue;
                $id = (int) $r['id'];
                $st->bind_param('ii', $pick, $id);
                $st->execute();
            }
            $st->close();
        } catch (Throwable $e) {
            error_log('[eLMS module links] backfill ' . $table . ': ' . $e->getMessage());
        }
    }

    /** Published modules of a subject, in order: [id => ['id','num','title']]. */
    /** True when a module's lesson HTML has real text in it. */
    function mlHasLessonText(string $html): bool
    {
        $t = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(preg_replace('/[\s\x{00A0}\x{200B}]+/u', '', $t)) !== '';
    }

    function mlIsPdfPath(string $path): bool
    {
        return $path !== '' && strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf';
    }

    /**
     * Plain text of a module's PDF attachment (pure PHP: smalot/pdfparser in vendor/smalot).
     * $relPath is modules.file_path (e.g. uploads/modules/mod_xxx.pdf). Only files inside
     * the site's uploads/ folder are read. Returns '' when it cannot get any text
     * (missing file, too big, scanned / image-only PDF, parser error).
     */
    function mlPdfText(string $relPath): string
    {
        if (!mlIsPdfPath($relPath)) return '';
        $root = realpath(__DIR__ . '/..');
        $file = $root ? realpath($root . '/' . ltrim($relPath, '/\\')) : false;
        $uploads = $root ? realpath($root . '/uploads') : false;
        if (!$file || !$uploads || strpos($file, $uploads . DIRECTORY_SEPARATOR) !== 0 || !is_file($file)) return '';
        if (filesize($file) > 25 * 1024 * 1024) return '';   // too heavy to parse on shared hosting

        // Tiny PSR-0 autoloader for the bundled parser (vendor/composer is left untouched)
        static $registered = false;
        if (!$registered) {
            spl_autoload_register(function ($cls) {
                if (strpos($cls, 'Smalot\\PdfParser\\') !== 0) return;
                $f = __DIR__ . '/../vendor/smalot/pdfparser/src/' . str_replace('\\', '/', $cls) . '.php';
                if (is_file($f)) require_once $f;
            });
            $registered = true;
        }
        if (!class_exists('Smalot\\PdfParser\\Parser')) return '';

        try {
            @set_time_limit(60);
            if ((int) ini_get('memory_limit') !== -1 && (int) ini_get('memory_limit') < 256) @ini_set('memory_limit', '256M');
            $parser = new \Smalot\PdfParser\Parser();
            $text = $parser->parseFile($file)->getText();
        } catch (Throwable $e) {
            error_log('[eLMS pdf text] ' . $e->getMessage());
            return '';
        }
        // Clean: valid UTF-8, no control chars, tidy whitespace
        $text = (string) @mb_convert_encoding((string) $text, 'UTF-8', 'UTF-8');
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x{FFFD}]/u', '', $text) ?? '';
        $text = preg_replace(["~[ \t\x{00A0}]+~u", "~\n\s*\n+~"], [' ', "\n"], $text) ?? '';
        return trim($text);
    }

    function mlModules(mysqli $conn, int $courseId): array
    {
        $hasNum = (bool) $conn->query("SHOW COLUMNS FROM modules LIKE 'module_number'")->num_rows;
        $st = $conn->prepare("SELECT id, " . ($hasNum ? 'module_number' : 'NULL') . " AS num, title, content, file_path FROM modules
                              WHERE course_id = ? AND published = 1 ORDER BY " . ($hasNum ? 'module_number' : 'week_number, created_at') . ", id");
        $st->bind_param('i', $courseId);
        $st->execute();
        $out = [];
        foreach ($st->get_result()->fetch_all(MYSQLI_ASSOC) as $r) $out[(int) $r['id']] = [
            'id' => (int) $r['id'], 'num' => $r['num'] !== null ? (int) $r['num'] : null, 'title' => $r['title'],
            // has a lesson the AI can draft from (not empty / only blank tags)
            'has_lesson' => mlHasLessonText((string) $r['content']) || mlIsPdfPath((string) ($r['file_path'] ?? '')),
        ];
        $st->close();
        return $out;
    }

    /**
     * The module the teacher's class is on now: the first module fewer than half
     * of their students have finished. No students yet → the module of the
     * teacher's latest linked work, else the first module. Null = no modules.
     */
    function mlAutoModule(mysqli $conn, int $courseId, int $teacherId): ?int
    {
        $mods = mlModules($conn, $courseId);
        if (!$mods) return null;
        $st = $conn->prepare("SELECT DISTINCT u.id FROM users u
                              JOIN enrollments e ON e.student_id = u.id AND e.course_id = ? AND e.status = 'Enrolled'
                              JOIN teacher_assignments ta ON ta.course_id = e.course_id AND ta.section = u.section_dept AND ta.teacher_id = ?
                              WHERE u.role = 'Student' AND u.status = 'Active'");
        $st->bind_param('ii', $courseId, $teacherId);
        $st->execute();
        $ids = array_map('intval', array_column($st->get_result()->fetch_all(MYSQLI_ASSOC), 'id'));
        $st->close();
        if ($ids) {
            $done = [];
            $res = $conn->query("SELECT module_id, COUNT(*) AS n FROM module_progress
                                 WHERE module_id IN (" . implode(',', array_keys($mods)) . ") AND student_id IN (" . implode(',', $ids) . ")
                                 GROUP BY module_id");
            while ($res && $r = $res->fetch_assoc()) $done[(int) $r['module_id']] = (int) $r['n'];
            foreach ($mods as $id => $m) if (($done[$id] ?? 0) * 2 < count($ids)) return $id;
            return array_key_last($mods);
        }
        if (mlEnsure($conn)) {
            foreach (['assignments', 'quizzes'] as $t) {
                $r = $conn->query("SELECT module_id FROM `$t` WHERE course_id = $courseId AND module_id IS NOT NULL ORDER BY id DESC LIMIT 1");
                if ($r && ($row = $r->fetch_assoc()) && isset($mods[(int) $row['module_id']])) return (int) $row['module_id'];
            }
        }
        return array_key_first($mods);
    }

    /**
     * Save the module of an assignment / quiz after it was created or updated.
     * $raw: the posted module_id — null (not sent: new work is placed
     * automatically, edited work keeps its module), 'auto', '0' (no module) or an id.
     */
    function mlApply(mysqli $conn, string $table, int $itemId, int $courseId, $raw, bool $isNew, int $teacherId): void
    {
        if (!in_array($table, ['assignments', 'quizzes'], true) || $itemId <= 0 || $courseId <= 0 || !mlEnsure($conn)) return;
        try {
            if ($raw === null && !$isNew) return;
            $raw = $raw === null ? 'auto' : trim((string) $raw);
            if ($raw === '0') $moduleId = null;
            elseif ($raw === '' || $raw === 'auto') $moduleId = mlAutoModule($conn, $courseId, $teacherId);
            else {
                $mods = mlModules($conn, $courseId);
                $moduleId = isset($mods[(int) $raw]) ? (int) $raw : mlAutoModule($conn, $courseId, $teacherId);   // module of another subject → auto
            }
            $cur = $conn->query("SELECT module_id, title, " . ($table === 'quizzes' ? 'status' : "'Published' AS status") . " FROM `$table` WHERE id = $itemId")->fetch_assoc();
            if (!$cur || ((int) $cur['module_id'] ?: null) === $moduleId) return;
            $st = $conn->prepare("UPDATE `$table` SET module_id = ? WHERE id = ?");
            $st->bind_param('ii', $moduleId, $itemId);
            $st->execute();
            $st->close();
            // Moved to another module after it was posted: open Modules pages refresh (quietly, no bell)
            if (!$isNew && $conn->query("SHOW TABLES LIKE 'sync_events'")->num_rows) {
                $kind = $table === 'assignments' ? 'assignment.moved' : 'quiz.moved';
                $title = mb_substr((string) $cur['title'], 0, 255);
                $st = $conn->prepare("INSERT INTO sync_events (kind, course_id, owner_id, ref_id, audience, title) VALUES (?, ?, ?, ?, ?, ?)");
                $aud = $cur['status'] === 'Published' ? 'all' : 'teachers';   // a draft quiz stays hidden from students
                $st->bind_param('siiiss', $kind, $courseId, $teacherId, $itemId, $aud, $title);
                $st->execute();
                $st->close();
            }
        } catch (Throwable $e) {
            error_log('[eLMS module links] ' . $e->getMessage());   // the assignment / quiz itself is already saved
        }
    }

    /** Module of each item: [item_id => module_id|null] for the given table and course. */
    function mlLinks(mysqli $conn, string $table, int $courseId): array
    {
        if (!in_array($table, ['assignments', 'quizzes'], true) || !mlEnsure($conn)) return [];
        $out = [];
        $res = $conn->query("SELECT id, module_id FROM `$table` WHERE course_id = $courseId");
        while ($res && $r = $res->fetch_assoc()) $out[(int) $r['id']] = $r['module_id'] !== null ? (int) $r['module_id'] : null;
        return $out;
    }

    /**
     * Lesson of a module, for the AI drafts (api/ai_quiz.php, api/ai_assignment.php).
     * $raw: the form's Module value — 'auto' (the module the class is on now), an id,
     * or '0' / '' (no module → null). Only a subject this teacher teaches.
     * Returns ['title' => 'Module 1 · …', 'text' => plain-text lesson] or null.
     */
    function mlLessonForAi(mysqli $conn, int $teacherId, int $courseId, $raw): ?array
    {
        $raw = trim((string) $raw);
        if ($courseId <= 0 || $raw === '' || $raw === '0') return null;
        $st = $conn->prepare("SELECT 1 FROM teacher_assignments WHERE teacher_id = ? AND course_id = ? LIMIT 1");
        $st->bind_param('ii', $teacherId, $courseId);
        $st->execute();
        $ok = (bool) $st->get_result()->fetch_row();
        $st->close();
        if (!$ok) return null;

        $mid = $raw === 'auto' ? (int) mlAutoModule($conn, $courseId, $teacherId) : (int) $raw;
        if ($mid <= 0) return null;
        $hasNum = (bool) $conn->query("SHOW COLUMNS FROM modules LIKE 'module_number'")->num_rows;
        $st = $conn->prepare("SELECT m.title, m.description, m.content, m.file_path, " . ($hasNum ? 'm.module_number' : 'NULL') . " AS num, c.course_name
                              FROM modules m JOIN courses c ON c.id = m.course_id
                              WHERE m.id = ? AND m.course_id = ? AND m.published = 1");
        $st->bind_param('ii', $mid, $courseId);
        $st->execute();
        $m = $st->get_result()->fetch_assoc();
        $st->close();
        if (!$m) return null;

        // HTML lesson → plain text, keeping headings / list items on their own lines
        $html = (string) $m['content'];
        $html = preg_replace('~<\s*(br|/p|/h[1-6]|/li|/tr|/div|/summary)\b[^>]*>~i', "\n", $html);
        $html = preg_replace('~<\s*li\b[^>]*>~i', '- ', $html);
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(preg_replace(["~[ \t]+~", "~\n\s*\n+~"], [' ', "\n"], $text));
        $hasContent = mlHasLessonText((string) $m['content']);

        // No typed lesson but a PDF attached → read the PDF's text instead
        $pdfUnreadable = false;
        if (!$hasContent && mlIsPdfPath((string) ($m['file_path'] ?? ''))) {
            $text = mlPdfText((string) $m['file_path']);
            $hasContent = mlHasLessonText($text);
            $pdfUnreadable = !$hasContent;   // scanned / image-only / unreadable
        }
        if (mb_strlen($text) > 12000) $text = mb_substr($text, 0, 12000) . ' …';

        $title = ($m['num'] !== null ? 'Module ' . (int) $m['num'] . ' · ' : '') . $m['title'];
        $parts = ['Subject: ' . $m['course_name'], 'Module: ' . $title];
        if (trim((string) $m['description']) !== '') $parts[] = 'Summary: ' . trim($m['description']);
        if ($text !== '') $parts[] = "Lesson content:\n" . $text;
        return ['id' => $mid, 'title' => $title, 'text' => implode("\n", $parts), 'has_content' => $hasContent, 'pdf_unreadable' => $pdfUnreadable];
    }

    /**
     * Why the AI may not draft (null = OK). It only writes from a module's lesson:
     * no subject / no module picked, or a module with no lesson → refused.
     */
    function mlAiBlockReason(?array $lesson, $courseId, $moduleRaw): ?string
    {
        if ($lesson && !empty($lesson['has_content'])) return null;
        if ($lesson && !empty($lesson['pdf_unreadable'])) return $lesson['title'] . '\'s PDF has no readable text (it may be a scanned or image-only PDF), so the AI has nothing to base it on. Type or paste the lesson in Modules, or upload a text-based PDF.';
        if ($lesson) return $lesson['title'] . ' has no lesson yet, so the AI has nothing to base it on. Add the lesson in Modules first, or choose another module.';
        if ((int) $courseId <= 0) return 'Choose the subject first.';
        $raw = trim((string) $moduleRaw);
        if ($raw === '' || $raw === '0') return 'Choose a module first. The AI writes from that module\'s lesson.';
        if ($raw === 'auto') return 'This subject has no modules yet. Add a module and its lesson in Modules first.';
        return 'That module was not found. Choose another module in the form.';
    }
}
