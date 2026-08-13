document.addEventListener('DOMContentLoaded', function() {

    const addEmployeeForm = document.getElementById('addEmployeeForm');
    const addEmployeeCedulaInput = document.getElementById('addEmployeeCedula');
    const addEmployeeNombresInput = document.getElementById('addEmployeeNombres');
    const addEmployeeApellidosInput = document.getElementById('addEmployeeApellidos');
    const addEmployeeTelefonoInput = document.getElementById('addEmployeeTelefono');
    const addEmployeeGerenciaSelect = document.getElementById('addEmployeeGerencia');
    const addEmployeeCargoSelect = document.getElementById('addEmployeeCargo'); // Nuevo campo

    function displayError(fieldElement, message) {
        if (!fieldElement) {
            console.error("Attempted to display error for a null element:", message);
            return;
        }
        const errorBox = fieldElement.parentElement.querySelector('.err-box');
        if (errorBox) {
            errorBox.textContent = message;
            errorBox.classList.add('show-error');
        }
    }

    function clearErrors(fieldElement = null) {
        if (fieldElement) {
            const errorBox = fieldElement.parentElement.querySelector('.err-box');
            if (errorBox) {
                errorBox.textContent = '';
                errorBox.classList.remove('show-error');
            }
        } else {
            document.querySelectorAll('.err-box').forEach(box => {
                box.textContent = '';
                box.classList.remove('show-error');
            });
        }
    }

    // Función para obtener y llenar las gerencias
    function fetchGerencias() {
        const gerenciaUrl = '/systemahidrofalcon/api/gerencias?action=gerenciasSolo';

        fetch(gerenciaUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    addEmployeeGerenciaSelect.innerHTML = '<option value="">Seleccione una Gerencia</option>';
                    data.gerencias.forEach(gerencia => {
                        const option = document.createElement('option');
                        option.value = gerencia.id;
                        option.textContent = gerencia.nombre_gerencia;
                        addEmployeeGerenciaSelect.appendChild(option);
                    });
                } else {
                    throw new Error(data.error || 'Error al cargar las gerencias.');
                }
            })
            .catch(error => {
                console.error('Error al cargar las gerencias:', error);
                iziToast.error({
                    title: 'Error',
                    message: 'No se pudieron cargar las gerencias.',
                    position: 'topRight'
                });
            });
    }

    // Nueva función para obtener y llenar los cargos
    function fetchCargos() {
        const cargosUrl = '/systemaHidrofalcon/api/empleados?action=getCargos';
        fetch(cargosUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(result => {
                if (result.success && Array.isArray(result.data)) {
                    addEmployeeCargoSelect.innerHTML = '<option value="">Seleccione un Cargo</option>';
                    result.data.forEach(cargo => {
                        const option = document.createElement('option');
                        option.value = cargo.id;
                        option.textContent = cargo.nombre_cargo;
                        addEmployeeCargoSelect.appendChild(option);
                    });
                } else {
                    throw new Error('La respuesta del servidor no contiene los datos esperados.');
                }
            })
            .catch(error => {
                console.error('Error al cargar los cargos:', error);
                iziToast.error({
                    title: 'Error',
                    message: 'No se pudieron cargar los cargos.',
                    position: 'topRight'
                });
            });
    }



    // Llamar a ambas funciones al cargar la página
    fetchGerencias();
    fetchCargos();

    addEmployeeForm.addEventListener('submit', function(event) {
        event.preventDefault();

        const cedula = addEmployeeCedulaInput.value.trim();
        const nombres = addEmployeeNombresInput.value.trim();
        const apellidos = addEmployeeApellidosInput.value.trim();
        const telefono = addEmployeeTelefonoInput.value.trim();
        const gerencia_id = addEmployeeGerenciaSelect.value;
        const cargo_id = addEmployeeCargoSelect.value; // El valor del select es el ID

        clearErrors();

        let isValid = true;
        if (!cedula) {
            displayError(addEmployeeCedulaInput, 'La cédula es obligatoria.');
            isValid = false;
        }
        if (!nombres) {
            displayError(addEmployeeNombresInput, 'El nombre es obligatorio.');
            isValid = false;
        }
        if (!apellidos) {
            displayError(addEmployeeApellidosInput, 'El apellido es obligatorio.');
            isValid = false;
        }
        if (!telefono) {
            displayError(addEmployeeTelefonoInput, 'El teléfono es obligatorio.');
            isValid = false;
        }
        if (!gerencia_id) {
            displayError(addEmployeeGerenciaSelect, 'Debe seleccionar una gerencia.');
            isValid = false;
        }
        if (!cargo_id) { // Validación para el nuevo campo
            displayError(addEmployeeCargoSelect, 'Debe seleccionar un cargo.');
            isValid = false;
        }

        if (!isValid) {
            return;
        }

        const addEmployeeUrl = '/systemahidrofalcon/api/empleados?action=addEmployee';
        const postData = {
            cedula: cedula,
            nombres: nombres,
            apellidos: apellidos,
            telefono: telefono,
            gerencia_id: gerencia_id,
            cargo_id: cargo_id, // Enviamos el ID del cargo
        };

        fetch(addEmployeeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(postData)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                iziToast.success({
                    title: 'Éxito',
                    message: result.message,
                    position: 'topRight'
                });
                if (typeof MicroModal !== 'undefined') {
                    MicroModal.close('add-employee-modal');
                }
                addEmployeeForm.reset();
            } else {
                if (result.message.includes('cédula')) {
                    displayError(addEmployeeCedulaInput, result.message);
                } else if (result.message.includes('nombre y apellido')) {
                    displayError(addEmployeeNombresInput, result.message);
                    clearErrors(addEmployeeApellidosInput);
                } else {
                    iziToast.error({
                        title: 'Error',
                        message: result.message,
                        position: 'topRight'
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error en la solicitud:', error);
            iziToast.error({
                title: 'Error',
                message: 'Hubo un problema con la solicitud.',
                position: 'topRight'
            });
        });
    });
});