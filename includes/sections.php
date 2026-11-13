<?php
// ============================================================
//  Arandia College eLMS — Section helpers
//  File: includes/sections.php
//  Sections live in the `sections` table (see
//  database/migration_sections_subjects.sql). If the migration
//  hasn't been imported yet, these fall back to the old fixed list
//  so nothing breaks.
// ============================================================

if (!function_exists('defaultSectionRows')) {
  function defaultSectionRows(): array
  {
    $rows = [];
    foreach (['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'] as $i => $n) {
      $rows[] = ['id' => 0, 'name' => $n, 'level' => 'HS', 'status' => 'Active', 'sort_order' => ($i + 1) * 10];
    }
    $i = 0;
    foreach (['11', '12'] as $g) {
      foreach (['ABM-A', 'ABM-B', 'GAS-A', 'GAS-B', 'HUMSS-A', 'HUMSS-B'] as $s) {
        $rows[] = ['id' => 0, 'name' => "Grade $g - $s", 'level' => 'SHS', 'status' => 'Active', 'sort_order' => 100 + (++$i) * 10];
      }
    }
    return $rows;
  }

  /** True once database/migration_sections_subjects.sql has been imported. */
  function sectionsTableExists(mysqli $conn): bool
  {
    static $exists = null;
    if ($exists === null) {
      try {
        $r = $conn->query("SHOW TABLES LIKE 'sections'");
        $exists = $r && $r->num_rows > 0;
      } catch (Throwable $e) {
        $exists = false;
      }
    }
    return $exists;
  }

  /** All section rows, SHS first then HS, in sort order. */
  function fetchSections(mysqli $conn, bool $activeOnly = true): array
  {
    if (!sectionsTableExists($conn)) return defaultSectionRows();
    $sql = "SELECT id, name, level, status, sort_order FROM sections"
         . ($activeOnly ? " WHERE status = 'Active'" : '')
         . " ORDER BY FIELD(level, 'SHS', 'HS'), sort_order, name";
    $out = [];
    $res = $conn->query($sql);
    while ($res && $row = $res->fetch_assoc()) $out[] = $row;
    return $out;
  }

  /** [$shsSectionNames, $hsSectionNames] — drop-in for the old hardcoded arrays. */
  function loadSectionLists(mysqli $conn): array
  {
    $shs = $hs = [];
    foreach (fetchSections($conn) as $s) {
      if ($s['level'] === 'SHS') $shs[] = $s['name']; else $hs[] = $s['name'];
    }
    return [$shs, $hs];
  }
}
