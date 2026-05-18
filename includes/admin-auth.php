<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_id"]) || !in_array($_SESSION["role"], ["admin", "system_admin"], true)) {
    header("Location: login.php");
    exit;
}
?>
