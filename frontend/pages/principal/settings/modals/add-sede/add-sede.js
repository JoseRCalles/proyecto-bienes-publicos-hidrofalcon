document.getElementById('addSedeForm').addEventListener('submit', function(event) {
    event.preventDefault();

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

    const addSedeNombreInput = document.getElementById('addSedeNombre');
    const addSedeDireccionInput = document.getElementById('addSedeDireccion');

    const nombreSede = addSedeNombreInput.value.trim();
    const direccionSede = addSedeDireccionInput.value.trim();

    // Clear previous errors from all fields
    clearErrors(addSedeNombreInput);
    clearErrors(addSedeDireccionInput);

    let isValid = true;

    // Client-side validation for empty fields
    if (!nombreSede) {
        displayError(addSedeNombreInput, 'El nombre de la sede es obligatorio.');
        isValid = false;
    }

    if (!direccionSede) {
        displayError(addSedeDireccionInput, 'La dirección es obligatoria.');
        isValid = false;
    }

    // If the form is not valid, stop execution here
    if (!isValid) {
        return;
    }

    // Single fetch to handle server-side validation and insertion
    const data = {
        nombre_sede: nombreSede,
        direccion_sede: direccionSede,
        action: 'addSede'
    };

    fetch('/systemahidrofalcon/api/sede', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
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
                MicroModal.close('add-sede-modal');
            }
            // Optional: reload the page or update the table
            // location.reload();
        } else {
            // Display validation or insertion error from the server
            if (result.message.includes('existe una sede')) {
                displayError(addSedeNombreInput, result.message);
            } else {
                // For other errors, use iziToast
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