<?php

// ¡MUY IMPORTANTE! session_start() debe ser la primera cosa en el script
// después de <?php, sin ningún espacio o línea en blanco antes.
session_start();


if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
    // --- DEBUGGING STEP 2: Message if redirection is about to happen ---
    
    // Si el usuario no está autenticado, redirige a la página de login.
    header('Location: /systemaHidrofalcon/frontend/index.php?acceso_denegado=true'); // Opcional: añade un parámetro para mensaje
    exit(); // ¡Importante! Termina la ejecución del script aquí
}


require '../../../../backend/connect/connect.php'; // This file should provide $conexion (mysqli object)

$sidebar_file_to_include = require '../../shared/design-helpers/get-sidebar-path.php';

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/systemaHidrofalcon/frontend/pages/principal/settings/settings.css">
    <link rel="stylesheet" href="/systemaHidrofalcon/frontend/node_modules/izitoast/dist/css/iziToast.min.css">
    <link rel="stylesheet" href="/systemaHidrofalcon/frontend/pages/principal/assets/modals/shared/css/modal.css">
    <link rel="stylesheet" href="/systemaHidrofalcon/frontend/pages/principal/assets/modals/shared/css/search-employee-and-propertie.css">
    <link rel="stylesheet" href="/systemaHidrofalcon/frontend/pages/principal/settings/modals/show-all__sedes/show-all__sedes.css">

    <title>Configuracion</title>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-logo">
            <!-- Ruta absoluta para imagen -->
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

        <a id="btnCerrarSesion" class="logout-link"  href="/systemaHidrofalcon/api/session?action=logout">
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
                    <p>Agregar</p>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M3.71209 6.52459C4.0782 6.15847 4.6718 6.15847 5.03791 6.52459L10 11.4867L14.9621 6.52459C15.3282 6.15847 15.9218 6.15847 16.2879 6.52459C16.654 6.8907 16.654 7.4843 16.2879 7.85041L10.6629 13.4754C10.2968 13.8415 9.7032 13.8415 9.33709 13.4754L3.71209 7.85041C3.34597 7.4843 3.34597 6.8907 3.71209 6.52459Z" fill="#1E293B"/>
                    </svg>

                    <div class="dropdown-item incorporar">
                        <ul class="options">
                            <li class="options-item__container">
                                <button class="options-item" data-micromodal-trigger="add-sede-modal">
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
                                    Sedes
                                </button>
                            </li>

                            <li class="options-item__container">
                                <button href="#" class="options-item" data-micromodal-trigger="add-employee-modal">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M17.53 7.77C17.46 7.76 17.39 7.76 17.32 7.77C15.77 7.72 14.54 6.45 14.54 4.89C14.54 3.3 15.83 2 17.43 2C19.02 2 20.32 3.29 20.32 4.89C20.31 6.45 19.08 7.72 17.53 7.77Z" fill="#374957"/>
                                        <path d="M20.7896 14.6999C19.6696 15.4499 18.0996 15.7299 16.6496 15.5399C17.0296 14.7199 17.2296 13.8099 17.2396 12.8499C17.2396 11.8499 17.0196 10.8999 16.5996 10.0699C18.0796 9.86991 19.6496 10.1499 20.7796 10.8999C22.3596 11.9399 22.3596 13.6499 20.7896 14.6999Z" fill="#374957"/>
                                        <path d="M6.44016 7.77C6.51016 7.76 6.58016 7.76 6.65016 7.77C8.20016 7.72 9.43016 6.45 9.43016 4.89C9.43016 3.29 8.14016 2 6.54016 2C4.95016 2 3.66016 3.29 3.66016 4.89C3.66016 6.45 4.89016 7.72 6.44016 7.77Z" fill="#374957"/>
                                        <path d="M6.55012 12.8501C6.55012 13.8201 6.76012 14.7401 7.14012 15.5701C5.73012 15.7201 4.26012 15.4201 3.18012 14.7101C1.60012 13.6601 1.60012 11.9501 3.18012 10.9001C4.25012 10.1801 5.76012 9.8901 7.18012 10.0501C6.77012 10.8901 6.55012 11.8401 6.55012 12.8501Z" fill="#374957"/>
                                        <path d="M12.1198 15.87C12.0398 15.86 11.9498 15.86 11.8598 15.87C10.0198 15.81 8.5498 14.3 8.5498 12.44C8.5598 10.54 10.0898 9 11.9998 9C13.8998 9 15.4398 10.54 15.4398 12.44C15.4298 14.3 13.9698 15.81 12.1198 15.87Z" fill="#374957"/>
                                        <path d="M8.8698 17.9401C7.3598 18.9501 7.3598 20.6101 8.8698 21.6101C10.5898 22.7601 13.4098 22.7601 15.1298 21.6101C16.6398 20.6001 16.6398 18.9401 15.1298 17.9401C13.4198 16.7901 10.5998 16.7901 8.8698 17.9401Z" fill="#374957"/>
                                    </svg>

                                    Trabajadores
                                </button>
                            </li>

                            <li class="options-item__container">
                                <button href="#" class="options-item" data-micromodal-trigger="add-gerencia-modal">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12.7 16.8798H13.4C14.55 16.8798 15.49 15.9398 15.49 14.7898V14.0898H12.7V16.8798Z" fill="#374957"/>
                                        <path d="M8.51001 14.7898C8.51001 15.9398 9.45001 16.8798 10.6 16.8798H11.3V14.0898H8.51001V14.7898Z" fill="#374957"/>
                                        <path d="M8.51001 12.0002V12.7002H11.3V9.91016H10.6C9.45001 9.91016 8.51001 10.8502 8.51001 12.0002Z" fill="#374957"/>
                                        <path d="M20.03 6.82018L14.28 2.79018C12.71 1.69018 10.31 1.75018 8.8 2.92018L3.79 6.83018C2.78 7.61018 2 9.21018 2 10.4702V17.3702C2 19.9202 4.07 22.0002 6.61 22.0002H17.38C19.92 22.0002 21.99 19.9302 21.99 17.3802V10.6002C22 9.25018 21.13 7.59018 20.03 6.82018ZM16.88 14.7902C16.88 16.7102 15.31 18.2802 13.39 18.2802H10.6C8.68 18.2802 7.11 16.7202 7.11 14.7902V12.0002C7.11 10.0802 8.68 8.51018 10.6 8.51018H13.39C15.31 8.51018 16.88 10.0702 16.88 12.0002V14.7902Z" fill="#374957"/>
                                        <path d="M13.4 9.91016H12.7V12.7002H15.49V12.0002C15.49 10.8502 14.55 9.91016 13.4 9.91016Z" fill="#374957"/>
                                    </svg>

                                    Gerencias
                                </button>
                            </li>
                        </ul>

                    </div>
                </div>
                

                
            </nav>
        </header>

        <section class="properties-table">
            <div class="table-header">
                <div class="header-rightside">
                    <h6 class="table-header__title">Sedes</h6>
                </div>
                <div class="header-leftside">
                    <form action="" method="get" class="searchbar-form" id="sedesSearchForm">
                        <input type="text" placeholder="Buscar Sede" class="properties__searchbar__settings" id="sedesSearchInput">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="searchbar-icon">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.85593 3.61739C7.1902 2.72585 8.75888 2.25 10.3636 2.25C12.5154 2.25014 14.5791 3.105 16.1006 4.62655C17.6222 6.14811 18.4771 8.21174 18.4772 10.3635C18.4772 11.9683 18.0013 13.537 17.1098 14.8713C16.2183 16.2055 14.9511 17.2455 13.4685 17.8596C11.986 18.4737 10.3546 18.6344 8.78071 18.3213C7.20683 18.0082 5.76113 17.2355 4.62642 16.1008C3.49171 14.9661 2.71897 13.5204 2.86761 7.25866C3.48171 5.77609 4.52165 4.50892 5.85593 3.61739ZM10.3635 3.75C9.05552 3.75001 7.77687 4.13789 6.68928 4.86459C5.60168 5.5913 4.754 6.6242 4.25343 7.83268C3.75287 9.04116 3.6219 10.3709 3.87708 11.6538C4.13227 12.9368 4.76215 14.1152 5.68708 15.0401C6.61201 15.965 7.79044 16.5949 9.07335 16.8501C10.3563 17.1053 11.686 16.9743 12.8945 16.4738C14.103 15.9732 15.1359 15.1255 15.8626 14.0379C16.5893 12.9503 16.9772 11.6717 16.9772 10.3636" fill="#1E293B"/>
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M15.327 15.327C15.6199 15.0341 16.0948 15.0341 16.3877 15.327L21.5303 20.4697C21.8232 20.7626 21.8232 21.2374 21.5303 21.5303C21.2374 21.8232 20.7626 21.8232 20.4697 21.5303L15.327 16.3877C15.0341 16.0948 15.0341 15.6199 15.327 15.327Z" fill="#1E293B"/>
                        </svg>
                    </form>
                    <div class="buttons">
                        <button class="button button-mostrar" id="showAllSedesBtn" data-micromodal-trigger="showAll-modal__sedes">Mostrar todo</button>
                    </div>
                </div>
            </div>
            <div>
                <div class="error-message" id="sedesErrorMessage" style="display:none;">
                    <p></p>
                </div>
            </div>
            <div class="table-settings__container">
                <table class="table-properties">
                    <thead class="head">
                        <tr class="head" id="sedesTableHeaderRow">
                            <th>Id</th>
                            <th>Sede</th>
                            <th>Municipio</th>
                        </tr>
                    </thead>
                    <tbody id="sedesTableBody">
                        <tr>
                            <td colspan="100%">Cargando bienes...</td> </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="pagination-controls">
                <button id="prev-sedes-page-btn" class="page-btn" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><g clip-path="url(#clip0_403_3101)"><path d="M17.1699 24C17.0383 24.0008 16.9078 23.9756 16.786 23.9258C16.6641 23.876 16.5533 23.8027 16.4599 23.71L8.28989 15.54C7.82426 15.0756 7.45483 14.5238 7.20277 13.9164C6.9507 13.3089 6.82095 12.6577 6.82095 12C6.82095 11.3424 6.9507 10.6912 7.20277 10.0837C7.45483 9.47626 7.82426 8.92451 8.28989 8.46005L16.4599 0.290063C16.5531 0.196825 16.6638 0.122864 16.7856 0.0724035C16.9075 0.0219432 17.038 -0.00402832 17.1699 -0.00402832C17.3018 -0.00402832 17.4323 0.0219432 17.5541 0.0724035C17.676 0.122864 17.7867 0.196825 17.8799 0.290063C17.9731 0.383301 18.0471 0.493991 18.0976 0.615813C18.148 0.737635 18.174 0.868203 18.174 1.00006C18.174 1.13192 18.148 1.26249 18.0976 1.38431C18.0471 1.50613 17.9731 1.61682 17.8799 1.71006L9.70989 9.88005C9.14809 10.4426 8.83253 11.205 8.83253 12C8.83253 12.7951 9.14809 13.5575 9.70989 14.12L17.8799 22.29C17.9736 22.383 18.048 22.4936 18.0988 22.6155C18.1496 22.7373 18.1757 22.868 18.1757 23C18.1757 23.132 18.1496 23.2628 18.0988 23.3846C18.048 23.5065 17.9736 23.6171 17.8799 23.71C17.7865 23.8027 17.6756 23.876 17.5538 23.9258C17.432 23.9756 17.3015 24.0008 17.1699 24Z" fill="#374957"/></g><defs><clipPath id="clip0_403_3101"><rect width="24" height="24" fill="white"/></clipPath></defs></svg>
                </button>
                <button id="next-sedes-page-btn" class="page-btn" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><g clip-path="url(#clip0_403_3214)"><path d="M6.99985 24C6.86824 24.0008 6.73778 23.9756 6.61594 23.9258C6.4941 23.876 6.38329 23.8027 6.28985 23.71C6.19612 23.6171 6.12172 23.5065 6.07096 23.3846C6.02019 23.2628 5.99405 23.132 5.99405 23C5.99405 22.868 6.02019 22.7373 6.07096 22.6155C6.12172 22.4936 6.19612 22.383 6.28985 22.29L14.4598 14.12C15.0216 13.5575 15.3372 12.7951 15.3372 12C15.3372 11.205 15.0216 10.4426 14.4598 9.88005L6.28985 1.71006C6.10154 1.52176 5.99576 1.26636 5.99576 1.00006C5.99576 0.733761 6.10154 0.478366 6.28985 0.290063C6.47815 0.101759 6.73355 -0.00402832 6.99985 -0.00402832C7.26615 -0.00402832 7.52154 0.101759 7.70985 0.290063L15.8798 8.46005C16.3455 8.92451 16.7149 9.47626 16.967 10.0837C17.219 10.6912 17.3488 11.3424 17.3488 12C17.3488 12.6577 17.219 13.3089 16.967 13.9164C16.7149 14.5238 16.3455 15.0756 15.8798 15.54L7.70985 23.71C7.61641 23.8027 7.50559 23.876 7.38375 23.9258C7.26192 23.9756 7.13145 24.0008 6.99985 24Z" fill="#374957"/></g><defs><clipPath id="clip0_403_3214"><rect width="24" height="24" fill="white"/></clipPath></defs></svg>
                </button>
                <div id="sedes-pagination-numbers" class="pagination-numbers">
                    </div>
                </div>
            </section>
            
            <section class="properties-table">
                <div class="table-header">
                    <div class="header-rightside">
                        <h6 class="table-header__title">Trabajadores</h6>
                    </div>
                    <div class="header-leftside">
                        <form action="" method="get" class="searchbar-form" id="employeesSearchForm">
                            <input type="text" placeholder="Buscar Trabajador" class="properties__searchbar__settings" id="employeesSearchInput">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="searchbar-icon">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M5.85593 3.61739C7.1902 2.72585 8.75888 2.25 10.3636 2.25C12.5154 2.25014 14.5791 3.105 16.1006 4.62655C17.6222 6.14811 18.4771 8.21174 18.4772 10.3635C18.4772 11.9683 18.0013 13.537 17.1098 14.8713C16.2183 16.2055 14.9511 17.2455 13.4685 17.8596C11.986 18.4737 10.3546 18.6344 8.78071 18.3213C7.20683 18.0082 5.76113 17.2355 4.62642 16.1008C3.49171 14.9661 2.71897 13.5204 2.86761 7.25866C3.48171 5.77609 4.52165 4.50892 5.85593 3.61739ZM10.3635 3.75C9.05552 3.75001 7.77687 4.13789 6.68928 4.86459C5.60168 5.5913 4.754 6.6242 4.25343 7.83268C3.75287 9.04116 3.6219 10.3709 3.87708 11.6538C4.13227 12.9368 4.76215 14.1152 5.68708 15.0401C6.61201 15.965 7.79044 16.5949 9.07335 16.8501C10.3563 17.1053 11.686 16.9743 12.8945 16.4738C14.103 15.9732 15.1359 15.1255 15.8626 14.0379C16.5893 12.9503 16.9772 11.6717 16.9772 10.3636" fill="#1E293B"/>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M15.327 15.327C15.6199 15.0341 16.0948 15.0341 16.3877 15.327L21.5303 20.4697C21.8232 20.7626 21.8232 21.2374 21.5303 21.5303C21.2374 21.8232 20.7626 21.8232 20.4697 21.5303L15.327 16.3877C15.0341 16.0948 15.0341 15.6199 15.327 15.327Z" fill="#1E293B"/>
                            </svg>
                        </form>
                        <div class="buttons">
                            <button class="button button-mostrar" id="showAllEmployeesBtn" data-micromodal-trigger="showAll-modal__employees">Mostrar todo</button>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="error-message" id="employeesErrorMessage" style="display:none;">
                        <p></p>
                    </div>
                </div>
                <div class="table-settings__container">
                    <table class="table-properties">
                        <thead class="head">
                            <tr class="head" id="employeesTableHeaderRow">
                                <th>Cedula</th>
                                <th>Nombres</th>
                                <th>Apellidos</th>
                                <th>Telefono</th>
                                <th>Cargo</th>
                                <th>Gerencia</th>
                            </tr>
                        </thead>
                        <tbody id="employeesTableBody">
                            <tr>
                                <td colspan="100%">Cargando bienes...</td> </tr>
                        </tbody>
                    </table>
                </div>
    
                <div class="pagination-controls">
                    <button id="prev-employees-page-btn" class="page-btn" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><g clip-path="url(#clip0_403_3101)"><path d="M17.1699 24C17.0383 24.0008 16.9078 23.9756 16.786 23.9258C16.6641 23.876 16.5533 23.8027 16.4599 23.71L8.28989 15.54C7.82426 15.0756 7.45483 14.5238 7.20277 13.9164C6.9507 13.3089 6.82095 12.6577 6.82095 12C6.82095 11.3424 6.9507 10.6912 7.20277 10.0837C7.45483 9.47626 7.82426 8.92451 8.28989 8.46005L16.4599 0.290063C16.5531 0.196825 16.6638 0.122864 16.7856 0.0724035C16.9075 0.0219432 17.038 -0.00402832 17.1699 -0.00402832C17.3018 -0.00402832 17.4323 0.0219432 17.5541 0.0724035C17.676 0.122864 17.7867 0.196825 17.8799 0.290063C17.9731 0.383301 18.0471 0.493991 18.0976 0.615813C18.148 0.737635 18.174 0.868203 18.174 1.00006C18.174 1.13192 18.148 1.26249 18.0976 1.38431C18.0471 1.50613 17.9731 1.61682 17.8799 1.71006L9.70989 9.88005C9.14809 10.4426 8.83253 11.205 8.83253 12C8.83253 12.7951 9.14809 13.5575 9.70989 14.12L17.8799 22.29C17.9736 22.383 18.048 22.4936 18.0988 22.6155C18.1496 22.7373 18.1757 22.868 18.1757 23C18.1757 23.132 18.1496 23.2628 18.0988 23.3846C18.048 23.5065 17.9736 23.6171 17.8799 23.71C17.7865 23.8027 17.6756 23.876 17.5538 23.9258C17.432 23.9756 17.3015 24.0008 17.1699 24Z" fill="#374957"/></g><defs><clipPath id="clip0_403_3101"><rect width="24" height="24" fill="white"/></clipPath></defs></svg>
                    </button>
                    <button id="next-employees-page-btn" class="page-btn" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><g clip-path="url(#clip0_403_3214)"><path d="M6.99985 24C6.86824 24.0008 6.73778 23.9756 6.61594 23.9258C6.4941 23.876 6.38329 23.8027 6.28985 23.71C6.19612 23.6171 6.12172 23.5065 6.07096 23.3846C6.02019 23.2628 5.99405 23.132 5.99405 23C5.99405 22.868 6.02019 22.7373 6.07096 22.6155C6.12172 22.4936 6.19612 22.383 6.28985 22.29L14.4598 14.12C15.0216 13.5575 15.3372 12.7951 15.3372 12C15.3372 11.205 15.0216 10.4426 14.4598 9.88005L6.28985 1.71006C6.10154 1.52176 5.99576 1.26636 5.99576 1.00006C5.99576 0.733761 6.10154 0.478366 6.28985 0.290063C6.47815 0.101759 6.73355 -0.00402832 6.99985 -0.00402832C7.26615 -0.00402832 7.52154 0.101759 7.70985 0.290063L15.8798 8.46005C16.3455 8.92451 16.7149 9.47626 16.967 10.0837C17.219 10.6912 17.3488 11.3424 17.3488 12C17.3488 12.6577 17.219 13.3089 16.967 13.9164C16.7149 14.5238 16.3455 15.0756 15.8798 15.54L7.70985 23.71C7.61641 23.8027 7.50559 23.876 7.38375 23.9258C7.26192 23.9756 7.13145 24.0008 6.99985 24Z" fill="#374957"/></g><defs><clipPath id="clip0_403_3214"><rect width="24" height="24" fill="white"/></clipPath></defs></svg>
                    </button>
                    <div id="employees-pagination-numbers" class="pagination-numbers">
                    </div>
                </div>
            </section>

       <section class="properties-table">
            <div class="table-header">
                <div class="header-rightside">
                    <h6 class="table-header__title">Gerencias</h6>
                </div>
                <div class="header-leftside">
                    <form action="" method="get" class="searchbar-form" id="gerenciasSearchForm">
                        <input type="text" placeholder="Buscar gerencia" class="properties__searchbar__settings" id="gerenciasSearchInput">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="searchbar-icon">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.85593 3.61739C7.1902 2.72585 8.75888 2.25 10.3636 2.25C12.5154 2.25014 14.5791 3.105 16.1006 4.62655C17.6222 6.14811 18.4771 8.21174 18.4772 10.3635C18.4772 11.9683 18.0013 13.537 17.1098 14.8713C16.2183 16.2055 14.9511 17.2455 13.4685 17.8596C11.986 18.4737 10.3546 18.6344 8.78071 18.3213C7.20683 18.0082 5.76113 17.2355 4.62642 16.1008C3.49171 14.9661 2.71897 13.5204 2.86761 7.25866C3.48171 5.77609 4.52165 4.50892 5.85593 3.61739ZM10.3635 3.75C9.05552 3.75001 7.77687 4.13789 6.68928 4.86459C5.60168 5.5913 4.754 6.6242 4.25343 7.83268C3.75287 9.04116 3.6219 10.3709 3.87708 11.6538C4.13227 12.9368 4.76215 14.1152 5.68708 15.0401C6.61201 15.965 7.79044 16.5949 9.07335 16.8501C10.3563 17.1053 11.686 16.9743 12.8945 16.4738C14.103 15.9732 15.1359 15.1255 15.8626 14.0379C16.5893 12.9503 16.9772 11.6717 16.9772 10.3636" fill="#1E293B"/>
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M15.327 15.327C15.6199 15.0341 16.0948 15.0341 16.3877 15.327L21.5303 20.4697C21.8232 20.7626 21.8232 21.2374 21.5303 21.5303C21.2374 21.8232 20.7626 21.8232 20.4697 21.5303L15.327 16.3877C15.0341 16.0948 15.0341 15.6199 15.327 15.327Z" fill="#1E293B"/>
                        </svg>
                    </form>
                    <div class="buttons">
                        <button class="button button-mostrar" id="showAllGerenciasBtn" data-micromodal-trigger="showAll-modal__gerencias">Mostrar todo</button>
                    </div>
                </div>
            </div>
            <div>
                <div class="error-message" id="gerenciasErrorMessage" style="display:none;">
                    <p></p>
                </div>
            </div>
            <div class="table-settings__container">
                <table class="table-properties">
                    <thead class="head">
                        <tr class="head" id="gerenciasTableHeaderRow">
                            <th>ID</th>
                            <th>Gerencia</th>
                            <th>Encargado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="gerenciasTableBody">
                        <tr>
                            <td colspan="100%">Cargando gerencias...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="pagination-controls">
                <button id="prev-gerencias-page-btn" class="page-btn" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><g clip-path="url(#clip0_403_3101)"><path d="M17.1699 24C17.0383 24.0008 16.9078 23.9756 16.786 23.9258C16.6641 23.876 16.5533 23.8027 16.4599 23.71L8.28989 15.54C7.82426 15.0756 7.45483 14.5238 7.20277 13.9164C6.9507 13.3089 6.82095 12.6577 6.82095 12C6.82095 11.3424 6.9507 10.6912 7.20277 10.0837C7.45483 9.47626 7.82426 8.92451 8.28989 8.46005L16.4599 0.290063C16.5531 0.196825 16.6638 0.122864 16.7856 0.0724035C16.9075 0.0219432 17.038 -0.00402832 17.1699 -0.00402832C17.3018 -0.00402832 17.4323 0.0219432 17.5541 0.0724035C17.676 0.122864 17.7867 0.196825 17.8799 0.290063C17.9731 0.383301 18.0471 0.493991 18.0976 0.615813C18.148 0.737635 18.174 0.868203 18.174 1.00006C18.174 1.13192 18.148 1.26249 18.0976 1.38431C18.0471 1.50613 17.9731 1.61682 17.8799 1.71006L9.70989 9.88005C9.14809 10.4426 8.83253 11.205 8.83253 12C8.83253 12.7951 9.14809 13.5575 9.70989 14.12L17.8799 22.29C17.9736 22.383 18.048 22.4936 18.0988 22.6155C18.1496 22.7373 18.1757 22.868 18.1757 23C18.1757 23.132 18.1496 23.2628 18.0988 23.3846C18.048 23.5065 17.9736 23.6171 17.8799 23.71C17.7865 23.8027 17.6756 23.876 17.5538 23.9258C17.432 23.9756 17.3015 24.0008 17.1699 24Z" fill="#374957"/></g><defs><clipPath id="clip0_403_3101"><rect width="24" height="24" fill="white"/></clipPath></defs></svg>
                </button>
                <button id="next-gerencias-page-btn" class="page-btn" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><g clip-path="url(#clip0_403_3214)"><path d="M6.99985 24C6.86824 24.0008 6.73778 23.9756 6.61594 23.9258C6.4941 23.876 6.38329 23.8027 6.28985 23.71C6.19612 23.6171 6.12172 23.5065 6.07096 23.3846C6.02019 23.2628 5.99405 23.132 5.99405 23C5.99405 22.868 6.02019 22.7373 6.07096 22.6155C6.12172 22.4936 6.19612 22.383 6.28985 22.29L14.4598 14.12C15.0216 13.5575 15.3372 12.7951 15.3372 12C15.3372 11.205 15.0216 10.4426 14.4598 9.88005L6.28985 1.71006C6.10154 1.52176 5.99576 1.26636 5.99576 1.00006C5.99576 0.733761 6.10154 0.478366 6.28985 0.290063C6.47815 0.101759 6.73355 -0.00402832 6.99985 -0.00402832C7.26615 -0.00402832 7.52154 0.101759 7.70985 0.290063L15.8798 8.46005C16.3455 8.92451 16.7149 9.47626 16.967 10.0837C17.219 10.6912 17.3488 11.3424 17.3488 12C17.3488 12.6577 17.219 13.3089 16.967 13.9164C16.7149 14.5238 16.3455 15.0756 15.8798 15.54L7.70985 23.71C7.61641 23.8027 7.50559 23.876 7.38375 23.9258C7.26192 23.9756 7.13145 24.0008 6.99985 24Z" fill="#374957"/></g><defs><clipPath id="clip0_403_3214"><rect width="24" height="24" fill="white"/></clipPath></defs></svg>
                </button>
                <div id="gerencias-pagination-numbers" class="pagination-numbers"></div>
            </div>
        </section>

    </main>


    <?php
        include('./modals/show-all__sedes/show-all__sedes.php');
        include('./modals/show-all__employees/show-all__employees.php');
        include('./modals/show-all__gerencias/show-all__gerencias.php');
        include('./modals/sede-edition/sede-edition.php');
        include('./modals/employee-edition/employee-edition.php');
        include('./modals/gerencia-edition/gerencia-edition.php');
        include('./modals/add-sede/add-sede.php');
        include('./modals/add-employee/add-employee.php');
        include('./modals/add-gerencia/add-gerencia.php');
    ?>
    <!-- Scripts con rutas absolutas -->
    <script type="module" src="/systemaHidrofalcon/frontend/pages/principal/settings/settings.js"></script>
    <script src="/systemaHidrofalcon/frontend/node_modules/micromodal/dist/micromodal.min.js"></script>
    <script src="/systemaHidrofalcon/frontend/node_modules/izitoast/dist/js/iziToast.min.js"></script>
    <script src="/systemaHidrofalcon/frontend/pages/principal/settings/modals/show-all__sedes/show-all__sedes.js"></script>
    <script src="/systemaHidrofalcon/frontend/pages/principal/settings/modals/show-all__employees/show-all__employees.js"></script>
    <script src="/systemaHidrofalcon/frontend/pages/principal/settings/modals/show-all__gerencias/show-all__gerencias.js"></script>
    <script src="/systemaHidrofalcon/frontend/pages/principal/settings/modals/add-sede/add-sede.js"></script>
    <script src="/systemaHidrofalcon/frontend/pages/principal/settings/modals/add-employee/add-employee.js"></script>
    <script src="/systemaHidrofalcon/frontend/pages/principal/settings/modals/add-gerencia/add-gerencia.js"></script>
</body>
</html>