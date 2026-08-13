// Asegúrate de que MicroModal y iziToast estén cargados.
// iziToast es opcional, pero ayuda a mejorar la experiencia del usuario.

document.addEventListener('DOMContentLoaded', () => {

    const excelModalId = 'relation-morethan-modal';
    const form = document.getElementById('excelUploadFormModal');
    const fileInput = document.getElementById('excelFileModal');
    const submitButton = document.getElementById('submitExcelModalBtn');
    const messageContainer = document.getElementById('excelMessageContainer');
    const resultsContainer = document.getElementById('excelResultsContainer');

    // Verificar si los elementos críticos existen
    if (!form || !fileInput || !submitButton || !messageContainer || !resultsContainer) {
        console.error('ERROR: No se encontraron todos los elementos DOM críticos del modal de Excel.');
        return;
    }

    // Función para mostrar mensajes dentro del modal
    function showExcelMessage(message, type = 'info', append = false) {
        const messageHtml = `<div class="message ${type}" style="padding: 10px; border-radius: 4px; margin-top: 10px;">${message}</div>`;
        if (append) {
            messageContainer.innerHTML += messageHtml;
        } else {
            messageContainer.innerHTML = messageHtml;
        }
    }

    // Función para limpiar mensajes y resultados
    function clearUI() {
        messageContainer.innerHTML = '';
        resultsContainer.innerHTML = '';
    }
    
    // Función principal para manejar el envío del formulario
    async function handleExcelFormSubmit(event) {
        event.preventDefault();

        clearUI();
        
        submitButton.disabled = true;
        submitButton.textContent = 'Procesando...';

        const file = fileInput.files[0];

        if (!file) {
            showExcelMessage('Por favor, seleccione un archivo Excel.', 'error');
            submitButton.disabled = false;
            submitButton.textContent = 'Subir y Procesar';
            return;
        }

        const allowedExtensions = ['xls', 'xlsx', 'xlsm', 'xlsb', 'xlam', 'xltm', 'xltx'];
        const fileExtension = file.name.split('.').pop().toLowerCase();

        if (!allowedExtensions.includes(fileExtension)) {
            showExcelMessage('Tipo de archivo no permitido. Verifique las extensiones.', 'error');
            submitButton.disabled = false;
            submitButton.textContent = 'Subir y Procesar';
            return;
        }

        const formData = new FormData(form);
        formData.append('action', 'import_assets');
        
        showExcelMessage('Subiendo y procesando archivo. Por favor, espere...', 'info');

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData
            });

            // Leer la respuesta como texto primero para evitar errores si no es JSON
            const responseText = await response.text();
            
            // Intentar parsear el JSON
            let responseData;
            try {
                responseData = JSON.parse(responseText);
            } catch (jsonError) {
                console.error('Error al parsear JSON:', jsonError);
                console.error('Respuesta recibida:', responseText);
                throw new Error('El servidor devolvió una respuesta no válida. Puede que haya errores de PHP. Revise la consola del servidor.');
            }

            if (!response.ok || !responseData.success) {
                const errorMessage = responseData.message || `Error del servidor: ${response.status} ${response.statusText}`;
                throw new Error(errorMessage);
            }

            // --- Lógica para manejar la respuesta exitosa ---
            const { message, total_rows_processed, successful_inserts, failed_inserts, errors, original_csv_url, result_csv_url } = responseData;

            let summaryHtml = `
                <div class="summary-box" style="margin-top: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 8px;">
                    <p><strong>Estado:</strong> ${message}</p>
                    <p><strong>Filas procesadas:</strong> ${total_rows_processed}</p>
                    <p><strong>Insertadas con éxito:</strong> <span style="color: green; font-weight: bold;">${successful_inserts}</span></p>
                    <p><strong>Fallidas:</strong> <span style="color: red; font-weight: bold;">${failed_inserts}</span></p>
                </div>
            `;
            resultsContainer.innerHTML = summaryHtml;
            
            if (errors && errors.length > 0) {
                let errorListHtml = `
                    <div style="margin-top: 15px;">
                        <p><strong>Detalles de los errores:</strong></p>
                        <ul style="list-style-type: none; padding-left: 0;">
                `;
                errors.forEach(error => {
                    errorListHtml += `<li style="color: red;">- ${error}</li>`;
                });
                errorListHtml += `</ul></div>`;
                resultsContainer.innerHTML += errorListHtml;
            }

            if (result_csv_url || original_csv_url) {
                let downloadLinksHtml = `
                    <div style="margin-top: 20px;">
                        <p><strong>Archivos generados:</strong></p>
                `;
                if (result_csv_url) {
                    downloadLinksHtml += `<a href="${result_csv_url}" class="button" download style="margin-right: 10px;">Descargar Reporte de Procesamiento</a>`;
                }
                if (original_csv_url) {
                    downloadLinksHtml += `<a href="${original_csv_url}" class="button button--secondary" download>Descargar CSV Original</a>`;
                }
                downloadLinksHtml += `</div>`;
                resultsContainer.innerHTML += downloadLinksHtml;
            }

            if (typeof iziToast !== 'undefined') {
                 if (failed_inserts > 0) {
                     iziToast.warning({
                         title: 'Importación Finalizada',
                         message: `Se procesaron ${total_rows_processed} filas. ${successful_inserts} insertadas, ${failed_inserts} fallidas.`,
                         position: 'topRight'
                     });
                 } else {
                     iziToast.success({
                         title: 'Importación Exitosa',
                         message: `Se insertaron ${successful_inserts} filas correctamente.`,
                         position: 'topRight'
                     });
                 }
            }
            
        } catch (error) {
            console.error('Error durante el proceso:', error);
            showExcelMessage(`Error de conexión o procesamiento: ${error.message}`, 'error');
            
            if (typeof iziToast !== 'undefined') {
                iziToast.error({
                    title: 'Error de Importación',
                    message: error.message,
                    position: 'topRight'
                });
            }

        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Subir y Procesar';
            form.reset();
        }
    }
    
    function handleFileInputChange() {
        clearUI();
    }

    function initializeExcelModalLogic() {
        form.removeEventListener('submit', handleExcelFormSubmit);
        form.addEventListener('submit', handleExcelFormSubmit);
        fileInput.removeEventListener('change', handleFileInputChange);
        fileInput.addEventListener('change', handleFileInputChange);
        form.reset();
        clearUI();
        submitButton.disabled = false;
        submitButton.textContent = 'Subir y Procesar';
    }


    MicroModal.init({
        onShow: (modal) => {
            if (modal.id === excelModalId) {
                console.log('Modal de importación de Excel abierto.');
                initializeExcelModalLogic();
            }
        },
        onClose: (modal) => {
            if (modal.id === excelModalId) {
                console.log('Modal de importación de Excel cerrado.');
                form.reset();
                clearUI();
            }
        },
        disableScroll: true,
        disableFocus: true,
        awaitCloseAnimation: true
    });
});