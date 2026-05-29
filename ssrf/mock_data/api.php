<?php
/**
 * SSRF模拟数据 - 内部API接口
 * 模拟内网API服务
 */

header('Content-Type: application/json');

$endpoint = $_GET['endpoint'] ?? 'list';

switch ($endpoint) {
    case 'list':
        $response = [
            'status' => 'success',
            'message' => 'Internal API Service v1.0',
            'endpoints' => [
                '/api/users' => 'Get all users',
                '/api/config' => 'Get system configuration',
                '/api/logs' => 'Get system logs',
                '/api/secrets' => 'Get sensitive secrets'
            ]
        ];
        break;
        
    case 'users':
        $response = [
            'status' => 'success',
            'data' => [
                ['id' => 1, 'name' => 'Admin', 'role' => 'admin'],
                ['id' => 2, 'name' => 'User1', 'role' => 'user'],
                ['id' => 3, 'name' => 'User2', 'role' => 'user']
            ]
        ];
        break;
        
    case 'config':
        $response = [
            'status' => 'success',
            'data' => [
                'db_host' => '192.168.1.100',
                'db_user' => 'root',
                'db_pass' => 'password123'
            ]
        ];
        break;
        
    case 'logs':
        $response = [
            'status' => 'success',
            'data' => [
                ['time' => '2024-01-01 10:00:00', 'level' => 'INFO', 'message' => 'System started'],
                ['time' => '2024-01-01 10:05:00', 'level' => 'ERROR', 'message' => 'Database connection failed'],
                ['time' => '2024-01-01 10:10:00', 'level' => 'WARNING', 'message' => 'High CPU usage']
            ]
        ];
        break;
        
    case 'secrets':
        $response = [
            'status' => 'success',
            'data' => [
                'aws_access_key' => 'AKIAIOSFODNN7EXAMPLE',
                'aws_secret_key' => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
                'stripe_key' => 'sk_test_example_key_for_demo_only',
                'github_token' => 'ghp_example_token_for_demo_purposes'
            ]
        ];
        break;
        
    default:
        $response = [
            'status' => 'error',
            'message' => 'Invalid endpoint'
        ];
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>