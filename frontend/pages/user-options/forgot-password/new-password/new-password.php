<?php

session_start(); // Siempre inicia la sesión al principio

include '/wamp64/www/systemaHidrofalcon/backend/connect/connect.php'; // Ajusta esta ruta según tu estructura de carpetas

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Olvidaste tu contraseña?</title>
    <link rel="stylesheet" href="/systemaHidrofalcon/frontend/node_modules/izitoast/dist/css/iziToast.min.css">
    <link rel="stylesheet" href="/systemaHidrofalcon/frontend/pages/user-options/forgot-password/new-password/new-password.css">
</head>
<body>
    <main class="main">
        <div class="leftSide">
                <div class="username">
                    <header class="leftSide__header">
                        <h5>Ingrese su nueva contraseña</h5>
                    </header>
                    <form class="enter-data" id="username-form">
                        <div class="text-inputs">
                            <div class="input-container">
                                <label for="forgot-username" class="label-input">Contraseña</label>
                                <input type="password" id="password" name="password" class="input">
                                <div class="err-password err-box"></div>
                            </div>

                        </div>

                        <div class="text-inputs">
                            <div class="input-container">
                                <label for="forgot-username" class="label-input">Confirmar contraseña</label>
                                <input type="password" id="confirm-password" name="confirm_password" class="input">
                                <div class="err-cpassword err-box"></div>
                            </div>

                            <div class="information">
                                <p class="information-header">La contraseña debe cumplir con</p>
                                <ul class="information__list">
                                    <li class="information-item unactive">
                                        <svg class="check-svg" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M382-240 154-468l57-57 171 171 367-367 57 57-424 424Z"/></svg>
                                        <svg class="information-svg" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/></svg>
                                        <p class="information-p">Minimo 8 caracteres</p>
                                    </li>
                                    <li class="information-item unactive">
                                        <svg class="check-svg" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M382-240 154-468l57-57 171 171 367-367 57 57-424 424Z"/></svg>
                                        <svg class="information-svg" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/></svg>
                                        <p class="information-p">Un caracter numerico</p>
                                    </li>
                                    <li class="information-item unactive">
                                        <svg class="check-svg" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M382-240 154-468l57-57 171 171 367-367 57 57-424 424Z"/></svg>
                                        <svg class="information-svg" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/></svg>
                                        <p class="information-p">Una letra minúscula (a,b,c)</p>
                                    </li>
                                    <li class="information-item unactive">
                                        <svg class="check-svg" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M382-240 154-468l57-57 171 171 367-367 57 57-424 424Z"/></svg>
                                        <svg class="information-svg" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/></svg>
                                        <p class="information-p">Un caracter especial (@,#,$)</p>
                                    </li>
                                    <li class="information-item unactive">
                                        <svg class="check-svg" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M382-240 154-468l57-57 171 171 367-367 57 57-424 424Z"/></svg>
                                        <svg class="information-svg" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/></svg>
                                        <p class="information-p">Una letra mayúsculas (A,B,C)</p>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="extra-actions">
                            <div class="button-container">
                                <button type="button" class="register-button" id="change-button">Verificar</button>
                            </div>
                        </div>
                    </form>
            </div>
        </div>
        <div class="rightSide">
            <img src="/systemaHidrofalcon/frontend/pages/shared/images/image.png" alt="Logo">
        </div>
    </main>

    <script src="/systemaHidrofalcon/frontend/pages/user-options/forgot-password/new-password/new-password.js"></script>
    <script src="/systemaHidrofalcon/frontend/node_modules/izitoast/dist/js/iziToast.min.js"></script>
</body>
</html>