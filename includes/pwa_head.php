<?php
// ============================================================
//  Arandia College eLMS — PWA / "Install app" support
//  File: includes/pwa_head.php
//  Included just before </head> on every page. Adds the web app manifest,
//  theme colour, iOS home-screen tags and registers the service worker.
//  Works whether the site is in the domain root or in a sub-folder.
// ============================================================
if (!isset($GLOBALS['__pwa_base'])) {
    $__root = realpath(__DIR__ . '/..');
    $__doc  = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
    $__base = '';
    if ($__root && $__doc) {
        $__root = str_replace('\\', '/', $__root);
        $__doc  = rtrim(str_replace('\\', '/', $__doc), '/');
        if (stripos($__root, $__doc) === 0) {
            $__base = substr($__root, strlen($__doc));
        }
    }
    $GLOBALS['__pwa_base'] = rtrim($__base, '/') . '/';
}
$__pwaBase = htmlspecialchars($GLOBALS['__pwa_base'], ENT_QUOTES);
?>
<link rel="manifest" href="<?= $__pwaBase ?>manifest.json">
<meta name="theme-color" content="#061A33">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="Arandia eLMS">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<link rel="apple-touch-icon" href="<?= $__pwaBase ?>picture/pwa/apple-touch-icon.png">
<script>
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
      navigator.serviceWorker.register('<?= $__pwaBase ?>sw.js', { scope: '<?= $__pwaBase ?>' }).catch(function () {});
    });
  }
</script>
