<?php
// Moved — kept so old links and bookmarks still work.
$q = $_GET;
unset($q['tab']);
$extra = $q ? '&' . http_build_query($q) : '';
header('Location: admins.php?tab=manage' . $extra);
exit;
