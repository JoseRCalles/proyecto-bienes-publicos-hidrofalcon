document.addEventListener('DOMContentLoaded', () => {
    const verifyUsernameButton = document.getElementById('verify-username-button');
    const verifyAnswersButton = document.getElementById('verify-answers-button');
    const usernameDiv = document.querySelector('.username');
    const securityQuestionsDiv = document.querySelector('.security-questions');
    const usernameInput = document.getElementById('forgot-username');
    const usernameErrorBox = document.querySelector('.err-fusername');
    const question1Box = document.querySelector('.question1-box');
    const question2Box = document.querySelector('.question2-box');
    const preguntaId1 = document.getElementById('pregunta_id_1');
    const preguntaId2 = document.getElementById('pregunta_id_2');
    const respuesta1Input = document.getElementById('respuesta1');
    const respuesta2Input = document.getElementById('respuesta2');
    const respuesta1ErrorBox = document.querySelector('.error-r1');
    const respuesta2ErrorBox = document.querySelector('.error-r2');
    const respuestasErrorBox = document.querySelector('.err-boxes');

    // Verificar usuario
    async function verificarUsuario(username) {
        try {
            const response = await fetch('/systemaHidrofalcon/api/cambiar-contrasena', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `usuario=${encodeURIComponent(username)}&action=verifyUserCedula`
            });
            return await response.json();
        } catch (error) {
            console.error('Error:', error);
            return { error: 'Error de conexión.' };
        }
    }

    // Obtener preguntas de seguridad
    async function obtenerPreguntasSeguridad() {
        try {
            const response = await fetch('/systemaHidrofalcon/api/cambiar-contrasena', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=getSecurityQuestions`
            });
            return await response.json();
        } catch (error) {
            console.error('Error:', error);
            return { error: 'Error de conexión.' };
        }
    }

    // Verificar respuestas de seguridad
    async function verificarRespuestasSeguridad(respuesta1, respuesta2, idPregunta1, idPregunta2) {
        try {
            const response = await fetch('/systemaHidrofalcon/api/cambiar-contrasena', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `respuesta1=${encodeURIComponent(respuesta1)}&respuesta2=${encodeURIComponent(respuesta2)}&id_pregunta1=${idPregunta1}&id_pregunta2=${idPregunta2}&action=verifySecurityAnswers`
            });
            return await response.json();
        } catch (error) {
            console.error('Error:', error);
            return { error: 'Error de conexión.' };
        }
    }

    // Evento: Verificar usuario
    verifyUsernameButton.addEventListener('click', async () => {
        const username = usernameInput.value.trim();
        if (!username) {
            usernameErrorBox.textContent = 'Ingrese su usuario.';
            return;
        }

        const usuarioData = await verificarUsuario(username);
        if (usuarioData.error) {
            usernameErrorBox.textContent = usuarioData.error;
            return;
        }

        if (usuarioData.response) {
            usernameDiv.style.display = 'none';
            usernameErrorBox.textContent = '';

            const preguntasData = await obtenerPreguntasSeguridad();
            if (preguntasData.error || !Array.isArray(preguntasData) || preguntasData.length < 2) {
                usernameErrorBox.textContent = preguntasData.error || 'Error al cargar preguntas.';
                return;
            }

            securityQuestionsDiv.style.display = 'block';
            question1Box.textContent = preguntasData[0].pregunta;
            preguntaId1.value = preguntasData[0].id;
            question2Box.textContent = preguntasData[1].pregunta;
            preguntaId2.value = preguntasData[1].id;
        } else {
            usernameErrorBox.textContent = usuarioData.error || 'Usuario no encontrado.';
        }
    });

    // Evento: Verificar respuestas
    verifyAnswersButton.addEventListener('click', async () => {
        const respuesta1 = respuesta1Input.value.trim();
        const respuesta2 = respuesta2Input.value.trim();
        const idPregunta1 = preguntaId1.value;
        const idPregunta2 = preguntaId2.value;

        if (!respuesta1 || !respuesta2) {
            respuesta1ErrorBox.textContent = !respuesta1 ? 'Respuesta requerida.' : '';
            respuesta2ErrorBox.textContent = !respuesta2 ? 'Respuesta requerida.' : '';
            return;
        }

        const respuestasData = await verificarRespuestasSeguridad(respuesta1, respuesta2, idPregunta1, idPregunta2);
        if (respuestasData.error) {
            respuestasErrorBox.textContent = respuestasData.error;
            return;
        }

        if (respuestasData.response) {
            window.location.href = '/systemaHidrofalcon/nueva-contrasena';
        } else {
            respuestasErrorBox.textContent = 'Respuestas incorrectas.';
        }
    });
});
