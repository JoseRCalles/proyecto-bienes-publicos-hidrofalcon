<?php

session_start(); // Siempre inicia la sesión al principio

// Incluye tu archivo de conexión a la base de datos
// Asegúrate de que la ruta sea correcta, ej: '../connect/connect.php' o '../../backend/connect/connect.php'
include '../connect/connect.php'; // Ajusta esta ruta según tu estructura de carpetas

$id_usuario = null; // Inicializa la variable
$acceso_permitido = false; // Bandera para controlar el acceso

// 1. Intentar obtener el ID del usuario de la URL
if (isset($_GET['id_usuario']) && !empty($_GET['id_usuario']) && is_numeric($_GET['id_usuario'])) {
    $id_usuario_url = (int)$_GET['id_usuario']; // Castear a entero para seguridad

    // 2. Validar que el ID de la URL coincida con el ID almacenado en la sesión
    // Esta es la parte de seguridad crucial: el ID en la URL debe coincidir con el que guardamos
    // en la sesión después de que el usuario verificó sus preguntas de seguridad.
    if (isset($_SESSION['restablecer_user_id']) && $_SESSION['restablecer_user_id'] === $id_usuario_url) {
        $id_usuario = $id_usuario_url; // Asigna el ID validado a $id_usuario
        $acceso_permitido = true;

        // Opcional: Si también pasaste un token más complejo en la URL o sesión
        // y quieres validarlo aquí (esto sería una capa extra de seguridad):
        /*
        if (isset($_SESSION['restablecer_token']) && isset($_GET['token']) && $_GET['token'] === $_SESSION['restablecer_token']) {
            $acceso_permitido = true;
        } else {
            $acceso_permitido = false; // Token no coincide o falta
        }
        */

    }
}

// Si el ID del usuario no es válido o el acceso no está permitido por la sesión, redirigir
if (!$acceso_permitido) {
    // Redirige a una página de error o al proceso de recuperación de contraseña
    header('Location: ../new-password.php');
    exit(); // Termina la ejecución del script aquí
}

// Si llegamos aquí, el acceso está permitido y $id_usuario contiene el ID del usuario validado.
// Ahora puedes mostrar el formulario de cambio de contraseña.
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Olvidaste tu contraseña?</title>
    <link rel="stylesheet" href="./forgot-password.css">
</head>
<body>
    <main class="main">
        <div class="leftSide">
                <div class="username">
                    <header class="leftSide__header">
                        <h5>Ingrese su usuario a recuperar</h5>
                    </header>
                    <form class="enter-data" id="username-form">
                        <div class="text-inputs">
                            <div class="input-container">
                                <label for="forgot-username" class="label-input">Usuario</label>
                                <input type="text" id="forgot-username" name="forgot-username" class="input">
                                <div class="err-fusername err-box"></div>
                            </div>
                        </div>

                        <div class="extra-actions">
                            <div class="button-container">
                                <button type="button" class="register-button" id="verify-username-button">Verificar</button>
                            </div>
                        </div>
                    </form>
            </div>
        </div>
        <div class="rightSide">
            <img src="../../shared/images/image.png" alt="Logo">
        </div>
    </main>

    <script src="./forgot-password.js"></script>
</body>
</html>