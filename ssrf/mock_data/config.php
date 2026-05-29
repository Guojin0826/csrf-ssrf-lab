<?php
/**
 * SSRF模拟数据 - 内部配置文件
 * 模拟内网配置服务器
 */

header('Content-Type: application/json');

$config = [
    'app_name' => 'Internal Management System',
    'version' => '2.5.1',
    'debug_mode' => true,
    'database' => [
        'host' => '192.168.1.100',
        'port' => 3306,
        'username' => 'root',
        'password' => 'P@ssw0rd!@#',
        'database' => 'internal_db'
    ],
    'redis' => [
        'host' => '192.168.1.101',
        'port' => 6379,
        'password' => 'redis_pass_123'
    ],
    'api_keys' => [
        'payment_gateway' => 'pk_live_51H7xyzABC123',
        'sms_service' => 'sms_key_secret_789',
        'email_service' => 'mail_api_key_456'
    ],
    'jwt_secret' => 'super_secret_jwt_key_do_not_share',
    'encryption_key' => 'AES256_encryption_key_32bytes!'
];

echo json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>