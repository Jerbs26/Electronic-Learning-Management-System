<?php
// ============================================================
//  Arandia College eLMS — System settings (Admin → Settings)
//  File: includes/settings.php
//
//  One place for the school-wide settings:
//    system_name   e.g. "Arandia College eLMS"   (page titles, footers, e-mails)
//    school_year   e.g. "2025-2026"              (default for new subjects / classes)
//    semester      "1st" | "2nd" | "Summer"      (default for new subjects / classes)
//    school_level  "both" | "shs" | "hs"         (label: "SHS & HS" …)
//
//  Stored in table system_settings (created automatically). Until something
//  is saved, the defaults below are used — they are the values the site
//  showed before Settings worked, so nothing changes by installing this.
//
//  Saving (api/settings.php) writes a 'settings.changed' sync event, so every
//  open page updates within seconds (includes/live.php).
// ============================================================

if (!function_exists('elmsSettings')) {

    function elmsSettingsDefaults(): array
    {
        return [
            'system_name'  => 'Arandia College eLMS',
            'school_year'  => '2025-2026',   // what the Settings page showed before it could save
            'semester'     => '1st',
            'school_level' => 'both',
        ];
    }

    /** Allowed values of the two pick lists, with their labels. */
    function elmsSemesterOptions(): array
    {
        return ['1st' => '1st Semester', '2nd' => '2nd Semester', 'Summer' => 'Summer'];
    }
    function elmsLevelOptions(): array
    {
        return ['both' => 'SHS & HS', 'shs' => 'Senior High School Only', 'hs' => 'High School Only'];
    }

    function elmsSettingsEnsureTable(mysqli $conn): bool
    {
        static $ok = null;
        if ($ok !== null) return $ok;
        try {
            $ok = (bool) $conn->query(
                "CREATE TABLE IF NOT EXISTS system_settings (
                    setting_key   VARCHAR(50)  NOT NULL PRIMARY KEY,
                    setting_value VARCHAR(255) NOT NULL,
                    updated_by    INT UNSIGNED NULL,
                    updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        } catch (Throwable $e) {
            error_log('[eLMS settings] ' . $e->getMessage());
            $ok = false;
        }
        return $ok;
    }

    /**
     * The current settings (saved values over the defaults). Read once per request.
     * Pass $fresh = true to re-read after saving.
     */
    function elmsSettings(?mysqli $conn = null, bool $fresh = false): array
    {
        static $cache = null;
        if ($cache !== null && !$fresh) return $cache;
        $s = elmsSettingsDefaults();
        if ($conn === null && isset($GLOBALS['conn']) && $GLOBALS['conn'] instanceof mysqli) $conn = $GLOBALS['conn'];
        $fromDb = false;
        if ($conn instanceof mysqli) {
            $fromDb = true;
            try {
                $res = $conn->query("SELECT setting_key, setting_value FROM system_settings");
                if ($res === false) throw new RuntimeException($conn->error);
                while ($r = $res->fetch_assoc()) {
                    if (array_key_exists($r['setting_key'], $s)) $s[$r['setting_key']] = (string) $r['setting_value'];
                }
            } catch (Throwable $e) {
                // table not there yet: defaults are used, the table is made on the first save
            }
        }
        // A bad value in the table must never break a page: fall back per field
        [$clean] = elmsValidateSettings($s);
        $out = array_merge(elmsSettingsDefaults(), $clean);
        if ($fromDb) $cache = $out;   // no database yet: don't remember the defaults
        return $out;
    }

    function elmsSetting(string $key): string
    {
        return (string) (elmsSettings()[$key] ?? '');
    }

    /**
     * Checks and tidies submitted values.
     * Returns [array $clean, array $errors]  ($errors: field => message).
     * Accepts the school year as "2025-2026", "2025 – 2026", "2025 to 2026".
     */
    function elmsValidateSettings(array $in): array
    {
        $clean = [];
        $err = [];

        if (array_key_exists('system_name', $in)) {
            $name = trim(preg_replace('/\s+/u', ' ', (string) $in['system_name']));
            if ($name === '') $err['system_name'] = 'Enter the system name.';
            elseif (mb_strlen($name) > 60) $err['system_name'] = 'Keep the system name to 60 characters or fewer.';
            elseif (preg_match('/[<>]/', $name)) $err['system_name'] = 'The system name cannot contain < or >.';
            else $clean['system_name'] = $name;
        }
        if (array_key_exists('school_year', $in)) {
            $sy = (string) $in['school_year'];
            if (preg_match('/^\s*(\d{4})\s*(?:-|–|—|to)\s*(\d{4})\s*$/u', $sy, $m)) {
                if ((int) $m[2] !== (int) $m[1] + 1) $err['school_year'] = 'The second year must be one after the first (e.g. 2025 – 2026).';
                elseif ((int) $m[1] < 2000 || (int) $m[1] > 2099) $err['school_year'] = 'Enter a school year between 2000 and 2100.';
                else $clean['school_year'] = $m[1] . '-' . $m[2];
            } else {
                $err['school_year'] = 'Enter the school year like 2025 – 2026.';
            }
        }
        if (array_key_exists('semester', $in)) {
            $sem = (string) $in['semester'];
            if (isset(elmsSemesterOptions()[$sem])) $clean['semester'] = $sem;
            else $err['semester'] = 'Choose a semester.';
        }
        if (array_key_exists('school_level', $in)) {
            $lv = (string) $in['school_level'];
            if (isset(elmsLevelOptions()[$lv])) $clean['school_level'] = $lv;
            else $err['school_level'] = 'Choose a school level.';
        }
        return [$clean, $err];
    }

    /** "2025-2026" → "2025 – 2026" */
    function elmsSchoolYearLabel(string $sy): string
    {
        return str_replace('-', ' – ', $sy);
    }

    /** Short level label used in footers / e-mails: "SHS & HS", "Senior High School", "High School". */
    function elmsLevelShort(string $lv): string
    {
        return ['both' => 'SHS & HS', 'shs' => 'Senior High School', 'hs' => 'High School'][$lv] ?? 'SHS & HS';
    }

    /** Everything a page needs to show the settings (also sent to the browser for live updates). */
    function elmsSettingsPublic(?array $s = null): array
    {
        $s = $s ?? elmsSettings();
        return [
            'system_name'       => $s['system_name'],
            'school_year'       => $s['school_year'],
            'school_year_label' => elmsSchoolYearLabel($s['school_year']),
            'semester'          => $s['semester'],
            'semester_label'    => elmsSemesterOptions()[$s['semester']] ?? $s['semester'],
            'school_level'      => $s['school_level'],
            'school_level_label' => elmsLevelShort($s['school_level']),
            'term_label'        => 'S.Y. ' . elmsSchoolYearLabel($s['school_year']) . ' · ' . (elmsSemesterOptions()[$s['semester']] ?? $s['semester']),
            // the short form used in class lists: "2025-2026 · 1st sem"
            'semester_short'    => $s['semester'] === 'Summer' ? 'Summer' : $s['semester'] . ' sem',
            'term_short'        => $s['school_year'] . ' · ' . ($s['semester'] === 'Summer' ? 'Summer' : $s['semester'] . ' sem'),
        ];
    }

    /** Escaped text of one public value — for HTML. */
    function elmsSetHtml(string $key): string
    {
        return htmlspecialchars((string) (elmsSettingsPublic()[$key] ?? ''), ENT_QUOTES, 'UTF-8');
    }

    /** <span data-elms-set="…">value</span> — includes/live.php keeps it up to date. */
    function elmsSetSpan(string $key): string
    {
        return '<span data-elms-set="' . htmlspecialchars($key, ENT_QUOTES) . '">' . elmsSetHtml($key) . '</span>';
    }

    /**
     * Saves settings. $values must already be clean (elmsValidateSettings).
     * Returns the keys whose value really changed.
     */
    function elmsSaveSettings(mysqli $conn, array $values, int $byUserId): array
    {
        if (!elmsSettingsEnsureTable($conn)) throw new RuntimeException('Could not create the settings table.');
        $before = elmsSettings($conn, true);
        $changed = [];
        $st = $conn->prepare(
            "INSERT INTO system_settings (setting_key, setting_value, updated_by) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_by = VALUES(updated_by)"
        );
        foreach ($values as $k => $v) {
            if (!array_key_exists($k, $before)) continue;
            $v = (string) $v;
            $st->bind_param('ssi', $k, $v, $byUserId);
            $st->execute();
            if ($before[$k] !== $v) $changed[] = $k;
        }
        $st->close();
        elmsSettings($conn, true);   // refresh the cache for the rest of this request
        return $changed;
    }
}