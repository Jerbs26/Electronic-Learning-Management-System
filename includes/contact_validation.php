<?php
// ============================================================
//  Arandia College eLMS — Contact form validation helpers
//  File: includes/contact_validation.php
//  Used by api/contact.php
//   - names: letters only, each word starts with a capital letter
//   - email: valid format AND the domain must really accept mail (MX lookup)
// ============================================================

if (!function_exists('contactNormalizeName')) {

  /** "  juan   DELA cruz " -> "Juan Dela Cruz",  "o'neil-smith" -> "O'Neil-Smith" */
  function contactNormalizeName(string $v): string
  {
    $v = str_replace('’', "'", trim($v));
    $v = preg_replace('/\s+/u', ' ', $v);
    if (!is_string($v)) return '';
    $v = mb_strtolower($v, 'UTF-8');
    $out = preg_replace_callback(
      "/(^|[ '\\-])(\\p{L})/u",
      fn($m) => $m[1] . mb_strtoupper($m[2], 'UTF-8'),
      $v
    );
    return is_string($out) ? $out : $v;
  }

  /** @return string|null  error message, or null when the name is fine */
  function contactNameError(string $name, string $label): ?string
  {
    if ($name === '') return "$label is required.";
    if (mb_strlen($name, 'UTF-8') > 50) return "$label is too long (50 characters max).";
    // letters (any language), accent marks, spaces, hyphen, apostrophe, period (e.g. "Jr.")
    if (!preg_match("/^\\p{L}[\\p{L}\\p{M} '.\\-]*$/u", $name)) {
      return "$label can only contain letters (no numbers or symbols).";
    }
    if (preg_match_all('/\p{L}/u', $name) < 2) return "$label must have at least 2 letters.";
    return null;
  }

  function contactNormalizeEmail(string $v): string
  {
    return mb_strtolower(trim($v), 'UTF-8');
  }

  /**
   * Does this domain accept e-mail?  true = yes, false = no, null = could not tell (DNS down).
   */
  function contactDomainAcceptsMail(string $domain): ?bool
  {
    if (!function_exists('getmxrr')) return null;
    $domain = rtrim($domain, '.');
    try {
      $hosts = [];
      if (@getmxrr($domain, $hosts)) {
        foreach ($hosts as $h) {
          $h = trim((string) $h);
          if ($h !== '' && $h !== '.') return true;   // "." = null MX (domain refuses mail)
        }
        return false;
      }
      // No MX answer: is DNS itself working? If not, don't punish the visitor.
      $probe = [];
      if (@getmxrr('google.com', $probe) && $probe) return false;
      return null;
    } catch (Throwable $e) {
      error_log('[contact_validation] ' . $e->getMessage());
      return null;
    }
  }

  /** @return string|null  error message, or null when the e-mail looks real */
  function contactEmailError(string $email): ?string
  {
    $bad = 'Please enter a valid email address.';
    if ($email === '' || strlen($email) > 190) return $bad;
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return $bad;
    $at = strrpos($email, '@');
    $local = substr($email, 0, $at);
    $domain = substr($email, $at + 1);
    if (strlen($local) > 64) return $bad;
    // domain needs a dot and an alphabetic TLD of 2+ letters (blocks "user@abc" and "user@abc.123")
    if (!preg_match('/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,24}$/', $domain)) return $bad;

    $ok = contactDomainAcceptsMail($domain);
    if ($ok === false) {
      return 'That email address does not seem to exist (its domain cannot receive email). Please check it and try again.';
    }
    return null;   // true, or null when DNS could not be reached
  }
}