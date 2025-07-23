<?php

// ملف بسيط للتحقق من صحة الخادم
echo json_encode([
    'status' => 'OK',
    'timestamp' => date('Y-m-d H:i:s'),
    'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'php_version' => PHP_VERSION,
    'laravel' => 'Running'
]);
