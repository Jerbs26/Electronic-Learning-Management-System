<?php
// ============================================================
//  Arandia College eLMS — Module lesson content helpers
//  File: includes/module_content.php
//  Lesson text is stored as a small, safe subset of HTML so the
//  admin's formatting (headings, bold, lists) shows to students
//  without letting scripts or styles through.
// ============================================================

if (!function_exists('sanitizeModuleHtml')) {
  function sanitizeModuleHtml(string $html): string
  {
    $html = preg_replace('#<(script|style|iframe|object|embed)[^>]*>.*?</\1>#is', '', $html);
    $html = str_ireplace(['<div>', '</div>'], ['<p>', '</p>'], $html);
    $html = strip_tags($html, '<p><br><h3><h4><strong><b><em><i><u><ul><ol><li><blockquote><table><thead><tbody><tr><th><td><details><summary>');
    // Drop every attribute (on*, style, href, ...) from the allowed tags
    $html = preg_replace('#<(p|br|h3|h4|strong|b|em|i|u|ul|ol|li|blockquote|table|thead|tbody|tr|th|td|details|summary)\b[^>]*>#i', '<$1>', $html);
    $html = preg_replace('#<p>(\s|&nbsp;|<br>)*</p>#i', '', $html);
    return trim($html);
  }

  function moduleHasText(?string $html): bool
  {
    return trim(html_entity_decode(strip_tags((string) $html), ENT_QUOTES, 'UTF-8')) !== '';
  }
}
