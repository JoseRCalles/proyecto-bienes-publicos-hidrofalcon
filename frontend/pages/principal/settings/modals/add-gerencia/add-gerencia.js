document.addEventListener('DOMContentLoaded', function() {
    const addGerenciaForm = document.getElementById('addGerenciaForm');
    const addGerenciaNombreInput = document.getElementById('addGerenciaNombre');
    const addGerenciaEncargadoInput = document.getElementById('addGerenciaEncargado');

    function displayError(fieldElement, message) {
        let errorBox = fieldElement.parentElement.querySelector('.err-box');
        if (!errorBox) {
            errorBox = document.createElement('div');
            errorBox.classList.add('err-box');
            fieldElement.parentElement.appendChild(errorBox);
        }
        errorBox.textContent = message;
        errorBox.classList.add('show-error');
    }

    function clearErrors() {
        document.querySelectorAll('.err-box').forEach(box => {
            box.textContent = '';
            box.classList.remove('show-error');
        });
    }

    addGerenciaForm.addEventListener('submit', function(event) {
        event.preventDefault();

        clearErrors();

        const nombre = addGerenciaNombreInput.value.trim();
        const encargado = addGerenciaEncargadoInput.value.trim();

        let isValid = true;
        if (!nombre) {
            displayError(addGerenciaNombreInput, 'El nombre de la gerencia es obligatorio.');
            isValid = false;
        }
        if (!encargado) {
            displayError(addGerenciaEncargadoInput, 'El nombre del encargado es obligatorio.');
            isValid = false;
        }

        if (!isValid) {
            return;
        }

        const addGerenciaUrl = '/systemahidrofalcon/api/gerencias?action=addGerencia';
        const postData = {
            gerencia: nombre,
            encargado: encargado
        };

        fetch(addGerenciaUrl, {
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
                    MicroModal.close('add-gerencia-modal');
                }
                addGerenciaForm.reset();
            } else {
                // Se usa displayError para el error de duplicado.
                if (result.message.includes('Ya existe una gerencia con este nombre.')) {
                    displayError(addGerenciaNombreInput, result.message);
                } else {
                    // Para otros errores no relacionados con el formulario, se mantiene iziToast.
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