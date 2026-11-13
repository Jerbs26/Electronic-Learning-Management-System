<?php
// ============================================================
//  Arandia College eLMS — Password rules (same as api/profile.php)
//  File: includes/password_policy.php
// ============================================================

if (!function_exists('pw_policy_error')) {
    /** Returns a human-readable error, or null when the password is acceptable. */
    function pw_policy_error(string $pw, array $personal = []): ?string
    {
        if (preg_match('/\s/', $pw)) {
            return 'Password cannot contain spaces.';
        }
        if (mb_strlen($pw) < 8) {
            return 'New password must be at least 8 characters.';
        }
        if (strlen($pw) > 72) {
            return 'New password is too long (maximum 64 characters).';
        }
        $missing = [];
        if (!preg_match('/[A-Z]/', $pw))          $missing[] = 'an uppercase letter (A-Z)';
        if (!preg_match('/[a-z]/', $pw))          $missing[] = 'a lowercase letter (a-z)';
        if (!preg_match('/\d/', $pw))             $missing[] = 'a number (0-9)';
        if (!preg_match('/[^A-Za-z0-9\s]/', $pw)) $missing[] = 'a special character (e.g. ! @ # $ %)';
        if ($missing) {
            return 'Password must include ' . implode(', ', $missing) . '.';
        }
        if (preg_match('/(.)\1{2,}/u', $pw)) {
            return 'Avoid repeating the same character 3 or more times in a row.';
        }
        $common = ['password', 'passw0rd', 'qwerty', 'qwertyuiop', 'asdfghjkl', 'admin', 'administrator', 'welcome',
                   'letmein', 'iloveyou', 'arandia', 'arandiacollege', 'teacher', 'student', 'changeme', 'login',
                   'master', 'dragon', 'monkey', 'football', 'sunshine', 'abcdefgh'];
        // undo common look-alike swaps (P@ssw0rd! -> password) and drop the rest
        $letters = preg_replace('/[^a-z]/', '', strtr(strtolower($pw), ['0' => 'o', '1' => 'i', '3' => 'e', '4' => 'a', '5' => 's', '7' => 't', '@' => 'a', '$' => 's', '!' => 'i']));
        foreach ($common as $w) {
            if (strpos($letters, $w) !== false && strlen($letters) - strlen($w) <= 3) {
                return 'That password is too common. Choose something harder to guess.';
            }
        }
        $low = strtolower($pw);
        foreach ($personal as $tok) {
            $tok = strtolower(trim((string) $tok));
            if (strlen($tok) >= 3 && strpos($low, $tok) !== false) {
                return 'Password must not contain your name, username, email or school ID.';
            }
        }
        return null;
    }
}