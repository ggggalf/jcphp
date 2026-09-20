<?php
ini_set('session.cookie_httponly',1);
session_start();
define('IN_SITE',true);
require_once 'jcnews.php';

header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

//校验登录状态
if (!isset($_SESSION['bid_admin_logged']) || !$_SESSION['bid_admin_logged']) {
    header("Location: bid_submit.php");
    exit;
}

//重要写操作，重新生成session id，防御会话固定
session_regenerate_id(true);

//CSRF校验
if(!isset($_POST['csrf_token']) || $_POST['csrf_token']!==$_SESSION['csrf_token']){
    $_SESSION['msg_success'] = "非法提交请求";
    header("Location: bid_submit.php");
    exit;
}
unset($_SESSION['csrf_token']);

$conn = mysqli_connect($db_host,$db_user,$db_pass,$db_name);
if(!$conn){
    $_SESSION['msg_success'] = "数据库连接异常";
    header("Location: bid_submit.php");
    exit;
}
mysqli_set_charset($conn,"utf8mb4");

$project_name = trim($_POST['project_name']??'');
$notice_type = trim($_POST['notice_type']??'');
$bid_address = trim($_POST['bid_address']??'');
$phone = trim($_POST['phone']??'');
$bid_no = trim($_POST['bid_no']??'');
$publish_time = trim($_POST['publish_time']??'');
$sign_start = trim($_POST['sign_start']??'');
$sign_end = trim($_POST['sign_end']??'');
$content = $_POST['content']??'';

//基础必填校验
if(empty($project_name) || empty($publish_time) || empty($sign_start) || empty($sign_end) || empty($content)){
    $_SESSION['msg_success'] = "必填项不能为空，请检查表单";
    mysqli_close($conn);
    header("Location: bid_submit.php");
    exit;
}

//管理员后台富文本简单过滤，移除危险js事件，前台展示页依然需要二次过滤
$dangerTags = ['onclick','onload','onerror','onmouseover','javascript:','eval('];
foreach ($dangerTags as $word){
    $content = str_ireplace($word,"",$content);
}

$sql = "INSERT INTO bid_list (project_name,notice_type,bid_address,phone,bid_no,publish_time,sign_start,sign_end,content,create_time)
VALUES (?,?,?,?,?,?,?,?,?,NOW())";
$stmt = mysqli_prepare($conn,$sql);
mysqli_stmt_bind_param($stmt,"sssssssss",
    $project_name,$notice_type,$bid_address,$phone,$bid_no,$publish_time,$sign_start,$sign_end,$content
);

if(mysqli_stmt_execute($stmt)){
    $_SESSION['msg_success'] = "公告发布成功！";
}else{
    //不直接暴露mysql错误文本给页面
    $_SESSION['msg_success'] = "发布失败，请检查数据格式";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

header("Location: bid_submit.php");
exit;
?>
