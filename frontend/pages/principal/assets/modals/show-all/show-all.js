// show-all.js

// --- MicroModal Initialization ---
function toggleVisibility(element, show = true) {
    if (!element) return;
    if (show) {
        setTimeout(() => {
            element.classList.add('show');
            element.classList.remove('hidden');
        }, 10);
    } else {
        element.classList.remove('show');
        element.classList.add('hidden');
    }
}



MicroModal.init({
    onShow: (modal) => {
        if (modal.id === 'showAll-modal') {
            assignModalDOMelements();

            if (!allAssetsTableBody || !allAssetsSearchInput || !allAssetsTableHeaderRow) {
                console.error('ERROR: No se encontraron todos los elementos DOM críticos después de assignModalDOMelements(). Verifique los IDs en el HTML.');
                return;
            }

            const initialColSpan = 20;
            allAssetsTableBody.innerHTML = createInitialLoadingRow(initialColSpan);

            loadEstatusFisicoOptions();
            loadEstatusAdmOptions();
            populateGerenciaFilter();

            // Load properties data initially
            loadPropertiesData();

            addModalEventListeners();
        }
    },
    onClose: (modal) => {
        if (modal.id === 'showAll-modal') {
            if (allAssetsTableBody) allAssetsTableBody.innerHTML = '';
            if (allAssetsSearchInput) allAssetsSearchInput.value = '';
            if (allAssetsEstatusSelect) allAssetsEstatusSelect.value = '';
            if (allAssetsEstatusAdmSelect) allAssetsEstatusAdmSelect.value = '';
            if (allAssetsGerenciaSelect) allAssetsGerenciaSelect.value = '';
            if (allAssetsFechaAdquisicionStartInput) allAssetsFechaAdquisicionStartInput.value = '';
            if (allAssetsFechaAdquisicionEndInput) allAssetsFechaAdquisicionEndInput.value = '';
            if (allAssetsTableHeaderRow) allAssetsTableHeaderRow.innerHTML = '';
            toggleErrorMessage('', false);
        }
    },
    disableScroll: true,
    disableFocus: true,
    awaitCloseAnimation: true
});

// --- GLOBAL DOM Elements (declared as `let` for dynamic assignment) ---
let allAssetsSearchInput;
let allAssetsEstatusSelect;
let allAssetsEstatusAdmSelect;
let allAssetsGerenciaSelect;
let allAssetsFechaAdquisicionStartInput;
let allAssetsFechaAdquisicionEndInput;
let allAssetsTableBody;
let allAssetsTableHeaderRow;
let allAssetsErrorMessage;
let allAssetsErrorMessageP;
let allAssetsSearchForm;
let gerenciaFilter;

let showAllModalPrintButton;
let showAllModalResetFiltersBtn;
let exportAllAssetsExcelButton;
const filterButton = document.getElementById('filter-button');
const filterShowallContainer = document.querySelectorAll('.filter-selectors');

if (filterButton && filterShowallContainer[0] && filterShowallContainer[1]) {
    filterButton.addEventListener('click', e => {
        e.preventDefault();
        if(filterShowallContainer[0].classList.contains('show') && filterShowallContainer[1].classList.contains('show')){
            toggleVisibility(filterShowallContainer[0], false)
            toggleVisibility(filterShowallContainer[1], false)
        } else {
            toggleVisibility(filterShowallContainer[0], true);
            toggleVisibility(filterShowallContainer[1], true)
        }
    });
} else {
    console.warn("Filter button or filter container not found. Check your HTML IDs.");
}

// --- Assign DOM elements dynamically when the modal is opened ---
function assignModalDOMelements() {
    allAssetsSearchInput = document.getElementById('allAssetsSearchInput');
    allAssetsTableBody = document.getElementById('allAssetsTableBody');
    allAssetsTableHeaderRow = document.getElementById('allAssetsTableHeaderRow');
    allAssetsErrorMessage = document.getElementById('allAssetsErrorMessage');
    allAssetsErrorMessageP = allAssetsErrorMessage ? allAssetsErrorMessage.querySelector('p') : null;
    allAssetsSearchForm = document.getElementById('allAssetsSearchForm');

    allAssetsEstatusSelect = document.getElementById('allAssetsEstatusSelect');
    allAssetsEstatusAdmSelect = document.getElementById('allAssetsEstatusAdmSelect');
    allAssetsFechaAdquisicionStartInput = document.getElementById('allAssets-fecha-adquisicion-start');
    allAssetsFechaAdquisicionEndInput = document.getElementById('allAssets-fecha-adquisicion-end');
    gerenciaFilter = document.getElementById('allAssets-gerencia-select');
    showAllModalPrintButton = document.getElementById('showAll-modalPrintButton');
    showAllModalResetFiltersBtn = document.getElementById('showAll-modalResetFiltersBtn');
    exportAllAssetsExcelButton = document.getElementById('exportAllAssetsExcelButton');

    // Attach event listener for export button here since it's a global element
    if (exportAllAssetsExcelButton) {
        exportAllAssetsExcelButton.addEventListener('click', () => {
            const searchVal = allAssetsSearchInput ? allAssetsSearchInput.value.trim() : '';
            const estatusFisicoVal = allAssetsEstatusSelect ? allAssetsEstatusSelect.value : '';
            const estatusAdmVal = allAssetsEstatusAdmSelect ? allAssetsEstatusAdmSelect.value : '';
            const gerenciaVal = allAssetsGerenciaSelect ? allAssetsGerenciaSelect.value : '';
            const fechaAdquisicionStartVal = allAssetsFechaAdquisicionStartInput ? allAssetsFechaAdquisicionStartInput.value : '';
            const fechaAdquisicionEndVal = allAssetsFechaAdquisicionEndInput ? allAssetsFechaAdquisicionEndInput.value : '';
    
            const params = new URLSearchParams({
                action: 'exportAssetsToExcel',
                search: searchVal,
                estatus_fisico_id: estatusFisicoVal,
                estatus_administrativo_id: estatusAdmVal,
                gerencia_id: gerenciaVal,
                fecha_adquisicion_start: fechaAdquisicionStartVal,
                fecha_adquisicion_end: fechaAdquisicionEndVal,
            });

            const exportUrl = `/systemahidrofalcon/api/activo?${params.toString()}`;
            window.open(exportUrl, '_blank');
        });
    }
}

// --- Initial loading message (for the <tbody>) ---
function createInitialLoadingRow(colSpan) {
    return `<tr><td colspan="${colSpan}" style="text-align: center;">Cargando bienes...</td></tr>`;
}

// --- Helper function to show/hide error messages ---
function toggleErrorMessage(message, show) {
    if (allAssetsErrorMessage && allAssetsErrorMessageP) {
        allAssetsErrorMessageP.textContent = message;
        allAssetsErrorMessage.style.display = show ? 'block' : 'none';
    }
}

// --- Function to render table headers ---
function renderPropertiesTableHeaders(headers) {
    if (!allAssetsTableHeaderRow) {
        console.error('renderPropertiesTableHeaders: allAssetsTableHeaderRow is null. Cannot render headers.');
        return;
    }
    allAssetsTableHeaderRow.innerHTML = '';

    headers.forEach(headerText => {
        const th = document.createElement('th');
        th.textContent = headerText;
        allAssetsTableHeaderRow.appendChild(th);
    });
}

// --- Function to render properties in the table body ---
function renderPropertiesTable(properties, headers) {
    if (!allAssetsTableBody) {
        console.error('renderPropertiesTable: allAssetsTableBody is null. Cannot render table.');
        return;
    }
    allAssetsTableBody.innerHTML = '';

    const colSpanForMessages = headers.length > 0 ? headers.length : 1;

    if (properties.length === 0) {
        const emptyRow = allAssetsTableBody.insertRow();
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
            'Observación': 'observacion',
            'Documento': 'documento',
            'Fecha': 'fecha',
            'Monto': 'monto',
        };

        properties.forEach(prop => {
            const row = allAssetsTableBody.insertRow();

            headers.forEach(headerText => {
                const td = row.insertCell();
                const originalFieldName = headerToFieldNameMap[headerText];
                td.textContent = prop[originalFieldName] !== undefined && prop[originalFieldName] !== null ? prop[originalFieldName] : '';
            });
        });
    }
}

// --- Asynchronous function to get properties (filters sent, and backend now processes them) ---
async function loadPropertiesData() {
    const searchVal = allAssetsSearchInput ? allAssetsSearchInput.value.trim() : '';
    const estatusFisicoVal = allAssetsEstatusSelect ? allAssetsEstatusSelect.value : '';
    const estatusAdmVal = allAssetsEstatusAdmSelect ? allAssetsEstatusAdmSelect.value : '';
    const gerenciaVal = gerenciaFilter ? gerenciaFilter.value : '';
    const fechaAdquisicionStartVal = allAssetsFechaAdquisicionStartInput ? allAssetsFechaAdquisicionStartInput.value : '';
    const fechaAdquisicionEndVal = allAssetsFechaAdquisicionEndInput ? allAssetsFechaAdquisicionEndInput.value : '';


    const currentHeadersCount = allAssetsTableHeaderRow ? allAssetsTableHeaderRow.children.length : 0;
    const loadingColSpan = currentHeadersCount > 0 ? currentHeadersCount : 20;
    if (allAssetsTableBody) allAssetsTableBody.innerHTML = createInitialLoadingRow(loadingColSpan);

    toggleErrorMessage('', false);

    // *******************************************************************
    // MODIFICACIÓN CLAVE: Agregar el parámetro 'all=true' para el backend
    // *******************************************************************
    const params = new URLSearchParams({
        action: 'getTables',
        all
        : true,  // Solicitar todos los registros
        search: searchVal,
        estatus_fisico_id: estatusFisicoVal,
        estatus_administrativo_id: estatusAdmVal,
        gerencia_id: gerenciaVal,
        fecha_adquisicion_start: fechaAdquisicionStartVal,
        fecha_adquisicion_end: fechaAdquisicionEndVal,
    });

    try {
        const fetchUrl = `/systemaHidrofalcon/api/activo?${params.toString()}`;

        const response = await fetch(fetchUrl);

        if (!response.ok) {
            const errorText = await response.text();
            console.error(`HTTP error! Estado: ${response.status}. Texto de respuesta: ${errorText}`);
            throw new Error(`HTTP error! Estado: ${response.status}. Error: ${errorText}`);
        }

        const data = await response.json();

        if (data.success) {
            const receivedHeaders = data.headers;
            renderPropertiesTableHeaders(receivedHeaders);
            renderPropertiesTable(data.properties, receivedHeaders);
        } else {
            console.error('Error al cargar propiedades (backend indicó fallo):', data.message);
            if (allAssetsTableBody) allAssetsTableBody.innerHTML = '';
            toggleErrorMessage(data.message || 'No se encontraron bienes.', true);
        }
    } catch (error) {
        console.error('Error de red o servidor al cargar propiedades:', error);
        if (allAssetsTableBody) allAssetsTableBody.innerHTML = '';
        toggleErrorMessage('Error de red al cargar bienes. Intente de nuevo más tarde.', true);
    }
}

// --- Event Listeners ---
function addModalEventListeners() {
    let searchTimeout;

    if (allAssetsSearchForm) {
        allAssetsSearchForm.addEventListener('submit', (e) => {
            e.preventDefault();
        });
    }

    if (allAssetsSearchInput) {
        allAssetsSearchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadPropertiesData();
            }, 300);
        });
    }

    if (allAssetsEstatusSelect) allAssetsEstatusSelect.addEventListener('change', () => { loadPropertiesData(); });
    if (allAssetsEstatusAdmSelect) allAssetsEstatusAdmSelect.addEventListener('change', () => { loadPropertiesData(); });
    if (gerenciaFilter) gerenciaFilter.addEventListener('change', () => { loadPropertiesData(); });
    if (allAssetsFechaAdquisicionStartInput) allAssetsFechaAdquisicionStartInput.addEventListener('change', () => { loadPropertiesData(); });
    if (allAssetsFechaAdquisicionEndInput) allAssetsFechaAdquisicionEndInput.addEventListener('change', () => { loadPropertiesData(); });

    if (showAllModalResetFiltersBtn) {
        showAllModalResetFiltersBtn.addEventListener('click', () => {
            if (allAssetsSearchInput) allAssetsSearchInput.value = '';
            if (allAssetsEstatusSelect) allAssetsEstatusSelect.value = '';
            if (allAssetsEstatusAdmSelect) allAssetsEstatusAdmSelect.value = '';
            if (allAssetsGerenciaSelect) allAssetsGerenciaSelect.value = '';
            if (allAssetsFechaAdquisicionStartInput) allAssetsFechaAdquisicionStartInput.value = '';
            if (allAssetsFechaAdquisicionEndInput) allAssetsFechaAdquisicionEndInput.value = '';
            loadPropertiesData();
        });
    }

    if (showAllModalPrintButton) {
        showAllModalPrintButton.addEventListener('click', () => {
            window.print();
        });
    }

    if (allAssetsTableBody) {
        allAssetsTableBody.addEventListener('click', (e) => {
            const targetButton = e.target.closest('.select-property-btn');
            if (targetButton) {
                const propertyId = targetButton.dataset.propertyId;
                const propertyCode = targetButton.dataset.propertyCode;
                const propertyDesc = targetButton.dataset.propertyDesc;

                alert(`Has seleccionado el bien: ${propertyDesc} (Cód: ${propertyCode}, ID: ${propertyId})`);
                MicroModal.close('showAll-modal');
            }
        });
    }
}

// --- Filter Option Loaders ---

async function loadEstatusFisicoOptions() {
    const selectElement = document.getElementById('allAssetsEstatusSelect');

    if (!selectElement) {
        console.error('Error: El elemento <select> con ID "allAssetsEstatusSelect" no fue encontrado en el DOM.');
        return;
    }

    try {
        const fetchUrl = '/systemahidrofalcon/api/activo?action=getEstatusData';

        const response = await fetch(fetchUrl);
        if (!response.ok) {
            throw new Error(`Error de red o servidor al cargar estatus físico: ${response.status} ${response.statusText}`);
        }

        const result = await response.json();

        if (result.success && Array.isArray(result.data)) {
            selectElement.innerHTML = '<option value="">Todos</option>';
            result.data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.nombre;
                selectElement.appendChild(option);
            });
        } else {
            console.error('Error al cargar estatus físico (backend indicó fallo o estructura inesperada):', result.message || 'Mensaje no disponible');
            selectElement.innerHTML = '<option value="">Error al cargar estatus físico</option>';
        }
    } catch (error) {
        console.error('Hubo un problema con la operación de fetch de estatus físico:', error);
        selectElement.innerHTML = '<option value="">Error de red al cargar estatus físico</option>';
    }
}

async function loadEstatusAdmOptions() {
    const selectElement = document.getElementById('allAssetsEstatusAdmSelect');

    if (!selectElement) {
        console.error('Error: El elemento <select> con ID "allAssetsEstatusAdmSelect" no fue encontrado en el DOM.');
        return;
    }

    try {
        const fetchUrl = '/systemahidrofalcon/api/activo?action=getEstatusAdm';

        const response = await fetch(fetchUrl);
        if (!response.ok) {
            throw new Error(`Error de red o servidor al cargar estatus administrativo: ${response.status} ${response.statusText}`);
        }

        const result = await response.json();

        if (result.success && Array.isArray(result.data)) {
            selectElement.innerHTML = '<option value="">Todos</option>';
            result.data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.estatus;
                selectElement.appendChild(option);
            });
        } else {
            console.error('Error al cargar estatus administrativo (backend indicó fallo o estructura inesperada):', result.message || 'Mensaje no disponible');
            selectElement.innerHTML = '<option value="">Error al cargar estatus administrativo</option>';
        }
    } catch (error) {
        console.error('Hubo un problema con la operación de fetch de estatus administrativo:', error);
        selectElement.innerHTML = '<option value="">Error de red al cargar estatus administrativo</option>';
    }
}

async function populateGerenciaFilter() {
    if (!gerenciaFilter) return;
    try {
        const response = await fetch('/systemahidrofalcon/api/gerencias?action=gerenciasSolo');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const result = await response.json();

        // Verificar si la llamada fue exitosa y si hay datos
        if (!result.success || !Array.isArray(result.gerencias)) {
            throw new Error('La respuesta del servidor no contiene datos válidos.');
        }

        const gerencias = result.gerencias;

        // Limpiar el select antes de añadir las nuevas opciones
        gerenciaFilter.innerHTML = '';

        // Añadir la opción por defecto
        const defaultOption = document.createElement('option');
        defaultOption.value = "";
        defaultOption.textContent = "Todas las Gerencias";
        gerenciaFilter.appendChild(defaultOption);

        // Llenar el select con los datos obtenidos
        gerencias.forEach(gerencia => {
            const option = document.createElement('option');
            option.value = gerencia.id;
            // Usar la clave correcta del objeto (nombre_gerencia)
            option.textContent = gerencia.nombre_gerencia; 
            gerenciaFilter.appendChild(option);
        });

    } catch (error) {
        console.error("Error fetching gerencias:", error);
        // Opcional: mostrar un mensaje de error en el select
        if (gerenciaFilter) {
            gerenciaFilter.innerHTML = '<option value="">Error al cargar gerencias</option>';
        }
    }
}