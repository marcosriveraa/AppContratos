<?php
session_start(); // Iniciar sesión

// Incluir el archivo de conexión a la base de datos
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuarioCorreo = $_POST['usuario'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE usuario = :usuarioCorreo OR correo = :usuarioCorreo");
    $stmt->bindParam(':usuarioCorreo', $usuarioCorreo);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['usuario'] = $user['usuario'];
            $_SESSION['correo'] = $user['correo'];
            header("Location: dashboard.php");
            exit();
        } else {
            header("Location: login_dashboard.php?login=false");
            exit();
        }
    } else {
        header("Location: login_dashboard.php?login=false");
        exit();
    }
}
?>
