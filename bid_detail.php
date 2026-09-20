<?php
header("Content-Type:text/html;charset=utf-8");
require_once 'jcnews.php';

header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");

$conn = mysqli_connect($db_host,$db_user,$db_pass,$db_name);
if(!$conn){
    echo "<script>alert('服务器繁忙');history.back();</script>";
    exit;
}
mysqli_set_charset($conn,"utf8mb4");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($id<=0){
    echo "<script>alert('参数错误');history.back();</script>";
    exit;
}

// 表名改为 bid_list，预处理查询
$sql = "SELECT id,project_name,notice_type,bid_address,phone,bid_no,publish_time,sign_start,sign_end,content,create_time FROM bid_list WHERE id=?";
$stmt = mysqli_prepare($conn,$sql);
mysqli_stmt_bind_param($stmt,"i",$id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$info = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if(!$info){
    echo "<script>alert('公告不存在');history.back();</script>";
    exit;
}
mysqli_close($conn);

//简单过滤富文本，清除on*、javascript危险代码
$contentHtml = $info['content'];
$dangerWords = ['onclick','onload','onerror','onmouseover','onmouseout','javascript:','eval('];
foreach ($dangerWords as $word){
    $contentHtml = str_ireplace($word,"",$contentHtml);
}
?>
<!doctype html>
<html lang="zh-CN">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<title><?php echo htmlspecialchars($info['project_name']);?>｜金策工程管理有限公司</title>
		<meta name="description" content="金策工程管理有限公司提供工程造价咨询、工程监理、全过程工程咨询服务。">
		<meta name="keywords" content="工程造价,工程监理,全过程工程咨询,招标代理">
		<link rel="stylesheet" href="css/swiper.min.css">
		<link rel="stylesheet" href="css/style.css">
		<link rel="stylesheet" href="css/responsive.css">
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
		<style>
.dropdown-wrap {
    position: relative;
}
.dropdown-title {
    padding:27px 0;
    position:relative;
    display: inline-block;
}
.dropdown-menu {
    position: absolute;
    left: 0;
    top: 100%;
    margin-top:-10px;
    background: #ffffff;
    box-shadow: 0 2px 14px rgba(0,0,0,0.12);
    min-width:130px;
    display: none;
    z-index:999;
}
.dropdown-menu a {
    display:block;
    padding:12px 16px;
    color:#273746;
    white-space:nowrap;
}
.dropdown-menu a:hover {
    color:var(--b);
}
.dropdown-wrap:hover .dropdown-menu {
    display:block;
}
@media (max-width:768px){
    .dropdown-menu{
        position:relative;
        box-shadow:none;
        top:auto;
        margin-top:0;
    }
}
.detail-wrap{max-width:960px;margin:30px auto;padding:0 15px;}
.detail-title{font-size:26px;color:#0b2b4c;text-align:center;margin-bottom:16px;line-height:1.5;}
.detail-meta{text-align:center;color:#666;font-size:14px;margin-bottom:24px;padding-bottom:16px;border-bottom:1px solid #eee;line-height:2;}
.detail-meta span{margin:0 8px;}
.detail-content{line-height:2.2;font-size:16px;color:#333;padding-bottom:40px;}
.detail-content img{max-width:100%;height:auto;}
.back-btn{margin-bottom:20px;}
.back-btn a{color:#0b2b4c;text-decoration:none;}
.back-btn a:hover{color:#d6a84f;}
		</style>
	</head>
	<body>
		<div class="top">
			<div class="container">
				<span>欢迎访问金策工程管理有限公司</span>
				<span>咨询热线：0355-2256011，0355-2256033</span>
			</div>
		</div>
		<header class="header">
			<div class="container head">
				<a class="logo" href="index.html">
					<img src="images/1.png">
				</a>
				<span class="menu">☰</span>
				<nav class="nav">
    <a class="" href="index.html">首页</a>
    <a class="" href="about.html">公司概况</a>
    <a class="" href="service.html">企业资质</a>
    <a class="" href="cases.html">经典案例</a>
    <div class="dropdown-wrap">
        <a href="javascript:;" class="dropdown-title">招投标公告</a>
        <div class="dropdown-menu">
            <a href="bid_list.php">招标公告</a>
            <a href="bida_list.php">中标公告</a>
        </div>
    </div>
    <a class="" href="contact.html">联系我们</a>
</nav>
			</div>
		</header>
		<div class="container" style="
    background-image: url('images/3.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    padding: 60px 20px;
    position: relative;
">
    <div style="
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 35, 70, 0.45);
    "></div>
    <h1 style="
        position: relative;
        z-index: 2;
        color: #ffffff;
        font-size: 32px;
        margin: 0 0 10px 0;
    ">招投标公告</h1>
    <div class="crumb" style="
        position: relative;
        z-index: 2;
        color: #ffffff;
        font-size: 16px;
        opacity: 0.9;
    "><a href="index.html">首页</a>　/　招投标公告 </div>
</div>
		<section class="section">
			<div class="container">
				<div class="detail-wrap">
					<div class="back-btn">
						<a href="javascript:history.back();">← 返回列表</a>
					</div>
					<h1 class="detail-title"><?php echo htmlspecialchars($info['project_name']);?></h1>
					<div class="detail-meta">
						<div>
							<span>公告类别：<?php echo htmlspecialchars($info['notice_type']);?></span>
							<?php if(!empty($info['bid_no'])){ ?>
							<span>招标编号：<?php echo htmlspecialchars($info['bid_no']);?></span>
							<?php } ?>
						</div>
						<div>
							<?php if(!empty($info['bid_address'])){ ?>
							<span>招标地址：<?php echo htmlspecialchars($info['bid_address']);?></span>
							<?php } ?>
							<?php if(!empty($info['phone'])){ ?>
							<span>联系电话：<?php echo htmlspecialchars($info['phone']);?></span>
							<?php } ?>
						</div>
						<div>
						    <span>发布时间：<?php echo htmlspecialchars($info['publish_time']);?></span>
							<?php if(!empty($info['sign_start']) && !empty($info['sign_end'])){ ?>
								<span>报名时段：<?php echo htmlspecialchars($info['sign_start']);?> 至 <?php echo htmlspecialchars($info['sign_end']);?></span>
							<?php } ?>
						</div>
					</div>
					<div class="detail-content">
						<?php echo $contentHtml;?>
					</div>
				</div>
			</div>
		</section>
		<footer class="footer">
			<div class="container footer-grid">
				<div><h3>金策工程管理有限公司</h3>
					<p>专注工程造价咨询、工程监理与全过程工程咨询。</p>
				</div>
				<div>
					<h3>快速导航</h3>
					<p>
						<a href="about.html">公司概况</a><br>
						<a href="service.html">企业资质</a><br>
						<a href="cases.html">经典案例</a>
					</p>
				</div>
				<div>
					<h3>联系我们</h3>
					<p>0355-2256011<br>0355-2256033<br>jczb@163.com<br>山西长治市紫金东街293号帝景苑七单元1002</p>
				</div>
			</div>
			<div class="container copy">© 2026 金策工程管理有限公司|　
			</div>
		</footer>
	<button class="back">↑</button>
	<script src="js/jquery.min.js">
	</script><script src="js/swiper.min.js">
	</script><script src="js/main.js">
	</script>
	</body>
</html>
