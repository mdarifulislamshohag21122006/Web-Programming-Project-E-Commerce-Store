<?php
require_once 'includes/db.php';
session_logout();
header('Location: index.php');
exit;
