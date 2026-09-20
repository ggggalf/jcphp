<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
define('IN_SITE', true);
require_once 'jcnews.php';

$conn = mysqli_connect($db_host,$db_user,$db_pass,$db_name);
if(!$conn){
    die("数据库连接失败：".mysqli_connect_error());
}
mysqli_set_charset($conn,"utf8mb4");

//读取中标公告
$sql = "SELECT id,project_name,notice_type,bid_no,publish_time FROM bid_info WHERE notice_type='中标公告' ORDER BY create_at DESC";
$result = mysqli_query($conn,$sql);
$bidData = [];
if($result){
    while($row = mysqli_fetch_assoc($result)){
        $bidData[] = $row;
    }
}
mysqli_close($conn);
?>
<!doctype html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>中标公告｜金策工程管理有限公司</title>
<meta name="description" content="金策工程管理有限公司提供工程造价咨询、工程监理、全过程工程咨询服务。">
<meta name="keywords" content="工程造价,工程监理,全过程工程咨询,招标代理">
<link rel="stylesheet" href="css/swiper.min.css">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/responsive.css">
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
<style>
.dropdown-wrap { position: relative; }
.dropdown-title { padding:27px 0; position:relative; display: inline-block; }
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
.dropdown-menu a:hover { color:var(--b); }
.dropdown-wrap:hover .dropdown-menu { display:block; }
@media (max-width:768px){
    .dropdown-menu{ position:relative; box-shadow:none; top:auto; margin-top:0; }
}
.bid-news-item{
    padding:14px 0;
    border-bottom:1px solid #eee;
    display:flex;
    justify-content:space-between;
    align-items:center;
}
.bid-news-item a{color:#0b2b4c;}
.bid-news-item a:hover{color:#d6a84f;}
.bid-date{color:#71808e;font-size:14px;}
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
            <img src="images/1.png" alt="金策工程管理有限公司">
        </a>
        <span class="menu">☰</span>
        <nav class="nav">
            <a class="" href="index.html">首页</a>
            <a class="" href="about.html">公司概况</a>
            <a class="" href="service.html">企业资质</a>
            <a class="" href="cases.html">经典案例</a>
            <div class="dropdown-wrap">
                <a href="javascript:void(0);" class="dropdown-title">招投标公告</a>
                <div class="dropdown-menu">
                    <a href="bid_list.php">招标公告</a>
                    <a href="bidb_list.php">中标公告</a>
                </div>
            </div>
            <a class="" href="contact.html">联系我们</a>
        </nav>
    </div>
</header>

<div class="container" style=" background-image: url('images/3.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat; padding: 60px 20px; position: relative; ">
    <div style=" position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 35, 70, 0.45); "></div>
    <h1 style=" position: relative; z-index: 2; color: #ffffff; font-size: 32px; margin: 0 0 10px 0; ">招投标公告</h1>
    <div class="crumb" style=" position: relative; z-index: 2; color: #ffffff; font-size: 16px; opacity: 0.9; ">
        <a href="index.html" style="color:#fff;">首页</a>　/　招投标公告
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="title">
            <small>Jcbid</small>
            <h2>中标公告</h2>
        </div>
        <?php if(!empty($bidData)){ ?>
        <div class="bid-news-wrap" style="margin-bottom:40px;">
            <?php foreach ($bidData as $row): ?>
            <div class="bid-news-item">
                <div>
                    <a href="bid_detail.php?id=<?php echo (int)$row['id']; ?>">
                        <?php echo htmlspecialchars($row['project_name']); ?>
                    </a>
                    <span style="margin-left:10px;color:#d6a84f;font-size:13px;">
                        <?php echo htmlspecialchars($row['notice_type']);?>
                        <?php if(!empty($row['bid_no'])) echo "｜".htmlspecialchars($row['bid_no']); ?>
                    </span>
                </div>
                <div class="bid-date">
                    <?php echo date("Y-m-d",strtotime($row['publish_time'])); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php }else{ ?>
        <div style="text-align:center;padding:30px 0;color:#71808e;">暂无公告数据</div>
        <?php } ?>

        <div class="grid3">
            <article class="card">
                <img class="card-img" src="images/AIwBCAAQAhgAINir6dcFKIeqjvQGMIAPOIAK.jpg" alt="行业政策与市场动态">
                <div class="card-body">
                    <span class="news-date">2026-08-11</span>
                    <h3>行业政策与市场动态</h3>
                    <p>围绕工程咨询、造价管理和项目管理开展行业信息分享。</p>
                </div>
            </article>
            <article class="card">
                <img class="card-img" src="images/AIwBCAAQAhgAIPqzi9cFKLW7r9wHMIAPOIEK.jpg" alt="造价管理知识分享">
                <div class="card-body">
                    <span class="news-date">2026-08-12</span>
                    <h3>造价管理知识分享</h3>
                    <p>围绕工程咨询、造价管理和项目管理开展行业信息分享。 </p>
                </div>
            </article>
            <article class="card">
                <img class="card-img" src="images/AIwBCAAQAhgAINir6dcFKIeqjvQGMIAPOIAK.jpg" alt="项目管理实践">
                <div class="card-body">
                    <span class="news-date">2026-08-13</span>
                    <h3>项目管理实践</h3>
                    <p>围绕工程咨询、造价管理和项目管理开展行业信息分享。</p>
                </div>
            </article>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="container footer-grid">
        <div>
            <h3>金策工程管理有限公司</h3>
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
    <div class="container copy">© 2026 金策工程管理有限公司|　</div>
</footer>

<button class="back">↑</button>
<script src="js/jquery.min.js"></script>
<script src="js/swiper.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
