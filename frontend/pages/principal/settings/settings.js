document.addEventListener('DOMContentLoaded', () => {
    MicroModal.init();

    // =============================================================
    // LÓGICA GENERAL Y ELEMENTOS COMUNES
    // =============================================================

    const urlBaseSedes = '/systemahidrofalcon/api/sede';
    const urlBaseGerencias = '/systemahidrofalcon/api/gerencias';
    const urlBaseEmployees = '/systemahidrofalcon/api/empleados';

    function toggleErrorMessage(errorMessageElement, message, show) {
        if (errorMessageElement) {
            const errorMessageP = errorMessageElement.querySelector('p');
            if (errorMessageP) {
                errorMessageP.textContent = message;
            }
            errorMessageElement.style.display = show ? 'block' : 'none';
        }
    }

    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        item.addEventListener('click', (event) => {
            event.stopPropagation();
            const dropdown = item.querySelector('.dropdown-item');
            document.querySelectorAll('.dropdown-item.show').forEach(openDropdown => {
                if (openDropdown !== dropdown) {
                    openDropdown.classList.remove('show');
                }
            });
            if (dropdown) {
                dropdown.classList.toggle('show');
            }
        });
    });

    document.addEventListener('click', () => {
        document.querySelectorAll('.dropdown-item.show').forEach(dropdown => {
            dropdown.classList.remove('show');
        });
    });

    function createPageButton(pageNumber, currentPage, onClick) {
        const button = document.createElement('button');
        button.textContent = pageNumber;
        button.classList.add('page-num-btn');
        if (pageNumber === currentPage) {
            button.classList.add('active');
        }
        button.addEventListener('click', onClick);
        return button;
    }

    function updatePaginationControls(container, prevBtn, nextBtn, currentPage, totalPages, onClickPage) {
        if (!container || !prevBtn || !nextBtn) return;
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage >= totalPages || totalPages === 0;
        container.innerHTML = '';
        const maxPageButtons = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxPageButtons / 2));
        let endPage = Math.min(totalPages, startPage + maxPageButtons - 1);
        if (endPage - startPage + 1 < maxPageButtons && totalPages > maxPageButtons) {
            startPage = Math.max(1, endPage - maxPageButtons + 1);
        }
        for (let i = startPage; i <= endPage; i++) {
            container.appendChild(createPageButton(i, currentPage, () => onClickPage(i)));
        }
    }

    function renderTableHeaders(headerRowElement, headers) {
        headerRowElement.innerHTML = '';
        headers.forEach(headerText => {
            const th = document.createElement('th');
            th.textContent = headerText;
            headerRowElement.appendChild(th);
        });
    }

    // =============================================================
    // LÓGICA ESPECÍFICA PARA LA TABLA DE SEDES
    // =============================================================

    const sedesEditModal = document.getElementById('edit-sede-modal');
    const sedesEditForm = document.getElementById('editSedeForm');
    const sedeIdInput = document.getElementById('editSedeId');
    const sedeNombreInput = document.getElementById('editSedeNombre');
    const sedeMunicipioInput = document.getElementById('editSedeMunicipio');
    
    let originalSedeNombre = '';
    let originalSedeMunicipio = '';
    
    const sedesTableHeaderRow = document.getElementById('sedesTableHeaderRow');
    const sedesTableBody = document.getElementById('sedesTableBody');
    const sedesErrorMessage = document.getElementById('sedesErrorMessage');
    const prevSedesPageBtn = document.getElementById('prev-sedes-page-btn');
    const nextSedesPageBtn = document.getElementById('next-sedes-page-btn');
    const sedesPaginationNumbersContainer = document.getElementById('sedes-pagination-numbers');
    const sedesSearchInput = document.getElementById('sedesSearchInput');
    const sedesShowAllBtn = document.getElementById('showAllSedesBtn');
    
    let currentSedesPage = 1;
    const sedesItemsPerPage = 5;
    let sedesTotalPages = 0;
    let sedesData = [];

    async function fetchSedes(searchValue = '') {
        try {
            sedesTableBody.innerHTML = `<tr><td colspan="4">Cargando sedes...</td></tr>`;
            toggleErrorMessage(sedesErrorMessage, '', false);
            
            const urlSedes = `${urlBaseSedes}?operation_type=select_municipio&search=${encodeURIComponent(searchValue)}`;
            
            const response = await fetch(urlSedes);
            if (!response.ok) throw new Error(`HTTP error! Estado: ${response.status}`);
            const data = await response.json();

            if (data.success && data.sedes) {
                sedesData = data.sedes;
                sedesTotalPages = Math.ceil(sedesData.length / sedesItemsPerPage);
                currentSedesPage = 1; 
                renderSedesTable();
            } else {
                sedesData = []; 
                sedesTotalPages = 0;
                renderSedesTable();
            }
        } catch (error) {
            console.error('Error de red al cargar sedes:', error);
            sedesData = [];
            sedesTotalPages = 0;
            renderSedesTable();
        }
    }

    function renderSedesTable() {
        if (!sedesTableBody || !sedesTableHeaderRow) return;
        sedesTableBody.innerHTML = '';
        renderTableHeaders(sedesTableHeaderRow, ['Id', 'Sede', 'Municipio', 'Acciones']);

        if (sedesData.length === 0) {
            const emptyRow = sedesTableBody.insertRow();
            const emptyCell = emptyRow.insertCell();
            emptyCell.colSpan = 4;
            emptyCell.textContent = 'No hay sedes registradas.';
            emptyCell.style.textAlign = 'center';
        } else {
            const start = (currentSedesPage - 1) * sedesItemsPerPage;
            const end = start + sedesItemsPerPage;
            const sedesToDisplay = sedesData.slice(start, end);

            sedesToDisplay.forEach(sede => {
                const row = sedesTableBody.insertRow();
                row.insertCell().textContent = sede.id;
                row.insertCell().textContent = sede.sede;
                row.insertCell().textContent = sede.municipio ?? '';
                
                const actionsCell = row.insertCell();
                actionsCell.innerHTML = `
                    <button class="button button-edit" data-id="${sede.id}" data-micromodal-trigger="edit-sede-modal">Editar</button>
                    <button class="button button-delete" data-id="${sede.id}">Eliminar</button>
                `;
            });
        }
        
        updatePaginationControls(sedesPaginationNumbersContainer, prevSedesPageBtn, nextSedesPageBtn, currentSedesPage, sedesTotalPages, (page) => {
            currentSedesPage = page;
            renderSedesTable();
        });
    }

    if (sedesEditForm) {
        sedesEditForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const sedeId = formData.get('id');
            const sedeNombre = formData.get('nombre_sede');
            const sedeMunicipio = formData.get('municipio');

            if (sedeNombre === originalSedeNombre && sedeMunicipio === originalSedeMunicipio) {
                iziToast.warning({
                    title: 'Advertencia',
                    message: 'No se detectaron cambios. No se realizará la actualización.',
                    position: 'topRight'
                });
                MicroModal.close('edit-sede-modal');
                return;
            }
            
            await updateSede(sedeId, sedeNombre, sedeMunicipio);
        });
    }

    sedesTableBody.addEventListener('click', (e) => {
        if (e.target.classList.contains('button-edit')) {
            const sedeId = e.target.getAttribute('data-id');
            const sedeToEdit = sedesData.find(sede => sede.id == sedeId);
            
            if (sedeToEdit) {
                sedeIdInput.value = sedeToEdit.id;
                sedeNombreInput.value = sedeToEdit.sede;
                sedeMunicipioInput.value = sedeToEdit.municipio;
                
                originalSedeNombre = sedeToEdit.sede;
                originalSedeMunicipio = sedeToEdit.municipio;
                
                MicroModal.show('edit-sede-modal');
            }
        } 
        else if (e.target.classList.contains('button-delete')) {
            const sedeId = e.target.getAttribute('data-id');
            
            iziToast.show({
                theme: 'dark',
                icon: 'icon-person',
                title: 'Confirmar Eliminación',
                message: `¿Estás seguro de que quieres eliminar la sede con ID ${sedeId}?`,
                position: 'center',
                progressBarColor: 'rgb(255, 0, 0)',
                buttons: [
                    ['<button><b>SI</b></button>', function (instance, toast) {
                        deleteSede(sedeId);
                        instance.hide({ transitionOut: 'fadeOutUp' }, toast, 'button');
                    }, true],
                    ['<button>NO</button>', function (instance, toast) {
                        instance.hide({ transitionOut: 'fadeOutDown' }, toast, 'button');
                    }]
                ],
                onOpening: function(instance, toast){
                    console.info('callback: onOpening', instance, toast);
                },
                onClosing: function(instance, toast, closedBy){
                    console.info('callback: onClosing', instance, toast, closedBy);
                }
            });
        }
    });

   async function updateSede(id, nombre, municipio) {
        console.log('Datos enviados:', { id_sede: id, nombre_sede: nombre, municipio: municipio }); // <- Añade esta línea

    try {
        const response = await fetch('/systemahidrofalcon/api/sede', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                // Asegúrate de que las claves aquí coincidan con las del PHP
                action: 'updateSede',  // <-- ¡Cambio aquí! 'operation_type' ahora es 'action'
                id: id,          // <-- ¡Cambio aquí! 'id' ahora es 'id_sede'
                sede: nombre,  // <-- ¡Cambio aquí! 'nombre' ahora es 'nombre_sede'
                municipio: municipio,  // Este ya coincidía, pero lo mantengo por claridad
            })
        });
        const data = await response.json();
        if (data.success) {
            iziToast.success({
                title: 'Éxito',
                message: 'Sede actualizada correctamente.',
                position: 'topRight'
            });
            MicroModal.close('edit-sede-modal');
            fetchSedes(sedesSearchInput.value.trim());
        } else {
            iziToast.error({
                title: 'Error',
                message: 'Error al actualizar la sede: ' + (data.error || 'Desconocido'),
                position: 'topRight'
            });
        }
    } catch (error) {
        console.error('Error en la conexión:', error);
        iziToast.error({
            title: 'Error',
            message: 'Error de conexión al actualizar la sede.',
            position: 'topRight'
        });
    }
}


    async function deleteSede(id) {
        try {
            const response = await fetch(urlBaseSedes, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'deleteSede',
                    id: id
                })
            });
            const data = await response.json();
            if (data.success) {
                iziToast.success({
                    title: 'Éxito',
                    message: 'Sede eliminada correctamente.',
                    position: 'topRight'
                });
                fetchSedes(sedesSearchInput.value.trim());
            } else {
                iziToast.error({
                    title: 'Error',
                    message: 'Error al eliminar la sede: ' + (data.error || 'Desconocido'),
                    position: 'topRight'
                });
            }
        } catch (error) {
            console.error('Error en la conexión:', error);
            iziToast.error({
                title: 'Error',
                message: 'Error de conexión al eliminar la sede.',
                position: 'topRight'
            });
        }
    }

    if (sedesSearchInput) {
        let searchTimeoutSedes;
        sedesSearchInput.addEventListener('input', () => {
            clearTimeout(searchTimeoutSedes);
            searchTimeoutSedes = setTimeout(() => {
                currentSedesPage = 1;
                fetchSedes(sedesSearchInput.value.trim());
            }, 300);
        });
    }

    if (sedesShowAllBtn) {
        sedesShowAllBtn.addEventListener('click', () => {
            sedesSearchInput.value = '';
            currentSedesPage = 1;
            fetchSedes();
        });
    }

    if (prevSedesPageBtn) prevSedesPageBtn.addEventListener('click', () => { if (currentSedesPage > 1) { currentSedesPage--; renderSedesTable(); } });
    if (nextSedesPageBtn) nextSedesPageBtn.addEventListener('click', () => { if (currentSedesPage < sedesTotalPages) { currentSedesPage++; renderSedesTable(); } });

    // =============================================================
    // LÓGICA ESPECÍFICA PARA LA TABLA DE TRABAJADORES
    // =============================================================

    const employeesEditModal = document.getElementById('edit-employee-modal');
    const employeesEditForm = document.getElementById('editEmployeeForm');
    const employeeIdInput = document.getElementById('editEmployeeId');
    const employeeCedulaInput = document.getElementById('editEmployeeCedula');
    const employeeNombresInput = document.getElementById('editEmployeeNombres');
    const employeeApellidosInput = document.getElementById('editEmployeeApellidos');
    const employeeTelefonoInput = document.getElementById('editEmployeeTelefono');

    let originalEmployeeData = {};

    const employeesTableHeaderRow = document.getElementById('employeesTableHeaderRow');
    const employeesTableBody = document.getElementById('employeesTableBody');
    const employeesErrorMessage = document.getElementById('employeesErrorMessage');
    const prevEmployeesPageBtn = document.getElementById('prev-employees-page-btn');
    const nextEmployeesPageBtn = document.getElementById('next-employees-page-btn');
    const employeesPaginationNumbersContainer = document.getElementById('employees-pagination-numbers');
    const employeesSearchInput = document.getElementById('employeesSearchInput');
    const employeesShowAllBtn = document.getElementById('employeesShowAllBtn');
    
    const employeesShowIdCheckbox = document.getElementById('employeesShowIdCheckbox');

    let currentEmployeesPage = 1;
    const employeesPerPage = 6;
    let totalEmployees = 0;
    let employeesData = [];

    async function fetchEmployees(searchValue = '', showId = true) {
        try {
            const colSpanCount = showId ? 8 : 7;
            employeesTableBody.innerHTML = `<tr><td colspan="${colSpanCount}">Cargando trabajadores...</td></tr>`; 
            toggleErrorMessage(employeesErrorMessage, '', false);

            const urlEmployees = `${urlBaseEmployees}?action=getEmployees&page=${currentEmployeesPage}&limit=${employeesPerPage}&search=${encodeURIComponent(searchValue)}&show_id=${showId}`;
            const response = await fetch(urlEmployees);
            
            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
            const data = await response.json();
            
            if (data.employees) {
                employeesData = data.employees;
                totalEmployees = data.total_employees;
                renderEmployeesTable(showId);
            } else {
                console.error('Error al cargar trabajadores:', data.error);
                employeesTableBody.innerHTML = `<tr><td colspan="${colSpanCount}">Error al cargar trabajadores.</td></tr>`;
                totalEmployees = 0;
                renderEmployeesTable(showId);
            }
        } catch (error) {
            console.error('Error de red al cargar trabajadores:', error);
            employeesTableBody.innerHTML = `<tr><td colspan="7">Error de red.</td></tr>`;
            totalEmployees = 0;
            renderEmployeesTable(showId);
        }
    }
    
    function renderEmployeesTable(showId) {
        if (!employeesTableBody) return;
        employeesTableBody.innerHTML = '';
        
        const headers = ['Cédula', 'Nombres', 'Apellidos', 'Teléfono', 'Cargo', 'Gerencia', 'Acciones'];
        if (showId) {
            headers.unshift('ID');
        }
        renderTableHeaders(employeesTableHeaderRow, headers);

        if (employeesData.length === 0) {
            const emptyRow = employeesTableBody.insertRow();
            const emptyCell = emptyRow.insertCell();
            emptyCell.colSpan = headers.length;
            emptyCell.textContent = 'No hay trabajadores registrados.';
            emptyCell.style.textAlign = 'center';
            return;
        }

        employeesData.forEach(employee => {
            const row = employeesTableBody.insertRow();
            
            if (showId) {
                row.insertCell().textContent = employee.id;
            }
            
            row.insertCell().textContent = employee.cedula;
            row.insertCell().textContent = employee.nombres;
            row.insertCell().textContent = employee.apellidos;
            row.insertCell().textContent = employee.telefono;
            row.insertCell().textContent = employee.nombre_cargo;
            row.insertCell().textContent = employee.nombre_gerencia;

            const actionsCell = row.insertCell();
            actionsCell.innerHTML = `
                <button class="button button-edit" data-id="${employee.id}" data-micromodal-trigger="edit-employee-modal">Editar</button>
                <button class="button button-delete" data-id="${employee.id}">Eliminar</button>
            `;
        });

        const totalPages = Math.ceil(totalEmployees / employeesPerPage);
        updatePaginationControls(employeesPaginationNumbersContainer, prevEmployeesPageBtn, nextEmployeesPageBtn, currentEmployeesPage, totalPages, (page) => {
            currentEmployeesPage = page;
            const showId = employeesShowIdCheckbox?.checked ?? true;
            fetchEmployees(employeesSearchInput.value.trim(), showId);
        });
    }

    if (employeesShowIdCheckbox) {
        employeesShowIdCheckbox.addEventListener('change', () => {
            currentEmployeesPage = 1;
            fetchEmployees(employeesSearchInput.value.trim(), employeesShowIdCheckbox.checked);
        });
    }

    employeesTableBody.addEventListener('click', (e) => {
        if (e.target.classList.contains('button-edit')) {
            const employeeId = e.target.getAttribute('data-id');
            const employeeToEdit = employeesData.find(emp => emp.id == employeeId);

            if (employeeToEdit) {
                employeeIdInput.value = employeeToEdit.id;
                employeeCedulaInput.value = employeeToEdit.cedula;
                employeeNombresInput.value = employeeToEdit.nombres;
                employeeApellidosInput.value = employeeToEdit.apellidos;
                employeeTelefonoInput.value = employeeToEdit.telefono;
                
                originalEmployeeData = {...employeeToEdit};
                
                MicroModal.show('edit-employee-modal');
            }
        } else if (e.target.classList.contains('button-delete')) {
            const employeeId = e.target.getAttribute('data-id');
            
            iziToast.show({
                theme: 'dark',
                icon: 'icon-person',
                title: 'Confirmar Eliminación',
                message: `¿Estás seguro de que quieres eliminar al trabajador con ID ${employeeId}?`,
                position: 'center',
                progressBarColor: 'rgb(255, 0, 0)',
                buttons: [
                    ['<button><b>SI</b></button>', function (instance, toast) {
                        deleteEmployee(employeeId);
                        instance.hide({ transitionOut: 'fadeOutUp' }, toast, 'button');
                    }, true],
                    ['<button>NO</button>', function (instance, toast) {
                        instance.hide({ transitionOut: 'fadeOutDown' }, toast, 'button');
                    }]
                ],
            });
        }
    });

    if (employeesEditForm) {
        employeesEditForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(e.target);
            const employeeId = formData.get('id');
            const updatedData = {
                id: employeeId,
                cedula: formData.get('cedula'),
                nombres: formData.get('nombres'),
                apellidos: formData.get('apellidos'),
                telefono: formData.get('telefono')
            };

            const hasChanges = Object.keys(updatedData).some(key => updatedData[key] !== originalEmployeeData[key]);
            
            if (!hasChanges) {
                iziToast.warning({
                    title: 'Advertencia',
                    message: 'No se detectaron cambios. No se realizará la actualización.',
                    position: 'topRight'
                });
                MicroModal.close('edit-employee-modal');
                return;
            }
            
            await updateEmployee(updatedData);
        });
    }

    async function updateEmployee(updatedData) {
        try {
            const response = await fetch(urlBaseEmployees + '?action=updateEmployee', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    ...updatedData,
                })
            });
            const data = await response.json();
            if (data.success) {
                iziToast.success({
                    title: 'Éxito',
                    message: 'Trabajador actualizado correctamente.',
                    position: 'topRight'
                });
                MicroModal.close('edit-employee-modal');
                
                const showId = employeesShowIdCheckbox?.checked ?? true;
                fetchEmployees(employeesSearchInput.value.trim(), showId);
                
            } else {
                iziToast.error({
                    title: 'Error',
                    message: 'Error al actualizar el trabajador: ' + (data.error || 'Desconocido'),
                    position: 'topRight'
                });
            }
        } catch (error) {
            console.error('Error en la conexión:', error);
            iziToast.error({
                title: 'Error',
                message: 'Error de conexión al actualizar el trabajador.',
                position: 'topRight'
            });
        }
    }

    async function deleteEmployee(id) {
        try {
            const response = await fetch(urlBaseEmployees + '?action=deleteEmployee', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });

            if (!response.ok) {
                throw new Error('Server returned an error.');
            }

            const data = await response.json();

            if (data.success) {
                iziToast.success({
                    title: 'Éxito',
                    message: 'Trabajador eliminado correctamente.',
                    position: 'topRight'
                });

                const showId = employeesShowIdCheckbox?.checked ?? true;
                fetchEmployees(employeesSearchInput.value.trim(), showId);
            } else {
                iziToast.error({
                    title: 'Error',
                    message: 'Error al eliminar el trabajador: ' + (data.error || 'Desconocido'),
                    position: 'topRight'
                });
            }
        } catch (error) {
            console.error('Error en la conexión:', error);
            iziToast.error({
                title: 'Error',
                message: 'Error de conexión al eliminar el trabajador.',
                position: 'topRight'
            });
        }
    }

    if (employeesSearchInput) {
        let searchTimeoutEmployees;
        employeesSearchInput.addEventListener('input', () => {
            clearTimeout(searchTimeoutEmployees);
            searchTimeoutEmployees = setTimeout(() => {
                currentEmployeesPage = 1;
                const showId = employeesShowIdCheckbox?.checked ?? true;
                fetchEmployees(employeesSearchInput.value.trim(), showId);
            }, 300);
        });
    }

    if (employeesShowAllBtn) {
        employeesShowAllBtn.addEventListener('click', () => {
            employeesSearchInput.value = '';
            currentEmployeesPage = 1;
            const showId = employeesShowIdCheckbox?.checked ?? true;
            fetchEmployees('', showId);
        });
    }

    if (prevEmployeesPageBtn) prevEmployeesPageBtn.addEventListener('click', () => { 
        if (currentEmployeesPage > 1) { 
            currentEmployeesPage--; 
            const showId = employeesShowIdCheckbox?.checked ?? true;
            fetchEmployees(employeesSearchInput.value.trim(), showId); 
        } 
    });

    if (nextEmployeesPageBtn) nextEmployeesPageBtn.addEventListener('click', () => { 
        if (currentEmployeesPage < Math.ceil(totalEmployees / employeesPerPage)) { 
            currentEmployeesPage++; 
            const showId = employeesShowIdCheckbox?.checked ?? true;
            fetchEmployees(employeesSearchInput.value.trim(), showId);
        } 
    });


   // =============================================================
// LÓGICA ESPECÍFICA PARA LA TABLA DE GERENCIAS (FRONTEND)
// =============================================================

const gerenciasTableHeaderRow = document.getElementById('gerenciasTableHeaderRow');
const gerenciasTableBody = document.getElementById('gerenciasTableBody');
const gerenciasErrorMessage = document.getElementById('gerenciasErrorMessage');
const prevGerenciasPageBtn = document.getElementById('prev-gerencias-page-btn');
const nextGerenciasPageBtn = document.getElementById('next-gerencias-page-btn');
const gerenciasPaginationNumbersContainer = document.getElementById('gerencias-pagination-numbers');
const gerenciasSearchInput = document.getElementById('gerenciasSearchInput');
const gerenciasShowAllBtn = document.getElementById('gerenciasShowAllBtn');

// Elementos del modal de edición de gerencias
const gerenciasEditModal = document.getElementById('edit-gerencia-modal');
const gerenciasEditForm = document.getElementById('editGerenciaForm');
const gerenciaIdInput = document.getElementById('editGerenciaId');
const gerenciaNombreInput = document.getElementById('editGerenciaNombre');
const gerenciaEncargadoInput = document.getElementById('editGerenciaEncargado');

let originalGerenciaData = {};

let currentGerenciasPage = 1;
const gerenciasItemsPerPage = 6;
let gerenciasTotalPages = 0;
let gerenciasData = [];

async function fetchGerencias(searchValue = '') {
    try {
        gerenciasTableBody.innerHTML = `<tr><td colspan="4">Cargando gerencias...</td></tr>`;
        toggleErrorMessage(gerenciasErrorMessage, '', false);
        const urlGerencias = `${urlBaseGerencias}?action=gerenciasConEncargado&search=${encodeURIComponent(searchValue)}`;
        const response = await fetch(urlGerencias);
        if (!response.ok) throw new Error(`HTTP error! Estado: ${response.status}`);
        const data = await response.json();

        if (data.success && data.gerencias) {
            gerenciasData = data.gerencias;
            gerenciasTotalPages = Math.ceil(gerenciasData.length / gerenciasItemsPerPage);
            renderGerenciasTable();
        } else {
            console.error('Error al cargar gerencias:', data.error);
            gerenciasTableBody.innerHTML = `<tr><td colspan="4">No hay gerencias registradas.</td></tr>`;
            gerenciasTotalPages = 0;
            renderGerenciasTable();
        }
    } catch (error) {
        console.error('Error de red al cargar gerencias:', error);
        gerenciasTableBody.innerHTML = `<tr><td colspan="4">Error de red.</td></tr>`;
        gerenciasTotalPages = 0;
        renderGerenciasTable();
    }
}

function renderGerenciasTable() {
    if (!gerenciasTableBody || !gerenciasTableHeaderRow) return;
    gerenciasTableBody.innerHTML = '';
    renderTableHeaders(gerenciasTableHeaderRow, ['ID', 'Gerencia', 'Encargado', 'Acciones']);

    if (gerenciasData.length === 0) {
        const emptyRow = gerenciasTableBody.insertRow();
        const emptyCell = emptyRow.insertCell();
        emptyCell.colSpan = 4;
        emptyCell.textContent = 'No hay gerencias registradas.';
        emptyCell.style.textAlign = 'center';
    } else {
        const start = (currentGerenciasPage - 1) * gerenciasItemsPerPage;
        const end = start + gerenciasItemsPerPage;
        const gerenciasToDisplay = gerenciasData.slice(start, end);

        gerenciasToDisplay.forEach(gerencia => {
            const row = gerenciasTableBody.insertRow();
            row.insertCell().textContent = gerencia.id;
            row.insertCell().textContent = gerencia.nombre_gerencia;
            row.insertCell().textContent = gerencia.encargado ?? 'N/A';
            
            const actionsCell = row.insertCell();
            actionsCell.innerHTML = `
                <button class="button button-edit" data-id="${gerencia.id}" data-micromodal-trigger="edit-gerencia-modal">Editar</button>
                <button class="button button-delete" data-id="${gerencia.id}">Eliminar</button>
            `;
        });
    }

    updatePaginationControls(gerenciasPaginationNumbersContainer, prevGerenciasPageBtn, nextGerenciasPageBtn, currentGerenciasPage, gerenciasTotalPages, (page) => {
        currentGerenciasPage = page;
        renderGerenciasTable();
    });
}

// Listener para los botones de editar y eliminar de la tabla de gerencias
gerenciasTableBody.addEventListener('click', (e) => {
    if (e.target.classList.contains('button-edit')) {
        const gerenciaId = e.target.getAttribute('data-id');
        const gerenciaToEdit = gerenciasData.find(g => g.id == gerenciaId);

        if (gerenciaToEdit) {
            gerenciaIdInput.value = gerenciaToEdit.id;
            gerenciaNombreInput.value = gerenciaToEdit.nombre_gerencia;
            gerenciaEncargadoInput.value = gerenciaToEdit.encargado;

            originalGerenciaData = {...gerenciaToEdit};

            MicroModal.show('edit-gerencia-modal');
        }
    } else if (e.target.classList.contains('button-delete')) {
        const gerenciaId = e.target.getAttribute('data-id');
        
        iziToast.show({
            theme: 'dark',
            icon: 'icon-person',
            title: 'Confirmar Eliminación',
            message: `¿Estás seguro de que quieres eliminar la gerencia con ID ${gerenciaId}?`,
            position: 'center',
            progressBarColor: 'rgb(255, 0, 0)',
            buttons: [
                ['<button><b>SI</b></button>', function (instance, toast) {
                    deleteGerencia(gerenciaId);
                    instance.hide({ transitionOut: 'fadeOutUp' }, toast, 'button');
                }, true],
                ['<button>NO</button>', function (instance, toast) {
                    instance.hide({ transitionOut: 'fadeOutDown' }, toast, 'button');
                }]
            ],
        });
    }
});

// Listener para el formulario de edición de gerencias
if (gerenciasEditForm) {
    gerenciasEditForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(e.target);
        const updatedData = {
            id: formData.get('id'),
            gerencia: formData.get('gerencia'),
            encargado: formData.get('encargado')
        };

        const hasChanges = Object.keys(updatedData).some(key => updatedData[key] !== originalGerenciaData[key]);
        
        if (!hasChanges) {
            iziToast.warning({
                title: 'Advertencia',
                message: 'No se detectaron cambios. No se realizará la actualización.',
                position: 'topRight'
            });
            MicroModal.close('edit-gerencia-modal');
            return;
        }
        
        await updateGerencia(updatedData);
    });
}

// Función para actualizar una gerencia
async function updateGerencia(updatedData) {
    try {
        const response = await fetch(urlBaseGerencias, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'updateGerencia',
                id: updatedData.id,
                gerencia: updatedData.gerencia,
                encargado: updatedData.encargado
            })
        });
        const data = await response.json();
        if (data.success) {
            iziToast.success({
                title: 'Éxito',
                message: 'Gerencia actualizada correctamente.',
                position: 'topRight'
            });
            MicroModal.close('edit-gerencia-modal');
            fetchGerencias(gerenciasSearchInput.value.trim());
        } else {
            iziToast.error({
                title: 'Error',
                message: 'Error al actualizar la gerencia: ' + (data.error || 'Desconocido'),
                position: 'topRight'
            });
        }
    } catch (error) {
        console.error('Error en la conexión:', error);
        iziToast.error({
            title: 'Error',
            message: 'Error de conexión al actualizar la gerencia.',
            position: 'topRight'
        });
    }
}

// Función para eliminar una gerencia
async function deleteGerencia(id) {
    try {
        const response = await fetch(urlBaseGerencias, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'deleteGerencia',
                id: id
            })
        });
        const data = await response.json();
        if (data.success) {
            iziToast.success({
                title: 'Éxito',
                message: 'Gerencia eliminada correctamente.',
                position: 'topRight'
            });
            fetchGerencias(gerenciasSearchInput.value.trim());
        } else {
            iziToast.error({
                title: 'Error',
                message: 'Error al eliminar la gerencia: ' + (data.error || 'Desconocido'),
                position: 'topRight'
            });
        }
    } catch (error) {
        console.error('Error en la conexión:', error);
        iziToast.error({
            title: 'Error',
            message: 'Error de conexión al eliminar la gerencia.',
            position: 'topRight'
        });
    }
}

if (gerenciasSearchInput) {
    let searchTimeoutGerencias;
    gerenciasSearchInput.addEventListener('input', () => {
        clearTimeout(searchTimeoutGerencias);
        searchTimeoutGerencias = setTimeout(() => {
            currentGerenciasPage = 1;
            fetchGerencias(gerenciasSearchInput.value.trim());
        }, 300);
    });
}

if (gerenciasShowAllBtn) {
    gerenciasShowAllBtn.addEventListener('click', () => {
        gerenciasSearchInput.value = '';
        currentGerenciasPage = 1;
        fetchGerencias();
    });
}

if (prevGerenciasPageBtn) prevGerenciasPageBtn.addEventListener('click', () => { if (currentGerenciasPage > 1) { currentGerenciasPage--; renderGerenciasTable(); } });
if (nextGerenciasPageBtn) nextGerenciasPageBtn.addEventListener('click', () => { if (currentGerenciasPage < gerenciasTotalPages) { currentGerenciasPage++; renderGerenciasTable(); } });
    fetchSedes();
    fetchEmployees();
    fetchGerencias();
});