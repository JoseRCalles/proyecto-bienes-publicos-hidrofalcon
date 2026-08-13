// relation.js

import showHide from '../shared/js/modalshow.js'; // Assuming this utility is still shared

// --- GLOBAL DOM Elements (declared as `let` for dynamic assignment) ---
// These will be assigned inside assignModalDOMelements when the modal opens
let assetForm;
let submitButton;

// Form Part Containers
let frstPartContainer;
let scndPartContainer;
let nextButton;
let goBackButton;
let estatusNewSelect; // This is for the relation form's specific status select

let modalCloseButton; // For the relation modal's close button

// --- Utility Functions ---
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

function clearErrors() {
    document.querySelectorAll('.err-box').forEach(box => {
        box.textContent = '';
        box.classList.remove('show-error');
    });
}

function toggleVisibility(element, show = true) {
    if (!element) return;
    if (show) {
        element.classList.add('show');
        element.classList.remove('hidden');
    } else {
        element.classList.remove('show');
        element.classList.add('hidden');
    }
}

// --- Assign DOM elements dynamically when the modal is opened ---
function assignModalDOMelements() {
    assetForm = document.getElementById('asset-form');
    submitButton = assetForm ? assetForm.querySelector('#submit-button__relation') : null;

    frstPartContainer = assetForm ? assetForm.querySelector('.frstpart-container') : null;
    scndPartContainer = assetForm ? assetForm.querySelector('.scndpart-container') : null;
    nextButton = assetForm ? assetForm.querySelector('.next-button') : null;
    goBackButton = assetForm ? assetForm.querySelector('.goback-button') : null;
    estatusNewSelect = document.getElementById('estatus_new__relation');

    modalCloseButton = document.querySelector('#relation-modal .modal__close');

    if (!assetForm || !frstPartContainer || !scndPartContainer || !estatusNewSelect) {
        console.error('ERROR: No se encontraron todos los elementos DOM críticos después de assignModalDOMelements() para el modal de relación. Verifique los IDs en el HTML.');
    }
}

// --- Data Loading Functions ---
function populateStatusOptions() {
    if (!estatusNewSelect) {
        console.error('Error: estatusNewSelect is null. Cannot load estatus options.');
        return;
    }
    const fetchUrl = '/systemahidrofalcon/api/activo?action=getEstatusData';
    fetch(fetchUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error('Error de red o servidor: ' + response.status + ' ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                estatusNewSelect.innerHTML = '<option value="">Seleccione un estatus</option>';
                data.data.forEach(estatus => {
                    const option = document.createElement('option');
                    option.value = estatus.id;
                    option.textContent = estatus.nombre;
                    estatusNewSelect.appendChild(option);
                });
            } else {
                console.error('Error al cargar estatus desde la API:', data.message);
                estatusNewSelect.innerHTML = '<option value="">Error al cargar estatus (API)</option>';
            }
        })
        .catch(error => {
            console.error('Hubo un problema con la operación de fetch de estatus:', error);
            estatusNewSelect.innerHTML = '<option value="">Error de red al cargar estatus</option>';
        });
}

async function populateSedeAdmSelect() {
    const sedeAdmSelect = document.getElementById('sede_adm__relation');
    const errSedeAdmBox = document.querySelector('.err-sede_adm');

    if (!sedeAdmSelect) {
        console.error('Error: Select element with ID "sede_adm__relation" not found.');
        return;
    }

    sedeAdmSelect.innerHTML = '<option value="">Cargando sedes...</option>';
    sedeAdmSelect.disabled = true;
    if (errSedeAdmBox) errSedeAdmBox.textContent = '';

    try {
        const fetchUrl = '/systemahidrofalcon/api/sede?action=getSedesData';
        console.log('Fetching Sede Administrativa from URL:', fetchUrl);

        const response = await fetch(fetchUrl);

        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`HTTP error! Status: ${response.status}. Response: ${errorText}`);
        }

        const data = await response.json();
        console.log('Sede Administrativa data received:', data);

        if (data.success && Array.isArray(data.sedes)) {
            sedeAdmSelect.innerHTML = '<option value="">Seleccione una sede</option>';
            if (data.sedes.length === 0) {
                const noOptionsOption = document.createElement('option');
                noOptionsOption.value = "";
                noOptionsOption.textContent = "No hay sedes disponibles";
                noOptionsOption.disabled = true;
                sedeAdmSelect.appendChild(noOptionsOption);
                if (errSedeAdmBox) errSedeAdmBox.textContent = 'No se encontraron sedes administrativas.';
            } else {
                data.sedes.forEach(sede => {
                    const option = document.createElement('option');
                    option.value = sede.id;
                    option.textContent = sede.sede;
                    sedeAdmSelect.appendChild(option);
                });
            }
        } else {
            console.error('Backend error or invalid data format for sedes:', data.message || 'Data format error');
            sedeAdmSelect.innerHTML = '<option value="">Error al cargar sedes</option>';
            if (errSedeAdmBox) errSedeAdmBox.textContent = data.message || 'Error al cargar las sedes.';
        }
    } catch (error) {
        console.error('Error fetching Sede Administrativa:', error);
        sedeAdmSelect.innerHTML = '<option value="">Error de red</option>';
        if (errSedeAdmBox) errSedeAdmBox.textContent = 'Error de red al cargar las sedes.';
    } finally {
        sedeAdmSelect.disabled = false;
    }
}

// --- Validation Functions ---
async function validateFirstPart() {
    let isValid = true;
    const form = assetForm;

    const cod_act_f = form.querySelector('#cod_act_f__relation');
    const color = form.querySelector('#color__relation');
    const marca = form.querySelector('#marca__relation');
    const modelo = form.querySelector('#modelo__relation');
    const sedeAdm = form.querySelector('#sede_adm__relation');
    const descripcion = form.querySelector('#descripcion__relation');
    const serial = form.querySelector('#serial__relation');
    const estatus = form.querySelector('#estatus_new__relation');
    // const codigo_u_u = form.querySelector('#codigo_u_u__relation'); // This field seems to be in the second part based on your original code

    clearErrors(); // Clear all previous errors before validating

    if (!cod_act_f || cod_act_f.value.trim() === '') {
        displayError(cod_act_f, 'Ingrese un Código de Activo Fijo.');
        isValid = false;
    } else if (isNaN(parseInt(cod_act_f.value))) {
        displayError(cod_act_f, 'El Código Activo Fijo debe ser un número entero.');
        isValid = false;
    }

    if (!color || color.value.trim() === '') {
        displayError(color, 'El Color es obligatorio.');
        isValid = false;
    }

    if (!descripcion || descripcion.value.trim() === '') {
        displayError(descripcion, 'Por favor, ingrese una Descripción.');
        isValid = false;
    }

    if (!marca || marca.value.trim() === '') {
        displayError(marca, 'La Marca es obligatoria.');
        isValid = false;
    }

    if (!modelo || modelo.value.trim() === '') {
        displayError(modelo, 'El Modelo es obligatorio.');
        isValid = false;
    }

    if (!serial || serial.value.trim() === '') {
        displayError(serial, 'El Serial es obligatorio.');
        isValid = false;
    }

    if (!estatus || estatus.value.trim() === '') {
        displayError(estatus, 'El Estatus es obligatorio.');
        isValid = false;
    } else if (isNaN(parseInt(estatus.value))) {
        displayError(estatus, 'El Estatus debe ser un número entero.');
        isValid = false;
    }

    if (!sedeAdm || sedeAdm.value.trim() === '') {
        displayError(sedeAdm, 'La sede administrativa es obligatoria');
        isValid = false;
    } else if (isNaN(parseInt(sedeAdm.value))) {
        displayError(sedeAdm, 'La sede Adm debe ser un número entero.');
        isValid = false;
    }

    if (!isValid) {
        return false; // If basic validation fails, no need for server check
    }

    try {
        const response = await fetch('/systemahidrofalcon/api/activo?action=checkAssetExists', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                cod_activo_fijo: cod_act_f.value.trim(), // Use correct field name
                serial: serial.value.trim()
            }),
        });

        const data = await response.json();

        if (data.exists) {
            isValid = false;
            if (data.field === 'cod_activo_fijo') { // Use correct field name
                displayError(cod_act_f, 'Código ya está registrado.');
            } else if (data.field === 'serial') {
                displayError(serial, 'Este Serial ya está registrado.');
            }
        }

    } catch (error) {
        console.error('Error al verificar la existencia del activo:', error);
        isValid = false; // Asumir que algo salió mal y no es válido
    }

    return isValid;
}

function validateSecondPart() {
    let isValid = true;
    const form = assetForm;

    const unidad = form.querySelector('#unidad__relation');
    const observacion = form.querySelector('#observacion__relation');
    const fecha = form.querySelector('#fecha__relation');
    const documento = form.querySelector('#documento__relation');
    const monto = form.querySelector('#monto__relation');
    const patronNumero = /^-?\d*\.?\d+(?:[eE][+\-]?\d+)?$/;

    function isValidDate(dateString) {
    // Expresión regular para el formato DD/MM/AA
    const regex = /^\d{2}\/\d{2}\/\d{2}$/;
    
    // Si la cadena no coincide con el formato, la validación falla
    if (!regex.test(dateString)) {
        return false;
    }
    
    // Opcional pero recomendado:
    // Puedes agregar una lógica más detallada aquí para
    // verificar que la fecha sea válida (ej. no 31/02/24)
    // Sin embargo, para una validación de formato simple, la regex es suficiente.
    
        return true;
    }


    clearErrors(); // Clear all previous errors before validating

    if (!documento || documento.value.trim() === '') {
        displayError(documento, 'El documento no puede estar vacío.');
        isValid = false;
    }


    // Observacion is usually optional, but your validation makes it mandatory here.
    if (!observacion || observacion.value.trim() === '') {
        displayError(observacion, 'Ingrese una Observación.');
        isValid = false;
    }

    if (!unidad || unidad.value.trim() === '') {
        displayError(unidad, 'El Código de Unidad es Obligatorio.');
        isValid = false;
    }


    if (!fecha || fecha.value.trim() === '') {
    displayError(fecha, 'Ingrese una Fecha de Adquisición.');
    isValid = false;
    } else if (!isValidDate(fecha.value.trim())) {
        // Si la fecha no tiene el formato correcto, muestra un error
        displayError(fecha, 'El formato de la fecha debe ser DD/MM/AA.');
        isValid = false;
    }
    if (!monto || monto.value.trim() === '') {
        displayError(monto, 'Ingrese un Monto.');
        isValid = false;
    } else if (!patronNumero.test(monto.value.trim())) {
        displayError(monto, 'El Monto debe ser un número válido.');
        isValid = false;
    } else if (parseFloat(monto.value) < 0) {
        displayError(monto, 'El Monto no puede ser negativo.');
        isValid = false;
    }

    return isValid;
}


// --- Submit Handler ---
async function submitRelationForm(event) {
    event.preventDefault();

    clearErrors();

    // Re-validate both parts before submission as the "Next" button only validates the first part
    const firstPartIsValid = await validateFirstPart();
    const secondPartIsValid = validateSecondPart();

    if (!firstPartIsValid || !secondPartIsValid) {
        iziToast.warning({
            title: 'Formulario Incompleto',
            message: 'Por favor, complete todos los campos requeridos.',
            position: 'topRight',
            timeout: 5000
        });
        // If validation fails in either part, ensure the correct part is visible
        if (!firstPartIsValid && frstPartContainer && scndPartContainer) {
            toggleVisibility(frstPartContainer, true);
            toggleVisibility(scndPartContainer, false);
        } else if (!secondPartIsValid && frstPartContainer && scndPartContainer) {
            toggleVisibility(frstPartContainer, false);
            toggleVisibility(scndPartContainer, true);
        }
        return;
    }

    if (submitButton) {
        submitButton.disabled = true;
        submitButton.value = 'Registrando...';
    }

    const formData = new FormData(assetForm);
    formData.append('operation_type', 'Sin Asignar'); // Explicitly set operation_type
    formData.append('user_id', window.currentUserId);

    try {
        const response = await fetch(assetForm.action, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`Server responded with status: ${response.status} ${response.statusText}. Details: ${errorText}`);
        }

        const result = await response.json();

        if (result.success) {
            const isModalOpen = document.getElementById('relation-modal').classList.contains('is-open');

            iziToast.success({
                title: 'Agregación Exitosa',
                message: 'El activo ha sido agregado correctamente.',
                position: 'topRight',
                timeout: 5000
            });

            document.getElementById('relation-modal').classList.remove('is-open');
            assetForm.reset(); // Reset the form

            // Reset form visibility and clear errors
            if (frstPartContainer && scndPartContainer) {
                toggleVisibility(frstPartContainer, true);
                toggleVisibility(scndPartContainer, false);
            }
            clearErrors();
        } else {
            iziToast.error({
                title: 'Error al registrar el activo',
                message: result.message || 'Hubo un error al agregar el activo.',
                position: 'topRight',
                timeout: 5000
            });
            if (result.errors) {
                for (const fieldName in result.errors) {
                    const inputElement = assetForm.querySelector(`[name="${fieldName}"]`);
                    if (inputElement) {
                        displayError(inputElement, result.errors[fieldName]);
                    } else {
                        console.error(`Error for field '${fieldName}' but no element found.`);
                    }
                }
            }
             // Keep the second part visible if that's where the error occurred
            if (scndPartContainer && !firstPartIsValid) { // If second part was active and first part failed
                toggleVisibility(frstPartContainer, true);
                toggleVisibility(scndPartContainer, false);
            } else if (scndPartContainer) { // If error was in second part
                toggleVisibility(frstPartContainer, false);
                toggleVisibility(scndPartContainer, true);
            }
        }
    } catch (error) {
        console.error('Submission Error:', error);
        iziToast.error({
            title: 'Error de Conexión',
            message: 'Ocurrió un error al registrar el activo: ' + error.message,
            position: 'topRight',
            timeout: 7000
        });
    } finally {
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.value = 'Registrar Nuevo Activo';
        }
    }
}

// --- Event Listeners Initialization Function ---
function initializeRelationLogic() {
    populateStatusOptions();
    populateSedeAdmSelect();

    if (nextButton) {
        nextButton.addEventListener('click', async (event) => {
            event.preventDefault();
            clearErrors();

            const isFirstPartValid = await validateFirstPart();

            if (isFirstPartValid) {
                if (frstPartContainer && scndPartContainer) {
                    toggleVisibility(frstPartContainer, false); // Hide first part
                    toggleVisibility(scndPartContainer, true); // Show second part
                    console.log('Validación primera parte OK. Cambiando a la segunda parte.');
                }
            } else {
                iziToast.warning({
                    title: 'Formulario Incompleto',
                    message: 'Por favor, completa todos los campos de la primera parte.',
                    position: 'topRight',
                    timeout: 5000
                });
                console.log('Validación primera parte FALLÓ. Permaneciendo en la primera parte.');
            }
        });
    }

    if (goBackButton) {
        goBackButton.addEventListener('click', (event) => {
            event.preventDefault();
            clearErrors();
            console.log('Botón "Volver" clicado. Regresando a la primera parte del formulario.');
            if (frstPartContainer && scndPartContainer) {
                toggleVisibility(scndPartContainer, false); // Hide second part
                toggleVisibility(frstPartContainer, true); // Show first part
            }
        });
    }

    if (assetForm) {
        assetForm.addEventListener('submit', submitRelationForm);
    }

    if (modalCloseButton) {
        modalCloseButton.addEventListener('click', () => {
            console.log('Modal close button clicked for relation modal. Custom reset handled by onClose.');
            // MicroModal's onClose will handle the reset
        });
    }
}


// --- MicroModal Initialization ---
MicroModal.init({
    onShow: (modal) => {
        if (modal.id === 'relation-modal') {
            console.log('Relation modal opened!');
            assignModalDOMelements(); // Assign all DOM elements when modal opens
            initializeRelationLogic(); // Attach event listeners and load initial data

            // Ensure first part of form is visible and second part is hidden
            if (frstPartContainer && scndPartContainer) {
                toggleVisibility(frstPartContainer, true);
                toggleVisibility(scndPartContainer, false);
            }
            clearErrors(); // Clear any existing errors when modal opens
        }
    },
    onClose: (modal) => {
        if (modal.id === 'relation-modal') {
            console.log('Relation modal closed!');
            if (assetForm) assetForm.reset();

            // Reset form visibility
            if (frstPartContainer && scndPartContainer) {
                toggleVisibility(frstPartContainer, true);
                toggleVisibility(scndPartContainer, false);
            }
            clearErrors();
        }
    },
    disableScroll: true,
    disableFocus: true,
    awaitCloseAnimation: true
});