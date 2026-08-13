document.addEventListener('DOMContentLoaded', function() {
    const verifyUsernameButton = document.getElementById('verify-username-button');
    const usernameDiv = document.querySelector('.username');
    const securityQuestionsDiv = document.querySelector('.security-questions');
    const usernameInput = document.getElementById('forgot-username');
    const usernameErrorBox = document.querySelector('.err-fusername');
    const question1Box = document.querySelector('.question1-box');
    const question2Box = document.querySelector('.question2-box');
    const preguntaId1 = document.getElementById('pregunta_id_1');
    const preguntaId2 = document.getElementById('pregunta_id_2');

    verifyUsernameButton.addEventListener('click', function() {
        const username = usernameInput.value.trim();

        if (username === '') {
            usernameErrorBox.textContent = 'Por favor, ingrese su nombre de usuario.';
            return;
        }

        fetch('./verify/username.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `username=${username}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                usernameDiv.style.display = 'none';
                securityQuestionsDiv.style.display = 'block';
                question1Box.textContent = data.pregunta1;
                question2Box.textContent = data.pregunta2;
                preguntaId1.value = data.id_pregunta1;
                preguntaId2.value = data.id_pregunta2;
                usernameErrorBox.textContent = ''; // Limpiar mensaje de error
            } else {
                usernameErrorBox.textContent = data.error;
            }
        })
        .catch(error => {
            console.error('Error al verificar el usuario:', error);
            usernameErrorBox.textContent = 'Ocurrió un error al verificar el usuario.';
        });
    });
});