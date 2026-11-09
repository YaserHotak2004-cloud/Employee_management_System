<?php
session_start();

if (isset($_SESSION["user_id"])) {
    header("Location: Dashboard.php");
} else {
    header("Location: Login.php");
}

exit();
