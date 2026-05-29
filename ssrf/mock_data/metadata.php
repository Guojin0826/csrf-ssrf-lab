<?php
/**
 * SSRF模拟数据 - 云元数据服务
 * 模拟云服务商的元数据接口（如AWS、阿里云等）
 */

header('Content-Type: text/plain');

$metadata_type = $_GET['type'] ?? 'default';

switch ($metadata_type) {
    case 'default':
        echo "AWS Metadata Service v2.0\n";
        echo "=========================\n";
        echo "Available endpoints:\n";
        echo "- /metadata?type=iam - IAM role information\n";
        echo "- /metadata?type=instance - Instance metadata\n";
        echo "- /metadata?type=network - Network configuration\n";
        echo "- /metadata?type=security - Security credentials\n";
        break;
        
    case 'iam':
        echo "IAM Role Information\n";
        echo "====================\n";
        echo "Role Name: ec2-admin-role\n";
        echo "Role ARN: arn:aws:iam::123456789012:role/ec2-admin-role\n";
        echo "Instance Profile: EC2-Admin-Profile\n";
        echo "\n";
        echo "Access Key: AKIAIOSFODNN7EXAMPLE\n";
        echo "Secret Key: wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY\n";
        echo "Token: FwoGZXIvYXdzEGMaDNu5EXAMPLE/secret/token\n";
        break;
        
    case 'instance':
        echo "Instance Metadata\n";
        echo "=================\n";
        echo "Instance ID: i-0abcd1234efgh5678\n";
        echo "Instance Type: t2.large\n";
        echo "AMI ID: ami-0abcdef1234567890\n";
        echo "Region: us-east-1\n";
        echo "Availability Zone: us-east-1a\n";
        echo "Private IP: 10.0.1.100\n";
        echo "Public IP: 54.123.45.67\n";
        break;
        
    case 'network':
        echo "Network Configuration\n";
        echo "=====================\n";
        echo "VPC ID: vpc-12345678\n";
        echo "Subnet ID: subnet-87654321\n";
        echo "Security Group: sg-11111111\n";
        echo "MAC Address: 06:17:04:d7:26:09\n";
        echo "\n";
        echo "Private DNS: ip-10-0-1-100.ec2.internal\n";
        echo "Public DNS: ec2-54-123-45-67.compute-1.amazonaws.com\n";
        break;
        
    case 'security':
        echo "Security Credentials\n";
        echo "====================\n";
        echo "Code: Success\n";
        echo "Last Updated: 2024-01-01T00:00:00Z\n";
        echo "Type: AWS-HMAC\n";
        echo "AccessKeyId: AKIAIOSFODNN7EXAMPLE\n";
        echo "SecretAccessKey: wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY\n";
        echo "Token: FwoGZXIvYXdzEGMaDNu5EXAMPLE...\n";
        echo "Expiration: 2024-12-31T23:59:59Z\n";
        break;
        
    default:
        echo "Invalid metadata type";
}
?>