<?php
ini_set('session.cookie_httponly', 1);
session_start();
// 再生会话ID，彻底销毁旧会话
session_regenerate_id(true);
session_unset();
session_destroy();

header("Location: bid_list.php");
exit;
?>
