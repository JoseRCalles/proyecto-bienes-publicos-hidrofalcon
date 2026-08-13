document.addEventListener('DOMContentLoaded', () => {

    // --- Selectors for the gerencias modal ---
    const searchInput = document.getElementById('allAssetsSearchInput__gerencias');
    const tableBody = document.getElementById('allAssetsTableBodygerencias__gerencias');
    const tableHeaderRow = document.getElementById('allAssetsTableHeaderRow__gerencias');
    const errorMessageDiv = document.getElementById('allAssetsErrorMessage__gerencias');
    const exportExcelButton = document.getElementById('exportAllAssetsExcelButton__gerencias');

    const urlBaseGerencias = '/systemahidrofalcon/api/gerencias';

    // --- Define Table Headers ---
    const headers = [
        { id: 'id', name: 'ID' },
        { id: 'nombre_gerencia', name: 'Gerencia' }
    ];

    function renderTableHeaders() {
        tableHeaderRow.innerHTML = '';
        headers.forEach(header => {
            const th = document.createElement('th');
            th.textContent = header.name;
            tableHeaderRow.appendChild(th);
        });
    }

    // --- Function to Fetch Gerencia Data ---
    async function fetchGerencias(searchTerm = '') {
        tableBody.innerHTML = '<tr><td colspan="2">Cargando gerencias...</td></tr>';
        errorMessageDiv.style.display = 'none';

        // La URL pide todos los datos sin paginación y aplica el filtro de búsqueda
        const url = `${urlBaseGerencias}?action=gerenciasSolo&search=${encodeURIComponent(searchTerm)}`;

        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`Error de red! Estado: ${response.status}`);
            }

            const data = await response.json();
            if (data.success && data.gerencias) {
                renderGerenciasTable(data.gerencias);
            } else {
                showError(data.error || 'Error desconocido al cargar gerencias.');
            }
        } catch (error) {
            console.error('Error al obtener gerencias:', error);
            showError('No se pudieron cargar las gerencias. Inténtelo de nuevo.');
        }
    }

    // --- Function to Render Gerencias Table ---
    function renderGerenciasTable(gerencias) {
        tableBody.innerHTML = '';
        if (gerencias.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="2">No se encontraron gerencias.</td></tr>';
            return;
        }

        gerencias.forEach(gerencia => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${gerencia.id}</td>
                <td>${gerencia.nombre_gerencia}</td>
            `;
            tableBody.appendChild(row);
        });
    }

    // --- Function to Display Errors ---
    function showError(message) {
        errorMessageDiv.querySelector('p').textContent = message;
        errorMessageDiv.style.display = 'block';
        tableBody.innerHTML = '';
    }

    // --- Event Listener for Search Input (con debounce) ---
    let searchTimeout;
    if (searchInput) {
        searchInput.addEventListener('input', (event) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const searchTerm = event.target.value.trim();
                fetchGerencias(searchTerm);
            }, 300); // Espera 300ms antes de buscar
        });
    }

    // --- Event Listener for Export to Excel Button ---
    if (exportExcelButton) {
        exportExcelButton.addEventListener('click', () => {
            const searchTerm = searchInput.value;
            // Adapta la URL para el script de exportación de gerencias
            const excelUrl = `/systemahidrofalcon/backend/getdata/export-gerencias-excel.php?search=${encodeURIComponent(searchTerm)}`;
            window.open(excelUrl, '_blank');
        });
    }

    // --- MicroModal Initialization and Callback ---
    MicroModal.init({
        onShow: modal => {
            if (modal.id === 'showAll-modal__gerencias') {
                console.log('Modal de gerencias abierto. Iniciando carga de datos.');
                renderTableHeaders();
                fetchGerencias(''); // Carga todos los datos al abrir
            }
        },
        onClose: modal => {
            if (modal.id === 'showAll-modal__gerencias') {
                console.log('Modal de gerencias cerrado. Limpiando.');
                searchInput.value = '';
                tableBody.innerHTML = '';
                errorMessageDiv.style.display = 'none';
            }
        }
    });
});