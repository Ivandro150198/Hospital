<?php
require_once __DIR__ . '/auth.php';
admin_require_login();
header('Location: index.php?tab=seo');
exit;
