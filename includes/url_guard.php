<?php
// ============================================================
//  Arandia College eLMS — URL guard (hand-edited URLs never "work")
//  File: includes/url_guard.php
//
//  Loaded by config/conn.php, so every page in student/, teacher/ and
//  admin/ runs it before anything else. For each page it knows which
//  URL parameters the page really uses and what a valid value looks
//  like. When someone edits the address bar and adds a parameter the page
//  does not use (awards.php?course=4), or a value that cannot be valid
//  (?course=abc, ?id=-5, ?tab=zzz, ?course[]=1), the browser is sent to
//  the same page with just the bad parameters removed. Valid parameters
//  are kept, so filters, tabs and links keep working.
//
//  Whether a valid-looking id really belongs to the signed-in user (an
//  enrolled subject, the teacher's own class, an existing course) is
//  checked by the page's own setup (student/_init.php, teacher/_init.php,
//  admin/modules.php, admin/partials/_tabbed_page.php).
//
//  Safe by design:
//    · only plain page loads (GET / HEAD) are redirected; forms (POST),
//      and the API folder, are never touched
//    · a page that is not listed below is left alone (fail-open, never a
//      crash)
//    · the target never contains a bad parameter, so it cannot loop
//    · a redirect to itself is never sent
// ============================================================

if (!function_exists('urlGuardRules')) {

    /** Allowed parameters per page: 'int' | 'str' | 'toast' | [allowed values]. */
    function urlGuardRules(): array
    {
        $tabYesNo = ['students', 'modules'];
        $msg = ['toast' => 'toast', 'error' => ['1', '0']];
        return [
            'student' => [
                'dashboard.php'     => [],
                'my-subjects.php'   => [],
                'announcements.php' => [],
                'profile.php'       => [],
                'awards.php'        => ['subject' => 'int', 'year' => 'str'],
                'modules.php'       => ['course' => 'int', 'open' => 'int'],
                'assignments.php'   => ['course' => 'int', 'item' => 'int', 'kind' => ['quiz', 'assignment']],
                'grades.php'        => ['course' => 'int'],
                'attendance.php'    => ['course' => 'int'],
                'quizzes.php'       => ['course' => 'int', 'item' => 'int'],
                'work.php'          => ['type' => ['quiz', 'assignment'], 'id' => 'int', 'course' => 'int', 'tab' => ['submissions', 'instructions']],
            ],
            'teacher' => [
                'dashboard.php'     => [],
                'my-subjects.php'   => [],
                'announcements.php' => [],
                'profile.php'       => [],
                'edubot.php'        => [],
                'awards.php'        => [],
                'my-students.php'   => ['search' => 'str', 'section' => 'str'],
                'classes.php'       => ['class' => 'int', 'tab' => $tabYesNo],
                'modules.php'       => ['course' => 'int', 'class' => 'int', 'module' => 'int'],
                'assignments.php'   => ['course' => 'int', 'section' => 'str', 'class' => 'int', 'module' => 'int', 'action' => ['new-assignment', 'new-quiz', 'ai-quiz']],
                'quizzes.php'       => ['course' => 'int', 'section' => 'str', 'class' => 'int', 'module' => 'int', 'action' => ['new-assignment', 'new-quiz', 'ai-quiz']],
                'attendance.php'    => ['course' => 'int', 'section' => 'str', 'class' => 'int'],
                'grades.php'        => ['course' => 'int', 'section' => 'str', 'class' => 'int'],
                'grade.php'         => ['id' => 'int', 'section' => 'str', 'student' => 'int'],
                'grades-classic.php'=> ['id' => 'int', 'section' => 'str', 'student' => 'int'],
                'work.php'          => ['type' => ['quiz', 'assignment'], 'id' => 'int', 'class' => 'int'],
            ],
            'admin' => [
                'dashboard.php'     => $msg + ['range' => ['7', '30']],
                'settings.php'      => $msg,
                'awards.php'        => $msg + ['tab' => ['types', 'given']],
                'modules.php'       => $msg + ['course' => 'int', 'added' => 'int'],
                'subjects-enrollment.php' => $msg + ['section' => 'str', 'tab' => ['subjects', 'students']],
                'accounts.php'      => $msg + ['tab' => 'str', 'role' => ['Student', 'Teacher', 'Admin'], 'page' => 'int', 'search' => 'str'],
                'admins.php'        => $msg + ['tab' => 'str', 'page' => 'int', 'search' => 'str'],
                'announcements.php' => $msg + ['tab' => 'str'],
                'messages.php'      => $msg + ['tab' => 'str', 'open' => 'int', 'page' => 'int'],
            ],
        ];
    }

    function urlGuardValueOk($spec, $v): bool
    {
        if (!is_string($v)) return false;                       // ?x[]=1 and the like
        if (is_array($spec)) return in_array($v, $spec, true);
        switch ($spec) {
            case 'int':   return (bool) preg_match('/^[1-9][0-9]{0,9}$/', $v);
            case 'toast': return strlen($v) <= 300;
            case 'str':   return strlen($v) <= 120 && !preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $v);
            default:      return false;
        }
    }

    /**
     * The same URL with every bad parameter removed, or null when the URL
     * is fine (or the page is not guarded).
     */
    function urlGuardCleanQuery(string $scriptName, array $get): ?array
    {
        $scriptName = str_replace('\\', '/', $scriptName);
        $page = basename($scriptName);
        $dir = basename(dirname($scriptName));
        $rules = urlGuardRules();
        if (!isset($rules[$dir][$page])) return null;
        $allowed = $rules[$dir][$page];

        $clean = [];
        foreach ($get as $k => $v) {
            if (is_string($k) && isset($allowed[$k]) && urlGuardValueOk($allowed[$k], $v)) $clean[$k] = $v;
        }
        return count($clean) === count($get) ? null : $clean;
    }

    function urlGuardRun(): void
    {
        if (PHP_SAPI === 'cli' || headers_sent()) return;
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($method !== 'GET' && $method !== 'HEAD') return;
        if (empty($_GET)) return;
        $script = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
        if ($script === '' || strpos(str_replace('\\', '/', $script), '/api/') !== false) return;

        $clean = urlGuardCleanQuery($script, $_GET);
        if ($clean === null) return;

        $to = basename($script) . ($clean ? '?' . http_build_query($clean) : '');
        header('Location: ' . $to, true, 302);
        exit;
    }

    urlGuardRun();
}
