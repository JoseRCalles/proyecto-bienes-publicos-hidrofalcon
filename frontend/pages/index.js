// frontend/index.js (Tu script de la página de login)

document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const errorUsuarioDiv = document.querySelector('.err-username');
    const errorContrasenaDiv = document.querySelector('.err-password');

    const urlParams = new URLSearchParams(window.location.search);
    const registroExitoso = urlParams.get('registroExitoso');
    const passwordResetSuccess = urlParams.get('passwordResetSuccess');
    const sesionCerrada = urlParams.get('sesion_cerrada');

    // --- Lógica para mostrar iziToast según los parámetros URL ---

    if (localStorage.getItem('paginaVisitada') === null) {
        iziToast.success({
            title: '¡Bienvenido!',
            message: 'Es un placer tenerte aquí.',
            position: 'topRight',
            timeout: 5000,
            progressBar: true
        });
        localStorage.setItem('paginaVisitada', 'true');
    }

    if (registroExitoso === 'true') {
        iziToast.success({
            title: '¡Registro Exitoso!',
            message: 'Tu cuenta ha sido creada correctamente. ¡Ahora puedes iniciar sesión!',
            position: 'topRight',
            timeout: 5000,
            progressBar: true
        });
        window.history.replaceState(null, null, window.location.pathname);
    }

    if (passwordResetSuccess === 'true') {
        iziToast.success({
            title: '¡Éxito!',
            message: 'Tu contraseña ha sido restablecida correctamente. ¡Ya puedes iniciar sesión!',
            position: 'topRight',
            timeout: 5000,
            progressBar: true
        });
        window.history.replaceState(null, null, window.location.pathname);
    }

    if (sesionCerrada === 'true') {
        iziToast.info({
            title: 'Sesión Cerrada',
            message: 'Has cerrado sesión correctamente.',
            position: 'topRight',
            timeout: 3000,
            progressBar: true
        });
        window.history.replaceState(null, null, window.location.pathname);
    }

    // --- Manejo del Formulario de Login ---
    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            event.preventDefault();

            errorUsuarioDiv.textContent = '';
            errorContrasenaDiv.textContent = '';

            const formData = new FormData(loginForm);
            formData.append('action', 'login');

            // **** RUTA CORRECTA PARA FETCH A VALIDATE.PHP ****
            fetch("/systemaHidrofalcon/api/sesion", {
                method: 'POST',
                body: formData
            })
            .then(response => response.json()) // Siempre esperamos JSON de validate.php ahora
            .then(data => {
                if (data.success) {
                    // **** REDIRECCIÓN MANEJADA POR JAVASCRIPT ****
                    // La ruta a tu landing.php debe ser ABSOLUTA desde la raíz web
                    window.location.href = '/systemaHidrofalcon/principal?loginExitoso=true';
                } else {
                    // Errores de validación o credenciales incorrectas.
                    errorUsuarioDiv.textContent = data.errors.usuario || '';
                    errorContrasenaDiv.textContent = data.errors.contrasena || '';

                    // Opcional: Si quieres un iziToast para errores de login (además del texto en el div)
                    // if (data.errors.usuario || data.errors.contrasena) {
                    //     iziToast.error({
                    //         title: 'Error de Login',
                    //         message: data.errors.usuario || data.errors.contrasena,
                    //         position: 'bottomLeft',
                    //         timeout: 5000
                    //     });
                    // }
                }
            })
            .catch(error => {
                console.error('Error en la solicitud de login o procesamiento de JSON:', error);
                iziToast.error({
                    title: 'Error de Conexión',
                    message: 'No se pudo contactar al servidor. Inténtalo de nuevo.',
                    position: 'topRight',
                    timeout: 5000
                });
            });
        });
    }
});