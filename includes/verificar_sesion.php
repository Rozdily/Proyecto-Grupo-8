<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /tecnologiasweb/views/login/index.php');
    exit;
}