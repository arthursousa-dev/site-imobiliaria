<?php
require_once __DIR__ . '/../bootstrap_sessao.php';
session_destroy();
header('Location: login.php');
exit;
