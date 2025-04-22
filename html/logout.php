<?php
session_start(); // Iniciar sesión

// Destruir todas las variables de sesión
session_unset();

// Destruir la sesión
session_destroy();

// Redirigir al usuario a la página de login
header("Location: login_dashboard.php");
exit();
?>
