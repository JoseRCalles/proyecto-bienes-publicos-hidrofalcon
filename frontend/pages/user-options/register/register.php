<?php
// Este archivo es PHP, pero el contenido principal es HTML.
// Si necesitas lógica PHP arriba, agrégala aquí.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicia Sesion</title>
    <link rel="stylesheet" href="/systemaHidrofalcon/frontend/pages/user-options/register/register.css"/>
    <link rel="stylesheet" href="/systemaHidrofalcon/frontend/node_modules/tom-select/dist/css/tom-select.css">
    <link rel="stylesheet" href="/systemaHidrofalcon/frontend/node_modules/izitoast/dist/css/iziToast.min.css">
</head>
<body>
    <main class="main">
        <header class="header">
            <a href="/systemahidrofalcon/api/validate">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000">
                    <path d="M400-80 0-480l400-400 71 71-329 329 329 329-71 71Z"/>
                </svg>
            </a>
        </header>
        <div class="leftSide">
            <header class="leftSide__header">
                <h4>Registro</h4>  
            </header>
            <form action="/systemaHidrofalcon/backend/user-actions/register-user.php" method="POST" class="enter-data">
                <?php include($_SERVER['DOCUMENT_ROOT'] . "/systemaHidrofalcon/frontend/pages/user-options/register/selection/personalData.php");?>
                <?php include($_SERVER['DOCUMENT_ROOT'] . "/systemaHidrofalcon/frontend/pages/user-options/register/selection/security-questions.php");?>
            </form>
        </div>
        <div class="rightSide">
            <img src="/systemaHidrofalcon/frontend/pages/shared/images/logo.png" alt="Logo" class="logo">
        </div>
    </main>
    <script src="/systemaHidrofalcon/frontend/node_modules/tom-select/dist/js/tom-select.complete.js"></script>
    <script src="/systemaHidrofalcon/frontend/pages/user-options/register/register.js" type="module"></script>
    <script src="/systemaHidrofalcon/frontend/node_modules/izitoast/dist/js/iziToast.min.js"></script>
</body>
</html>