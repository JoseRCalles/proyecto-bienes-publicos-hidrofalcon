<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicia Sesion</title>
    <link rel="stylesheet" href="/systemahidrofalcon/frontend/pages/login.css">
    <link rel="stylesheet" href="/systemahidrofalcon/frontend/node_modules/izitoast/dist/css/iziToast.min.css">
</head>
<body>

    <base href="/systemahidrofalcon/">
    <main class="main">
        <div class="leftSide">
            <div class="backdrop-blur"></div>
            <header class="leftSide__header">
                <h4>Inicia Sesion</h4>
            </header>
            <form method="POST" class="enter-data" id="loginForm">
                <div class="text-inputs">
                    <div class="input-container">
                        <label for="username" class="label-input">Usuario</label>
                        <input type="text" name="usuario" id="username" class="input">
                        <div class="err-username err-box"></div>
                    </div>

                    <div class="input-container">
                        <label for="password" class="label-input">Contraseña</label>
                        <input type="password" name="contrasena" id="password" class="input">
                        <div class="err-password err-box"></div>
                    </div>
                </div>

                <div class="submit-container">
                    <input type="submit" value="Ingresar" class="submit" class="">
                </div>
                <a href="/systemahidrofalcon/olvidar-contrasena" class="forgot__password">¿Olvidaste tu contraseña? </a>
            </form>

            <div class="extra-actions">
                <div class="button-container">
                    <a href="/systemahidrofalcon/registro" class="register-button">Registro</a>
                </div>
            </div>
        </div>
        <div class="rightSide">
            <img src="/systemahidrofalcon/frontend/pages/shared/images/logo.png" alt="Logo" class="logo">
        </div>

    </main>
    <script src="/systemahidrofalcon/frontend/node_modules/izitoast/dist/js/iziToast.min.js"></script>
    <script src="/systemahidrofalcon/frontend/pages/index.js"></script>
</body>
</html>