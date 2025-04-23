<?php
session_start();
$loginError = isset($_GET['login']) && $_GET['login'] === "false";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <style>
/* Reset de algunos estilos predeterminados */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Fondo general */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f7fc;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

/* Contenedor principal */
.login-container {
    width: 100%;
    max-width: 500px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    padding: 40px;
    text-align: center;
}

/* Logotipo */
.logo-container {
    margin-bottom: 30px;
}

.logo {
    width: 270px;
    height: auto;
}

/* Título del formulario */
h2 {
    margin-bottom: 30px;
    color: #333;
    font-size: 28px;
}

/* Grupo de inputs */
.input-group {
    margin-bottom: 25px;
    text-align: left;
}

.input-group label {
    display: block;
    font-size: 16px;
    color: #666;
}

.input-group input {
    width: 100%;
    padding: 15px;
    margin-top: 8px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 18px;
    color: #333;
}

.input-group input:focus {
    border-color: #007bff;
    outline: none;
}

/* Botón de inicio de sesión */
.submit-btn {
    width: 100%;
    padding: 15px;
    background-color: #94161b;
    color: white;
    font-size: 18px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

.submit-btn:hover {
    background-color: #791317;
}

/* Enlaces de recuperación y registro */
.footer {
    margin-top: 30px;
    font-size: 16px;
    color: #666;
}

.footer a {
    color: #007bff;
    text-decoration: none;
}

.footer a:hover {
    text-decoration: underline;
}

/* Estilos para el mensaje de error */
.error-message {
    background-color: #ffdddd;
    color: #d8000c;
    padding: 10px;
    border: 1px solid #d8000c;
    border-radius: 5px;
    margin-bottom: 20px;
}

    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <!-- Logotipo -->
            <div class="logo-container">
                <img src="atalaya.jpg" alt="Logotipo" class="logo">
            </div>
            <h2>Iniciar Sesión</h2>

            <!-- Mostrar mensaje de error si login=false -->
            <?php if ($loginError): ?>
                <div class="error-message">
                    Usuario o contraseña incorrectos.
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="input-group">
                    <label for="usuario">Correo electrónico o Usuario</label>
                    <input type="text" id="usuario" name="usuario" required>
                </div>
                <div class="input-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="submit-btn">Iniciar Sesión</button>
            </form>
        </div>
    </div>
</body>
</html>
