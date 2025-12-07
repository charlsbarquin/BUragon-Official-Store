<?php
function getCurrentUserId() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
}
