<?php
session_start();
unset($_SESSION['cart']);
session_destroy();
header('Location: index.php');
exit; 