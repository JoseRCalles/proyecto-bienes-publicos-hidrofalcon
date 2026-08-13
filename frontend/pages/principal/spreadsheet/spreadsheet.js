document.addEventListener('DOMContentLoaded', () => {
    const uploadForm = document.getElementById('uploadForm');
    const excelFile = document.getElementById('excelFile');
    const submitBtn = document.getElementById('submitBtn');
    const messageDiv = document.getElementById('message');

    uploadForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (excelFile.files.length === 0) {
            showMessage('Por favor, selecciona un archivo.', 'error');
            return;
        }

        const file = excelFile.files[0];
        const formData = new FormData();
        formData.append('excelFile', file);

        submitBtn.disabled = true;
        showMessage('Procesando el archivo, por favor espera...', 'success');

        try {
            const response = await fetch('/systemahidrofalcon/backend/data/processxls.php', {
                method: 'POST',
                body: formData,
            });

            if (response.ok) {
                // Obtener el nombre del archivo de las cabeceras HTTP
                const contentDisposition = response.headers.get('Content-Disposition');
                let fileName = 'descarga.csv';
                if (contentDisposition) {
                    const matches = /filename="(.+?)"/.exec(contentDisposition);
                    if (matches && matches[1]) {
                        fileName = matches[1];
                    }
                }
                
                // Obtener el contenido como un Blob
                const blob = await response.blob();
                
                // Crear un objeto URL para el Blob
                const url = window.URL.createObjectURL(blob);
                
                // Crear un enlace de descarga y simular un clic
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                
                // Limpiar después de la descarga
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);

                showMessage('El archivo CSV se ha generado y descargado correctamente.', 'success');
            } else {
                // Si la respuesta no es 200 OK, intentar leer el error como texto
                const errorText = await response.text();
                showMessage(`Error del servidor: ${errorText || 'Error desconocido.'}`, 'error');
            }

        } catch (error) {
            console.error('Error:', error);
            showMessage('Ocurrió un error inesperado al enviar el archivo.', 'error');
        } finally {
            submitBtn.disabled = false;
        }
    });

    function showMessage(msg, type) {
        messageDiv.textContent = msg;
        messageDiv.className = type;
        messageDiv.style.display = 'block';
    }
});