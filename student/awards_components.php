<?php
// ============================================================
//  Arandia College eLMS — Student award components
//  File: student/awards_components.php
//
//  Rendering helpers + styles for the student's awards, used by
//  student/dashboard.php (recent awards card) and student/awards.php
//  (full list). Read-only: nothing here writes to the database.
//  Requires includes/awards.php to be loaded first.
// ============================================================

if (!function_exists('student_award_card')) {

    /** Full award card (student/awards.php). */
    function student_award_card(array $a): string
    {
        $h = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
        $date = awards_format_date($a['awarded_at'] ?? null);
        $iso = !empty($a['awarded_at']) ? date('Y-m-d', strtotime($a['awarded_at'])) : '';
        $subject = trim(($a['course_name'] ?? '') . (!empty($a['course_code']) ? ' (' . $a['course_code'] . ')' : ''));

        return '<article class="aw-card" style="' . awards_tone_style($a['tone'] ?? null) . '">'
            . '<div class="aw-card-top">'
            . awards_emblem($a['icon'] ?? 'medal', $a['tone'] ?? 'navy', 'md')
            . '<div class="aw-card-head">'
            . '<span class="aw-cat">' . $h($a['category'] ?? '') . '</span>'
            . '<h3 class="aw-title">' . $h($a['award_name'] ?? '') . '</h3>'
            . '<span class="aw-subject">' . $h($subject) . '</span>'
            . '</div></div>'
            . (!empty($a['reason']) ? '<p class="aw-reason">' . $h($a['reason']) . '</p>' : '')
            . '<dl class="aw-meta">'
            . '<div><dt>Awarded by</dt><dd>' . $h(awards_teacher_name($a)) . '</dd></div>'
            . '<div><dt>Date awarded</dt><dd><time datetime="' . $h($iso) . '">' . $h($date) . '</time></dd></div>'
            . '</dl>'
            . '</article>';
    }

    /** Compact row (Student Dashboard "My Awards" card). */
    function student_award_row(array $a): string
    {
        $h = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
        $iso = !empty($a['awarded_at']) ? date('Y-m-d', strtotime($a['awarded_at'])) : '';
        return '<div class="aw-row">'
            . awards_emblem($a['icon'] ?? 'medal', $a['tone'] ?? 'navy', 'sm')
            . '<div class="aw-row-body">'
            . '<div class="aw-row-title">' . $h($a['award_name'] ?? '') . '</div>'
            . '<div class="aw-row-sub">' . $h($a['course_name'] ?? '') . '</div>'
            . (!empty($a['reason']) ? '<div class="aw-row-reason">' . $h($a['reason']) . '</div>' : '')
            . '<div class="aw-row-meta">Awarded by ' . $h(awards_teacher_name($a))
            . ' on <time datetime="' . $h($iso) . '">' . $h(awards_format_date($a['awarded_at'] ?? null)) . '</time></div>'
            . '</div>'
            . '</div>';
    }

    /** Empty state shared by the dashboard card and the awards page. */
    function student_awards_empty(): string
    {
        return '<div class="aw-empty">'
            . '<span class="aw-empty-mark" aria-hidden="true">' . awards_icon_svg('medal') . '</span>'
            . '<div class="aw-empty-title">No awards yet</div>'
            . '<p class="aw-empty-text">Your achievements and recognitions from your teachers will appear here.</p>'
            . '</div>';
    }

    /** Styles for the components above (Student layout variables, dark-mode aware). */
    function student_awards_css(): string
    {
        return awards_emblem_css() . <<<CSS
<style>
  /* Full cards */
  .aw-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 300px), 1fr)); gap: 1.25rem; }
  .aw-card {
    background: var(--bg-card); border: 1px solid var(--border); border-top: 3px solid var(--aw-fg);
    border-radius: 4px 4px var(--radius) var(--radius); box-shadow: var(--shadow);
    padding: 0 1.25rem 1.1rem; display: flex; flex-direction: column; gap: .8rem; min-width: 0;
  }
  .aw-card-top { display: flex; gap: .9rem; align-items: flex-start; min-width: 0; }
  .aw-card-head { padding-top: .8rem; min-width: 0; display: flex; flex-direction: column; gap: .15rem; }
  .aw-cat { font-size: .72rem; font-weight: 600; color: var(--aw-fg); }
  .aw-title { font-family: 'Nunito', sans-serif; font-size: 1.05rem; font-weight: 800; color: var(--text-main); line-height: 1.3; overflow-wrap: anywhere; }
  .aw-subject { font-size: .8rem; color: var(--text-muted); overflow-wrap: anywhere; }
  .aw-reason { font-size: .86rem; color: var(--text-main); line-height: 1.55; white-space: pre-line; overflow-wrap: anywhere; }
  .aw-meta { display: flex; flex-wrap: wrap; justify-content: space-between; gap: .5rem 1rem; margin-top: auto; padding-top: .7rem; border-top: 1px solid var(--border); }
  .aw-meta dt { font-size: .7rem; color: var(--text-muted); }
  .aw-meta dd { font-size: .8rem; font-weight: 600; color: var(--text-main); }

  /* Dashboard rows */
  .aw-list { display: flex; flex-direction: column; }
  .aw-row { display: flex; gap: .85rem; align-items: flex-start; padding: .85rem 0; }
  .aw-row + .aw-row { border-top: 1px solid var(--border); }
  .aw-row-body { flex: 1; min-width: 0; }
  .aw-row-title { font-weight: 700; font-size: .92rem; color: var(--text-main); overflow-wrap: anywhere; }
  .aw-row-sub { font-size: .78rem; color: var(--text-muted); margin-top: .05rem; }
  .aw-row-reason { font-size: .82rem; color: var(--text-main); margin-top: .3rem; line-height: 1.45;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; overflow-wrap: anywhere; }
  .aw-row-meta { font-size: .74rem; color: var(--text-muted); margin-top: .3rem; }

  /* Empty state */
  .aw-empty { text-align: center; padding: 1.5rem 1rem; }
  .aw-empty-mark { display: inline-flex; width: 44px; height: 44px; border-radius: 50%; align-items: center; justify-content: center;
    background: var(--primary-light); color: var(--primary); margin-bottom: .6rem; }
  .aw-empty-mark .aw-ico { width: 22px; height: 22px; }
  .aw-empty-title { font-weight: 700; color: var(--text-main); }
  .aw-empty-text { font-size: .85rem; color: var(--text-muted); margin-top: .25rem; max-width: 44ch; margin-left: auto; margin-right: auto; }

  html[data-theme="dark"] .aw-cat { color: var(--text-muted); }
  html[data-theme="dark"] .aw-empty-mark { color: #fff; }
</style>
CSS;
    }
}
