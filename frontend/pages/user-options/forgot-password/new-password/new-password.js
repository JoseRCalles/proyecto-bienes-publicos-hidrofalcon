// new-password.js

const list = document.querySelectorAll('.information-item');
const password = document.getElementById('password');
const confirmPassword = document.getElementById('confirm-password');
const changeButton = document.getElementById('change-button');
const errorPassword = document.querySelector('.err-password');
const errorConfirmPassword = document.querySelector('.err-cpassword');

let hasAttemptedSubmit = false;

const values = [
    {
        regex: /.{8,}/, index: 0
    },
    {
        regex: /[0-9]/, index: 1
    },
    {
        regex: /[a-z]/, index: 2
    },
    {
        regex: /[^A-Za-z0-9]/, index: 3
    },
    {
        regex: /[A-Z]/, index: 4
    }
];

function updatePasswordRequirementsList() {
    const passwordValue = password.value;
    values.forEach(value => {
        const isValid = value.regex.test(passwordValue);
        const requirementsItem = list[value.index];

        if (isValid) {
            requirementsItem.classList.remove('unactive');
            requirementsItem.classList.add('active');
        } else {
            requirementsItem.classList.remove('active');
            requirementsItem.classList.add('unactive');
        }
    });
}

function displayFieldErrors() {
    const passwordValue = password.value;
    const confirmPasswordValue = confirmPassword.value;

    const passwordRequirementsMet = values.every(value => value.regex.test(passwordValue));
    const passwordsMatch = passwordValue === confirmPasswordValue;

    if (!passwordRequirementsMet && passwordValue.length > 0) {
        errorPassword.textContent = 'La contraseña no cumple todos los requisitos.';
        errorPassword.style.display = 'block';
    } else {
        errorPassword.textContent = '';
        errorPassword.style.display = 'none';
    }

    if (!passwordsMatch && (passwordValue.length > 0 || confirmPasswordValue.length > 0)) {
        errorConfirmPassword.textContent = 'Las contraseñas no coinciden.';
        errorConfirmPassword.style.display = 'block';
        confirmPassword.style.borderColor = 'red';
    } else {
        errorConfirmPassword.textContent = '';
        errorConfirmPassword.style.display = 'none';
        confirmPassword.style.borderColor = '';
    }
}

function validateForm() {
    updatePasswordRequirementsList();
    const passwordValue = password.value;
    const confirmPasswordValue = confirmPassword.value;

    const passwordRequirementsMet = values.every(value => value.regex.test(passwordValue));
    const passwordsMatch = passwordValue === confirmPasswordValue;

    if (passwordRequirementsMet && passwordsMatch && passwordValue.length > 0) {
        changeButton.disabled = false;
    } else {
        changeButton.disabled = true;
    }

    if (hasAttemptedSubmit) {
        displayFieldErrors();
    }
}

password.addEventListener('keyup', validateForm);
confirmPassword.addEventListener('keyup', validateForm);

validateForm();

changeButton.addEventListener('click', e => {
    e.preventDefault();
    hasAttemptedSubmit = true;
    validateForm();

    if (!changeButton.disabled) {
        const formData = new FormData();
        formData.append('password', password.value);
        formData.append('confirm-password', confirmPassword.value);
        formData.append('action', 'changePassword');

        fetch('/systemaHidrofalcon/api/cambiar-contrasena', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                iziToast.success({
                    title: 'Éxito',
                    message: data.message,
                    position: 'topRight',
                    onClosing: function () {
                        window.location.href = '/systemaHidrofalcon/login?passwordResetSuccess=true';
                    }
                });

                password.value = '';
                confirmPassword.value = '';
                list.forEach(item => {
                    item.classList.remove('active');
                    item.classList.add('unactive');
                });
                errorPassword.textContent = '';
                errorPassword.style.display = 'none';
                errorConfirmPassword.textContent = '';
                errorConfirmPassword.style.display = 'none';
                changeButton.disabled = true;
                hasAttemptedSubmit = false;

            } else {
                // Siempre muestra la alerta con iziToast
                iziToast.error({
                    title: 'Error',
                    message: data.message,
                    position: 'topRight'
                });

                // Lógica de visualización de errores específicos de campo
                // Reiniciar los mensajes de error para evitar duplicados
                errorPassword.textContent = '';
                errorPassword.style.display = 'none';
                errorConfirmPassword.textContent = '';
                errorConfirmPassword.style.display = 'none';

                if (data.message === 'Las contraseñas no coinciden.') {
                    errorConfirmPassword.textContent = data.message;
                    errorConfirmPassword.style.display = 'block';
                } else if (data.message === 'La nueva contraseña no puede ser la misma que la actual.') {
                    errorPassword.textContent = data.message;
                    errorPassword.style.display = 'block';
                } else {
                    // Fallback para cualquier otro mensaje de error
                    // Aquí se puede manejar si el mensaje no coincide con los esperados
                    // (por ejemplo, "Ambos campos de contraseña son obligatorios.")
                    // Se pueden mostrar en el campo de contraseña principal
                    errorPassword.textContent = data.message;
                    errorPassword.style.display = 'block';
                }
            }
        })
        .catch(error => {
            console.error('Error en la solicitud Fetch:', error);
            iziToast.error({
                title: 'Error',
                message: 'No se pudo conectar con el servidor. Por favor, revisa tu conexión a internet.',
                position: 'topRight'
            });
        });
    }
});