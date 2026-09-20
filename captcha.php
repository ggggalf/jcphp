<?php
session_start();
header("Content-type:image/png");
$w=120;
$h=42;
$img=imagecreate($w,$h);
$bg=imagecolorallocate($img,245,247,250);
$text_color=imagecolorallocate($img,11,43,76);
$line_color=imagecolorallocate($img,180,180,180);

$chars='ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$code='';
for($i=0;$i<4;$i++){
    $code .= $chars[mt_rand(0,strlen($chars)-1)];
}
$_SESSION['bid_captcha'] = $code;

for($i=0;$i<4;$i++){
    imageline($img,mt_rand(0,$w),mt_rand(0,$h),mt_rand(0,$w),mt_rand(0,$h),$line_color);
}
imagestring($img,5,25,10,$code,$text_color);
imagepng($img);
imagedestroy($img);
?>
