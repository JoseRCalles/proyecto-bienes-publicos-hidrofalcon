<?php

require '../../../../backend/connect/connect.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/systemahidrofalcon/frontend/node_modules/izitoast/dist/css/iziToast.min.css">

    <title>Olvidaste tu contraseña?</title>
    <link rel="stylesheet" href="/systemahidrofalcon/frontend/pages/user-options/forgot-password/forgot-password.css">
</head>
<body>
    <main class="main">
        <div class="leftSide">
            <?php require './verify/username.php'?>
            <?php require './verify/security-questions.php'?>
        </div>
        <div class="rightSide">
            <img src="/systemahidrofalcon/frontend/pages/shared/images/logo.png" alt="Logo" class="logo">
        </div>
    </main>

    <script src="/systemahidrofalcon/frontend/pages/user-options/forgot-password/forgot-password.js"></script>
    <script src="/systemahidrofalcon/frontend/node_modules/izitoast/src/js/iziToast.min.js"></script>
</body>
</html>