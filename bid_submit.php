<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('session.cookie_httponly', 1);
ini_set("session.use_only_cookies", 1);
ini_set('session.cookie_lifetime', 0);
session_start();
define('IN_SITE', true);
require_once 'jcnews.php';

header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

$conn = mysqli_connect($db_host,$db_user,$db_pass,$db_name);
if(!$conn){
    die("数据库连接失败：".mysqli_connect_error());
}
mysqli_set_charset($conn,"utf8mb4");

//CSRF token生成
if(empty($_SESSION['csrf_token'])){
    $_SESSION['csrf_token'] = md5(uniqid(mt_rand(),true));
}
$csrf_token = $_SESSION['csrf_token'];

$clientIp = $_SERVER['REMOTE_ADDR'];
$now = time();

$isPass = false;
if(isset($_SESSION['bid_admin_logged']) && $_SESSION['bid_admin_logged']){
    $isPass = true;
}

// 查询该IP锁定记录
$sqlCheckIp = "SELECT fail_count,lock_end_time FROM bid_login_lock WHERE ip=?";
$stmtCheck = mysqli_prepare($conn,$sqlCheckIp);
mysqli_stmt_bind_param($stmtCheck,"s",$clientIp);
mysqli_stmt_execute($stmtCheck);
$resCheck = mysqli_stmt_get_result($stmtCheck);
$ipRow = mysqli_fetch_assoc($resCheck);
mysqli_stmt_close($stmtCheck);

$isIpLocked = false;
if($ipRow){
    if($ipRow['lock_end_time'] > $now){
        $isIpLocked = true;
    }
}

$error = '';
if(isset($_POST['login'])){
    if(!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']){
        die("非法请求");
    }
    unset($_SESSION['csrf_token']);

    if($isIpLocked){
        $error = "登录失败次数过多，IP已被临时锁定，请24小时后重试";
    }else{
        $user = trim($_POST['username'] ?? '');
        $pwdInput = trim($_POST['password'] ?? '');
        $codeInput = strtolower(trim($_POST['verifycode'] ?? ''));

        $failAction = false;
        if(empty($_SESSION['verify_code']) || $codeInput !== strtolower($_SESSION['verify_code'])){
            $error = "验证码错误";
            unset($_SESSION['verify_code']);
            $failAction = true;
        }else{
            unset($_SESSION['verify_code']);
            $sqlUser = "SELECT id,username,pwd FROM bid_admin WHERE username=? LIMIT 1";
            $stmtUser = mysqli_prepare($conn,$sqlUser);
            mysqli_stmt_bind_param($stmtUser,"s",$user);
            mysqli_stmt_execute($stmtUser);
            $resUser = mysqli_stmt_get_result($stmtUser);
            $rowUser = mysqli_fetch_assoc($resUser);
            mysqli_stmt_close($stmtUser);

            if($rowUser && password_verify($pwdInput, $rowUser['pwd'])){
                $sqlReset = "REPLACE INTO bid_login_lock(ip,fail_count,lock_end_time,last_fail_time) VALUES (?,0,0,?)";
                $stmtReset = mysqli_prepare($conn,$sqlReset);
                mysqli_stmt_bind_param($stmtReset,"si",$clientIp,$now);
                mysqli_stmt_execute($stmtReset);
                mysqli_stmt_close($stmtReset);

                $_SESSION['bid_admin_logged'] = true;
                $_SESSION['bid_admin_id'] = $rowUser['id'];
                $_SESSION['bid_admin_user'] = $rowUser['username'];
                $isPass = true;
                $failAction = false;
            }else{
                $error = "账号或密码错误";
                $failAction = true;
            }
        }

        if($failAction){
            $currentCount = $ipRow ? $ipRow['fail_count'] : 0;
            $currentCount++;
            $lockEnd = 0;
            if($currentCount >=5){
                $lockEnd = $now + 24*3600;
                $error = "登录失败次数过多，IP已被临时锁定，请24小时后重试";
                $isIpLocked = true;
            }
            $sqlUpdate = "REPLACE INTO bid_login_lock(ip,fail_count,lock_end_time,last_fail_time) VALUES (?,?,?,?)";
            $stmtUpdate = mysqli_prepare($conn,$sqlUpdate);
            mysqli_stmt_bind_param($stmtUpdate,"siii",$clientIp,$currentCount,$lockEnd,$now);
            mysqli_stmt_execute($stmtUpdate);
            mysqli_stmt_close($stmtUpdate);
        }
    }
}
?>
<!doctype html>
<html lang="zh-CN">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
	<title>发布招标公告｜金策工程管理有限公司</title>
	<meta name="description" content="发布招标公告">
	<link rel="stylesheet" href="css/swiper.min.css">
	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" href="css/responsive.css">
	<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
	<script src="https://cdn.jsdelivr.net/npm/wangeditor@4.7.11/dist/wangEditor.min.js"></script>
	<style>
.container{max-width:720px;margin:40px auto;padding:0 15px;}
.login-wrap{max-width:420px;margin:80px auto;padding:28px 24px;border:1px solid #dde1e7;border-radius:8px;box-shadow:0 2px 12px rgba(0,0,0,0.06);}
.form-item{margin-bottom:18px;}
.form-item label{display:block;margin-bottom:6px;font-weight:bold;color:#0b2b4c;}
.form-item input,.form-item select{width:100%;padding:10px 12px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;}
.code-row{display:flex;gap:10px;}
.code-row input{flex:1;}
.code-img{height:42px;cursor:pointer;border:1px solid #ccc;border-radius:4px;}
.date-row{display:flex;gap:12px;align-items:center;}
.date-row input{flex:1;}
#editor-box{border:1px solid #ccc;border-radius:4px;}
#content{display:none;}
.btn-submit{width:100%;padding:12px 30px;background:#0b2b4c;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:15px;}
.btn-submit:hover{background:#143b63;}
.err{color:#e53935;margin-bottom:14px;}
.success-tip{background:#d4edda;color:#155724;border:1px solid #c3e6cb;padding:12px;border-radius:6px;margin-bottom:15px;}
	</style>
</head>
<body>
<div class="container">
<?php if(!$isPass): ?>
	<div class="login-wrap">
		<h3 style="text-align:center;margin-top:0;color:#0b2b4c">管理员登录</h3>
		<?php if($error): ?><div class="err"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <?php if(!$isIpLocked): ?>
		<form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token);?>">
			<div class="form-item">
				<label>账号</label>
				<input type="text" name="username" placeholder="请输入账号" required>
			</div>
			<div class="form-item">
				<label>密码</label>
				<input type="password" name="password" placeholder="请输入密码" required>
			</div>
            <div class="form-item">
                <label>验证码</label>
                <div class="code-row">
                    <input type="text" name="verifycode" placeholder="输入验证码" required>
                    <img class="code-img" src="code.php" onclick="this.src='code.php?t='+Math.random()" title="点击刷新验证码">
                </div>
            </div>
			<div class="form-item">
				<button class="btn-submit" name="login" type="submit">登录</button>
			</div>
		</form>
        <?php endif; ?>
	</div>
<?php else: ?>
<?php
$_SESSION['csrf_token'] = md5(uniqid(mt_rand(),true));
$postCsrf = $_SESSION['csrf_token'];
?>
<?php if(isset($_SESSION['msg_success'])): ?>
    <div class="success-tip">
        <?php echo htmlspecialchars($_SESSION['msg_success']); unset($_SESSION['msg_success']); ?>
    </div>
<?php endif; ?>
    <div style="text-align:right;margin-bottom:10px;">
        <a href="logout.php" style="color:#0b2b4c;">退出登录</a>
    </div>
    <h2>发布招标公告</h2>
    <form id="myform" action="bid_save.php" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($postCsrf);?>">
        <div class="form-item">
            <label>招标项目名称 *</label>
            <input type="text" name="project_name" required placeholder="请输入招标项目名称">
        </div>
        <div class="form-item">
            <label>公告类别</label>
            <select name="notice_type">
                <option value="招标公告">招标公告</option>
                <option value="中标公告">中标公告</option>
            </select>
        </div>
        <div class="form-item">
            <label>招标地址</label>
            <input type="text" name="bid_address" placeholder="请填写招标地址">
        </div>
        <div class="form-item">
            <label>联系电话</label>
            <input type="text" name="phone" placeholder="请填写联系电话">
        </div>
        <div class="form-item">
            <label>招标编号</label>
            <input type="text" name="bid_no" placeholder="例如：JC-2026-001">
        </div>
        <div class="form-item">
            <label>发布时间（日期+时间）*</label>
            <input type="datetime-local" name="publish_time" required>
        </div>
        <div class="form-item">
            <label>报名时段（手动填写起止日期）*</label>
			<div class="date-row">
				<input type="date" name="sign_start" required>
				<span>至</span>
				<input type="date" name="sign_end" required>
			</div>
        </div>
        <div class="form-item">
            <label>招标内容 *</label>
            <div id="editor-box"></div>
            <textarea id="content" name="content" required></textarea>
        </div>
        <div class="form-item">
            <button class="btn-submit" type="submit">提交发布公告</button>
        </div>
    </form>
<?php endif; ?>
</div>
<?php if($isPass): ?>
<script>
var E = window.wangEditor;
var editor = new E('#editor-box');
editor.config.height = 320;
editor.config.onchange = function(html){
    document.getElementById('content').value = html;
};
editor.create();
document.getElementById('myform').onsubmit = function(){
    var html = editor.txt.html();
    document.getElementById('content').value = html;
    if(!html || html.trim() === ''){
        alert('请填写招标内容！');
        return false;
    }
    return true;
};
</script>
<?php endif; ?>
</body>
</html>
