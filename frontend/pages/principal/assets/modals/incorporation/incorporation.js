// incorporation.js

import showHide from '../shared/js/modalshow.js';

// --- GLOBAL DOM Elements ---
let estatusNewSelect;
let optionNuevo;
let optionExistente;
let switchIndicator;
let newAssetForm;
let existingAssetForm;
let toggleSearchBtn;
let employeeSearchSection;
let employeeSearchInput;
let gerenciaFilter;
let employeeTableBody;
let noResultsMessage;
let prevPageBtn;
let nextPageBtn;
let pageNumbersContainer;
let displayEmployeeNameInput;
let hiddenEmployeeIdCommon;
let trabajadorIdNewAssetForm;
let trabajadorIdExistingAssetForm;
let toggleAssetSearchBtn;
let assetSearchSection;
let displayAssetNameInput;
let hiddenAssetIdInput;
let assetSearchInput;
let assetEstatusFilter;
let assetTableBody;
let noAssetResultsMessage;
let prevAssetPageBtn;
let nextAssetPageBtn;
let assetPageNumbersContainer;
let newAssetFirstPartContainer;
let newAssetSecondPartContainer;
let nextButton;
let goBackButton;
let submitNewAssetButton;
let submitExistingButton;
let modalCloseButton;

// Pagination state variables
let currentPage = 1;
const recordsPerPage = 5;
let totalEmployees = 0;

let currentAssetPage = 1;
const assetsPerPage = 5;
let totalAssets = 0;

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

function toggleVisibility(element, show = true) {
    if (!element) return;
    if (show) {
        element.classList.remove('hidden');
        element.classList.add('show');
    } else {
        element.classList.remove('show');
        element.classList.add('hidden');
    }
}

// --- Assign DOM elements dynamically when the modal is opened ---
function assignModalDOMelements() {
    // Elements for Estatus Select
    estatusNewSelect = document.getElementById('estatus_new__incorporation');
    optionNuevo = document.getElementById('option-nuevo');
    optionExistente = document.getElementById('option-existente');
    switchIndicator = document.getElementById('switch-indicator');
    newAssetForm = document.getElementById('new-asset-form__incorporation');
    existingAssetForm = document.getElementById('existing-asset-form');

    // Elements for Employee Selection
    toggleSearchBtn = document.getElementById('toggle-search-employee-btn__incorporation');
    employeeSearchSection = document.getElementById('employee-search-section');
    employeeSearchInput = document.getElementById('employee-search-input');
    gerenciaFilter = document.getElementById('gerencia-filter');
    employeeTableBody = document.getElementById('employee-table-body');
    noResultsMessage = document.getElementById('no-results-message');
    prevPageBtn = document.getElementById('prev-page-btn__incorporation');
    nextPageBtn = document.getElementById('next-page-btn__incorporation');
    pageNumbersContainer = document.getElementById('page-numbers__incorporation');
    displayEmployeeNameInput = document.getElementById('display_employee_name');
    hiddenEmployeeIdCommon = document.getElementById('hidden_employee_id_common');
    trabajadorIdNewAssetForm = document.getElementById('trabajador_id_new_asset_form');
    trabajadorIdExistingAssetForm = document.getElementById('trabajador_id_existing_asset_form');

    // Elements for Asset Selection
    toggleAssetSearchBtn = document.getElementById('toggle-asset-search-btn');
    assetSearchSection = document.getElementById('asset-search-section');
    displayAssetNameInput = document.getElementById('display_asset_name');
    hiddenAssetIdInput = document.getElementById('hidden_asset_id');

    // Elements for Asset Search Section
    assetSearchInput = document.getElementById('asset-search-input');
    assetEstatusFilter = document.getElementById('estatus-filter');
    assetTableBody = document.getElementById('asset-table-body');
    noAssetResultsMessage = document.getElementById('no-asset-results-message');
    prevAssetPageBtn = document.getElementById('prev-asset-page-btn__incorporation');
    nextAssetPageBtn = document.getElementById('next-asset-page-btn__incorporation');
    assetPageNumbersContainer = document.getElementById('asset-page-numbers__incorporation');

    // Form Navigation and Submission
    newAssetFirstPartContainer = newAssetForm ? newAssetForm.querySelector('.frstpart-container') : null;
    newAssetSecondPartContainer = newAssetForm ? newAssetForm.querySelector('.scndpart-container') : null;
    nextButton = newAssetForm ? newAssetForm.querySelector('.next-button') : null;
    goBackButton = newAssetForm ? newAssetForm.querySelector('.goback-button') : null;
    submitNewAssetButton = document.getElementById('submit-button__incorporation');
    submitExistingButton = document.getElementById('existing-submit');

    // Modal close button
    modalCloseButton = document.querySelector('#incorporation-modal .modal__close');
}

// Function to update the switch (Nuevo/Existente) state
function updateSwitchState(activeOptionId) {
    if (optionNuevo) optionNuevo.classList.remove('active');
    if (optionExistente) optionExistente.classList.remove('active');

    if (activeOptionId === 'option-nuevo') {
        if (optionNuevo) optionNuevo.classList.add('active');
        if (switchIndicator) switchIndicator.style.left = '0%';
        toggleVisibility(existingAssetForm, false);
        toggleVisibility(newAssetForm, true);
        toggleVisibility(employeeSearchSection, false);
        toggleVisibility(assetSearchSection, false);
    } else {
        if (optionExistente) optionExistente.classList.add('active');
        if (optionNuevo && switchIndicator) {
            switchIndicator.style.left = `${optionNuevo.clientWidth}px`;
        }
        toggleVisibility(newAssetForm, false);
        toggleVisibility(existingAssetForm, true);
        toggleVisibility(employeeSearchSection, false);
        toggleVisibility(assetSearchSection, false);

        if (displayAssetNameInput) displayAssetNameInput.value = '';
        if (hiddenAssetIdInput) hiddenAssetIdInput.value = '';
    }
    clearErrors();
}

// --- Data Loading Functions ---
async function populateSedeAdmSelect() {
    const sedeAdmSelect = document.getElementById('sede_adm__incorporation');
    const errSedeAdmBox = document.querySelector('.err-sede_adm');

    if (!sedeAdmSelect) {
        console.error('Error: Select element with ID "sede_adm__incorporation" not found.');
        return;
    }

    sedeAdmSelect.innerHTML = '<option value="">Cargando sedes... </option>';
    sedeAdmSelect.disabled = true;
    if (errSedeAdmBox) errSedeAdmBox.textContent = '';

    try {
        const fetchUrl = '/systemahidrofalcon/api/sede';

        const response = await fetch(fetchUrl);

        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`HTTP error! Status: ${response.status}. Response: ${errorText}`);
        }

        const data = await response.json();

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

async function loadEstatusOptions() {
    if (!estatusNewSelect) {
        console.error('Error: estatusNewSelect is null. Cannot load estatus options.');
        return;
    }
    const fetchUrl = '/systemahidrofalcon/api/activo?action=getEstatusData';
    try {
        const response = await fetch(fetchUrl);
        if (!response.ok) {
            throw new Error('Error de red o servidor: ' + response.status + ' ' + response.statusText);
        }
        const data = await response.json();
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
    } catch (error) {
        console.error('Hubo un problema con la operación de fetch de estatus:', error);
        estatusNewSelect.innerHTML = '<option value="">Error de red al cargar estatus</option>';
    }
}

async function fetchEmployees() {
    const searchTerm = employeeSearchInput ? employeeSearchInput.value.trim() : '';
    const gerenciaId = gerenciaFilter ? gerenciaFilter.value : '';

    const params = new URLSearchParams();
    if (searchTerm) params.append('search', searchTerm);
    if (gerenciaId) params.append('gerencia', gerenciaId);
    params.append('page', currentPage);
    params.append('limit', recordsPerPage);
    params.append('show_id', true);
    params.append('action', 'getEmployees');

    try {
        const response = await fetch(`/systemahidrofalcon/api/empleados?${params.toString()}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();

        totalEmployees = data.total_employees;

        renderEmployeeTable(data.employees);
        updatePaginationControls();

    } catch (error) {
        console.error("Error fetching employees:", error);
        if (employeeTableBody) employeeTableBody.innerHTML = '<tr><td colspan="7">Error al cargar trabajadores.</td></tr>';
        if (noResultsMessage) noResultsMessage.style.display = 'block';
        if (pageNumbersContainer) pageNumbersContainer.style.display = 'none';
        if (prevPageBtn) prevPageBtn.disabled = true;
        if (nextPageBtn) nextPageBtn.disabled = true;
    }
}

function renderEmployeeTable(employees) {
    if (!employeeTableBody || !pageNumbersContainer || !noResultsMessage) return;

    employeeTableBody.innerHTML = '';
    pageNumbersContainer.innerHTML = '';

    if (employees && employees.length > 0) {
        noResultsMessage.style.display = 'none';
        employees.forEach(employee => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${employee.cedula || ''}</td>
                <td>${employee.nombres || ''}</td>
                <td>${employee.apellidos || ''}</td>
                <td>${employee.telefono || ''}</td>
                <td>${employee.nombre_cargo || ''}</td>
                <td>${employee.nombre_gerencia || ''}</td>
                <td><button type="button" class="select-employee-btn button" data-id="${employee.id || ''}" data-name="${(employee.nombres || '') + ' ' + (employee.apellidos || '')}">Seleccionar</button></td>
            `;
            employeeTableBody.appendChild(row);
        });
        pageNumbersContainer.style.display = 'inline-block';
    } else {
        noResultsMessage.style.display = 'block';
        pageNumbersContainer.style.display = 'none';
    }
}

function updatePaginationControls() {
    if (!prevPageBtn || !nextPageBtn || !pageNumbersContainer) return;

    const totalPages = Math.ceil(totalEmployees / recordsPerPage);

    prevPageBtn.disabled = currentPage === 1;
    nextPageBtn.disabled = currentPage === totalPages || totalPages === 0;

    renderPageNumbers(totalPages);
}

function renderPageNumbers(totalPages) {
    if (!pageNumbersContainer) return;
    pageNumbersContainer.innerHTML = '';
    const maxPagesToShow = 5;

    let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
    let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);

    if (endPage - startPage + 1 < maxPagesToShow) {
        startPage = Math.max(1, endPage - maxPagesToShow + 1);
    }

    if (startPage > 1) {
        const span = document.createElement('span');
        span.textContent = '...';
        span.classList = 'abreviation'
        pageNumbersContainer.appendChild(span);
    }

    for (let i = startPage; i <= endPage; i++) {
        const pageNumBtn = document.createElement('button');
        pageNumBtn.textContent = i;
        pageNumBtn.classList.add('page-num-btn');
        if (i === currentPage) {
            pageNumBtn.classList.add('active');
        }
        pageNumBtn.addEventListener('click', () => {
            currentPage = i;
            fetchEmployees();
        });
        pageNumbersContainer.appendChild(pageNumBtn);
    }

    if (endPage < totalPages) {
        const span = document.createElement('span');
        span.textContent = '...';
        span.classList = 'abreviation'
        pageNumbersContainer.appendChild(span);
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

        if (!result.success || !Array.isArray(result.gerencias)) {
            throw new Error('La respuesta del servidor no contiene datos válidos.');
        }

        const gerencias = result.gerencias;

        gerenciaFilter.innerHTML = '';

        const defaultOption = document.createElement('option');
        defaultOption.value = "";
        defaultOption.textContent = "Todas las Gerencias";
        gerenciaFilter.appendChild(defaultOption);

        gerencias.forEach(gerencia => {
            const option = document.createElement('option');
            option.value = gerencia.id;
            option.textContent = gerencia.nombre_gerencia; 
            gerenciaFilter.appendChild(option);
        });

    } catch (error) {
        console.error("Error fetching gerencias:", error);
    }
}

async function loadAssetEstatusFilterOptions() {
    if (!assetEstatusFilter) return;
    const fetchUrl = '/systemahidrofalcon/api/activo?action=getEstatusData';

    try {
        const response = await fetch(fetchUrl);
        if (!response.ok) {
            throw new Error('Error de red o servidor: ' + response.status + ' ' + response.statusText);
        }
        const data = await response.json();
        if (data.success) {
            assetEstatusFilter.innerHTML = '<option value="">Todos los Estatus</option>';
            data.data.forEach(estatus => {
                const option = document.createElement('option');
                option.value = estatus.id;
                option.textContent = estatus.nombre;
                assetEstatusFilter.appendChild(option);
            });
        } else {
            console.error('Error al cargar estatus para el filtro de activos desde la API:', data.message);
            assetEstatusFilter.innerHTML = '<option value="">Error al cargar estatus (API)</option>';
        }
    } catch (error) {
        console.error('Hubo un problema con la operación de fetch de estatus para el filtro de activos:', error);
        assetEstatusFilter.innerHTML = '<option value="">Error de red al cargar estatus</option>';
    }
}

async function fetchAssets() {
    const searchTerm = assetSearchInput ? assetSearchInput.value.trim() : '';
    const estatus = assetEstatusFilter ? assetEstatusFilter.value : '';

    const params = new URLSearchParams();
    if (searchTerm) params.append('search', searchTerm);
    if (estatus) params.append('estatus_fisico_id', estatus);
    params.append('page', currentAssetPage);
    params.append('limit', assetsPerPage);
    params.append('custodian_filter', 'none');

    try {
        const response = await fetch(`/systemahidrofalcon/api/activo?action=getFilteredAssets&${params.toString()}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();

        if (data.success) {
            totalAssets = data.total_assets;
            renderAssetTable(data.assets);
            updateAssetPaginationControls();
        } else {
            console.error('Error fetching assets:', data.message);
            if (assetTableBody) assetTableBody.innerHTML = '<tr><td colspan="6">No se encontraron activos. ' + (data.message || '') + '</td></tr>';
            if (noAssetResultsMessage) {
                noAssetResultsMessage.style.display = 'block';
                noAssetResultsMessage.textContent = data.message || 'No se encontraron activos.';
            }
            updateAssetPaginationControls();
        }
    } catch (error) {
        console.error('Network or server error:', error);
        if (assetTableBody) assetTableBody.innerHTML = '<tr><td colspan="6">Error de red al cargar activos.</td></tr>';
        if (noAssetResultsMessage) {
            noAssetResultsMessage.style.display = 'block';
            noAssetResultsMessage.textContent = 'Error de red al cargar activos.';
        }
        updateAssetPaginationControls();
    }
}

function renderAssetTable(assets) {
    if (!assetTableBody || !assetPageNumbersContainer || !noAssetResultsMessage) return;

    assetTableBody.innerHTML = '';
    assetPageNumbersContainer.innerHTML = '';

    if (assets && assets.length > 0) {
        noAssetResultsMessage.style.display = 'none';
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
                            data-id="${asset.id || ''}"
                            data-cod-act-f="${asset.codigo_activo_fijo || ''}"
                            data-modelo="${asset.modelo || ''}"
                            data-name="${(asset.codigo_activo_fijo || '') + ' - ' + (asset.modelo || '')}">
                        Seleccionar
                    </button>
                </td>
            `;
            assetTableBody.appendChild(row);
        });
        assetPageNumbersContainer.style.display = 'inline-block';

        assetTableBody.querySelectorAll('.select-asset-btn').forEach(button => {
            button.addEventListener('click', (event) => {
                const assetId = event.target.dataset.id;
                const selectedCodActF = event.target.dataset.codActF;
                const selectedModelo = event.target.dataset.modelo;
                const assetName = `${selectedCodActF} - ${selectedModelo}`;

                if (displayAssetNameInput) displayAssetNameInput.value = assetName;
                if (hiddenAssetIdInput) hiddenAssetIdInput.value = assetId;

                toggleVisibility(assetSearchSection, false);
                if (submitExistingButton) toggleVisibility(submitExistingButton, true);
                clearErrors(displayAssetNameInput);
            });
        });

    } else {
        noAssetResultsMessage.style.display = 'block';
        assetPageNumbersContainer.style.display = 'none';
    }
}

function updateAssetPaginationControls() {
    if (!prevAssetPageBtn || !nextAssetPageBtn || !assetPageNumbersContainer) return;

    const totalPages = Math.ceil(totalAssets / assetsPerPage);

    prevAssetPageBtn.disabled = currentAssetPage === 1;
    nextAssetPageBtn.disabled = currentAssetPage === totalPages || totalPages === 0;

    renderAssetPageNumbers(totalPages);

    if (totalPages > 1) {
        assetPageNumbersContainer.style.display = 'inline-block';
    } else {
        assetPageNumbersContainer.style.display = 'none';
    }
}

function renderAssetPageNumbers(totalPages) {
    if (!assetPageNumbersContainer) return;
    assetPageNumbersContainer.innerHTML = '';
    const maxPagesToShow = 5;

    let startPage = Math.max(1, currentAssetPage - Math.floor(maxPagesToShow / 2));
    let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);

    if (endPage - startPage + 1 < maxPagesToShow) {
        startPage = Math.max(1, endPage - maxPagesToShow + 1);
    }

    if (startPage > 1) {
        const span = document.createElement('span');
        span.textContent = '...';
        span.classList = 'abreviation'
        assetPageNumbersContainer.appendChild(span);
    }

    for (let i = startPage; i <= endPage; i++) {
        const pageNumBtn = document.createElement('button');
        pageNumBtn.textContent = i;
        pageNumBtn.classList.add('page-num-btn');
        pageNumBtn.type = 'button';
        if (i === currentAssetPage) {
            pageNumBtn.classList.add('active');
        }
        pageNumBtn.addEventListener('click', () => {
            currentAssetPage = i;
            fetchAssets();
        });
        assetPageNumbersContainer.appendChild(pageNumBtn);
    }

    if (endPage < totalPages) {
        const span = document.createElement('span');
        span.textContent = '...';
        span.classList = 'abreviation'
        assetPageNumbersContainer.appendChild(span);
    }
}

// --- Validation Functions ---
async function validateNewAssetFirstPart() {
    let isValid = true;
    const form = newAssetForm;

    const cod_act_f = form.querySelector('#cod_act_f__incorporation');
    const color = form.querySelector('#color__incorporation');
    const sedeAdm = form.querySelector('#sede_adm__incorporation');
    const marca = form.querySelector('#marca__incorporation');
    const modelo = form.querySelector('#modelo__incorporation');
    const serial = form.querySelector('#serial__incorporation');
    const estatus = form.querySelector('#estatus_new__incorporation');
    const descripcion = form.querySelector('#descripcion__incorporation');

    clearErrors(cod_act_f);
    clearErrors(color);
    clearErrors(sedeAdm);
    clearErrors(marca);
    clearErrors(modelo);
    clearErrors(serial);
    clearErrors(estatus);
    clearErrors(descripcion);

    if (!cod_act_f || cod_act_f.value.trim() === '') {
        displayError(cod_act_f, 'Ingrese un Código');
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
        displayError(descripcion, 'Por favor, ingrese un activo');
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
    }

    if (!isValid) {
        return false;
    }

    try {
        const response = await fetch('/systemahidrofalcon/api/activo?action=checkAssetExists', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                cod_activo_fijo: cod_act_f.value.trim(),
                serial: serial.value.trim()
            }),
        });

        const data = await response.json();

        if (data.exists) {
            isValid = false;
            if (data.field === 'cod_activo_fijo') {
                displayError(cod_act_f, 'Código ya está registrado.');
            } else if (data.field === 'serial') {
                displayError(serial, 'Este Serial ya está registrado.');
            }
        }

    } catch (error) {
        console.error('Error al verificar la existencia del activo:', error);
        isValid = false;
    }

    return isValid;
}

function validateNewAssetSecondPart() {
    let isValid = true;
    const form = newAssetForm;

    const observacion = form.querySelector('#observacion__incorporation');
    const doc = form.querySelector('#documento__incorporation');
    const fecha = form.querySelector('#fecha__incorporation');
    const monto = form.querySelector('#monto__incorporation');
    const codU = form.querySelector('#codigo_u_u__incorporation');

    function isValidDate(dateString) {
        const regex = /^\d{2}\/\d{2}\/\d{2}$/;
        if (!regex.test(dateString)) {
            return false;
        }
        return true;
    }
    
    clearErrors(observacion);
    clearErrors(doc);
    clearErrors(fecha);
    clearErrors(monto);
    clearErrors(codU);

    if (!observacion || observacion.value.trim() === '') {
        displayError(observacion, 'Ingrese una Observación.');
        isValid = false;
    }

    if (!codU || codU.value.trim() === '') {
        displayError(codU, 'El Cod. U. es Obligatorio.');
        isValid = false;
    }

    if (!doc || doc.value.trim() === '') {
        displayError(doc, 'Ingrese un documento.');
        isValid = false;
    }

    if (!fecha || fecha.value.trim() === '') {
        displayError(fecha, 'Ingrese una Fecha de Adquisición.');
        isValid = false;
    } else if (!isValidDate(fecha.value.trim())) {
        displayError(fecha, 'El formato de la fecha debe ser DD/MM/AA.');
        isValid = false;
    }

    if (!monto || monto.value.trim() === '') {
        displayError(monto, 'Ingrese un monto.');
        isValid = false;
    } else if (isNaN(parseFloat(monto.value))) {
        displayError(monto, 'El Monto debe ser un número.');
        isValid = false;
    } else if (parseFloat(monto.value) < 0) {
        displayError(monto, 'El Monto no puede ser negativo.');
        isValid = false;
    }

    return isValid;
}

function validateExistingAssetForm() {
    let isValid = true;

    clearErrors(displayEmployeeNameInput);
    clearErrors(displayAssetNameInput);

    if (!hiddenEmployeeIdCommon || hiddenEmployeeIdCommon.value.trim() === '' || isNaN(parseInt(hiddenEmployeeIdCommon.value))) {
        displayError(displayEmployeeNameInput, 'Debe seleccionar un trabajador.');
        isValid = false;
    }

    if (!hiddenAssetIdInput || hiddenAssetIdInput.value.trim() === '') {
        displayError(displayAssetNameInput, 'Debe seleccionar un activo existente.');
        isValid = false;
    }

    return isValid;
}

// --- Submit Handlers ---
async function submitNewAssetForm(event) {
    event.preventDefault();
    clearErrors();

    let formIsValid = true;

    if (!hiddenEmployeeIdCommon || hiddenEmployeeIdCommon.value.trim() === '' || isNaN(parseInt(hiddenEmployeeIdCommon.value))) {
        displayError(displayEmployeeNameInput, 'Debe seleccionar un trabajador.');
        formIsValid = false;
    } else {
        clearErrors(displayEmployeeNameInput);
    }

    const firstPartValidationResult = await validateNewAssetFirstPart();
    if (!firstPartValidationResult) {
        formIsValid = false;
    }

    if (!validateNewAssetSecondPart()) {
        formIsValid = false;
    }

    if (!formIsValid) {
        iziToast.warning({
            title: 'Formulario Incompleto',
            message: 'Por favor, complete todos los campos requeridos.',
            position: 'topRight',
            timeout: 5000
        });
        return;
    }

    if (submitNewAssetButton) {
        submitNewAssetButton.disabled = true;
        submitNewAssetButton.value = 'Registrando...';
    }

    const formData = new FormData(newAssetForm);
    formData.append('custodio_id', hiddenEmployeeIdCommon.value);
    formData.append('operation_type', 'Incorporacion');

    try {
        const response = await fetch('/systemahidrofalcon/api/activo?action=addAsset', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`El servidor respondió con el estado: ${response.status} ${response.statusText}. Detalles: ${errorText}`);
        }

        const result = await response.json();

        if (result.success) {
            iziToast.success({
                title: 'Agregación Exitosa',
                message: 'El activo ha sido agregado correctamente.',
                position: 'topRight',
                timeout: 5000
            });
            newAssetForm.reset();
            if(displayEmployeeNameInput) displayEmployeeNameInput.value = '';
            if(hiddenEmployeeIdCommon) hiddenEmployeeIdCommon.value = '';
            document.getElementById('incorporation-modal').classList.remove('is-open');

            if (newAssetFirstPartContainer && newAssetSecondPartContainer) {
                toggleVisibility(newAssetFirstPartContainer, true);
                toggleVisibility(newAssetSecondPartContainer, false);
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
                    const inputElement = newAssetForm.querySelector(`[name="${fieldName}"]`);
                    if (inputElement) {
                        displayError(inputElement, result.errors[fieldName]);
                    } else {
                        console.error(`Error para el campo '${fieldName}' pero no se encontró el elemento.`);
                    }
                }
            }
        }
    } catch (error) {
        console.error('Error de red o del servidor al registrar activo:', error);
        iziToast.error({
            title: 'Error de Conexión',
            message: 'Ocurrió un error de conexión al registrar el activo. Inténtalo de nuevo. ' + error.message,
            position: 'topRight',
            timeout: 7000
        });
    } finally {
        if (submitNewAssetButton) {
            submitNewAssetButton.disabled = false;
            submitNewAssetButton.value = 'Registrar Activo';
        }
    }
}

async function submitExistingForm(event) {
    event.preventDefault();

    if (!validateExistingAssetForm()) {
        iziToast.warning({
            title: 'Validación Fallida',
            message: 'Por favor, completa todos los campos requeridos.',
            position: 'topRight',
            timeout: 5000
        });
        return;
    }

    const assetId = hiddenAssetIdInput.value;
    const newCustodioId = hiddenEmployeeIdCommon.value;

    const formData = new FormData();
    formData.append('assetId', assetId);
    formData.append('employeeId', newCustodioId);
    formData.append('operation_type', 'Incorporacion');
    
    try {
        const response = await fetch('/systemahidrofalcon/api/activo?action=assignAssetToEmployee', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`Error de red o del servidor: ${response.status} - ${response.statusText}. Detalles: ${errorText}`);
        }

        const result = await response.json();

        if (result.success) {
            iziToast.success({
                title: 'Éxito',
                message: 'Activo asignado y custodio actualizado exitosamente.',
                position: 'topRight',
                timeout: 5000
            });

            if(existingAssetForm) existingAssetForm.reset();
            if(displayEmployeeNameInput) displayEmployeeNameInput.value = '';
            if(displayAssetNameInput) displayAssetNameInput.value = '';
            if(hiddenEmployeeIdCommon) hiddenEmployeeIdCommon.value = '';
            if(hiddenAssetIdInput) hiddenAssetIdInput.value = '';

            document.getElementById('incorporation-modal').classList.remove('is-open');
            clearErrors();
        } else {
            iziToast.error({
                title: 'Error',
                message: 'Error al asignar el activo y actualizar el custodio: ' + result.message,
                position: 'topRight',
                timeout: 7000
            });
        }
    } catch (error) {
        iziToast.error({
            title: 'Error de Conexión',
            message: 'Ocurrió un error de conexión al asignar el activo. ' + error.message,
            position: 'topRight',
            timeout: 7000
        });
    }
}

// --- Event Listeners Initialization Function ---
function initializeIncorporationLogic() {
    if (optionNuevo) {
        optionNuevo.addEventListener('click', () => {
            updateSwitchState('option-nuevo');
            if (newAssetFirstPartContainer && newAssetSecondPartContainer) {
                 toggleVisibility(newAssetFirstPartContainer, true);
                 toggleVisibility(newAssetSecondPartContainer, false);
            }
        });
    }

    if (optionExistente) {
        optionExistente.addEventListener('click', () => {
            updateSwitchState('option-existente');
        });
    }

    if (optionNuevo) {
        updateSwitchState('option-nuevo');
    }

    populateSedeAdmSelect();
    if (estatusNewSelect) {
        loadEstatusOptions();
    }
    if (assetEstatusFilter) {
        loadAssetEstatusFilterOptions();
    }

    if (toggleSearchBtn) {
        toggleSearchBtn.addEventListener('click', () => {
            toggleVisibility(employeeSearchSection, true);
            populateGerenciaFilter();
            fetchEmployees();
        });
    }

    if (employeeSearchInput) {
        employeeSearchInput.addEventListener('input', () => {
            currentPage = 1;
            fetchEmployees();
        });
    }
    if (gerenciaFilter) {
        gerenciaFilter.addEventListener('change', () => {
            currentPage = 1;
            fetchEmployees();
        });
    }

    if (prevPageBtn) {
        prevPageBtn.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                fetchEmployees();
            }
        });
    }
    if (nextPageBtn) {
        nextPageBtn.addEventListener('click', () => {
            const totalPages = Math.ceil(totalEmployees / recordsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                fetchEmployees();
            }
        });
    }

    if (employeeTableBody) {
        employeeTableBody.addEventListener('click', (event) => {
            if (event.target.classList.contains('select-employee-btn')) {
                const employeeId = event.target.dataset.id;
                const employeeName = event.target.dataset.name;
                
                if(displayEmployeeNameInput) displayEmployeeNameInput.value = employeeName;
                
                // Actualizar TODOS los campos hidden del trabajador
                if(hiddenEmployeeIdCommon) hiddenEmployeeIdCommon.value = employeeId;
                if(trabajadorIdNewAssetForm) trabajadorIdNewAssetForm.value = employeeId;
                if(trabajadorIdExistingAssetForm) trabajadorIdExistingAssetForm.value = employeeId;

                toggleVisibility(employeeSearchSection, false);
                clearErrors(displayEmployeeNameInput);
            }
        });
    }

    if (toggleAssetSearchBtn && assetSearchSection) {
        toggleAssetSearchBtn.addEventListener('click', () => {
            toggleVisibility(assetSearchSection, true);
            if (submitExistingButton) toggleVisibility(submitExistingButton, false);
            fetchAssets();
        });
    }

    if (assetSearchInput) {
        assetSearchInput.addEventListener('input', () => {
            currentAssetPage = 1;
            fetchAssets();
        });
    }
    if (assetEstatusFilter) {
        assetEstatusFilter.addEventListener('change', () => {
            currentAssetPage = 1;
            fetchAssets();
        });
    }

    if (prevAssetPageBtn) {
        prevAssetPageBtn.addEventListener('click', () => {
            if (currentAssetPage > 1) {
                currentAssetPage--;
                fetchAssets();
            }
        });
    }
    if (nextAssetPageBtn) {
        nextAssetPageBtn.addEventListener('click', () => {
            const totalPages = Math.ceil(totalAssets / assetsPerPage);
            if (currentAssetPage < totalPages) {
                currentAssetPage++;
                fetchAssets();
            }
        });
    }

    if (nextButton && newAssetFirstPartContainer && newAssetSecondPartContainer) {
        nextButton.addEventListener('click', async (event) => {
            event.preventDefault();
            clearErrors();
            
            if (optionNuevo.classList.contains('active')) {
                let commonValid = true;

                if (!hiddenEmployeeIdCommon || hiddenEmployeeIdCommon.value.trim() === '' || isNaN(parseInt(hiddenEmployeeIdCommon.value))) {
                    displayError(displayEmployeeNameInput, 'Debe seleccionar un trabajador.');
                    iziToast.warning({
                            title: 'Formulario Incompleto',
                            message: 'Por favor, complete todos los campos',
                            position: 'topRight',
                            timeout: 5000
                    })
                    commonValid = false;
                } else {
                    clearErrors(displayEmployeeNameInput);
                }

                if (commonValid) {
                    const firstPartIsValid = await validateNewAssetFirstPart();
                    if (firstPartIsValid) {
                        toggleVisibility(newAssetFirstPartContainer, false);
                        toggleVisibility(newAssetSecondPartContainer, true)
                    } else {
                        iziToast.warning({
                            title: 'Formulario Incompleto',
                            message: 'Por favor, complete todos los campos',
                            position: 'topRight',
                            timeout: 5000
                        })
                    }
                }
            }
        });
    }

    if (goBackButton && newAssetFirstPartContainer && newAssetSecondPartContainer) {
        goBackButton.addEventListener('click', (event) => {
            event.preventDefault();
            clearErrors();
            toggleVisibility(newAssetFirstPartContainer, true);
            toggleVisibility(newAssetSecondPartContainer, false)
        });
    }

    if (newAssetForm) {
        newAssetForm.addEventListener('submit', submitNewAssetForm);
    }

    if (submitExistingButton) {
        submitExistingButton.addEventListener('click', submitExistingForm);
    }
}

// --- MicroModal Initialization ---
MicroModal.init({
    onShow: (modal) => {
        if (modal.id === 'incorporation-modal') {
            assignModalDOMelements();
            initializeIncorporationLogic();
            updateSwitchState('option-nuevo');
            if (employeeSearchSection) toggleVisibility(employeeSearchSection, false);
            if (assetSearchSection) toggleVisibility(assetSearchSection, false);
            if (submitExistingButton) toggleVisibility(submitExistingButton, false);

            if (newAssetFirstPartContainer && newAssetSecondPartContainer) {
                toggleVisibility(newAssetFirstPartContainer, true);
                toggleVisibility(newAssetSecondPartContainer, false);
            }
            clearErrors();
        }
    },
    onClose: (modal) => {
        if (modal.id === 'incorporation-modal') {
            if (newAssetForm) newAssetForm.reset();
            if (existingAssetForm) existingAssetForm.reset();

            if (displayEmployeeNameInput) displayEmployeeNameInput.value = '';
            if (hiddenEmployeeIdCommon) hiddenEmployeeIdCommon.value = '';
            if (displayAssetNameInput) displayAssetNameInput.value = '';
            if (hiddenAssetIdInput) hiddenAssetIdInput.value = '';

            if (employeeSearchSection) toggleVisibility(employeeSearchSection, false);
            if (assetSearchSection) toggleVisibility(assetSearchSection, false);

            currentPage = 1;
            currentAssetPage = 1;

            if (optionNuevo) updateSwitchState('option-nuevo');

            if (newAssetFirstPartContainer && newAssetSecondPartContainer) {
                toggleVisibility(newAssetFirstPartContainer, true);
                toggleVisibility(newAssetSecondPartContainer, false);
            }
            clearErrors();
            if (submitExistingButton) toggleVisibility(submitExistingButton, false);
        }
    },
    disableScroll: true,
    disableFocus: true,
    awaitCloseAnimation: true
});