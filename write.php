<?php
// 生产环境错误配置
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// 只允许POST请求访问
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    session_start();
    header("Location: contact.html");
    exit;
}
session_start();

define('IN_SITE', true);
require_once 'jcnews.php';

// 数据库连接
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (!$conn) {
    error_log("write.php 数据库连接失败: " . mysqli_connect_error());
    $_SESSION['msg'] = "提交失败，请稍后重试";
    header("Location: contact.html");
    exit;
}
mysqli_set_charset($conn, "utf8mb4");

// 提交频率限制（60秒内仅允许提交1次）
$now = time();
if (isset($_SESSION['last_submit_time']) && ($now - $_SESSION['last_submit_time']) < 60) {
    mysqli_close($conn);
    $_SESSION['msg'] = "提交过于频繁，请稍后再试";
    header("Location: contact.html");
    exit;
}

// 接收并过滤输入
$username = str_replace(["\r","\n"], '', trim($_POST['username'] ?? ''));
$phone    = str_replace(["\r","\n"], '', trim($_POST['phone'] ?? ''));
$type     = str_replace(["\r","\n"], '', trim($_POST['type'] ?? ''));
$demand   = str_replace(["\r","\n"], '', trim($_POST['demand'] ?? ''));

// 输入校验
if (mb_strlen($username, 'UTF-8') > 50 || mb_strlen($username, 'UTF-8') < 1) {
    mysqli_close($conn);
    $_SESSION['msg'] = "姓名长度不合规";
    header("Location: contact.html");
    exit;
}
if (!preg_match('/^[\d\-\+\s]{5,20}$/', $phone)) {
    mysqli_close($conn);
    $_SESSION['msg'] = "电话格式不正确";
    header("Location: contact.html");
    exit;
}
if (mb_strlen($demand, 'UTF-8') > 2000 || mb_strlen($demand, 'UTF-8') < 1) {
    mysqli_close($conn);
    $_SESSION['msg'] = "需求内容长度不合规";
    header("Location: contact.html");
    exit;
}
if (mb_strlen($type, 'UTF-8') > 30) {
    mysqli_close($conn);
    $_SESSION['msg'] = "咨询类型异常";
    header("Location: contact.html");
    exit;
}

$time = date('Y-m-d H:i:s');
// 预处理插入
$stmt = mysqli_prepare($conn, "INSERT INTO contact_msg(username,phone,type,demand,create_time) VALUES (?,?,?,?,?)");
mysqli_stmt_bind_param($stmt, "sssss", $username, $phone, $type, $demand, $time);

if (!mysqli_stmt_execute($stmt)) {
    error_log("write.php 留言插入失败: " . mysqli_stmt_error($stmt));
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    $_SESSION['msg'] = "提交失败，请稍后重试";
    header("Location: contact.html");
    exit;
}
mysqli_stmt_close($stmt);
mysqli_close($conn);

// 记录防刷时间戳 + 成功提示消息
$_SESSION['last_submit_time'] = $now;
$_SESSION['msg'] = "✅提交成功，我们会尽快联系您！";

header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Location: contact.html");
exit;
?>
