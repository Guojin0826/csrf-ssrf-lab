<?php
/**
 * SSRF模拟数据 - Redis服务
 * 模拟内网Redis服务
 */

header('Content-Type: text/plain');

$command = $_GET['cmd'] ?? 'info';

switch ($command) {
    case 'info':
        echo "# Redis Server Information\n";
        echo "# =======================\n\n";
        echo "# Server\n";
        echo "redis_version:6.2.6\n";
        echo "redis_mode:standalone\n";
        echo "os:Linux 5.4.0-42-generic\n";
        echo "arch_bits:64\n";
        echo "tcp_port:6379\n";
        echo "uptime_in_seconds:1234567\n";
        echo "uptime_in_days:14\n\n";
        echo "# Clients\n";
        echo "connected_clients:5\n";
        echo "client_longest_output_list:0\n\n";
        echo "# Memory\n";
        echo "used_memory:1234567\n";
        echo "used_memory_human:1.18M\n";
        echo "used_memory_peak:2345678\n";
        echo "used_memory_peak_human:2.24M\n\n";
        echo "# Keyspace\n";
        echo "db0:keys=100,expires=50,avg_ttl=3600000\n";
        break;
        
    case 'keys':
        echo "1) \"user:1:session\"\n";
        echo "2) \"user:2:session\"\n";
        echo "3) \"admin:token\"\n";
        echo "4) \"cache:config\"\n";
        echo "5) \"cache:users\"\n";
        echo "6) \"secret:api_key\"\n";
        echo "7) \"secret:jwt_key\"\n";
        echo "8) \"temp:upload_123\"\n";
        break;
        
    case 'get':
        $key = $_GET['key'] ?? '';
        switch ($key) {
            case 'secret:api_key':
                echo "sk_test_example_key_for_demo";
                break;
            case 'secret:jwt_key':
                echo "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.example_jwt_token_for_demo";
                break;
            case 'admin:token':
                echo "admin_token_abc123xyz789";
                break;
            default:
                echo "(nil)";
        }
        break;
        
    default:
        echo "ERR unknown command '$command'";
}
?>