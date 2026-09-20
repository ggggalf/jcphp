<?php
define('IN_SITE', true);
require_once 'jcnews.php';
$conn = mysqli_connect($db_host,$db_user,$db_pass,$db_name);
if(!$conn){
    echo "数据库连接失败：" . mysqli_connect_error();
}else{
    echo "✅数据库连接成功";
}
