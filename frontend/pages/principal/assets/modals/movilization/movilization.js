// --- Elementos DOM Globales ---
let movilizarForm;
let displayEmployeeNameInput;
let hiddenEmployeeIdInput;
let displayAssetNameInput;
let hiddenAssetIdInput;
let errEmployeeBox;
let errAssetBox;

// Elementos de búsqueda de empleados
let toggleEmployeeSearchBtn;
let employeeSearchSection;
let employeeSearchInput;
let gerenciaFilterSelect;
let employeeTableBody;
let noEmployeeResultsMessage;
let prevEmployeePageBtn;
let nextEmployeePageBtn;
let employeePageNumbers;

// Elementos de búsqueda de activos
let toggleAssetSearchBtn;
let assetSearchSection;
let assetSearchInput;
let estatusFilterSelect;
let assetTableBody;
let noAssetResultsMessage;
let prevAssetPageBtn;
let nextAssetPageBtn;
let assetPageNumbers;

let movilizarSubmitButton;
let modalCloseButton;
let extraActions;

// Variables de estado para paginación
let currentEmployeePage = 1;
let currentAssetPage = 1;
const itemsPerPage = 5;
let totalEmployeePages = 1;
let totalAssetPages = 1;

// --- Funciones de Utilidad ---
function displayError(fieldElement, message) {
    if (!fieldElement) return;
    const errorBox = fieldElement.parentElement.querySelector('.err-box');
    if (errorBox) {
        errorBox.textContent = message;
        errorBox.classList.add('show-error');
    }
}

function clearErrors() {
    document.querySelectorAll('#movilization-modal .err-box').forEach(box => {
        box.textContent = '';
        box.classList.remove('show-error');
    });
}

function toggleVisibility(element, show = true) {
    if (!element) return;
    element.style.display = show ? '' : 'none';
    element.classList.toggle('show', show);
    element.classList.toggle('hidden', !show);
}

// --- Asignación de Elementos DOM ---
function assignModalDOMelements() {
    // Elementos principales
    movilizarForm = document.getElementById('movilizar-form');
    displayEmployeeNameInput = document.getElementById('display_employee_name__movilization');
    hiddenEmployeeIdInput = document.getElementById('hidden_employee_id_common__movilization');
    displayAssetNameInput = document.getElementById('display_asset_name__movilization');
    hiddenAssetIdInput = document.getElementById('hidden_asset_id__movilization');
    errEmployeeBox = document.getElementById('err-employee__movilization');
    errAssetBox = document.getElementById('err-asset__movilization');
    movilizarSubmitButton = document.getElementById('movilizar-submit-btn');

    // Elementos de búsqueda de empleados
    toggleEmployeeSearchBtn = document.getElementById('toggle-search-employee-btn__movilization');
    employeeSearchSection = document.getElementById('employee-search-section__movilization');
    employeeSearchInput = document.getElementById('employee-search-input__movilization');
    gerenciaFilterSelect = document.getElementById('gerencia-filter__movilization');
    employeeTableBody = document.getElementById('employee-table-body__movilization');
    noEmployeeResultsMessage = document.getElementById('no-results-message__movilization');
    prevEmployeePageBtn = document.getElementById('prev-page-employee-btn__movilization');
    nextEmployeePageBtn = document.getElementById('next-page-employee-btn__movilization');
    employeePageNumbers = document.getElementById('page-numbers__movilization');

    // Elementos de búsqueda de activos
    toggleAssetSearchBtn = document.getElementById('toggle-asset-search-btn__movilization');
    assetSearchSection = document.getElementById('asset-search-section__movilization');
    assetSearchInput = document.getElementById('asset-search-input__movilization');
    estatusFilterSelect = document.getElementById('estatus-filter__movilization');
    assetTableBody = document.getElementById('asset-table-body__movilization');
    noAssetResultsMessage = document.getElementById('no-asset-results-message__movilization');
    prevAssetPageBtn = document.getElementById('prev-page-asset-btn__movilization');
    nextAssetPageBtn = document.getElementById('next-page-asset-btn__movilization');
    assetPageNumbers = document.getElementById('asset-page-numbers__movilization');
    extraActions = document.getElementById('extra-actions__movilization');
}

// --- Carga de Datos ---
async function loadGerenciaFilterOptions() {
    if (!gerenciaFilterSelect) return;
    
    try {
        // Hacer la petición al endpoint correcto
        const response = await fetch('/systemahidrofalcon/api/gerencias?action=gerenciasSolo');
        
        if (!response.ok) throw new Error('Error al cargar gerencias');
        
        const result = await response.json();
        
        // Verificar si la respuesta fue exitosa según la estructura del backend
        if (!result.success) {
            throw new Error(result.message || 'Error en la respuesta del servidor');
        }
        
        // Limpiar el select y añadir opción por defecto
        gerenciaFilterSelect.innerHTML = '<option value="">Todas las Gerencias</option>';
        
        // Iterar sobre las gerencias (que están en result.gerencias)
        result.gerencias.forEach(gerencia => {
            const option = document.createElement('option');
            option.value = gerencia.id;
            option.textContent = gerencia.nombre_gerencia; // Usar nombre_gerencia en lugar de nombre
            gerenciaFilterSelect.appendChild(option);
        });
    } catch (error) {
        console.error('Error al cargar gerencias:', error);
        // Mostrar mensaje de error al usuario si es necesario
    }
}

async function loadEstatusFilterOptions() {
    if (!estatusFilterSelect) return;
    
    try {
        const response = await fetch('/systemahidrofalcon/api/activos?action=getEstatusData');
        if (!response.ok) throw new Error('Error al cargar estatus');
        const data = await response.json();
        
        estatusFilterSelect.innerHTML = '<option value="">Todos los Estatus</option>';
        data.data.forEach(estatus => {
            const option = document.createElement('option');
            option.value = estatus.id;
            option.textContent = estatus.nombre;
            estatusFilterSelect.appendChild(option);
        });
    } catch (error) {
        console.error('Error al cargar estatus:', error);
    }
}

// --- Funciones para Empleados ---
async function fetchEmployees() {
    if (!employeeTableBody) return;

    employeeTableBody.innerHTML = '<tr><td colspan="7">Cargando empleados...</td></tr>';
    toggleVisibility(noEmployeeResultsMessage, false);

    const searchTerm = employeeSearchInput?.value.trim() || '';
    const gerenciaId = gerenciaFilterSelect?.value || '';

    const params = new URLSearchParams({
        search: searchTerm,
        gerencia: gerenciaId,
        page: currentEmployeePage,
        limit: itemsPerPage,
        show_id: true
    });

    try {
        const response = await fetch(`/systemahidrofalcon/api/empleados?action=getEmployees&${params}`);
        if (!response.ok) throw new Error('Error al buscar empleados');
        const data = await response.json();

        if (data?.employees && data.total_employees !== undefined) {
            totalEmployeePages = Math.ceil(data.total_employees / itemsPerPage);
            renderEmployeeTable(data.employees);
            updateEmployeePaginationControls();
        } else {
            throw new Error(data?.message || 'Datos de empleados inválidos');
        }
    } catch (error) {
        console.error('Error al buscar empleados:', error);
        employeeTableBody.innerHTML = '';
        toggleVisibility(noEmployeeResultsMessage, true);
        noEmployeeResultsMessage.textContent = 'Error al cargar empleados';
    }
}

function renderEmployeeTable(employees) {
    if (!employeeTableBody) return;

    employeeTableBody.innerHTML = '';
    if (employees.length === 0) {
        toggleVisibility(noEmployeeResultsMessage, true);
        return;
    }

    employees.forEach(employee => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${employee.cedula || ''}</td>
            <td>${employee.nombres || ''}</td>
            <td>${employee.apellidos || ''}</td>
            <td>${employee.telefono || ''}</td>
            <td>${employee.nombre_cargo || ''}</td>
            <td>${employee.nombre_gerencia || ''}</td>
            <td>
                <button type="button" class="select-employee-btn button"
                        data-id="${employee.id}"
                        data-name="${employee.nombres} ${employee.apellidos}">
                    Seleccionar
                </button>
            </td>
        `;
        employeeTableBody.appendChild(row);
    });

    employeeTableBody.querySelectorAll('.select-employee-btn').forEach(btn => {
        btn.addEventListener('click', selectEmployee);
    });
}

function selectEmployee(event) {
    const employeeId = event.target.dataset.id;
    const employeeName = event.target.dataset.name;

    if (displayEmployeeNameInput && hiddenEmployeeIdInput) {
        displayEmployeeNameInput.value = employeeName;
        hiddenEmployeeIdInput.value = employeeId;
        toggleVisibility(employeeSearchSection, false);
        checkSubmitButtonVisibility();
    }
}

function updateEmployeePaginationControls() {
    if (!prevEmployeePageBtn || !nextEmployeePageBtn || !employeePageNumbers) return;

    prevEmployeePageBtn.disabled = currentEmployeePage === 1;
    nextEmployeePageBtn.disabled = currentEmployeePage === totalEmployeePages || totalEmployeePages === 0;

    // Prevenir el comportamiento por defecto del botón
    prevEmployeePageBtn.onclick = (e) => {
        e.preventDefault();
        if (currentEmployeePage > 1) {
            currentEmployeePage--;
            fetchEmployees();
        }
    };

    nextEmployeePageBtn.onclick = (e) => {
        e.preventDefault();
        if (currentEmployeePage < totalEmployeePages) {
            currentEmployeePage++;
            fetchEmployees();
        }
    };

    renderPageNumbers(employeePageNumbers, currentEmployeePage, totalEmployeePages, (page) => {
        currentEmployeePage = page;
        fetchEmployees();
    });
}

// --- Funciones para Activos ---
async function fetchAssets() {
    if (!assetTableBody) return;

    assetTableBody.innerHTML = '<tr><td colspan="6">Cargando activos...</td></tr>';
    toggleVisibility(noAssetResultsMessage, false);

    const searchTerm = assetSearchInput?.value.trim() || '';
    const estatusId = estatusFilterSelect?.value || '';

    const params = new URLSearchParams({
        search: searchTerm,
        estatus_fisico_id: estatusId,
        page: currentAssetPage,  // Cambiado de currentPage a currentAssetPage
        limit: itemsPerPage,
        custodian_filter: 'all'
    });

    try {
        const response = await fetch(`/systemaHidrofalcon/api/activo?action=getFilteredAssets&${params}`);
        if (!response.ok) throw new Error('Error al buscar activos');
        const data = await response.json();

        if (data?.success) {
            totalAssetPages = Math.ceil(data.total_assets / itemsPerPage);
            renderAssetTable(data.assets);
            updateAssetPaginationControls();
        } else {
            throw new Error(data?.message || 'Datos de activos inválidos');
        }
    } catch (error) {
        console.error('Error al buscar activos:', error);
        assetTableBody.innerHTML = '';
        toggleVisibility(noAssetResultsMessage, true);
        noAssetResultsMessage.textContent = 'Error al cargar activos';
    }
}

function renderAssetTable(assets) {
    if (!assetTableBody) return;

    assetTableBody.innerHTML = '';
    if (assets.length === 0) {
        toggleVisibility(noAssetResultsMessage, true);
        return;
    }

    assets.forEach(asset => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${asset.codigo_activo_fijo || ''}</td>
            <td>${asset.descripcion || ''}</td>
            <td>${asset.serial || ''}</td>
            <td>${asset.marca || ''}</td>
            <td>${asset.modelo || ''}</td>
            <td>${asset.estatus_fisico_name || ''}</td>
            <td>
                <button type="button" class="select-asset-btn button"
                        data-id="${asset.id}"
                        data-cod-act-f="${asset.codigo_activo_fijo}"
                        data-modelo="${asset.modelo}"
                        data-name="${asset.codigo_activo_fijo} - ${asset.modelo}">
                    Seleccionar
                </button>
            </td>
        `;
        assetTableBody.appendChild(row);
    });

    assetTableBody.querySelectorAll('.select-asset-btn').forEach(btn => {
        btn.addEventListener('click', selectAsset);
    });
}

function selectAsset(event) {
    const assetId = event.target.dataset.id;
    const assetName = event.target.dataset.name;

    if (displayAssetNameInput && hiddenAssetIdInput) {
        displayAssetNameInput.value = assetName;
        hiddenAssetIdInput.value = assetId;
        toggleVisibility(assetSearchSection, false);
        checkSubmitButtonVisibility();
    }
}

function updateAssetPaginationControls() {
    if (!prevAssetPageBtn || !nextAssetPageBtn || !assetPageNumbers) return;

    prevAssetPageBtn.disabled = currentAssetPage === 1;
    nextAssetPageBtn.disabled = currentAssetPage === totalAssetPages || totalAssetPages === 0;

    // Prevenir el comportamiento por defecto del botón
    prevAssetPageBtn.onclick = (e) => {
        e.preventDefault();
        if (currentAssetPage > 1) {
            currentAssetPage--;
            fetchAssets();
        }
    };

    nextAssetPageBtn.onclick = (e) => {
        e.preventDefault();
        if (currentAssetPage < totalAssetPages) {
            currentAssetPage++;
            fetchAssets();
        }
    };

    renderPageNumbers(assetPageNumbers, currentAssetPage, totalAssetPages, (page) => {
        currentAssetPage = page;
        fetchAssets();
    });
}

// --- Funciones de Paginación ---
function renderPageNumbers(container, currentPage, totalPages, callback) {
    if (!container) return;
    container.innerHTML = '';

    if (totalPages <= 1) return;

    const maxPagesToShow = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
    let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);

    if (endPage - startPage + 1 < maxPagesToShow) {
        startPage = Math.max(1, endPage - maxPagesToShow + 1);
    }

    if (startPage > 1) {
        const span = document.createElement('span');
        span.classList = 'abreviation'
        span.textContent = '...';
        span.classList = 'abreviation'
        container.appendChild(span);
    }

    for (let i = startPage; i <= endPage; i++) {
        const btn = document.createElement('button');
        btn.textContent = i;
        btn.className = 'page-num-btn' + (i === currentPage ? ' active' : '');
        btn.type = 'button'; // Importante: evitar que sea type="submit"
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            callback(i);
        });
        container.appendChild(btn);
    }

    if (endPage < totalPages) {
        const span = document.createElement('span');
        span.textContent = '...';
        span.classList = 'abreviation'
        container.appendChild(span);
    }
}

// --- Funciones de Validación y Envío ---
function checkSubmitButtonVisibility() {
    if (!movilizarSubmitButton) return;
    const hasEmployee = hiddenEmployeeIdInput?.value.trim() !== '';
    const hasAsset = hiddenAssetIdInput?.value.trim() !== '';
    toggleVisibility(movilizarSubmitButton, hasEmployee && hasAsset);
}

function validateFormOnSubmit() {
    let isValid = true;
    clearErrors();

    if (!hiddenEmployeeIdInput?.value.trim()) {
        displayError(displayEmployeeNameInput, 'Debe seleccionar un trabajador');
        isValid = false;
    }

    if (!hiddenAssetIdInput?.value.trim()) {
        displayError(displayAssetNameInput, 'Debe seleccionar un activo');
        isValid = false;
    }

    return isValid;
}

async function handleMovilizationSubmit(event) {
    event.preventDefault();

    if (!validateFormOnSubmit()) {
        iziToast.warning({
            title: 'Formulario Incompleto',
            message: 'Por favor, complete todos los campos requeridos',
            position: 'topRight',
            timeout: 5000
        });
        return;
    }

    if (movilizarSubmitButton) {
        movilizarSubmitButton.disabled = true;
        movilizarSubmitButton.textContent = 'Movilizando...';
    }

    const formData = new FormData();
    formData.append('employeeId', hiddenEmployeeIdInput.value);
    formData.append('assetId', hiddenAssetIdInput.value);

    try {
        const response = await fetch('/systemahidrofalcon/api/activo?action=assignAssetToEmployee', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            iziToast.success({
                title: 'Éxito',
                message: result.message || 'Activo movilizado correctamente',
                position: 'topRight',
                timeout: 5000
            });
            document.getElementById('movilization-modal').classList.remove('is-open');
            resetForm();
        } else {
            throw new Error(result.message || 'Error al movilizar el activo');
        }
    } catch (error) {
        iziToast.error({
            title: 'Error',
            message: error.message,
            position: 'topRight',
            timeout: 5000
        });
    } finally {
        if (movilizarSubmitButton) {
            movilizarSubmitButton.disabled = false;
            movilizarSubmitButton.textContent = 'Movilizar';
        }
    }
}

function resetForm() {
    if (movilizarForm) movilizarForm.reset();
    if (displayEmployeeNameInput) displayEmployeeNameInput.value = '';
    if (hiddenEmployeeIdInput) hiddenEmployeeIdInput.value = '';
    if (displayAssetNameInput) displayAssetNameInput.value = '';
    if (hiddenAssetIdInput) hiddenAssetIdInput.value = '';
    toggleVisibility(employeeSearchSection, false);
    toggleVisibility(assetSearchSection, false);
    toggleVisibility(movilizarSubmitButton, false);
    clearErrors();
    currentEmployeePage = 1;
    currentAssetPage = 1;
}

// --- Configuración de Event Listeners ---
function setupSearchEventListeners() {
    // Empleados
    if (toggleEmployeeSearchBtn) {
        toggleEmployeeSearchBtn.addEventListener('click', () => {
            const isVisible = employeeSearchSection.classList.contains('show');
            toggleVisibility(employeeSearchSection, !isVisible);
            if (!isVisible) fetchEmployees();
        });
    }

    if (employeeSearchInput) {
        let timeout;
        employeeSearchInput.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                currentEmployeePage = 1;
                fetchEmployees();
            }, 300);
        });
    }

    if (gerenciaFilterSelect) {
        gerenciaFilterSelect.addEventListener('change', () => {
            currentEmployeePage = 1;
            fetchEmployees();
        });
    }

    // Activos
    if (toggleAssetSearchBtn) {
        toggleAssetSearchBtn.addEventListener('click', () => {
            const isVisible = assetSearchSection.classList.contains('show');
            toggleVisibility(assetSearchSection, !isVisible);
            toggleVisibility(extraActions, !isVisible);
            if (!isVisible) fetchAssets();
        });
    }

    if (assetSearchInput) {
        let timeout;
        assetSearchInput.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                currentAssetPage = 1;
                fetchAssets();
            }, 300);
        });
    }

    if (estatusFilterSelect) {
        estatusFilterSelect.addEventListener('change', () => {
            currentAssetPage = 1;
            fetchAssets();
        });
    }
}

// --- Inicialización ---
function initializeMovilizationLogic() {
    loadGerenciaFilterOptions();
    loadEstatusFilterOptions();
    setupSearchEventListeners();
    
    if (movilizarForm) {
        movilizarForm.addEventListener('submit', handleMovilizationSubmit);
    }
}

// --- MicroModal Configuration ---
MicroModal.init({
    onShow: (modal) => {
        if (modal.id === 'movilization-modal') {
            assignModalDOMelements();
            initializeMovilizationLogic();
            resetForm();
            fetchEmployees();
            fetchAssets();
        }
    },
    onClose: (modal) => {
        if (modal.id === 'movilization-modal') {
            resetForm();
        }
    },
    disableScroll: true,
    disableFocus: true,
    awaitCloseAnimation: true
});
