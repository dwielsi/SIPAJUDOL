<?php

return [

    'max_pages' => env('SCANNER_MAX_PAGES', 15),
    'max_depth' => env('SCANNER_MAX_DEPTH', 2),
    'timeout' => env('SCANNER_TIMEOUT', 10),
    'user_agent' => 'SIPAJUDOL-Scanner/1.0 (+Diskominfo Kubu Raya Security Monitoring)',

    'severity_weights' => [
        'critical' => 25,
        'high' => 15,
        'medium' => 8,
        'low' => 3,
        'info' => 1,
    ],

    'backdoor_probe_paths' => [
        'shell.php',
        'wso.php',
        'c99.php',
        'r57.php',
        'b374k.php',
        'adminer.php',
        'phpspy.php',
        'indoxploit.php',
        'mini.php',
        'wp-content/uploads/shell.php',
        'wp-content/uploads/wso.php',
        'uploads/shell.php',
    ],

    'foreign_file_directories' => [
        'wp-content/uploads',
        'uploads',
        'images',
        'assets',
    ],

    'foreign_file_extensions' => ['php', 'phtml'],

    'malware_signatures' => [
        ['pattern' => '/eval\s*\(\s*gzinflate\s*\(/i', 'message' => 'Payload terenkripsi gzinflate+eval terdeteksi'],
        ['pattern' => '/eval\s*\(\s*str_rot13\s*\(/i', 'message' => 'Payload terenkripsi str_rot13+eval terdeteksi'],
        ['pattern' => '/eval\s*\(\s*base64_decode\s*\(/i', 'message' => 'Payload terenkripsi base64_decode+eval terdeteksi'],
        ['pattern' => '/(c99|r57|wso|b374k|indoxploit|mini)\s*shell/i', 'message' => 'Tanda tangan web shell dikenal terdeteksi'],
        ['pattern' => '/FilesMan|FileManager\s*by/i', 'message' => 'Tanda tangan file manager berbahaya terdeteksi'],
        ['pattern' => '/preg_replace\s*\([^)]*\/e[\'"]/i', 'message' => 'Penggunaan preg_replace dengan modifier /e (eksekusi kode) terdeteksi'],
        ['pattern' => '/shell_exec\s*\(|passthru\s*\(|proc_open\s*\(/i', 'message' => 'Pemanggilan fungsi eksekusi shell (shell_exec/passthru/proc_open) terdeteksi'],
    ],

];
