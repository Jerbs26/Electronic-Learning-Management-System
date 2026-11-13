<?php
// ============================================================
//  Arandia College eLMS
//  File: includes/header.php  (do not rename or move this file)
// ============================================================
require_once __DIR__ . '/settings.php';   // system name etc. (Admin → Settings)
?>
<?php $__role = $_SESSION['role'] ?? ''; ?>
<?php if ($__role === 'Admin' || $__role === 'Head Admin' || $__role === 'Admission'): ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? ($__role === 'Admission' ? 'Admission' : 'Admin')) ?> — <?= elmsSetHtml('system_name') ?></title>
  <link rel="icon" type="image/png" href="../picture/logo.png">
  <script>
    // Dark mode: apply the saved choice before the page paints (no white flash).
    // Same setting as the Teacher and Student sides (localStorage "elms_theme").
    (function () { try { if (localStorage.getItem('elms_theme') === 'dark') document.documentElement.setAttribute('data-theme', 'dark'); } catch (e) { } })();
  </script>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/elms-admin-base.css?v=3">
  <link rel="stylesheet" href="../assets/css/elms-admin.css?v=2">
  <link rel="stylesheet" href="../assets/css/elms-admin-dark.css?v=2">
  <link rel="stylesheet" href="../assets/css/elms-ui.css?v=4">
  <link rel="stylesheet" href="../assets/css/elms-responsive.css?v=6">
  <link rel="stylesheet" href="../assets/css/elms-motion.css?v=1">
  <script src="../assets/js/elms-nav.js?v=1" defer></script>
  <script src="../assets/js/elms-motion.js?v=1" defer></script>
<?php include __DIR__ . '/pwa_head.php'; ?>
</head>

<body class="elms-app role-admin">
<?php
/* Expects: $first_name, $last_name, $currentPage (set by the including page) */
function navlink($file, $icon, $label, $currentPage) {
  $active = ($currentPage === $file) ? 'active' : '';
  echo '<a class="sidebar-link ' . $active . '" href="' . $file . '"' . ($active ? ' aria-current="page"' : '') . '>'
     . '<span class="sidebar-icon">' . $icon . '</span> ' . $label . '</a>' . "\r\n";
}

/* ── Admin SVG icons (same line-icon style as the Student & Teacher side) ── */
if (!function_exists('a_icon_paths')) {
    function a_icon_paths() {
        static $p = null;
        if ($p === null) {
            $p = [
                'home'         => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
                'user'         => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
                'users'        => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
                'user-plus'    => '<path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/>',
                'user-check'   => '<path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/>',
                'book'         => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>',
                'bell'         => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
                'settings'     => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
                'logout'       => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
                'shield'       => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
                'award'        => '<circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/>',
                'graduation'   => '<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>',
                'presentation' => '<path d="M2 3h20"/><path d="M21 3v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V3"/><path d="m7 21 5-5 5 5"/>',
                'calendar'     => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
                'clipboard'    => '<path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>',
                'edit'         => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>',
                'trash'        => '<polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>',
                'plus'         => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
                'check'        => '<polyline points="20 6 9 17 4 12"/>',
                'inbox'        => '<polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>',
                'alert'        => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
                'clock'        => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
                'lock'         => '<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
                'image'        => '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>',
                'folder'       => '<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>',
                'menu'         => '<line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>',
                'search'       => '<circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
                'chevron-down' => '<polyline points="6 9 12 15 18 9"/>',
                'moon'         => '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>',
            ];
        }
        return $p;
    }
}
if (!function_exists('a_icon')) {
    function a_icon($name) {
        $p = a_icon_paths();
        if (!isset($p[$name])) return '';
        return '<svg class="ico" viewBox="0 0 24 24" aria-hidden="true">' . $p[$name] . '</svg>';
    }
}
if (!function_exists('a_icon_map')) {
    function a_icon_map() {
        $out = [];
        foreach (array_keys(a_icon_paths()) as $k) { $out[$k] = a_icon($k); }
        return $out;
    }
}
?>
  <!-- SIDEBAR -->
  
<?php elseif ($__role === 'Teacher'): ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Teacher') ?> — <?= elmsSetHtml('system_name') ?></title>
  <link rel="icon" type="image/png" href="../picture/logo.png">
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Open+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <script>
        // Apply the saved theme before any CSS paints, so there's no flash
        // of the light theme on load. The toggle itself lives in the topbar.
        (function () {
            try {
                if (localStorage.getItem('elms_theme') === 'dark') {
                    document.documentElement.setAttribute('data-theme', 'dark');
                }
            } catch (e) { /* localStorage unavailable — falls back to light */ }
        })();
    </script>
    <link rel="stylesheet" href="../assets/css/elms-teacher-base.css?v=2">
  <link rel="stylesheet" href="../assets/css/elms-teacher.css?v=7">
  <link rel="stylesheet" href="../assets/css/elms-ui.css?v=4">
  <link rel="stylesheet" href="../assets/css/elms-responsive.css?v=6">
  <link rel="stylesheet" href="../assets/css/elms-motion.css?v=1">
  <script src="../assets/js/elms-nav.js?v=1" defer></script>
  <script src="../assets/js/elms-motion.js?v=1" defer></script>
<?php include __DIR__ . '/pwa_head.php'; ?>
</head>

<body class="elms-app role-teacher">
<?php
/* Expects: $first_name, $last_name, $dept, $currentPage */
function navlink($file, $icon, $label, $currentPage) {
  $active = ($currentPage === $file) ? 'active' : '';
  // A real link (same as the Admin side): open in new tab, long-press and
  // screen readers all work; the title is the tooltip on the icon rail.
  echo '<a class="sidebar-link ' . $active . '" href="' . htmlspecialchars($file) . '" title="' . htmlspecialchars($label) . '" aria-label="' . htmlspecialchars($label) . '"' . ($active ? ' aria-current="page"' : '') . '>'
     . '<span class="sidebar-icon">' . $icon . '</span> ' . $label . '</a>' . "\r\n";
}

/* ── Teacher SVG icons (same line-icon style as the Student side) ── */
function t_icon_paths() {
    static $p = null;
    if ($p === null) {
        $p = [
            'home'         => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
            'book'         => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>',
            'folder'       => '<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>',
            'quiz'         => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>',
            'bell'         => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
            'search'       => '<circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
            'sparkles'     => '<path d="M12 3l1.8 4.7L18.5 9.5l-4.7 1.8L12 16l-1.8-4.7L5.5 9.5l4.7-1.8z"/><path d="M19 15l.9 2.1L22 18l-2.1.9L19 21l-.9-2.1L16 18l2.1-.9z"/>',
            'clipboard-plus' => '<path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/><line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/>',
            'bell-off'     => '<path d="M13.73 21a2 2 0 0 1-3.46 0"/><path d="M18.63 13A17.89 17.89 0 0 1 18 8"/><path d="M6.26 6.26A5.86 5.86 0 0 0 6 8c0 7-3 9-3 9h14"/><path d="M18 8a6 6 0 0 0-9.33-5"/><line x1="1" y1="1" x2="23" y2="23"/>',
            'chart'        => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
            'user'         => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
            'users'        => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            'logout'       => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
            'clipboard'    => '<path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>',
            'check-square' => '<polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>',
            'check'        => '<polyline points="20 6 9 17 4 12"/>',
            'x'            => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
            'cpu'          => '<rect x="4" y="4" width="16" height="16" rx="2" ry="2"/><rect x="9" y="9" width="6" height="6"/><line x1="9" y1="1" x2="9" y2="4"/><line x1="15" y1="1" x2="15" y2="4"/><line x1="9" y1="20" x2="9" y2="23"/><line x1="15" y1="20" x2="15" y2="23"/><line x1="20" y1="9" x2="23" y2="9"/><line x1="20" y1="14" x2="23" y2="14"/><line x1="1" y1="9" x2="4" y2="9"/><line x1="1" y1="14" x2="4" y2="14"/>',
            'layers'       => '<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>',
            'award'        => '<circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/>',
            'edit'         => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>',
            'trash'        => '<polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>',
            'inbox'        => '<polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>',
            'paperclip'    => '<path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>',
            'clock'        => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
            'alert'        => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
        ];
    }
    return $p;
}
function t_icon($name) {
    $p = t_icon_paths();
    if (!isset($p[$name])) return '';
    return '<svg class="ico" viewBox="0 0 24 24" aria-hidden="true">' . $p[$name] . '</svg>';
}
function t_icon_map() {
    $out = [];
    foreach (array_keys(t_icon_paths()) as $k) { $out[$k] = t_icon($k); }
    return $out;
}
?>
  <!-- ADMIN-STYLE SIDEBAR -->
  
<?php elseif ($__role === 'Student'): ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Student') ?> — <?= elmsSetHtml('system_name') ?></title>
  <link rel="icon" type="image/png" href="../picture/logo.png">
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Open+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <script>
        // Apply the saved theme before any CSS paints, so there's no flash
        // of the light theme on load. The toggle itself lives in the
        // account menu (topbar).
        (function () {
            try {
                if (localStorage.getItem('elms_theme') === 'dark') {
                    document.documentElement.setAttribute('data-theme', 'dark');
                }
            } catch (e) { /* localStorage unavailable — falls back to light */ }
        })();
    </script>
    <link rel="stylesheet" href="../assets/css/elms-student-base.css?v=3">
    <link rel="stylesheet" href="../assets/css/elms-student.css?v=2">
  <link rel="stylesheet" href="../assets/css/elms-ui.css?v=4">
  <link rel="stylesheet" href="../assets/css/elms-responsive.css?v=6">
  <link rel="stylesheet" href="../assets/css/elms-motion.css?v=1">
  <script src="../assets/js/elms-nav.js?v=1" defer></script>
  <script src="../assets/js/elms-motion.js?v=1" defer></script>
<?php include __DIR__ . '/pwa_head.php'; ?>
</head>

<body class="elms-app role-student">
<?php
/* Expects: $first_name, $last_name, $level, $section, $currentPage */
function navlink2($file, $svg, $label, $currentPage) {
  $active = ($currentPage === $file) ? 'active' : '';
  echo '<a class="nav-link ' . $active . '" href="' . htmlspecialchars($file) . '" title="' . htmlspecialchars($label) . '" aria-label="' . htmlspecialchars($label) . '"' . ($active ? ' aria-current="page"' : '') . '>' . "\r\n"
     . $svg . "\r\n" . '    ' . $label . "\r\n" . '</a>' . "\r\n";
}
$svgHome = '<svg class="icon" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>';
$svgBook = '<svg class="icon" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>';
$svgFolder = '<svg class="icon" viewBox="0 0 24 24"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>';
$svgQuiz = '<svg class="icon" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>';
$svgAssign = '<svg class="icon" viewBox="0 0 24 24"><rect x="9" y="2" width="6" height="4" rx="1"></rect><path d="M9 4H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-4"></path><path d="M9 14l2 2 4-4"></path></svg>';
$svgAnn = '<svg class="icon" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>';
$svgGrades = '<svg class="icon" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>';
$svgUser = '<svg class="icon" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>';
$svgAward = '<svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>';
?>
    <!-- SIDEBAR -->
    
<?php endif; ?>
<?php require __DIR__ . '/sidebar.php'; ?>
<?php require __DIR__ . '/topbar.php'; ?>
<?php $__role = $_SESSION['role'] ?? ''; ?>
<?php if ($__role === 'Admin' || $__role === 'Head Admin' || $__role === 'Admission'): ?>
<main class="main-content">
<?php elseif ($__role === 'Teacher'): ?>
<main class="main-content">
<?php elseif ($__role === 'Student'): ?>
<main class="main-scroll">
<?php endif; ?>