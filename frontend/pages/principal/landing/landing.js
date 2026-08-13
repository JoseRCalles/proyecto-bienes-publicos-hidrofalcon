// frontend/pages/principal/landing/landing.js

document.addEventListener('DOMContentLoaded', () => {
    // --- Lógica para mostrar la alerta de "Inicio de Sesión Exitoso" ---
    const urlParams = new URLSearchParams(window.location.search);
    const loginExitoso = urlParams.get('loginExitoso');
    const logoutButton = document.getElementById('btnCerrarSesion');

    if (loginExitoso === 'true') {
        iziToast.success({
            title: '¡Inicio de Sesión Exitoso!',
            message: 'Bienvenido de nuevo a tu panel de control.',
            position: 'topRight',
            timeout: 4000,
            progressBar: true
        });
        window.history.replaceState(null, null, window.location.pathname);
    }

    logoutButton.addEventListener('click', () => {
        window.location.href = '/systemaHidrofalcon/api/sesion?action=logout';
    });

    // --- Lógica del botón de Simular Cerrar Sesión ---
    // Cualquier otra lógica JS específica de tu landing.php iría aquí.
});