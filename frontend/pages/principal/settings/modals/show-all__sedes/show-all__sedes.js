document.addEventListener('DOMContentLoaded', () => {
    // --- Selectors updated to match the latest HTML structure ---
    const searchInput = document.getElementById('allAssetsSearchInput__sedes');
    const tableBody = document.getElementById('allAssetsTableBodyemployees__sedes'); // <-- Updated ID
    const tableHeaderRow = document.getElementById('allAssetsTableHeaderRow__sedes');
    const errorMessageDiv = document.getElementById('allAssetsErrorMessage__sedes');
    const exportExcelButton = document.getElementById('exportAllAssetsExcelButton__employees');

    // --- 1. Define Table Headers ---
    const headers = [
        { id: 'id', name: 'ID' },
        { id: 'sede', name: 'Sede' },
        { id: 'municipio', name: 'Municipio' }
    ];

    function renderTableHeaders() {
        tableHeaderRow.innerHTML = '';
        headers.forEach(header => {
            const th = document.createElement('th');
            th.textContent = header.name;
            tableHeaderRow.appendChild(th);
        });
    }

    // --- 2. Function to Fetch Sedes Data ---
    async function fetchSedes(searchTerm = '') {
        tableBody.innerHTML = '<tr><td colspan="3">Cargando sedes...</td></tr>';
        errorMessageDiv.style.display = 'none';

        const url = `/systemahidrofalcon/api/sede?action=getSedesData&search=${encodeURIComponent(searchTerm)}`;

        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`Error de red! Estado: ${response.status}`);
            }

            const data = await response.json();
            if (data.success) {
                renderSedesTable(data.sedes);
            } else {
                showError(data.message || 'Error desconocido al cargar sedes.');
            }
        } catch (error) {
            console.error('Error al obtener sedes:', error);
            showError('No se pudieron cargar las sedes. Inténtelo de nuevo.');
        }
    }

    // --- 3. Function to Render Sedes Table ---
    function renderSedesTable(sedes) {
        tableBody.innerHTML = '';
        if (sedes.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="3">No se encontraron sedes.</td></tr>';
            return;
        }

        sedes.forEach(sede => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${sede.id}</td>
                <td>${sede.sede}</td>
                <td>${sede.municipio}</td>
            `;
            tableBody.appendChild(row);
        });
    }

    // --- 4. Function to Display Errors ---
    function showError(message) {
        errorMessageDiv.querySelector('p').textContent = message;
        errorMessageDiv.style.display = 'block';
        tableBody.innerHTML = '';
    }

    // --- 5. Event Listener for Search Input ---
    let searchTimeout;
    searchInput.addEventListener('input', (event) => {
        clearTimeout(searchTimeout);
        const searchTerm = event.target.value;
        searchTimeout = setTimeout(() => {
            fetchSedes(searchTerm);
        }, 300);
    });

    // --- 6. Event Listener for Export to Excel Button ---
    exportExcelButton.addEventListener('click', () => {
        const searchTerm = searchInput.value;
        const excelUrl = `/systemahidrofalcon/backend/getdata/export-sedes-excel.php?search=${encodeURIComponent(searchTerm)}`;
        
        window.open(excelUrl, '_blank');
    });

    // --- 7. MicroModal Initialization and Callback ---
    MicroModal.init({
        onShow: modal => {
            if (modal.id === 'showAll-modal__sedes') {
                console.log('Modal de sedes abierto. Iniciando carga de datos.');
                renderTableHeaders();
                fetchSedes();
            }
        },
        onClose: modal => {
            if (modal.id === 'showAll-modal__sedes') {
                console.log('Modal de sedes cerrado. Limpiando.');
                searchInput.value = '';
                tableBody.innerHTML = '';
                errorMessageDiv.style.display = 'none';
            }
        }
    });
});