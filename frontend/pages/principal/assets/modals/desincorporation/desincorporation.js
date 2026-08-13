// desincorporation.js

// Assuming iziToast and MicroModal are loaded globally or via other means.

// --- GLOBAL DOM Elements (declared as `let` for dynamic assignment) ---
let desincorporarForm;
let displayAssetNameInput;
let hiddenAssetIdInput;
let errAssetBox; // Element to display asset selection errors

let toggleAssetSearchBtn;
let assetSearchSection;
let assetSearchInput;
let estatusFilterSelect;
let assetTableBody;
let noAssetResultsMessage;
let prevAssetPageBtn;
let nextAssetPageBtn;
let assetPageInfo; // Keep this declared, but we will ensure it's not displayed in HTML
let assetPageNumbers; // Container for page number buttons

let desincorporarSubmitButton;
let modalCloseButton; // For the desincorporation modal's close button

// Pagination state variables (kept global within this module)
let currentPage = 1;
const itemsPerPage = 5; // Records per page for asset search
let totalPages = 1; // Total pages for asset search results


// --- Utility Functions ---

function displayError(fieldElement, message) {
    if (!fieldElement) {
        console.error("Attempted to display error for a null element:", message);
        return;
    }
    // Specific error box for asset selection input
    const errorBox = document.getElementById('err-asset__desincorporation');

    if (errorBox) {
        errorBox.textContent = message;
        errorBox.classList.add('show-error');
    } else {
        console.warn(`No specific .err-box found for ${fieldElement.id}. Displaying general iziToast.`);
        iziToast.error({
            title: 'Error de Validación',
            message: message,
            position: 'topRight',
            timeout: 5000
        });
    }
}

function clearErrors() {
    // Clear all error messages within the modal
    document.querySelectorAll('#desincorporation-modal .err-box').forEach(box => {
        box.textContent = '';
        box.classList.remove('show-error');
    });
    // Also clear custom validity messages for form inputs
    document.querySelectorAll('#desincorporation-modal .input').forEach(input => {
        if (input.setCustomValidity) { // Check if method exists
            input.setCustomValidity('');
        }
    });
}

function toggleVisibility(element, show = true) {
    if (!element) return;
    if (show) {
        element.classList.add('show');
        element.classList.remove('hidden');
        element.style.display = ''; // Reset display to default (block, flex, etc.)
    } else {
        element.classList.remove('show');
        element.classList.add('hidden');
        element.style.display = 'none'; // Explicitly hide
    }
}


// --- Assign DOM elements dynamically when the modal is opened ---
function assignModalDOMelements() {
    desincorporarForm = document.getElementById('desincorporar-form');
    displayAssetNameInput = document.getElementById('display_asset_name__desincorporation');
    hiddenAssetIdInput = document.getElementById('hidden_asset_id__desincorporation');
    errAssetBox = document.getElementById('err-asset__desincorporation');

    toggleAssetSearchBtn = document.getElementById('toggle-asset-search-btn__desincorporation');
    assetSearchSection = document.getElementById('asset-search-section__desincorporation');
    assetSearchInput = document.getElementById('asset-search-input__desincorporation');
    estatusFilterSelect = document.getElementById('estatus-filter__desincorporation');
    assetTableBody = document.getElementById('asset-table-body__desincorporation');
    noAssetResultsMessage = document.getElementById('no-asset-results-message__desincorporation');
    prevAssetPageBtn = document.getElementById('prev-asset-page-btn__desincorporation');
    nextAssetPageBtn = document.getElementById('next-asset-page-btn__desincorporation');
    assetPageInfo = document.getElementById('asset-page-info__desincorporation');
    assetPageNumbers = document.getElementById('asset-page-numbers__desincorporation');

    desincorporarSubmitButton = document.getElementById('desincorporar-submit');
    modalCloseButton = document.querySelector('#desincorporation-modal .modal__close');

    // Basic sanity check
    if (!desincorporarForm || !displayAssetNameInput || !hiddenAssetIdInput || !assetSearchSection || !assetTableBody) {
        console.error('ERROR: Missing critical DOM elements for desincorporation modal. Check HTML IDs.');
    }
}


// --- Data Loading & Search Functions ---

// Function to load Estatus options for the asset filter dropdown
async function loadEstatusFilterOptionsDesincorporation() {
    if (!estatusFilterSelect) {
        console.error('estatusFilterSelect is not defined. Cannot load estatus options.');
        return;
    }
    const fetchUrl = '/systemahidrofalcon/api/activo?action=getEstatusData'; // Ensure this path is correct
    try {
        const response = await fetch(fetchUrl);
        if (!response.ok) {
            throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
        }
        const data = await response.json();
        if (data.success) {
            estatusFilterSelect.innerHTML = '<option value="">Todos los Estatus</option>';
            data.data.forEach(estatus => {
                const option = document.createElement('option');
                option.value = estatus.id;
                option.textContent = estatus.nombre;
                estatusFilterSelect.appendChild(option);
            });
        } else {
            console.error('Error loading status filter options from API:', data.message);
            estatusFilterSelect.innerHTML = '<option value="">Error al cargar estatus (API)</option>';
        }
    } catch (error) {
        console.error('Problem with fetching status filter options:', error);
        estatusFilterSelect.innerHTML = '<option value="">Error de red al cargar estatus</option>';
    }
}

// Function to fetch assets for Desincorporation
async function fetchAssets() {
    if (!assetTableBody || !noAssetResultsMessage || !assetSearchInput || !estatusFilterSelect) {
        console.error('Missing required DOM elements for asset fetch.');
        return;
    }

    assetTableBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Cargando activos...</td></tr>';
    toggleVisibility(noAssetResultsMessage, false); // Hide "No results" message while loading

    const searchTerm = assetSearchInput.value.trim();
    const estatusId = estatusFilterSelect.value;

    const params = new URLSearchParams({
        search: searchTerm,
        estatus: estatusId,
        estatus_administrativo_id: '1',
        page: currentPage,
        limit: itemsPerPage,
        custodian_filter: 'all' // Based on your original code's `get-filtered-properties.php`
    });

    try {
        const response = await fetch(`/systemahidrofalcon/api/activo?action=getFilteredAssets&${params.toString()}`);
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        const data = await response.json();

        if (data.success) {
            totalPages = Math.ceil(data.total_assets / itemsPerPage);
            renderAssetTable(data.assets);
            updatePaginationControls();
        } else {
            console.error('Error fetching assets for Desincorporation:', data.message);
            assetTableBody.innerHTML = ''; // Clear table
            toggleVisibility(noAssetResultsMessage, true);
            noAssetResultsMessage.textContent = data.message || 'No se encontraron activos.';
            totalPages = 0; // Indicate no pages if no results or error
            updatePaginationControls();
        }
    } catch (error) {
        console.error('Network or server error fetching assets for Desincorporation:', error);
        assetTableBody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: red;">Error al cargar activos.</td></tr>';
        toggleVisibility(noAssetResultsMessage, false); // Hide message if network error
        totalPages = 0;
        updatePaginationControls();
    }
}

// Function to render asset table for Desincorporation
function renderAssetTable(assets) {
    if (!assetTableBody || !noAssetResultsMessage || !assetPageNumbers) return;

    assetTableBody.innerHTML = '';
    assetPageNumbers.innerHTML = ''; // Clear page numbers when re-rendering table

    if (assets && assets.length > 0) {
        toggleVisibility(noAssetResultsMessage, false);
        assets.forEach(asset => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${asset.codigo_activo_fijo || ''}</td>
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

        // Add event listeners to select buttons for each row
        assetTableBody.querySelectorAll('.select-asset-btn').forEach(button => {
            button.addEventListener('click', selectAsset);
        });
    } else {
        toggleVisibility(noAssetResultsMessage, true);
    }
}

// Function to update asset pagination controls for Desincorporation
function updatePaginationControls() {
    if (!prevAssetPageBtn || !nextAssetPageBtn || !assetPageNumbers) return;

    prevAssetPageBtn.disabled = currentPage === 1;
    nextAssetPageBtn.disabled = currentPage === totalPages || totalPages === 0;

    // Explicitly hide assetPageInfo if it exists, as requested.
    if (assetPageInfo) {
        toggleVisibility(assetPageInfo, false); // Always hide this element
    }

    assetPageNumbers.innerHTML = ''; // Clear previous page numbers
    const maxPagesToShow = 5;

    if (totalPages > 1) {
        let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
        let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);

        // Adjust startPage if not enough pages to fill maxPagesToShow from the end
        if (endPage - startPage + 1 < maxPagesToShow) {
            startPage = Math.max(1, endPage - maxPagesToShow + 1);
        }

        if (startPage > 1) {
            const span = document.createElement('span');
            span.textContent = '...';
                span.classList = 'abreviation'
            assetPageNumbers.appendChild(span);
        }

        for (let i = startPage; i <= endPage; i++) {
            const pageNumBtn = document.createElement('button');
            pageNumBtn.textContent = i;
            pageNumBtn.classList.add('page-num-btn');
            pageNumBtn.type = 'button';
            if (i === currentPage) {
                pageNumBtn.classList.add('active');
            }
            pageNumBtn.addEventListener('click', () => {
                currentPage = i;
                fetchAssets();
            });
            assetPageNumbers.appendChild(pageNumBtn);
        }

        if (endPage < totalPages) {
            const span = document.createElement('span');
            span.textContent = '...';
            span.classList = 'abreviation'
            assetPageNumbers.appendChild(span);
        }
        toggleVisibility(assetPageNumbers, true); // Show the page number buttons
    } else {
        toggleVisibility(assetPageNumbers, false); // Hide page number buttons if only one page or zero
    }
}

// Function to handle asset selection from the search table
function selectAsset(event) {
    const assetId = event.target.dataset.id;
    const assetName = event.target.dataset.name;

    if (!displayAssetNameInput || !hiddenAssetIdInput || !assetSearchSection || !desincorporarSubmitButton) return;

    displayAssetNameInput.value = assetName;
    hiddenAssetIdInput.value = assetId;

    toggleVisibility(assetSearchSection, false); // Hide the search section
    toggleVisibility(desincorporarSubmitButton, true); // Show the submit button
    clearErrors(); // Clear any existing errors for asset selection


}


// --- Form Validation ---
function validateForm() {
    let isValid = true;
    clearErrors(); // Clear previous errors

    if (!hiddenAssetIdInput || hiddenAssetIdInput.value.trim() === '') {
        displayError(displayAssetNameInput, 'Debe seleccionar un activo fijo para desincorporar.');
        isValid = false;
    }
    // Add any other specific validation rules for the desincorporation form here if needed.

    return isValid;
}


// --- Form Submission Handler ---
async function submitDesincorporationForm(event) {
    event.preventDefault();

    if (!validateForm()) {
        iziToast.warning({
            title: 'Formulario Incompleto',
            message: 'Por favor, complete todos los campos requeridos.',
            position: 'topRight',
            timeout: 5000
        });
        return;
    }

    if (desincorporarSubmitButton) {
        desincorporarSubmitButton.disabled = true;
        desincorporarSubmitButton.value = 'Desincorporando...';
    }

    const formData = new FormData();
    formData.append('assetId', hiddenAssetIdInput.value); // Only send the selected asset ID

    try {
        const response = await fetch('/systemahidrofalcon/api/activo?action=desincorporateAsset', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (result.success) {
            iziToast.success({
                title: 'Desincorporación Exitosa',
                message: result.message || 'El activo ha sido desincorporado correctamente.',
                position: 'topRight',
                timeout: 5000
            });
            document.getElementById('desincorporation-modal').classList.remove('is-open');

            // Potentially trigger a refresh of the main asset list if applicable
            // For example: if (typeof fetchAllAssets === 'function') fetchAllAssets();

        } else {
            iziToast.error({
                title: 'Error de Desincorporación',
                message: result.message || 'Hubo un error al desincorporar el activo.',
                position: 'topRight',
                timeout: 5000
            });
            // If API returns field-specific errors, display them
            if (result.field === 'assetId' && displayAssetNameInput) {
                displayError(displayAssetNameInput, result.message);
            }
        }
    } catch (error) {
        console.error('Submission Error:', error);
        iziToast.error({
            title: 'Error de Conexión',
            message: 'Ocurrió un error al desincorporar el activo: ' + error.message,
            position: 'topRight',
            timeout: 7000
        });
    } finally {
        if (desincorporarSubmitButton) {
            desincorporarSubmitButton.disabled = false;
            desincorporarSubmitButton.value = 'Desincorporar Activo';
        }
    }
}


// --- Event Listeners Initialization Function ---
function initializeDesincorporationLogic() {
    loadEstatusFilterOptionsDesincorporation(); // Load status options initially

    // Toggle Asset Search Section
    if (toggleAssetSearchBtn && assetSearchSection) {
        toggleAssetSearchBtn.addEventListener('click', () => {
            const isVisible = assetSearchSection.classList.contains('show');
            toggleVisibility(assetSearchSection, !isVisible);
            if (!isVisible) { // If showing the section, fetch assets
                currentPage = 1; // Reset to first page
                fetchAssets();
            }
        });
    } else {
        console.warn('initializeDesincorporationLogic: toggleAssetSearchBtn is NULL when trying to attach listener. Check HTML ID.');
    }


    // Asset Search Input & Filter (con debounce)
    let searchTimeout;
    const debounceDelay = 300; // ms

    if (assetSearchInput) {
        assetSearchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentPage = 1; // Reset to first page on new search
                fetchAssets();
            }, debounceDelay);
        });
    }

    if (estatusFilterSelect) {
        estatusFilterSelect.addEventListener('change', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentPage = 1; // Reset to first page on new filter
                fetchAssets();
            }, debounceDelay);
        });
    }

    // Asset Pagination Buttons
    if (prevAssetPageBtn) {
        prevAssetPageBtn.addEventListener('click', (event) => {
            event.stopPropagation(); // Prevent issues with nested elements
            if (currentPage > 1) {
                currentPage--;
                fetchAssets();
            }
        });
    }

    if (nextAssetPageBtn) {
        nextAssetPageBtn.addEventListener('click', (event) => {
            event.stopPropagation(); // Prevent issues with nested elements
            if (currentPage < totalPages) {
                currentPage++;
                fetchAssets();
            }
        });
    }

    // Form Submission
    if (desincorporarForm) {
        desincorporarForm.addEventListener('submit', submitDesincorporationForm);
    }

    // Modal close button (Micromodal's onClose also handles clean up)
    if (modalCloseButton) {
        modalCloseButton.addEventListener('click', () => {
            // MicroModal.close() will trigger the onClose handler automatically
        });
    }
}


// --- MicroModal Initialization ---
MicroModal.init({
    onShow: (modal) => {
        if (modal.id === 'desincorporation-modal') {
            assignModalDOMelements(); // Get fresh DOM references
            initializeDesincorporationLogic(); // Attach all event listeners and initial fetches

            // Set initial state of the form and search section
            if (desincorporarForm) desincorporarForm.reset(); // Clear any previous form data
            if (displayAssetNameInput) displayAssetNameInput.value = '';
            if (hiddenAssetIdInput) hiddenAssetIdInput.value = '';
            toggleVisibility(assetSearchSection, false); // Hide search section by default
            toggleVisibility(desincorporarSubmitButton, false); // Hide submit button until an asset is selected
            clearErrors(); // Clear any errors from previous uses
            currentPage = 1; // Reset pagination for next search
            
            // Explicitly hide assetPageInfo element when modal opens
            if (assetPageInfo) {
                toggleVisibility(assetPageInfo, false);
            }
        }
    },
    onClose: (modal) => {
        if (modal.id === 'desincorporation-modal') {
            // Reset all relevant state variables and UI elements
            if (desincorporarForm) desincorporarForm.reset();
            if (displayAssetNameInput) displayAssetNameInput.value = '';
            if (hiddenAssetIdInput) hiddenAssetIdInput.value = '';
            toggleVisibility(assetSearchSection, false);
            toggleVisibility(desincorporarSubmitButton, false); // Ensure hidden on close
            clearErrors();
            currentPage = 1;
            totalPages = 1; // Reset total pages count
            if (assetSearchInput) assetSearchInput.value = ''; // Clear search input
            if (estatusFilterSelect) estatusFilterSelect.value = ''; // Reset status filter
            if (assetTableBody) assetTableBody.innerHTML = ''; // Clear table content
            toggleVisibility(noAssetResultsMessage, false); // Hide no results message

            // Ensure assetPageInfo remains hidden on close
            if (assetPageInfo) {
                toggleVisibility(assetPageInfo, false);
            }
        }
    },
    disableScroll: true,
    disableFocus: true,
    awaitCloseAnimation: true
});