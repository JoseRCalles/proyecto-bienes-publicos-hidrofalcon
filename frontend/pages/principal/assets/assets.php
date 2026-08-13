<?php

// ¡MUY IMPORTANTE! session_start() debe ser la primera cosa en el script
// después de <?php, sin ningún espacio o línea en blanco antes.
session_start();


if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
    // --- DEBUGGING STEP 2: Message if redirection is about to happen ---
    
    // Si el usuario no está autenticado, redirige a la página de login.
    header('Location: /systemaHidrofalcon/login?acceso_denegado=true'); // Opcional: añade un parámetro para mensaje
    exit(); // ¡Importante! Termina la ejecución del script aquí
}


require $_SERVER['DOCUMENT_ROOT'] . "/systemaHidrofalcon/backend/connect/connect.php";

$sidebar_file_to_include = require $_SERVER['DOCUMENT_ROOT'] . "/systemaHidrofalcon/frontend/pages/shared/design-helpers/get-sidebar-path.php";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing page</title>
    <link rel="stylesheet" href="/systemaHidrofalcon/frontend/node_modules/izitoast/dist/css/iziToast.min.css">
    <link rel="stylesheet" href="/systemaHidrofalcon/frontend/pages/principal/assets/assets.css">
    <link rel="stylesheet" href="/systemaHidrofalcon/frontend/pages/principal/assets/modals/incorporation/incorporation.css">
    <link rel="stylesheet" href="/systemaHidrofalcon/frontend/pages/principal/assets/modals/desincorporation/desincorporation.css">
    <link rel="stylesheet" href="/systemaHidrofalcon/frontend/pages/principal/assets/modals/movilization/movilizacion.css">
    <link rel="stylesheet" href="/systemaHidrofalcon/frontend/pages/principal/assets/modals/relation/relation.css">
    <link rel="stylesheet" href="/systemaHidrofalcon/frontend/pages/principal/assets/modals/show-all/show-all.css">
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
                    if (isset($sidebar_file_to_include) && is_string($sidebar_file_to_include) && file_exists($sidebar_file_to_include)) {
                        include $sidebar_file_to_include;
                    } else {
                        echo '<li>Error: Archivo de barra lateral no encontrado o ruta inválida.</li>';
                    }
                ?>
            </ul>
        </nav>

        <a id="btnCerrarSesion" class="logout-link" href="/systemaHidrofalcon/api/logout">
            <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="20" viewBox="0 0 24 20" fill="none">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M0.87868 1.38534C1.44129 0.818465 2.20435 0.5 3 0.5H13.2857C14.0814 0.5 14.8444 0.818465 15.407 1.38534C15.9696 1.95221 16.2857 2.72105 16.2857 3.52273V5.68182C16.2857 6.15879 15.902 6.54545 15.4286 6.54545C14.9552 6.54545 14.5714 6.15879 14.5714 5.68182V3.52273C14.5714 3.17915 14.436 2.84965 14.1949 2.6067C13.9537 2.36376 13.6267 2.22727 13.2857 2.22727H3C2.65901 2.22727 2.33198 2.36376 2.09086 2.6067C1.84974 2.84965 1.71429 3.17915 1.71429 3.52273V16.4773C1.71429 16.8208 1.84974 17.1504 2.09086 17.3933C2.33198 17.6362 2.65901 17.7727 3 17.7727H13.2857C13.6267 17.7727 13.9537 17.6362 14.1949 17.3933C14.436 17.1504 14.5714 16.8208 14.5714 16.4773V14.3182C14.5714 13.8412 14.9552 13.4545 15.4286 13.4545C15.902 13.4545 16.2857 13.8412 16.2857 14.3182V16.4773C16.2857 17.2789 15.9696 18.0478 15.407 18.6147C14.8444 19.1815 14.0814 19.5 13.2857 19.5H3C2.20435 19.5 1.44129 19.1815 0.87868 18.6147C0.31607 18.0478 0 17.2789 0 16.4773V3.52273C0 2.72105 0.31607 1.95221 0.87868 1.38534Z" fill="#1E293B"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M18.2511 5.07114C18.5858 4.73386 19.1285 4.73386 19.4632 5.07114L23.749 9.38932C24.0837 9.72659 24.0837 10.2734 23.749 10.6107L19.4632 14.9289C19.1285 15.2661 18.5858 15.2661 18.2511 14.9289C17.9163 14.5916 17.9163 14.0448 18.2511 13.7075L21.9307 10L18.2511 6.2925C17.9163 5.95523 17.9163 5.40841 18.2511 5.07114Z" fill="#1E293B"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M7.66071 10C7.66071 9.52303 8.04447 9.13636 8.51786 9.13636H23.1429C23.6162 9.13636 24 9.52303 24 10C24 10.477 23.6162 10.8636 23.1429 10.8636H8.51786C8.04447 10.8636 7.66071 10.477 7.66071 10Z" fill="#1E293B"/>
            </svg>
            <p>Salir</p>
        </a>
    </aside>

    <main class="main">
        <header class="header">
            <nav class="nav">
                <div class="nav-item">
                    <p>Relacionar</p>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M3.71209 6.52459C4.0782 6.15847 4.6718 6.15847 5.03791 6.52459L10 11.4867L14.9621 6.52459C15.3282 6.15847 15.9218 6.15847 16.2879 6.52459C16.654 6.8907 16.654 7.4843 16.2879 7.85041L10.6629 13.4754C10.2968 13.8415 9.7032 13.8415 9.33709 13.4754L3.71209 7.85041C3.34597 7.4843 3.34597 6.8907 3.71209 6.52459Z" fill="#1E293B"/>
                    </svg>

                    <div class="dropdown-item incorporar">
                        <ul class="options">
                            <div class="options-item__container">
                                <button class="options-item" data-micromodal-trigger="relation-modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <g clip-path="url(#clip0_403_3168)">
                                            <path d="M23 11H13V1C13 0.734784 12.8946 0.48043 12.7071 0.292893C12.5196 0.105357 12.2652 0 12 0V0C11.7348 0 11.4804 0.105357 11.2929 0.292893C11.1054 0.48043 11 0.734784 11 1V11H1C0.734784 11 0.48043 11.1054 0.292893 11.2929C0.105357 11.4804 0 11.7348 0 12H0C0 12.2652 0.105357 12.5196 0.292893 12.7071C0.48043 12.8946 0.734784 13 1 13H11V23C11 23.2652 11.1054 23.5196 11.2929 23.7071C11.4804 23.8946 11.7348 24 12 24C12.2652 24 12.5196 23.8946 12.7071 23.7071C12.8946 23.5196 13 23.2652 13 23V13H23C23.2652 13 23.5196 12.8946 23.7071 12.7071C23.8946 12.5196 24 12.2652 24 12C24 11.7348 23.8946 11.4804 23.7071 11.2929C23.5196 11.1054 23.2652 11 23 11Z" fill="#374957"/>
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_403_3168">
                                                <rect width="24" height="24" fill="white"/>
                                            </clipPath>
                                        </defs>
                                    </svg>
                                    Uno
                                </button>
                            </div>

                            <li class="options-item__container">
                                <button href="#" class="options-item" data-micromodal-trigger="relation-morethan-modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M7 6.00024H23C23.2652 6.00024 23.5196 5.89488 23.7071 5.70735C23.8946 5.51981 24 5.26546 24 5.00024C24 4.73503 23.8946 4.48067 23.7071 4.29314C23.5196 4.1056 23.2652 4.00024 23 4.00024H7C6.73478 4.00024 6.48043 4.1056 6.29289 4.29314C6.10536 4.48067 6 4.73503 6 5.00024C6 5.26546 6.10536 5.51981 6.29289 5.70735C6.48043 5.89488 6.73478 6.00024 7 6.00024Z" fill="#374957"/>
                                        <path d="M23 10.9998H7C6.73478 10.9998 6.48043 11.1051 6.29289 11.2927C6.10536 11.4802 6 11.7345 6 11.9998C6 12.265 6.10536 12.5193 6.29289 12.7069C6.48043 12.8944 6.73478 12.9998 7 12.9998H23C23.2652 12.9998 23.5196 12.8944 23.7071 12.7069C23.8946 12.5193 24 12.265 24 11.9998C24 11.7345 23.8946 11.4802 23.7071 11.2927C23.5196 11.1051 23.2652 10.9998 23 10.9998Z" fill="#374957"/>
                                        <path d="M23 18H7C6.73478 18 6.48043 18.1054 6.29289 18.2929C6.10536 18.4804 6 18.7348 6 19C6 19.2652 6.10536 19.5196 6.29289 19.7071C6.48043 19.8947 6.73478 20 7 20H23C23.2652 20 23.5196 19.8947 23.7071 19.7071C23.8946 19.5196 24 19.2652 24 19C24 18.7348 23.8946 18.4804 23.7071 18.2929C23.5196 18.1054 23.2652 18 23 18Z" fill="#374957"/>
                                        <path d="M2 6.99999C3.10457 6.99999 4 6.10456 4 5C4 3.89543 3.10457 3 2 3C0.89543 3 0 3.89543 0 5C0 6.10456 0.89543 6.99999 2 6.99999Z" fill="#374957"/>
                                        <path d="M2 14.0002C3.10457 14.0002 4 13.1048 4 12.0002C4 10.8957 3.10457 10.0002 2 10.0002C0.89543 10.0002 0 10.8957 0 12.0002C0 13.1048 0.89543 14.0002 2 14.0002Z" fill="#374957"/>
                                        <path d="M2 20.9997C3.10457 20.9997 4 20.1043 4 18.9997C4 17.8951 3.10457 16.9997 2 16.9997C0.89543 16.9997 0 17.8951 0 18.9997C0 20.1043 0.89543 20.9997 2 20.9997Z" fill="#374957"/>
                                    </svg>
                                    Varios
                                </button>
                            </li>
                        </ul>

                    </div>
                </div>
                <div class="nav-item">
                    <p>Incorporar</p>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M3.71209 6.52459C4.0782 6.15847 4.6718 6.15847 5.03791 6.52459L10 11.4867L14.9621 6.52459C15.3282 6.15847 15.9218 6.15847 16.2879 6.52459C16.654 6.8907 16.654 7.4843 16.2879 7.85041L10.6629 13.4754C10.2968 13.8415 9.7032 13.8415 9.33709 13.4754L3.71209 7.85041C3.34597 7.4843 3.34597 6.8907 3.71209 6.52459Z" fill="#1E293B"/>
                    </svg>

                    <div class="dropdown-item incorporar">
                        <ul class="options">
                            <div class="options-item__container">
                                <button class="options-item" data-micromodal-trigger="incorporation-modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <g clip-path="url(#clip0_403_3168)">
                                            <path d="M23 11H13V1C13 0.734784 12.8946 0.48043 12.7071 0.292893C12.5196 0.105357 12.2652 0 12 0V0C11.7348 0 11.4804 0.105357 11.2929 0.292893C11.1054 0.48043 11 0.734784 11 1V11H1C0.734784 11 0.48043 11.1054 0.292893 11.2929C0.105357 11.4804 0 11.7348 0 12H0C0 12.2652 0.105357 12.5196 0.292893 12.7071C0.48043 12.8946 0.734784 13 1 13H11V23C11 23.2652 11.1054 23.5196 11.2929 23.7071C11.4804 23.8946 11.7348 24 12 24C12.2652 24 12.5196 23.8946 12.7071 23.7071C12.8946 23.5196 13 23.2652 13 23V13H23C23.2652 13 23.5196 12.8946 23.7071 12.7071C23.8946 12.5196 24 12.2652 24 12C24 11.7348 23.8946 11.4804 23.7071 11.2929C23.5196 11.1054 23.2652 11 23 11Z" fill="#374957"/>
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_403_3168">
                                                <rect width="24" height="24" fill="white"/>
                                            </clipPath>
                                        </defs>
                                    </svg>
                                    Uno
                                </button>
                            </div>

                            <li class="options-item__container">
                                <button href="#" class="options-item" data-micromodal-trigger="incorporation-excel-upload-modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M7 6.00024H23C23.2652 6.00024 23.5196 5.89488 23.7071 5.70735C23.8946 5.51981 24 5.26546 24 5.00024C24 4.73503 23.8946 4.48067 23.7071 4.29314C23.5196 4.1056 23.2652 4.00024 23 4.00024H7C6.73478 4.00024 6.48043 4.1056 6.29289 4.29314C6.10536 4.48067 6 4.73503 6 5.00024C6 5.26546 6.10536 5.51981 6.29289 5.70735C6.48043 5.89488 6.73478 6.00024 7 6.00024Z" fill="#374957"/>
                                        <path d="M23 10.9998H7C6.73478 10.9998 6.48043 11.1051 6.29289 11.2927C6.10536 11.4802 6 11.7345 6 11.9998C6 12.265 6.10536 12.5193 6.29289 12.7069C6.48043 12.8944 6.73478 12.9998 7 12.9998H23C23.2652 12.9998 23.5196 12.8944 23.7071 12.7069C23.8946 12.5193 24 12.265 24 11.9998C24 11.7345 23.8946 11.4802 23.7071 11.2927C23.5196 11.1051 23.2652 10.9998 23 10.9998Z" fill="#374957"/>
                                        <path d="M23 18H7C6.73478 18 6.48043 18.1054 6.29289 18.2929C6.10536 18.4804 6 18.7348 6 19C6 19.2652 6.10536 19.5196 6.29289 19.7071C6.48043 19.8947 6.73478 20 7 20H23C23.2652 20 23.5196 19.8947 23.7071 19.7071C23.8946 19.5196 24 19.2652 24 19C24 18.7348 23.8946 18.4804 23.7071 18.2929C23.5196 18.1054 23.2652 18 23 18Z" fill="#374957"/>
                                        <path d="M2 6.99999C3.10457 6.99999 4 6.10456 4 5C4 3.89543 3.10457 3 2 3C0.89543 3 0 3.89543 0 5C0 6.10456 0.89543 6.99999 2 6.99999Z" fill="#374957"/>
                                        <path d="M2 14.0002C3.10457 14.0002 4 13.1048 4 12.0002C4 10.8957 3.10457 10.0002 2 10.0002C0.89543 10.0002 0 10.8957 0 12.0002C0 13.1048 0.89543 14.0002 2 14.0002Z" fill="#374957"/>
                                        <path d="M2 20.9997C3.10457 20.9997 4 20.1043 4 18.9997C4 17.8951 3.10457 16.9997 2 16.9997C0.89543 16.9997 0 17.8951 0 18.9997C0 20.1043 0.89543 20.9997 2 20.9997Z" fill="#374957"/>
                                    </svg>
                                    Varios
                                </button>
                            </li>
                        </ul>

                    </div>
                </div>

                <div class="nav-item">
                    <p>Desincorporar</p>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M3.71209 6.52459C4.0782 6.15847 4.6718 6.15847 5.03791 6.52459L10 11.4867L14.9621 6.52459C15.3282 6.15847 15.9218 6.15847 16.2879 6.52459C16.654 6.8907 16.654 7.4843 16.2879 7.85041L10.6629 13.4754C10.2968 13.8415 9.7032 13.8415 9.33709 13.4754L3.71209 7.85041C3.34597 7.4843 3.34597 6.8907 3.71209 6.52459Z" fill="#1E293B"/>
                    </svg>

                    <div class="dropdown-item desincorporar">
                        <ul class="options">
                            <li class="options-item__container">
                                <button class="options-item" data-micromodal-trigger="desincorporation-modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <g clip-path="url(#clip0_403_3168)">
                                            <path d="M23 11H13V1C13 0.734784 12.8946 0.48043 12.7071 0.292893C12.5196 0.105357 12.2652 0 12 0V0C11.7348 0 11.4804 0.105357 11.2929 0.292893C11.1054 0.48043 11 0.734784 11 1V11H1C0.734784 11 0.48043 11.1054 0.292893 11.2929C0.105357 11.4804 0 11.7348 0 12H0C0 12.2652 0.105357 12.5196 0.292893 12.7071C0.48043 12.8946 0.734784 13 1 13H11V23C11 23.2652 11.1054 23.5196 11.2929 23.7071C11.4804 23.8946 11.7348 24 12 24C12.2652 24 12.5196 23.8946 12.7071 23.7071C12.8946 23.5196 13 23.2652 13 23V13H23C23.2652 13 23.5196 12.8946 23.7071 12.7071C23.8946 12.5196 24 12.2652 24 12C24 11.7348 23.8946 11.4804 23.7071 11.2929C23.5196 11.1054 23.2652 11 23 11Z" fill="#374957"/>
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_403_3168">
                                                <rect width="24" height="24" fill="white"/>
                                            </clipPath>
                                        </defs>
                                    </svg>
                                    Uno
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="nav-item">
                    <p>Movilizar</p>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M3.71209 6.52459C4.0782 6.15847 4.6718 6.15847 5.03791 6.52459L10 11.4867L14.9621 6.52459C15.3282 6.15847 15.9218 6.15847 16.2879 6.52459C16.654 6.8907 16.654 7.4843 16.2879 7.85041L10.6629 13.4754C10.2968 13.8415 9.7032 13.8415 9.33709 13.4754L3.71209 7.85041C3.34597 7.4843 3.34597 6.8907 3.71209 6.52459Z" fill="#1E293B"/>
                    </svg>

                    <div class="dropdown-item movilizar">
                        <ul class="options">
                            <li class="options-item__container">
                                <button class="options-item" data-micromodal-trigger="movilization-modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <g clip-path="url(#clip0_403_3168)">
                                            <path d="M23 11H13V1C13 0.734784 12.8946 0.48043 12.7071 0.292893C12.5196 0.105357 12.2652 0 12 0V0C11.7348 0 11.4804 0.105357 11.2929 0.292893C11.1054 0.48043 11 0.734784 11 1V11H1C0.734784 11 0.48043 11.1054 0.292893 11.2929C0.105357 11.4804 0 11.7348 0 12H0C0 12.2652 0.105357 12.5196 0.292893 12.7071C0.48043 12.8946 0.734784 13 1 13H11V23C11 23.2652 11.1054 23.5196 11.2929 23.7071C11.4804 23.8946 11.7348 24 12 24C12.2652 24 12.5196 23.8946 12.7071 23.7071C12.8946 23.5196 13 23.2652 13 23V13H23C23.2652 13 23.5196 12.8946 23.7071 12.7071C23.8946 12.5196 24 12.2652 24 12C24 11.7348 23.8946 11.4804 23.7071 11.2929C23.5196 11.1054 23.2652 11 23 11Z" fill="#374957"/>
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_403_3168">
                                                <rect width="24" height="24" fill="white"/>
                                            </clipPath>
                                        </defs>
                                    </svg>
                                    Uno
                                </button>

                            </li>

                        </ul>
                    </div>
                </div>
            </nav>
        </header>


        <section class="data">
            <div class="data-card">
                <h6 id="assets-title" class="card-title">Bienes Incorporados</h6>
                <h4 id="incorporation-count" class="card-data">Cargando...</h4> 
            </div>
            <div class="data-card">
                <h6 class="card-title">Bienes Desincorporados</h6>
                <h4 id="desincorporation-count" class="card-data">Cargando...</h4>
            </div>
            <div class="data-card">
                <h6 class="card-title">Bienes Sin Asignar</h6>
                <h4 id="sin-asignar-count" class="card-data">Cargando...</h4> 
            </div>
        </section>


        <section class="properties-table">
            <div class="table-header">
                <form action="" method="get" class="searchbar-form" id="propertiesSearchForm">
                    <input type="text" placeholder="Buscar" class="properties__searchbar" id="propertiesSearchInput">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="searchbar-icon">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M5.85593 3.61739C7.1902 2.72585 8.75888 2.25 10.3636 2.25C12.5154 2.25014 14.5791 3.105 16.1006 4.62655C17.6222 6.14811 18.4771 8.21174 18.4772 10.3635C18.4772 11.9683 18.0013 13.537 17.1098 14.8713C16.2183 16.2055 14.9511 17.2455 13.4685 17.8596C11.986 18.4737 10.3546 18.6344 8.78071 18.3213C7.20683 18.0082 5.76113 17.2355 4.62642 16.1008C3.49171 14.9661 2.71897 13.5204 2.86761 7.25866C3.48171 5.77609 4.52165 4.50892 5.85593 3.61739ZM10.3635 3.75C9.05552 3.75001 7.77687 4.13789 6.68928 4.86459C5.60168 5.5913 4.754 6.6242 4.25343 7.83268C3.75287 9.04116 3.6219 10.3709 3.87708 11.6538C4.13227 12.9368 4.76215 14.1152 5.68708 15.0401C6.61201 15.965 7.79044 16.5949 9.07335 16.8501C10.3563 17.1053 11.686 16.9743 12.8945 16.4738C14.103 15.9732 15.1359 15.1255 15.8626 14.0379C16.5893 12.9503 16.9772 11.6717 16.9772 10.3636" fill="#1E293B"/>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M15.327 15.327C15.6199 15.0341 16.0948 15.0341 16.3877 15.327L21.5303 20.4697C21.8232 20.7626 21.8232 21.2374 21.5303 21.5303C21.2374 21.8232 20.7626 21.8232 20.4697 21.5303L15.327 16.3877C15.0341 16.0948 15.0341 15.6199 15.327 15.327Z" fill="#1E293B"/>
                    </svg>
                </form>

                <div class="header-leftside">
                    <div class="buttons">
                        <button class="button button-mostrar" id="showAllPropertiesBtn" data-micromodal-trigger="showAll-modal">Mostrar todo</button>
                    </div>
                </div>
            </div>
            <div>
                <div class="error-message" id="propertiesErrorMessage" style="display:none;">
                    <p></p>
                </div>
            </div>
            <div class="table-properties__container">
                <table class="table-properties">
                    <thead class="head">
                        <tr class="head" id="propertiesTableHeaderRow">
                            </tr>
                    </thead>
                    <tbody id="propertiesTableBody">
                        <tr>
                            <td colspan="100%">Cargando bienes...</td> </tr>
                    </tbody>
                </table>
            </div>

            <div class="pagination-controls">
                <button id="prev-asset-page-btn" class="page-btn" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><g clip-path="url(#clip0_403_3101)"><path d="M17.1699 24C17.0383 24.0008 16.9078 23.9756 16.786 23.9258C16.6641 23.876 16.5533 23.8027 16.4599 23.71L8.28989 15.54C7.82426 15.0756 7.45483 14.5238 7.20277 13.9164C6.9507 13.3089 6.82095 12.6577 6.82095 12C6.82095 11.3424 6.9507 10.6912 7.20277 10.0837C7.45483 9.47626 7.82426 8.92451 8.28989 8.46005L16.4599 0.290063C16.5531 0.196825 16.6638 0.122864 16.7856 0.0724035C16.9075 0.0219432 17.038 -0.00402832 17.1699 -0.00402832C17.3018 -0.00402832 17.4323 0.0219432 17.5541 0.0724035C17.676 0.122864 17.7867 0.196825 17.8799 0.290063C17.9731 0.383301 18.0471 0.493991 18.0976 0.615813C18.148 0.737635 18.174 0.868203 18.174 1.00006C18.174 1.13192 18.148 1.26249 18.0976 1.38431C18.0471 1.50613 17.9731 1.61682 17.8799 1.71006L9.70989 9.88005C9.14809 10.4426 8.83253 11.205 8.83253 12C8.83253 12.7951 9.14809 13.5575 9.70989 14.12L17.8799 22.29C17.9736 22.383 18.048 22.4936 18.0988 22.6155C18.1496 22.7373 18.1757 22.868 18.1757 23C18.1757 23.132 18.1496 23.2628 18.0988 23.3846C18.048 23.5065 17.9736 23.6171 17.8799 23.71C17.7865 23.8027 17.6756 23.876 17.5538 23.9258C17.432 23.9756 17.3015 24.0008 17.1699 24Z" fill="#374957"/></g><defs><clipPath id="clip0_403_3101"><rect width="24" height="24" fill="white"/></clipPath></defs></svg>
                </button>
                <button id="next-asset-page-btn" class="page-btn" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><g clip-path="url(#clip0_403_3214)"><path d="M6.99985 24C6.86824 24.0008 6.73778 23.9756 6.61594 23.9258C6.4941 23.876 6.38329 23.8027 6.28985 23.71C6.19612 23.6171 6.12172 23.5065 6.07096 23.3846C6.02019 23.2628 5.99405 23.132 5.99405 23C5.99405 22.868 6.02019 22.7373 6.07096 22.6155C6.12172 22.4936 6.19612 22.383 6.28985 22.29L14.4598 14.12C15.0216 13.5575 15.3372 12.7951 15.3372 12C15.3372 11.205 15.0216 10.4426 14.4598 9.88005L6.28985 1.71006C6.10154 1.52176 5.99576 1.26636 5.99576 1.00006C5.99576 0.733761 6.10154 0.478366 6.28985 0.290063C6.47815 0.101759 6.73355 -0.00402832 6.99985 -0.00402832C7.26615 -0.00402832 7.52154 0.101759 7.70985 0.290063L15.8798 8.46005C16.3455 8.92451 16.7149 9.47626 16.967 10.0837C17.219 10.6912 17.3488 11.3424 17.3488 12C17.3488 12.6577 17.219 13.3089 16.967 13.9164C16.7149 14.5238 16.3455 15.0756 15.8798 15.54L7.70985 23.71C7.61641 23.8027 7.50559 23.876 7.38375 23.9258C7.26192 23.9756 7.13145 24.0008 6.99985 24Z" fill="#374957"/></g><defs><clipPath id="clip0_403_3214"><rect width="24" height="24" fill="white"/></clipPath></defs></svg>
                </button>
                <div id="pagination-numbers" class="pagination-numbers">
                </div>
            </div>
        </section>

        <?php
            include('./modals/relation/relation.php');
            include('./modals/incorporation/incorporation.php');
            include('./modals/desincorporation/desincorporation.php');
            include('./modals/movilization/movilization.php');
            include('./modals/show-all/show-all.php');
            include('./modals/relation/relation-morethan/relation-morethan.php');
            include('./modals/incorporation/incorporation-morethan/incorporation-morethan.php');
        ?>
    </main>


    <script>
        // Se declara la variable directamente en el objeto global "window"
        // No es necesario que sea un "type=module"
        window.currentUserId = <?php echo json_encode($_SESSION['usuario_id']); ?>;
    </script>
    <script src="/systemaHidrofalcon/frontend/node_modules/micromodal/dist/micromodal.min.js"></script>
    <script src="/systemaHidrofalcon/frontend/pages/principal/assets/assets.js"></script>
    <script type="module" src="/systemaHidrofalcon/frontend/pages/principal/assets/modals/relation/relation.js"></script>
    <script type="module" src="/systemaHidrofalcon/frontend/pages/principal/assets/modals/incorporation/incorporation.js"></script>
    <script type="module" src="/systemaHidrofalcon/frontend/pages/principal/assets/modals/movilization/movilization.js"></script>
    <script type="module" src="/systemaHidrofalcon/frontend/pages/principal/assets/modals/desincorporation/desincorporation.js"></script>
    <script type="module" src="/systemaHidrofalcon/frontend/pages/principal/assets/modals/show-all/show-all.js"></script>
    <script type="module" src="/systemaHidrofalcon/frontend/pages/principal/assets/modals/shared/js/modalshow.js"></script>
    <script src="/systemaHidrofalcon/frontend/node_modules/izitoast/dist/js/iziToast.min.js"></script>
    <script src="/systemaHidrofalcon/frontend/pages/notifications/landing.js"></script>
    <script type="module" src="/systemaHidrofalcon/frontend/pages/principal/assets/modals/relation/relation-morethan/relation-morethan.js"></script>
    <script type="module" src="/systemaHidrofalcon/frontend/pages/principal/assets/modals/incorporation/incorporation-morethan/incorporation-morethan.js"></script>
</body>
</html>