<?php
session_start();
session_unset();
session_destroy();
header("Location: /mandi_manager/user_login.php");
exit;
?>
