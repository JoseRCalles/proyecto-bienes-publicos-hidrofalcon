document.addEventListener('DOMContentLoaded', function() {
    const personalInfoContainer = document.getElementById('personalInfo');
    const confirmPasswordSection = document.getElementById('confirmPasswordSection');
    const securityQuestionsContainer = document.querySelectorAll('.select-inputs');
    const buttonContainer = document.querySelector('.button-container');
    const continueButton = document.querySelector('.continue-container .continue');
    const goBackButton = document.querySelector('.goback-container .goback');
    const registerButton = document.querySelector('.submit-container .submit');
    const form = document.querySelector('.enter-data');
    const usuarioInput = document.getElementById('usuario');
    const errorUsuario = document.querySelector('.error-usuario');
    const contrasenaInput = document.getElementById('contrasena');
    const errorContrasena = document.querySelector('.error-contrasena');
    const cedulaInput = document.getElementById('cedula');
    const errorCedula = document.querySelector('.error-cedula');
    const gerenciaSelect = document.getElementById('gerencia');
    const cargoSelect = document.getElementById('cargo');
    const pregunta1Select = document.getElementById('p1');
    const pregunta2Select = document.getElementById('p2');

    function populateSelect(selectElement, data, valueKey, textKey) {
        while (selectElement.options.length > 1) {
            selectElement.remove(1);
        }
        data.forEach(item => {
            const option = document.createElement('option');
            option.value = item[valueKey];
            option.textContent = item[textKey];
            selectElement.appendChild(option);
        });
    }

    async function fetchDataAndPopulateForm() {
        try {
            const bodyData = new URLSearchParams();
            bodyData.append('action', 'getFormData');

            const response = await fetch('/systemaHidrofalcon/api/registrar-usuario', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: bodyData.toString(),
            });
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            
            if (data.success) {
                populateSelect(gerenciaSelect, data.gerencias, 'id', 'nombre_gerencia');
                populateSelect(cargoSelect, data.cargos, 'id', 'nombre_cargo');
                populateSelect(pregunta1Select, data.preguntas, 'id', 'pregunta');
                populateSelect(pregunta2Select, data.preguntas, 'id', 'pregunta');
            } else {
                console.error('Error desde el servidor:', data.message);
            }
        } catch (error) {
            console.error('Error al obtener datos del formulario:', error);
        }
    }
    
    fetchDataAndPopulateForm();

    form.addEventListener('keypress', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
        }
    });

    async function checkUserExistence(type, value) {
        if (value.trim() === '') {
            return { exists: false, error: `Por favor, ingresa el ${type === 'usuario' ? 'usuario' : 'número de cédula'}.` };
        }
        try {
            const bodyData = new URLSearchParams();
            bodyData.append(type, value);
            bodyData.append('action', 'checkUserOrCedula');

            const response = await fetch('/systemaHidrofalcon/api/registrar-usuario', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: bodyData.toString(),
            });

            const data = await response.json();
            return data;
        } catch (error) {
            console.error(`Error al verificar ${type}:`, error);
            return { exists: true, error: `Error al verificar el ${type === 'usuario' ? 'usuario' : 'número de cédula'}.` };
        }
    }

    async function checkUsernameAvailability(username) {
        const result = await checkUserExistence('usuario', username);
        if (result.exists) {
            errorUsuario.textContent = 'Este nombre de usuario ya está en uso.';
            return false;
        } else if (result.error) {
            errorUsuario.textContent = result.error;
            return false;
        } else {
            errorUsuario.textContent = '';
            return true;
        }
    }

    async function checkCedulaUniqueness(cedula) {
        const result = await checkUserExistence('cedula', cedula);
        if (result.exists) {
            errorCedula.textContent = 'Este número de cédula ya está registrado.';
            return false;
        } else if (result.error) {
            errorCedula.textContent = result.error;
            return false;
        } else {
            errorCedula.textContent = '';
            return true;
        }
    }

    // --- NUEVOS LISTENERS PARA VALIDACIÓN EN TIEMPO REAL ---
    usuarioInput.addEventListener('keyup', () => {
        checkUsernameAvailability(usuarioInput.value);
    });

    cedulaInput.addEventListener('keyup', () => {
        checkCedulaUniqueness(cedulaInput.value);
    });

    // Validar el formulario completo al hacer clic en el botón "Continuar"
    async function validatePersonalInfoAndShowSecurity(event) {
        event.preventDefault();
        let isValidPersonalInfo = true;
        
        // Ejecuta todas las validaciones asincrónicas en paralelo
        const isUserAvailable = await checkUsernameAvailability(usuarioInput.value.trim());
        const isCedulaUnique = await checkCedulaUniqueness(cedulaInput.value.trim());
        
        // Ahora, chequea el resto de los campos síncronos
        const nombres = document.getElementById('nombres');
        const apellidos = document.getElementById('apellidos');
        const password = contrasenaInput.value;
        const confirmarContrasena = document.getElementById('confirmar_contrasena');
        const gerencia = gerenciaSelect;
        const cargo = cargoSelect;
        const errorNombres = document.querySelector('.error-nombres');
        const errorApellidos = document.querySelector('.error-apellidos');
        const errorConfirmarContrasena = document.querySelector('.error-confirmar_contrasena');
        const errorDepartamento = document.querySelector('.error-gerencia');
        const errorCargo = document.querySelector('.error-cargo');
        
        if (nombres.value.trim() === '') {
            errorNombres.textContent = 'Por favor, ingresa tus nombres.';
            isValidPersonalInfo = false;
        } else {
            errorNombres.textContent = '';
        }

        if (apellidos.value.trim() === '') {
            errorApellidos.textContent = 'Por favor, ingresa tus apellidos.';
            isValidPersonalInfo = false;
        } else {
            errorApellidos.textContent = '';
        }
        
        if (password === '') {
            errorContrasena.textContent = 'Por favor, ingresa una contraseña.';
            isValidPersonalInfo = false;
        } else if (password.length < 6) {
            errorContrasena.textContent = 'La contraseña debe tener al menos 6 caracteres.';
            isValidPersonalInfo = false;
        } else {
            errorContrasena.textContent = '';
        }
        
        if (confirmarContrasena.value === '') {
            errorConfirmarContrasena.textContent = 'Por favor, confirma tu contraseña.';
            isValidPersonalInfo = false;
        } else if (confirmarContrasena.value !== password) {
            errorConfirmarContrasena.textContent = 'Las contraseñas no coinciden.';
            isValidPersonalInfo = false;
        } else {
            errorConfirmarContrasena.textContent = '';
        }
        
        if (gerencia.value.trim() === '') {
            errorDepartamento.textContent = 'Por favor, ingresa el departamento.';
            isValidPersonalInfo = false;
        } else {
            errorDepartamento.textContent = '';
        }

        if (cargo.value.trim() === '') {
            errorCargo.textContent = 'Por favor, ingresa el cargo.';
            isValidPersonalInfo = false;
        } else {
            errorCargo.textContent = '';
        }
        
        // El formulario es válido solo si todas las validaciones (síncronas y asíncronas) pasaron
        if (isValidPersonalInfo && isUserAvailable && isCedulaUnique) {
            personalInfoContainer.style.display = 'none';
            confirmPasswordSection.style.display = 'none';
            securityQuestionsContainer.forEach(container => container.style.display = 'flex');
            buttonContainer.style.display = 'flex';
        } else {
            console.log('Error en la información personal. Por favor, revisa los campos.');
        }
    }

    async function validateFinalForm(event) {
    event.preventDefault(); 
    
    const respuesta1 = document.getElementById('r1').value.trim();
    const respuesta2 = document.getElementById('r2').value.trim();
    const pregunta1 = document.getElementById('p1').value.trim();
    const pregunta2 = document.getElementById('p2').value.trim();

    const errorRespuesta1 = document.querySelector('.error-r1');
    const errorRespuesta2 = document.querySelector('.error-r2');
    const errorPregunta1 = document.querySelector('.error-p1');
    const errorPregunta2 = document.querySelector('.error-p2');

    let isValidFinalForm = true;
    
    // --- Validaciones de preguntas de seguridad ---
    if (pregunta1 === '') {
        errorPregunta1.textContent = 'Por favor, selecciona una pregunta.';
        isValidFinalForm = false;
    } else {
        errorPregunta1.textContent = '';
    }
    
    if (pregunta2 === '') {
        errorPregunta2.textContent = 'Por favor, selecciona una pregunta.';
        isValidFinalForm = false;
    } else {
        errorPregunta2.textContent = '';
    }

    if (pregunta1 === pregunta2 && pregunta1 !== '') {
        errorPregunta1.textContent = 'Las preguntas no pueden ser iguales.';
        isValidFinalForm = false;
    } else if (isValidFinalForm) { // Limpia el error si ya no aplica
        errorPregunta1.textContent = '';
    }

    // --- Validaciones de respuestas de seguridad ---
    if (respuesta1.length > 8) {
        errorRespuesta1.textContent = 'La respuesta no debe tener más de 8 caracteres.';
        isValidFinalForm = false;
    } else if (respuesta1.length < 3) {
        errorRespuesta1.textContent = 'La respuesta debe ser mayor a 3 caracteres.';
        isValidFinalForm = false;
    } else if (!/^[a-z0-9]+$/.test(respuesta1)) {
        errorRespuesta1.textContent = 'La respuesta solo puede contener letras minúsculas y números.';
        isValidFinalForm = false;
    } else {
        errorRespuesta1.textContent = '';
    }

    if (respuesta2.length > 8) {
        errorRespuesta2.textContent = 'La respuesta no debe tener más de 8 caracteres.';
        isValidFinalForm = false;
    } else if (respuesta2.length < 3) {
        errorRespuesta2.textContent = 'La respuesta debe ser mayor a 3 caracteres.';
        isValidFinalForm = false;
    } else if (!/^[a-z0-9]+$/.test(respuesta2)) {
        errorRespuesta2.textContent = 'La respuesta solo puede contener letras minúsculas y números.';
        isValidFinalForm = false;
    } else {
        errorRespuesta2.textContent = '';
    }
    
    // Si la validación local es exitosa, envía el formulario
    if (isValidFinalForm) {
        const formData = new FormData(form);
        formData.append('action', 'registerUser');
        try {
            const response = await fetch('/systemaHidrofalcon/api/registrar-usuario', {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    iziToast.success({
                        title: 'Éxito',
                        message: data.message,
                        position: 'topRight'
                    });
                    
                    // Redirige al login después de un breve delay
                    setTimeout(() => {
                        window.location.href = '/systemaHidrofalcon/login?registroExitoso=true';
                    }, 1000);

                } else {
                    iziToast.error({
                        title: 'Error en el Registro',
                        message: data.message,
                        position: 'topRight'
                    });
                    console.error('Error en el registro:', data);
                }
            } else {
                iziToast.error({
                    title: 'Error de Conexión',
                    message: 'Error en la comunicación con el servidor. Por favor, inténtalo de nuevo.',
                    position: 'topRight'
                });
                console.error('Error en la respuesta del servidor:', response.status);
            }
        } catch (error) {
            iziToast.error({
                title: 'Error',
                message: 'Ocurrió un error al enviar el formulario. Por favor, inténtalo de nuevo.',
                position: 'topRight'
            });
            console.error('Error al enviar el formulario:', error);
        }
    } else {
        console.log('El formulario final tiene errores. Por favor, revisa las respuestas de seguridad.');
    }
}

    function showPersonalInfoSection() {
        securityQuestionsContainer.forEach(container => container.style.display = 'none');
        buttonContainer.style.display = 'none';
        personalInfoContainer.style.display = 'flex';
        confirmPasswordSection.style.display = 'flex';
    }

    function disableSelectedOptions() {
        const select1 = document.getElementById('p1');
        const select2 = document.getElementById('p2');

        const updateOptions = (currentSelect, otherSelect) => {
            for (let option of otherSelect.options) {
                option.disabled = false;
                if (currentSelect.value && option.value === currentSelect.value) {
                    option.disabled = true;
                }
            }
        };

        if (select1 && select2) {
            updateOptions(select1, select2);
            updateOptions(select2, select1);
        }
    }

    continueButton.addEventListener('click', validatePersonalInfoAndShowSecurity);
    goBackButton.addEventListener('click', showPersonalInfoSection);
    registerButton.addEventListener('click', validateFinalForm);

    const pregunta1 = document.getElementById('p1');
    const pregunta2 = document.getElementById('p2');
    if (pregunta1 && pregunta2) {
        pregunta1.addEventListener('change', disableSelectedOptions);
        pregunta2.addEventListener('change', disableSelectedOptions);
    }
});