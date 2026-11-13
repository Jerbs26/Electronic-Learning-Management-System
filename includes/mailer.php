<?php
// ============================================================
//  Arandia College eLMS — E-mail sending (sign-in codes, password reset)
//  File: includes/mailer.php
//
//  One place for the SMTP settings used by login.php and verify_otp.php.
//  The account comes from config/secrets.php (see secrets.example.php):
//    mail_username / mail_password  Gmail address + 16-letter App Password
//    mail_from / mail_from_name     sender shown to the student / teacher
//    mail_host / mail_port / mail_secure   optional (default Gmail, 465, ssl)
//
//  elmsMailer()   → a PHPMailer ready to use (or null + reason when e-mail
//                   is not set up / PHPMailer is missing)
//  elmsSendMail() → sends; if Gmail's port 465 is blocked it retries on 587
//                   (STARTTLS). Returns '' on success or the reason it failed
//                   (the reason is logged, never shown to students).
// ============================================================
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

if (!function_exists('elmsMailer')) {

    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require_once __DIR__ . '/../vendor/autoload.php';
    }

    /** Why e-mail cannot be sent right now, or '' when it is set up. */
    function elmsMailProblem(): string
    {
        if (!class_exists(PHPMailer::class)) return 'PHPMailer is missing (vendor/ folder).';
        if (!defined('MAIL_USERNAME') || MAIL_USERNAME === '' || MAIL_PASSWORD === '') {
            return file_exists(__DIR__ . '/../config/secrets.php')
                ? 'config/secrets.php has no mail_username / mail_password.'
                : 'config/secrets.php is missing — copy config/secrets.example.php to config/secrets.php and fill in the Gmail account.';
        }
        if (!extension_loaded('openssl')) return 'The PHP openssl extension is off (enable extension=openssl in php.ini).';
        return '';
    }

    function elmsMailer(): ?PHPMailer
    {
        if (elmsMailProblem() !== '') return null;
        $m = new PHPMailer(true);
        $m->CharSet = 'UTF-8';
        $m->SMTPDebug = SMTP::DEBUG_OFF;
        $m->isSMTP();
        $m->Host = defined('MAIL_HOST') && MAIL_HOST !== '' ? MAIL_HOST : 'smtp.gmail.com';
        $secure = defined('MAIL_SECURE') ? MAIL_SECURE : 'ssl';
        $m->Port = defined('MAIL_PORT') && (int) MAIL_PORT > 0 ? (int) MAIL_PORT : ($secure === 'tls' ? 587 : 465);
        if ($secure === 'ssl') $m->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        elseif ($secure === 'tls') $m->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        else { $m->SMTPSecure = ''; $m->SMTPAutoTLS = false; }
        $m->SMTPAuth = $secure !== 'none';
        $m->Username = MAIL_USERNAME;
        $m->Password = str_replace(' ', '', MAIL_PASSWORD);   // Gmail shows app passwords with spaces
        $m->Timeout = 15;
        $m->setFrom(MAIL_FROM !== '' ? MAIL_FROM : MAIL_USERNAME, MAIL_FROM_NAME);
        return $m;
    }

    /** @return string '' when sent, otherwise the reason (for the log / localhost only) */
    function elmsSendMail(string $to, string $subject, string $html, string $text): string
    {
        $problem = elmsMailProblem();
        if ($problem !== '') { error_log('[eLMS mail] ' . $problem); return $problem; }
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) { error_log('[eLMS mail] bad address: ' . $to); return 'The account has no valid e-mail address.'; }

        $attempts = [null];
        // Gmail: if port 465 (SSL) is blocked by the network / antivirus, try 587 (STARTTLS)
        if ((!defined('MAIL_HOST') || MAIL_HOST === '' || MAIL_HOST === 'smtp.gmail.com') && (!defined('MAIL_PORT') || (int) MAIL_PORT === 0)) $attempts[] = 587;
        $last = '';
        foreach ($attempts as $port) {
            $m = elmsMailer();
            if ($port) { $m->Port = $port; $m->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; }
            try {
                $m->addAddress($to);
                $m->isHTML(true);
                $m->Subject = $subject;
                $m->Body = $html;
                $m->AltBody = $text;
                $m->send();
                return '';
            } catch (\Throwable $e) {
                $last = $m->ErrorInfo ?: $e->getMessage();
                error_log('[eLMS mail] port ' . $m->Port . ': ' . $last);
                // Wrong password: retrying another port will not help
                if (stripos($last, 'authenticate') !== false) break;
            }
        }
        if (stripos($last, 'authenticate') !== false) return 'Gmail rejected the login — check mail_username and the App Password in config/secrets.php.';
        return 'Could not reach the mail server: ' . $last;
    }

    /**
     * Base URL (no trailing slash) used inside e-mailed links, e.g. the password-reset link.
     * 1. app_url in config/secrets.php (or APP_URL env) wins, e.g. https://elms.school.edu/capstone1
     * 2. If the site was opened as localhost / 127.0.0.1, a phone can never open that link,
     *    so the computer's Wi-Fi/LAN address is used instead (phone must be on the same Wi-Fi).
     * 3. Otherwise the address the page was opened with.
     */
    function elmsBaseUrl(): string
    {
        if (defined('APP_URL') && APP_URL !== '') return rtrim(APP_URL, '/');

        $https  = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $scheme = $https ? 'https://' : 'http://';
        $host   = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $path   = rtrim(str_replace('\\', '/', dirname($_SERVER['PHP_SELF'] ?? '/')), '/');

        if (preg_match('/^(localhost|127\.\d+\.\d+\.\d+|\[?::1\]?)(:\d+)?$/i', $host, $hm)) {
            $ip = '';
            try {
                $sock = @stream_socket_client('udp://8.8.8.8:53', $en, $es, 1);   // UDP "connect" sends nothing
                if ($sock) {
                    $name = (string) stream_socket_get_name($sock, false);
                    fclose($sock);
                    $ip = preg_replace('/:\d+$/', '', $name);
                }
            } catch (\Throwable $e) { $ip = ''; }
            if ($ip === '' || strpos($ip, '127.') === 0) {
                $srv = (string) ($_SERVER['SERVER_ADDR'] ?? '');
                $ip  = (strpos($srv, '127.') === 0 || $srv === '::1') ? '' : $srv;
            }
            if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $host = $ip . (isset($hm[2]) ? $hm[2] : '');
            }
        }
        return $scheme . $host . $path;
    }

    /** Show the technical reason only to someone working on the server itself. */
    function elmsIsLocalRequest(): bool
    {
        return in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
    }
}
