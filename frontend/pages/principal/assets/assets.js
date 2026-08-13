const navItems = document.querySelectorAll('.nav-item');
const dropItems = document.querySelectorAll('.dropdown-item');

for (let i = 0; i < navItems.length; i++) {
    navItems[i].addEventListener('click', e => {
        // Toggle the 'show' class
        dropItems[i].classList.toggle('show');

        // Optional: Close other open dropdowns when one is opened
        // This part depends on your desired user experience
        // dropItems.forEach((item, index) => {
        //     if (index !== i && item.classList.contains('show')) {
        //         item.classList.remove('show');
        //     }
        // });
    });
}
// --- Elementos de la tabla principal de Propiedades/Activos ---
const propertiesSearchInput = document.getElementById('propertiesSearchInput');
const printButton = document.querySelector('.button-imprimir');
const showAllPropertiesBtn = document.getElementById('showAllPropertiesBtn');
const propertiesTableBody = document.getElementById('propertiesTableBody');
const propertiesTableHeaderRow = document.getElementById('propertiesTableHeaderRow');
const propertiesErrorMessage = document.getElementById('propertiesErrorMessage');
const propertiesErrorMessageP = propertiesErrorMessage ? propertiesErrorMessage.querySelector('p') : null;

// --- Elementos de Paginación ---
const prevPageBtn = document.getElementById('prev-asset-page-btn');
const nextPageBtn = document.getElementById('next-asset-page-btn');
const paginationNumbersContainer = document.getElementById('pagination-numbers');

// --- Variables de Paginación ---
let currentPage = 1;
const itemsPerPage = 10; // Puedes ajustar este valor
let totalPages = 0;
let currentSearchTerm = ''; // Para mantener el término de búsqueda a través de la paginación

// --- Mensaje de carga inicial (para el <tbody>) ---
const initialLoadingRow = '<tr><td colspan="100%" style="text-align: center;">Cargando bienes...</td></tr>';

// --- Función auxiliar para mostrar/ocultar mensajes de error ---
function toggleErrorMessage(message, show) {
    if (propertiesErrorMessage && propertiesErrorMessageP) {
        propertiesErrorMessageP.textContent = message;
        propertiesErrorMessage.style.display = show ? 'block' : 'none';
    }
}

// --- Función asíncrona para obtener todas las propiedades (con paginación) ---
async function fetchAllProperties() {
    currentSearchTerm = propertiesSearchInput ? propertiesSearchInput.value.trim() : '';

    if (propertiesTableBody) propertiesTableBody.innerHTML = initialLoadingRow;
    toggleErrorMessage('', false);

    const params = new URLSearchParams({
        search: currentSearchTerm,
        page: currentPage,
        limit: itemsPerPage,
        'action': 'getTables'
    });

    try {
        const response = await fetch(`/systemahidrofalcon/api/activo?${params.toString()}`);

        if (!response.ok) {
            throw new Error(`HTTP error! Estado: ${response.status}`);
        }

        const data = await response.json();

        if (data.success) {
            // El backend debe devolver el total de registros para la paginación
            // Por ejemplo: data.totalProperties
            totalPages = Math.ceil(data.totalProperties / itemsPerPage);

            renderPropertiesTableHeaders(data.headers);
            renderPropertiesTable(data.properties, data.headers);
            updatePaginationControls();
        } else {
            console.error('Error al cargar propiedades:', data.message);
            if (propertiesTableBody) propertiesTableBody.innerHTML = '';
            toggleErrorMessage(data.message || 'No se encontraron bienes.', true);
            totalPages = 0; // Reset total pages on error
            updatePaginationControls();
        }
    } catch (error) {
        console.error('Error de red o servidor al cargar propiedades:', error);
        if (propertiesTableBody) propertiesTableBody.innerHTML = '';
        toggleErrorMessage('Error de red al cargar bienes. Intente de nuevo más tarde.', true);
        totalPages = 0; // Reset total pages on error
        updatePaginationControls();
    }
}

// --- Función para renderizar los encabezados de la tabla ---
function renderPropertiesTableHeaders(headers) {
    if (!propertiesTableHeaderRow) return;
    propertiesTableHeaderRow.innerHTML = '';

    headers.forEach(headerText => {
        const th = document.createElement('th');
        th.textContent = headerText;
        propertiesTableHeaderRow.appendChild(th);
    });
}

// --- Función para renderizar las propiedades en el cuerpo de la tabla ---
// --- Función para renderizar las propiedades en el cuerpo de la tabla ---
// --- Función para renderizar las propiedades en el cuerpo de la tabla ---
// --- Función para renderizar las propiedades en el cuerpo de la tabla ---
// Ensure this variable is correctly declared and references your <tbody> element's ID.
// For example, if your HTML table body has id="propertiesTableBody"
// make sure you have: const propertiesTableBody = document.getElementById('propertiesTableBody');

function renderPropertiesTable(properties, headers) {
    // *** FIX: Use propertiesTableBody instead of allAssetsTableBody ***
    if (!propertiesTableBody) {
        console.error('renderPropertiesTable: propertiesTableBody is null. Cannot render table.');
        return;
    }
    propertiesTableBody.innerHTML = ''; // Clear existing rows

    const colSpanForMessages = headers.length > 0 ? headers.length + 1 : 1; // +1 for the actions column

    if (properties.length === 0) {
        const emptyRow = propertiesTableBody.insertRow();
        const emptyCell = emptyRow.insertCell();
        emptyCell.colSpan = colSpanForMessages;
        emptyCell.textContent = 'No se encontraron bienes.';
        emptyCell.style.textAlign = 'center';
    } else {
        const headerToFieldNameMap = {
            'ID': 'id',
            'Sede Adm': 'sede_adm',
            'Cód. Act. Fijo': 'codigo_activo_fijo',
            'Descripción': 'descripcion',
            'Marca': 'marca',
            'Modelo': 'modelo',
            'Serial': 'serial',
            'Estado Físico': 'estado_fisico_name',
            'Estatus Admin.': 'estatus_administrativo_name',
            'Unidad': 'unidad',
            'Custodio': 'custodio_name',
            'Cédula Custodio': 'custodio_cedula',
            'Cargo Custodio': 'cargo_custodio',
            'Gerencia Custodio': 'gerencia_custodio',
            'Asignación': 'asignacion',
            'Observación': 'observacion',
            'Adquisicion': 'adquisicion',
            'Documento': 'documento',
            'Fecha': 'fecha',
            'Monto': 'monto', // Keep this mapping
        };

        properties.forEach(prop => {
            const row = propertiesTableBody.insertRow();

            headers.forEach(headerText => {
                const td = row.insertCell();
                const originalFieldName = headerToFieldNameMap[headerText];
                // Check if the field exists and is not null/undefined
                let cellValue = prop[originalFieldName];
                if (cellValue === undefined || cellValue === null) {
                    cellValue = ''; // Display empty string if data is missing
                } else if (originalFieldName === 'fecha') {
                    cellValue = formatDate(cellValue); // Apply date formatting
                }
                // *** ELIMINAR O COMENTAR LA LÓGICA DE FORMATO DE MONTO AQUÍ ***
                // else if (originalFieldName === 'monto' && cellValue !== '') {
                //     cellValue = parseFloat(cellValue).toLocaleString('es-VE', { style: 'currency', currency: 'VEF' });
                // }
                // El valor de 'monto' ya viene formateado desde PHP, así que no se necesita más procesamiento aquí.
                
                td.textContent = cellValue;
            });

            // If you had an actions column, keep that logic outside the headers loop
            // For example:
            // const actionsTd = row.insertCell();
            // actionsTd.innerHTML = '<button>View</button><button>Edit</button>';
        });
    }
}

// Función auxiliar para formatear fechas (opcional)
function formatDate(dateString) {
    if (!dateString) return '';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-VE'); // Formato para Venezuela
    } catch (e) {
        return dateString; // Devuelve el string original si no se puede parsear
    }
}

// --- Funciones para la paginación ---
function updatePaginationControls() {
    // Deshabilitar/Habilitar botones prev/next
    prevPageBtn.disabled = currentPage === 1;
    nextPageBtn.disabled = currentPage === totalPages || totalPages === 0;

    // Renderizar botones de números de página
    paginationNumbersContainer.innerHTML = '';
    const maxPageButtons = 5; // Número máximo de botones de página a mostrar
    let startPage = Math.max(1, currentPage - Math.floor(maxPageButtons / 2));
    let endPage = Math.min(totalPages, startPage + maxPageButtons - 1);

    // Ajustar startPage si endPage alcanza el totalPages
    if (endPage - startPage + 1 < maxPageButtons && totalPages > maxPageButtons) {
        startPage = Math.max(1, endPage - maxPageButtons + 1);
    }


    if (startPage > 1) {
        const firstPageBtn = createPageButton(1);
        paginationNumbersContainer.appendChild(firstPageBtn);
        if (startPage > 2) {
            const ellipsis = document.createElement('span');
            ellipsis.textContent = '...';
            paginationNumbersContainer.appendChild(ellipsis);
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        const pageBtn = createPageButton(i);
        paginationNumbersContainer.appendChild(pageBtn);
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            const ellipsis = document.createElement('span');
            ellipsis.textContent = '...';
            paginationNumbersContainer.appendChild(ellipsis);
        }
        const lastPageBtn = createPageButton(totalPages);
        paginationNumbersContainer.appendChild(lastPageBtn);
    }
}

function createPageButton(pageNumber) {
    const button = document.createElement('button');
    button.textContent = pageNumber;
    button.classList.add('page-num-btn');
    if (pageNumber === currentPage) {
        button.classList.add('active');
    }
    button.addEventListener('click', () => {
        if (pageNumber !== currentPage) {
            currentPage = pageNumber;
            fetchAllProperties();
        }
    });
    return button;
}

// --- Event Listeners para la tabla principal de Propiedades y Paginación ---
document.addEventListener('DOMContentLoaded', () => {
    // Carga inicial de propiedades al cargar la página
    fetchAllProperties();

    // Listener para la barra de búsqueda (cuando el usuario escribe)
    if (propertiesSearchInput) {
        propertiesSearchInput.addEventListener('input', () => {
            currentPage = 1; // Resetear a la primera página con cada nueva búsqueda
            fetchAllProperties();
        });
    }

    // Listener para el botón "Mostrar todo"
    if (showAllPropertiesBtn) {
        showAllPropertiesBtn.addEventListener('click', () => {
            if (propertiesSearchInput) propertiesSearchInput.value = '';
            currentPage = 1; // Resetear a la primera página al mostrar todo
            fetchAllProperties();
        });
    }

    // Listener para el botón "Imprimir"
    if (printButton) {
        printButton.addEventListener('click', () => {
            window.print();
        });
    }

    // Listeners para los botones de paginación
    if (prevPageBtn) {
        prevPageBtn.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                fetchAllProperties();
            }
        });
    }

    if (nextPageBtn) {
        nextPageBtn.addEventListener('click', () => {
            if (currentPage < totalPages) {
                currentPage++;
                fetchAllProperties();
            }
        });
    }


    function fetchAssetCounts() {
        // Path to your PHP backend script
        const backendUrl = '/systemaHidrofalcon/api/activo';
        const params = new URLSearchParams({ action: 'getAssetCounts' });

        fetch(backendUrl + '?' + params.toString())
            .then(response => {
                // Check if the network response was successful (status code 200-299)
                if (!response.ok) {
                    // Throw an error if the response is not OK
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                // Parse the JSON response
                return response.json();
            })
            .then(data => {
                // Check if the backend script reported success
                if (data.success) {
                    // Update the HTML elements with the fetched data
                    document.getElementById('incorporation-count').textContent = data.counts.incorporado; 
                    document.getElementById('desincorporation-count').textContent = data.counts.desincorporado; 
                    document.getElementById('sin-asignar-count').textContent = data.counts.sin_asignar; 

                } else {
                    // Handle errors reported by the backend script
                    console.error('Backend reported an error:', data.message, data.errors);
                    // Optionally, update the UI to show an error message
                    document.getElementById('incorporation-count').textContent = 'Error';
                    document.getElementById('desincorporation-count').textContent = 'Error';
                    document.getElementById('sin-asignar-count').textContent = 'Error';
                }
            })
            .catch(error => {
                // Handle any network or parsing errors
                console.error('Fetch error:', error);
                // Update UI to show that an error occurred
                document.getElementById('incorporation-count').textContent = 'N/A';
                document.getElementById('desincorporation-count').textContent = 'N/A';
                document.getElementById('sin-asignar-count').textContent = 'N/A';
                alert('Hubo un error al cargar los datos de activos. Por favor, inténtelo de nuevo.');
            });
    }

    // Call the function when the DOM is fully loaded
    fetchAssetCounts();
});
