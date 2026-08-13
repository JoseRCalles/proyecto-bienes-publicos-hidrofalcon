<?php
// backend/helpers/get_sidebar_path.php

// NO LLAMES session_start() AQUÍ. Ya debe haber sido llamado en donotAutorize.php
// que es incluido antes en landing.php.

// Asegúrate de que $_SESSION['rango_id'] esté definido y sea un número.
// Si este archivo se llama directamente sin una sesión activa, podría dar error.
// Sin embargo, como se va a incluir DESPUÉS de donotAutorize.php, $_SESSION['rango_id']
// debería estar disponible si el usuario está logueado.
$user_rango_id = isset($_SESSION['rango_id']) ? (int)$_SESSION['rango_id'] : 0;

// Definir la ruta base para los archivos de sidebar en el sistema de archivos del servidor.
// $_SERVER['DOCUMENT_ROOT'] es la ruta a la raíz de tu servidor web (ej. C:/xampp/htdocs/).
$sidebar_base_server_path = $_SERVER['DOCUMENT_ROOT'] . '/systemahidrofalcon/frontend/pages/shared/sidebar-ranges/';

$sidebar_file_name = ''; // Para almacenar solo el nombre del archivo

switch ($user_rango_id) {
    case 0: // Asumamos que 1 es para Administrador
        $sidebar_file_name = 'admin.php';
        break;
    case 1: // Asumamos que 2 es para Usuario Regular
        $sidebar_file_name = 'member.php';
        break;
    default:
        // Sidebar por defecto o si el rango no es reconocido
        $sidebar_file_name = 'user.php'; // Asegúrate de crear este archivo si lo usas
        break;
}

$full_sidebar_server_path = $sidebar_base_server_path . $sidebar_file_name;

// Verificación de seguridad: Asegurarse de que el archivo existe antes de devolver la ruta
if (!file_exists($full_sidebar_server_path)) {
    error_log("Sidebar file not found: " . $full_sidebar_server_path . " for rango_id: " . $user_rango_id);
    // Puedes tener un sidebar de fallback para errores o usuarios sin rango específico
    $fallback_sidebar_path = $sidebar_base_server_path . 'sidebar_error.php';
    if (file_exists($fallback_sidebar_path)) {
        return $fallback_sidebar_path;
    }
    // Si no se encuentra ni el principal ni el de fallback, retorna null
    return null;
}

return $full_sidebar_server_path; // ¡Esto es lo que devuelve este archivo!
?>