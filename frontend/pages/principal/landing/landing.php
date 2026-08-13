<?php
// Start the session if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
    header('Location: /systemaHidrofalcon/frontend/pages/no_autorizado.php');
    exit();
}

// If the user is not authenticated, the above file will redirect to no_autorizado.php
$sidebar_file_to_include = require $_SERVER['DOCUMENT_ROOT'] . "/systemaHidrofalcon/frontend/pages/shared/design-helpers/get-sidebar-path.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing page</title>
    <link rel="stylesheet" href="/systemaHidrofalcon/frontend/node_modules/izitoast/dist/css/iziToast.min.css">
    <link rel="stylesheet" href="/systemaHidrofalcon/frontend/pages/principal/landing/landing.css">
</head>
<body>
    <aside class="sidebar">

        <div class="sidebar-logo">
            <img class="logo-img" src="/systemaHidrofalcon/frontend/pages/principal/shared/logo-5.jpg" alt="Logo Hidrofalcón">
            <h6 class="logo-description">Hidrofalcon</h6>
        </div>
        
        <nav class="sidebar-links">
            <ul class="links">
                <?php
                    include $sidebar_file_to_include;
                ?>
            </ul>
        </nav>

        <button id="btnCerrarSesion" class="logout-link">
            <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="20" viewBox="0 0 24 20" fill="none">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M0.87868 1.38534C1.44129 0.818465 2.20435 0.5 3 0.5H13.2857C14.0814 0.5 14.8444 0.818465 15.407 1.38534C15.9696 1.95221 16.2857 2.72105 16.2857 3.52273V5.68182C16.2857 6.15879 15.902 6.54545 15.4286 6.54545C14.9552 6.54545 14.5714 6.15879 14.5714 5.68182V3.52273C14.5714 3.17915 14.436 2.84965 14.1949 2.6067C13.9537 2.36376 13.6267 2.22727 13.2857 2.22727H3C2.65901 2.22727 2.33198 2.36376 2.09086 2.6067C1.84974 2.84965 1.71429 3.17915 1.71429 3.52273V16.4773C1.71429 16.8208 1.84974 17.1504 2.09086 17.3933C2.33198 17.6362 2.65901 17.7727 3 17.7727H13.2857C13.6267 17.7727 13.9537 17.6362 14.1949 17.3933C14.436 17.1504 14.5714 16.8208 14.5714 16.4773V14.3182C14.5714 13.8412 14.9552 13.4545 15.4286 13.4545C15.902 13.4545 16.2857 13.8412 16.2857 14.3182V16.4773C16.2857 17.2789 15.9696 18.0478 15.407 18.6147C14.8444 19.1815 14.0814 19.5 13.2857 19.5H3C2.20435 19.5 1.44129 19.1815 0.87868 18.6147C0.31607 18.0478 0 17.2789 0 16.4773V3.52273C0 2.72105 0.31607 1.95221 0.87868 1.38534Z" fill="#1E293B"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M18.2511 5.07114C18.5858 4.73386 19.1285 4.73386 19.4632 5.07114L23.749 9.38932C24.0837 9.72659 24.0837 10.2734 23.749 10.6107L19.4632 14.9289C19.1285 15.2661 18.5858 15.2661 18.2511 14.9289C17.9163 14.5916 17.9163 14.0448 18.2511 13.7075L21.9307 10L18.2511 6.2925C17.9163 5.95523 17.9163 5.40841 18.2511 5.07114Z" fill="#1E293B"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M7.66071 10C7.66071 9.52303 8.04447 9.13636 8.51786 9.13636H23.1429C23.6162 9.13636 24 9.52303 24 10C24 10.477 23.6162 10.8636 23.1429 10.8636H8.51786C8.04447 10.8636 7.66071 10.477 7.66071 10Z" fill="#1E293B"/>
            </svg>
            <p>Salir</p>
        </button>
    </aside>

    <main class="main">
        <h5 class="main-title">¡Bienvenido al Sistema de Gestión de Bienes Públicos!</h5>
        <p class="main-description">
            Esta herramienta ha sido diseñada para optimizar y simplificar la administración de todos los activos de nuestra institución. Aquí podrás llevar un control riguroso de cada bien, desde su adquisición hasta su eventual desincorporación.
            <br><br>
            El sistema se organiza en módulos clave que te permitirán:
            <br> <br>
            <b class="bold">Incorporaciones:</b> Registrar de forma sencilla la entrada de nuevos bienes al inventario, asegurando su correcta clasificación y seguimiento.
            <br> <br>
            <b class="bold">Desincorporaciones:</b> Gestionar la salida de bienes por baja, obsolescencia o cualquier otra razón, manteniendo el inventario actualizado y preciso.
            <br> <br>
            <b class="bold">Movilizaciones:</b> Documentar los traslados internos de bienes entre dependencias o ubicaciones, garantizando su trazabilidad.
            <br> <br>
            Cada una de estas secciones cuenta con planillas específicas para facilitar el registro detallado de la información. Además, dispondrás de configuraciones para adaptar el sistema a las necesidades particulares de nuestra gestión.
            <br> <br>
            Nuestro objetivo es proporcionarte una plataforma intuitiva y eficiente para una administración de bienes pública transparente y organizada.
        </p>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/js/iziToast.min.js"></script>
    <script src="/systemaHidrofalcon/frontend/pages/principal/landing/landing.js"></script>
    <script src="/systemaHidrofalcon/frontend/node_modules/izitoast/dist/js/iziToast.min.js"></script>
    <script src="/systemaHidrofalcon/frontend/pages/notifications/landing.js"></script>
</body>
</html>