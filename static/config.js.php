<?php
header('Content-Type: application/javascript; charset=UTF-8');

// 获取协议
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

// 获取主机名（含端口）
$host = $_SERVER['HTTP_HOST'];

// 拼接完整的 BASE_URL
$baseUrl = "$protocol://$host";
?>

window.config = {
    'BASE_URL': '<?php echo $baseUrl; ?>' /* No '/' at the end */
};
