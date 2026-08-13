document.addEventListener('DOMContentLoaded', () => {

    // --- Selectors for the employees modal ---
    const searchInput = document.getElementById('allAssetsSearchInput__employees');
    const tableBody = document.getElementById('allAssetsTableBodyemployees__employees'); 
    const tableHeaderRow = document.getElementById('allAssetsTableHeaderRow__employees');
    const errorMessageDiv = document.getElementById('allAssetsErrorMessage__employees');
    const exportExcelButton = document.getElementById('exportAllAssetsExcelButton__employees');

    const urlBaseEmployees = '/systemahidrofalcon/api/empleados'; 

    // --- Define Table Headers ---
    const headers = [
        { id: 'cedula', name: 'Cédula' },
        { id: 'nombres', name: 'Nombres' },
        { id: 'apellidos', name: 'Apellidos' },
        { id: 'telefono', name: 'Teléfono' },
        { id: 'nombre_cargo', name: 'Cargo' },
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

    // --- Function to Fetch Employee Data ---
    // Adaptada para el modal: siempre usa página 1 y un límite muy alto.
    async function fetchEmployees(searchTerm = '') {
        tableBody.innerHTML = '<tr><td colspan="6">Cargando empleados...</td></tr>';
        errorMessageDiv.style.display = 'none';

        const page = 1;
        const limit = 999999;
        const url = `${urlBaseEmployees}?action=getEmployees&page=${page}&limit=${limit}&search=${encodeURIComponent(searchTerm)}`;

        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`Error de red! Estado: ${response.status}`);
            }

            const data = await response.json();
            if (data.employees) {
                renderEmployeesTable(data.employees);
            } else {
                showError(data.error || 'Error desconocido al cargar empleados.');
            }
        } catch (error) {
            console.error('Error al obtener empleados:', error);
            showError('No se pudieron cargar los empleados. Inténtelo de nuevo.');
        }
    }

    // --- Function to Render Employees Table ---
    function renderEmployeesTable(employees) {
        tableBody.innerHTML = '';
        if (employees.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="6">No se encontraron empleados.</td></tr>';
            return;
        }

        employees.forEach(employee => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${employee.cedula}</td>
                <td>${employee.nombres}</td>
                <td>${employee.apellidos}</td>
                <td>${employee.telefono}</td>
                <td>${employee.nombre_cargo}</td>
                <td>${employee.nombre_gerencia}</td>
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
    // Usamos debounce para evitar llamadas excesivas al servidor
    let searchTimeout;
    if (searchInput) {
        searchInput.addEventListener('keydown', (event) => {
            clearTimeout(searchTimeout);
            console.log('mira');
            searchTimeout = setTimeout(() => {
                const searchTerm = event.target.value.trim();
                fetchEmployees(searchTerm);
            }, 300); // Espera 300ms antes de buscar
        });
    }

    // --- Event Listener for Export to Excel Button ---
    if (exportExcelButton) {
        exportExcelButton.addEventListener('click', () => {
            const searchTerm = searchInput.value;
            const excelUrl = `${urlBaseEmployees.replace('get-employees.php', 'export-employees-excel.php')}?search=${encodeURIComponent(searchTerm)}&limit=999999`;
            
            window.open(excelUrl, '_blank');
        });
    }

    // --- MicroModal Initialization and Callback ---
    MicroModal.init({
        onShow: modal => {
            if (modal.id === 'showAll-modal__employees') {
                console.log('Modal de empleados abierto. Iniciando carga de datos.');
                renderTableHeaders();
                fetchEmployees(searchInput.value.trim()); 
            }
        },
        onClose: modal => {
            if (modal.id === 'showAll-modal__employees') {
                console.log('Modal de empleados cerrado. Limpiando.');
                searchInput.value = '';
                tableBody.innerHTML = '';
                errorMessageDiv.style.display = 'none';
            }
        }
    });
});