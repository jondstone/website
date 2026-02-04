<?php
// Ends admin session.

session_start();

require __DIR__ . 'classes/AdminAuth.php';

AdminAuth::logout();

header('Location: login.php');
exit;