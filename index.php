<?php
session_start();
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit();
}

switch ($_SESSION['role']) {
    case 'faculty':
        header('Location: ' . BASE_URL . '/faculty/dashboard.php');
        break;
    case 'hod':
        header('Location: ' . BASE_URL . '/hod/dashboard.php');
        break;
    case 'qa':
        header('Location: ' . BASE_URL . '/qa/dashboard.php');
        break;
    default:
        header('Location: ' . BASE_URL . '/login.php');
}
exit();
