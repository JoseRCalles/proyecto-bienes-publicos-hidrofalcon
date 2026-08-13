const currentUserId = window._currentUserId || '';
const currentUserRank = parseInt(window._currentUserRank) || 0; // Asegurar que sea número


// --- Elementos de la tabla de Sedes (Usuarios) ---
const sedesSearchInput = document.getElementById('sedesSearchInput');
const showAllSedesBtn = document.getElementById('showAllSedesBtn');
const sedesTableBody = document.getElementById('sedesTableBody');
const sedesTableHeaderRow = document.getElementById('sedesTableHeaderRow');
const sedesErrorMessage = document.getElementById('sedesErrorMessage');
const sedesErrorMessageP = sedesErrorMessage ? sedesErrorMessage.querySelector('p') : null;

// --- Elementos de Paginación de Sedes ---
const prevSedesPageBtn = document.getElementById('prev-sedes-page-btn');
const nextSedesPageBtn = document.getElementById('next-sedes-page-btn');
const sedesPaginationNumbersContainer = document.getElementById('sedes-pagination-numbers');

// --- Variables de Paginación y Estado de Sedes ---
let currentSedesPage = 1;
const itemsPerPage = 10;
let totalSedesPages = 0;
let currentSearchSedesTerm = '';
const initialLoadingRow = '<tr><td colspan="100%" style="text-align: center;">Cargando...</td></tr>';

// --- Elementos de la tabla de Bitácora ---
const bitacoraTableBody = document.getElementById('bitacoraTableBody');
const bitacoraSearchInput = document.getElementById('bitacoraSearchInput');
const bitacoraErrorMessage = document.getElementById('bitacoraErrorMessage');
const bitacoraErrorMessageP = bitacoraErrorMessage ? bitacoraErrorMessage.querySelector('p') : null;

// --- Elementos de Paginación de Bitácora ---
const prevBitacoraPageBtn = document.getElementById('prev-bitacora-page-btn');
const nextBitacoraPageBtn = document.getElementById('next-bitacora-page-btn');
const bitacoraPaginationNumbersContainer = document.getElementById('bitacora-pagination-numbers');

// --- Variables de Paginación y Estado de Bitácora ---
let currentBitacoraPage = 1;
const bitacoraItemsPerPage = 10;
let totalBitacoraPages = 0;
let currentSearchBitacoraTerm = '';

// --- Variables para el modal de modificación de rango ---
let currentEditingUserId = null;

// --- Función auxiliar para mostrar/ocultar mensajes de error de Sedes ---
function toggleSedesErrorMessage(message, show) {
    if (sedesErrorMessage && sedesErrorMessageP) {
        sedesErrorMessageP.textContent = message;
        sedesErrorMessage.style.display = show ? 'block' : 'none';
    }
}

// --- Función auxiliar para mostrar/ocultar mensajes de error de Bitácora ---
function toggleBitacoraErrorMessage(message, show) {
    if (bitacoraErrorMessage && bitacoraErrorMessageP) {
        bitacoraErrorMessageP.textContent = message;
        bitacoraErrorMessage.style.display = show ? 'block' : 'none';
    }
}

// --- Función asíncrona para obtener usuarios (con paginación) ---
async function fetchAllSedes() {
    currentSearchSedesTerm = sedesSearchInput ? sedesSearchInput.value.trim() : '';

    if (sedesTableBody) sedesTableBody.innerHTML = initialLoadingRow;
    toggleSedesErrorMessage('', false);

    const params = new URLSearchParams({
        search: currentSearchSedesTerm,
        page: currentSedesPage,
        limit: itemsPerPage,
        currentUserId: currentUserId
    });

    const endpointUrl = `/systemahidrofalcon/api/administrador?action=getUsers&${params.toString()}`;

    try {
        const response = await fetch(endpointUrl);

        if (!response.ok) {
            throw new Error(`HTTP error! Estado: ${response.status}`);
        }

        const data = await response.json();

        if (data.success) {
            totalSedesPages = Math.ceil(data.totalUsers / itemsPerPage);
            renderSedesTableHeaders(data.headers);
            renderSedesTable(data.users, data.headers);
            updateSedesPaginationControls();
        } else {
            console.error('Error al cargar usuarios:', data.message);
            if (sedesTableBody) sedesTableBody.innerHTML = '';
            toggleSedesErrorMessage(data.message || 'No se encontraron usuarios.', true);
            totalSedesPages = 0;
            updateSedesPaginationControls();
        }
    } catch (error) {
        console.error('Error de red o servidor al cargar usuarios:', error);
        if (sedesTableBody) sedesTableBody.innerHTML = '';
        toggleSedesErrorMessage('Error de red al cargar usuarios. Intente de nuevo más tarde.', true);
        totalSedesPages = 0;
        updateSedesPaginationControls();
    }
}

// --- Función para renderizar los encabezados de la tabla ---
function renderSedesTableHeaders(headers) {
    if (!sedesTableHeaderRow) return;
    sedesTableHeaderRow.innerHTML = '';

    headers.forEach(headerText => {
        const th = document.createElement('th');
        th.textContent = headerText;
        sedesTableHeaderRow.appendChild(th);
    });

    const thActions = document.createElement('th');
    thActions.textContent = 'Acciones';
    sedesTableHeaderRow.appendChild(thActions);
}

// --- Función para renderizar los datos en el cuerpo de la tabla ---
function renderSedesTable(users, headers) {
    if (!sedesTableBody) {
        console.error('renderSedesTable: sedesTableBody is null. Cannot render table.');
        return;
    }
    sedesTableBody.innerHTML = '';

    const headerToFieldNameMap = {
        'ID': 'id',
        'Nombres': 'nombres',
        'Apellidos': 'apellidos',
        'Cédula': 'cedula',
        'Cargo': 'cargo',
        'Gerencia': 'gerencia',
        'Usuario': 'usuario',
        'Rango': 'rango',
        'Estado': 'estado',
        'Intentos': 'intentos',
        'acciones': 'acciones'
    };
    
    const allHeaders = [...headers, 'Acciones'];

    if (users.length === 0) {
        const emptyRow = sedesTableBody.insertRow();
        const emptyCell = emptyRow.insertCell();
        emptyCell.colSpan = allHeaders.length;
        emptyCell.textContent = 'No se encontraron usuarios.';
        emptyCell.style.textAlign = 'center';
    } else {
        users.forEach(user => {
            const row = sedesTableBody.insertRow();
            allHeaders.forEach(headerText => {
                const td = row.insertCell();

                if (headerText === 'Acciones') {
                    const navItemDiv = document.createElement('div');
                    navItemDiv.classList.add('nav-item');

                    const p = document.createElement('p');
                    p.textContent = 'Acciones';

                    const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
                    svg.setAttribute('width', '20');
                    svg.setAttribute('height', '20');
                    svg.setAttribute('viewBox', '0 0 20 20');
                    svg.setAttribute('fill', 'none');
                    const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
                    path.setAttribute('fill-rule', 'evenodd');
                    path.setAttribute('clip-rule', 'evenodd');
                    path.setAttribute('d', 'M3.71209 6.52459C4.0782 6.15847 4.6718 6.15847 5.03791 6.52459L10 11.4867L14.9621 6.52459C15.3282 6.15847 15.9218 6.15847 16.2879 6.52459C16.654 6.8907 16.654 7.4843 16.2879 7.85041L10.6629 13.4754C10.2968 13.8415 9.7032 13.8415 9.33709 13.4754L3.71209 7.85041C3.34597 7.4843 3.34597 6.8907 3.71209 6.52459Z');
                    path.setAttribute('fill', '#1E293B');
                    svg.appendChild(path);

                    const dropdownItemDiv = document.createElement('div');
                    dropdownItemDiv.classList.add('dropdown-item', 'incorporar');
                    
                    const ul = document.createElement('ul');
                    ul.classList.add('options');
                    
                    if (user.estado === 'activo') {
                        const liBloquear = document.createElement('li');
                        liBloquear.classList.add('options-item__container');
                        liBloquear.innerHTML = `<button class="options-item" data-action="bloquear" data-id="${user.id}"><i class="fas fa-lock"></i> Bloquear</button>`;
                        ul.appendChild(liBloquear);
                        
                        const liEliminar = document.createElement('li');
                        liEliminar.classList.add('options-item__container');
                        liEliminar.innerHTML = `<button class="options-item" data-action="eliminar" data-id="${user.id}"><i class="fas fa-trash-alt"></i> Eliminar</button>`;
                        ul.appendChild(liEliminar);

                        const liRango = document.createElement('li');
                        liRango.classList.add('options-item__container');
                        liRango.innerHTML = `<button class="options-item" data-action="asignar-rango" data-id="${user.id}"><i class="fas fa-user-tag"></i> Asignar Rango</button>`;
                        ul.appendChild(liRango);
                    } else if (user.estado === 'bloqueado' || user.estado === 'restringido') {
                        const liDesbloquear = document.createElement('li');
                        liDesbloquear.classList.add('options-item__container');
                        liDesbloquear.innerHTML = `<button class="options-item" data-action="desbloquear" data-id="${user.id}"><i class="fas fa-unlock"></i> Desbloquear</button>`;
                        ul.appendChild(liDesbloquear);

                        const liEliminar = document.createElement('li');
                        liEliminar.classList.add('options-item__container');
                        liEliminar.innerHTML = `<button class="options-item" data-action="eliminar" data-id="${user.id}"><i class="fas fa-trash-alt"></i> Eliminar</button>`;
                        ul.appendChild(liEliminar);
                    }

                    dropdownItemDiv.appendChild(ul);
                    navItemDiv.appendChild(p);
                    navItemDiv.appendChild(svg);
                    navItemDiv.appendChild(dropdownItemDiv);

                    td.appendChild(navItemDiv);
                } else {
                    const originalFieldName = headerToFieldNameMap[headerText];

                    if (originalFieldName === 'rango') {
                        if (user[originalFieldName] == 1) {
                            td.textContent = 'Asociado';
                        } else if (user[originalFieldName] == 2) {
                            td.textContent = 'Usuario';
                        } else {
                            td.textContent = 'Administrador';
                        }
                    } else if (originalFieldName === 'estado') {
                        const statusDiv = document.createElement('div');
                        statusDiv.classList.add('status-container');
                        statusDiv.textContent = user[originalFieldName] || '';

                        if (user[originalFieldName] === 'activo') {
                            statusDiv.classList.add('status-active');
                        } else if (user[originalFieldName] === 'bloqueado') {
                            statusDiv.classList.add('status-blocked');
                        } else if (user[originalFieldName] === 'restringido') {
                            statusDiv.classList.add('status-restricted');
                        }
                        td.appendChild(statusDiv);
                    } else {
                        td.textContent = user[originalFieldName] || '';
                    }
                }
            });
        });
    }
}

// --- Funciones para la paginación de Sedes ---
function updateSedesPaginationControls() {
    if (prevSedesPageBtn) prevSedesPageBtn.disabled = currentSedesPage === 1;
    if (nextSedesPageBtn) nextSedesPageBtn.disabled = currentSedesPage === totalSedesPages || totalSedesPages === 0;

    if (!sedesPaginationNumbersContainer) return;
    sedesPaginationNumbersContainer.innerHTML = '';
    const maxPageButtons = 5;
    let startPage = Math.max(1, currentSedesPage - Math.floor(maxPageButtons / 2));
    let endPage = Math.min(totalSedesPages, startPage + maxPageButtons - 1);

    if (endPage - startPage + 1 < maxPageButtons && totalSedesPages > maxPageButtons) {
        startPage = Math.max(1, endPage - maxPageButtons + 1);
    }

    if (startPage > 1) {
        const firstPageBtn = createSedesPageButton(1);
        sedesPaginationNumbersContainer.appendChild(firstPageBtn);
        if (startPage > 2) {
            const ellipsis = document.createElement('span');
            ellipsis.textContent = '...';
            sedesPaginationNumbersContainer.appendChild(ellipsis);
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        const pageBtn = createSedesPageButton(i);
        sedesPaginationNumbersContainer.appendChild(pageBtn);
    }

    if (endPage < totalSedesPages) {
        if (endPage < totalSedesPages - 1) {
            const ellipsis = document.createElement('span');
            ellipsis.textContent = '...';
            sedesPaginationNumbersContainer.appendChild(ellipsis);
        }
        const lastPageBtn = createSedesPageButton(totalSedesPages);
        sedesPaginationNumbersContainer.appendChild(lastPageBtn);
    }
}

function createSedesPageButton(pageNumber) {
    const button = document.createElement('button');
    button.textContent = pageNumber;
    button.classList.add('page-num-btn');
    if (pageNumber === currentSedesPage) {
        button.classList.add('active');
    }
    button.addEventListener('click', () => {
        if (pageNumber !== currentSedesPage) {
            currentSedesPage = pageNumber;
            fetchAllSedes();
        }
    });
    return button;
}

// --- Función asíncrona para actualizar el estado del usuario ---
async function updateUserStatus(userId, newStatus) {
    try {
        const response = await fetch('/systemahidrofalcon/api/administrador?action=updateUserStatus', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: userId,
                status: newStatus
            })
        });
        const data = await response.json();
        if (data.success) {
            iziToast.success({
                title: 'Éxito',
                message: data.message,
                position: 'topRight'
            });
            fetchAllSedes();
        } else {
            iziToast.error({
                title: 'Error',
                message: data.message,
                position: 'topRight'
            });
        }
    } catch (error) {
        iziToast.error({
            title: 'Error de Conexión',
            message: 'No se pudo conectar al servidor.',
            position: 'topRight'
        });
        console.error('Error de red al actualizar el estado del usuario:', error);
    }
}

// --- Función asíncrona para obtener la bitácora (con paginación) ---
async function fetchBitacora() {
    currentSearchBitacoraTerm = bitacoraSearchInput ? bitacoraSearchInput.value.trim() : '';

    if (bitacoraTableBody) bitacoraTableBody.innerHTML = initialLoadingRow;
    toggleBitacoraErrorMessage('', false);

    const params = new URLSearchParams({
        search: currentSearchBitacoraTerm,
        page: currentBitacoraPage,
        limit: bitacoraItemsPerPage
    });

    const endpointUrl = `/systemahidrofalcon/api/administrador?action=getBitacora&${params.toString()}`;

    try {
        const response = await fetch(endpointUrl);
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        const data = await response.json();

        if (data.success) {
            totalBitacoraPages = Math.ceil(data.totalLogs / bitacoraItemsPerPage);
            renderBitacoraTable(data.logs);
            updateBitacoraPaginationControls();
        } else {
            console.error('Error al cargar la bitácora:', data.message);
            if (bitacoraTableBody) bitacoraTableBody.innerHTML = '<tr><td colspan="4" style="text-align: center;">' + (data.message || 'No se encontraron registros.') + '</td></tr>';
            totalBitacoraPages = 0;
            updateBitacoraPaginationControls();
        }
    } catch (error) {
        console.error('Error de red al cargar la bitácora:', error);
        if (bitacoraTableBody) bitacoraTableBody.innerHTML = '<tr><td colspan="4" style="text-align: center;">Error de red al cargar la bitácora.</td></tr>';
        totalBitacoraPages = 0;
        updateBitacoraPaginationControls();
    }
}

// --- Función para renderizar los datos en la tabla de bitácora ---
function renderBitacoraTable(logs) {
    if (!bitacoraTableBody) {
        console.error('renderBitacoraTable: bitacoraTableBody no encontrado.');
        return;
    }
    bitacoraTableBody.innerHTML = '';

    if (logs.length === 0) {
        const emptyRow = bitacoraTableBody.insertRow();
        const emptyCell = emptyRow.insertCell();
        emptyCell.colSpan = 4;
        emptyCell.textContent = 'No se encontraron registros en la bitácora.';
        emptyCell.style.textAlign = 'center';
        return;
    }

    logs.forEach(log => {
        const row = bitacoraTableBody.insertRow();
        row.insertCell().textContent = log.id;
        row.insertCell().textContent = log.id_usuario;
        row.insertCell().textContent = new Date(log.fecha).toLocaleString();
        row.insertCell().textContent = log.mensaje;
    });
}

// --- Funciones para la paginación de Bitácora ---
function updateBitacoraPaginationControls() {
    if (prevBitacoraPageBtn) prevBitacoraPageBtn.disabled = currentBitacoraPage === 1;
    if (nextBitacoraPageBtn) nextBitacoraPageBtn.disabled = currentBitacoraPage === totalBitacoraPages || totalBitacoraPages === 0;

    if (!bitacoraPaginationNumbersContainer) return;
    bitacoraPaginationNumbersContainer.innerHTML = '';
    const maxPageButtons = 5;
    let startPage = Math.max(1, currentBitacoraPage - Math.floor(maxPageButtons / 2));
    let endPage = Math.min(totalBitacoraPages, startPage + maxPageButtons - 1);

    if (endPage - startPage + 1 < maxPageButtons && totalBitacoraPages > maxPageButtons) {
        startPage = Math.max(1, endPage - maxPageButtons + 1);
    }

    if (startPage > 1) {
        const firstPageBtn = createBitacoraPageButton(1);
        bitacoraPaginationNumbersContainer.appendChild(firstPageBtn);
        if (startPage > 2) {
            const ellipsis = document.createElement('span');
            ellipsis.textContent = '...';
            bitacoraPaginationNumbersContainer.appendChild(ellipsis);
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        const pageBtn = createBitacoraPageButton(i);
        bitacoraPaginationNumbersContainer.appendChild(pageBtn);
    }

    if (endPage < totalBitacoraPages) {
        if (endPage < totalBitacoraPages - 1) {
            const ellipsis = document.createElement('span');
            ellipsis.textContent = '...';
            bitacoraPaginationNumbersContainer.appendChild(ellipsis);
        }
        const lastPageBtn = createBitacoraPageButton(totalBitacoraPages);
        bitacoraPaginationNumbersContainer.appendChild(lastPageBtn);
    }
}

function createBitacoraPageButton(pageNumber) {
    const button = document.createElement('button');
    button.textContent = pageNumber;
    button.classList.add('page-num-btn');
    if (pageNumber === currentBitacoraPage) {
        button.classList.add('active');
    }
    button.addEventListener('click', () => {
        if (pageNumber !== currentBitacoraPage) {
            currentBitacoraPage = pageNumber;
            fetchBitacora();
        }
    });
    return button;
}

// --- FUNCIÓN AGREGADA PARA ELIMINAR EL USUARIO ---
async function deleteUser(userId) {
    try {
        const response = await fetch('/systemahidrofalcon/api/administrador?action=deleteUser', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: userId
            })
        });
        const data = await response.json();
        if (data.success) {
            iziToast.success({
                title: 'Éxito',
                message: data.message,
                position: 'topRight'
            });
            fetchAllSedes();
        } else {
            iziToast.error({
                title: 'Error',
                message: data.message,
                position: 'topRight'
            });
        }
    } catch (error) {
        iziToast.error({
            title: 'Error de Conexión',
            message: 'No se pudo conectar al servidor.',
            position: 'topRight'
        });
        console.error('Error de red al eliminar el usuario:', error);
    }
}

async function openUpdateRangeModal(userId) {
    currentEditingUserId = userId;
    
    try {
        // Obtener los rangos disponibles según el rango del usuario actual
        const response = await fetch('/systemahidrofalcon/api/administrador?action=getAvailableRanks', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                current_user_rank: currentUserRank
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Llenar el select con los rangos disponibles - CORREGIDO: usa 'newRange' no 'addSedeNombre'
            const selectElement = document.getElementById('newRange');
            if (!selectElement) {
                console.error('Error: No se encontró el elemento newRange');
                iziToast.error({
                    title: 'Error',
                    message: 'Error al cargar el formulario',
                    position: 'topRight'
                });
                return;
            }
            
            selectElement.innerHTML = '';
            
            data.ranks.forEach(rank => {
                const option = document.createElement('option');
                option.value = rank.id;
                option.textContent = rank.rango;
                selectElement.appendChild(option);
            });
            
            // Obtener el rango actual del usuario
            try {
                const userResponse = await fetch(`/systemahidrofalcon/api/administrador?action=getUserRank&user_id=${userId}`);
                const userData = await userResponse.json();
                
                if (userData.success) {
                    selectElement.value = userData.rank;
                    console.log(userData.rank)
                }
            } catch (userError) {
                console.warn('No se pudo obtener el rango actual:', userError);
            }
            
            // Mostrar el modal usando el trigger oculto
            MicroModal.show('update-range-modal');
            
        } else {
            console.error('Error al cargar rangos:', data.message);
            iziToast.error({
                title: 'Error',
                message: 'No se pudieron cargar los rangos disponibles.',
                position: 'topRight'
            });
        }
    } catch (error) {
        console.error('Error al cargar rangos:', error);
        iziToast.error({
            title: 'Error de Conexión',
            message: 'No se pudo conectar al servidor.',
            position: 'topRight'
        });
    }
}

// Función para actualizar el rango de un usuario
async function updateUserRank(userId, newRank) {
    try {
        const response = await fetch('/systemahidrofalcon/api/administrador?action=updateUserRank', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                user_id: userId,
                new_rank: newRank
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            iziToast.success({
                title: 'Éxito',
                message: data.message,
                position: 'topRight'
            });
            // Recargar la tabla de usuarios
            fetchAllSedes();
        } else {
            iziToast.error({
                title: 'Error',
                message: data.message,
                position: 'topRight'
            });
        }
    } catch (error) {
        iziToast.error({
            title: 'Error de Conexión',
            message: 'No se pudo conectar al servidor.',
            position: 'topRight'
        });
        console.error('Error al actualizar el rango:', error);
    }
}

async function submitRangeUpdate() {
    const newRank = document.getElementById('newRange').value;
    
    if (currentEditingUserId && newRank) {
        await updateUserRank(currentEditingUserId, newRank);
        MicroModal.close('update-range-modal');
    }
}

// --- Event Listeners iniciales ---
document.addEventListener('DOMContentLoaded', () => {
    fetchAllSedes();
    fetchBitacora();

    // Inicializar MicroModal
    MicroModal.init({
        openTrigger: 'data-micromodal-trigger',
        closeTrigger: 'data-micromodal-close',
        disableScroll: true,
        disableFocus: false,
        awaitOpenAnimation: true,
        awaitCloseAnimation: true
    });

    // Configurar el formulario de modificación de rango
    const updateRangeForm = document.getElementById('addSedeForm');
    if (updateRangeForm) {
        updateRangeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const newRank = document.getElementById('addSedeNombre').value;
            
            if (currentEditingUserId && newRank) {
                updateUserRank(currentEditingUserId, newRank);
                MicroModal.close('update-range-modal');
            }
        });
    }

    if (sedesSearchInput) {
        sedesSearchInput.addEventListener('input', () => {
            currentSedesPage = 1;
            fetchAllSedes();
        });
    }

    if (showAllSedesBtn) {
        showAllSedesBtn.addEventListener('click', () => {
            if (sedesSearchInput) sedesSearchInput.value = '';
            currentSedesPage = 1;
            fetchAllSedes();
        });
    }

    if (prevSedesPageBtn) {
        prevSedesPageBtn.addEventListener('click', () => {
            if (currentSedesPage > 1) {
                currentSedesPage--;
                fetchAllSedes();
            }
        });
    }

    if (nextSedesPageBtn) {
        nextSedesPageBtn.addEventListener('click', () => {
            if (currentSedesPage < totalSedesPages) {
                currentSedesPage++;
                fetchAllSedes();
            }
        });
    }

    if (bitacoraSearchInput) {
        bitacoraSearchInput.addEventListener('input', () => {
            currentBitacoraPage = 1;
            fetchBitacora();
        });
    }
    
    if (prevBitacoraPageBtn) {
        prevBitacoraPageBtn.addEventListener('click', () => {
            if (currentBitacoraPage > 1) {
                currentBitacoraPage--;
                fetchBitacora();
            }
        });
    }

    if (nextBitacoraPageBtn) {
        nextBitacoraPageBtn.addEventListener('click', () => {
            if (currentBitacoraPage < totalBitacoraPages) {
                currentBitacoraPage++;
                fetchBitacora();
            }
        });
    }

    // --- Manejador de eventos para los dropdowns y acciones ---
    sedesTableBody.addEventListener('click', (e) => {
        const navItem = e.target.closest('.nav-item');
        if (navItem) {
            e.stopPropagation();
            const dropdown = navItem.querySelector('.dropdown-item');
            
            document.querySelectorAll('.dropdown-item.show').forEach(openDropdown => {
                if (openDropdown !== dropdown) {
                    openDropdown.classList.remove('show');
                }
            });
            
            if (dropdown) {
                dropdown.classList.toggle('show');
            }
        }

        const actionButton = e.target.closest('.options-item');
        if (actionButton) {
            const action = actionButton.dataset.action;
            const id = actionButton.dataset.id;
            
            const openDropdown = actionButton.closest('.dropdown-item');
            if (openDropdown) {
                openDropdown.classList.remove('show');
            }
            
            let confirmationText = '';
            let confirmAction = () => {};

            switch (action) {
                case 'bloquear':
                    confirmationText = `¿Estás seguro de que quieres **bloquear** a este usuario?`;
                    confirmAction = () => updateUserStatus(id, 'restringido'); 
                    break;
                case 'desbloquear':
                    confirmationText = `¿Estás seguro de que quieres **desbloquear** a este usuario?`;
                    confirmAction = () => updateUserStatus(id, 'activo');
                    break;
                case 'eliminar':
                    confirmationText = `¿Estás seguro de que quieres **eliminar** al usuario con ID ${id}? Esta acción es irreversible.`;
                    confirmAction = () => deleteUser(id);
                    break;
                case 'asignar-rango':
                    openUpdateRangeModal(id);
                    return;
                default:
                    console.error('Acción no reconocida:', action);
                    return;
            }
            
            if (confirmationText) {
                iziToast.show({
                    theme: 'dark',
                    icon: 'icon-person',
                    title: 'Confirmar Acción',
                    message: confirmationText,
                    position: 'center',
                    progressBarColor: 'rgb(255, 0, 0)',
                    buttons: [
                        ['<button><b>SI</b></button>', function (instance, toast) {
                            confirmAction();
                            instance.hide({ transitionOut: 'fadeOutUp' }, toast, 'button');
                        }, true],
                        ['<button>NO</button>', function (instance, toast) {
                            instance.hide({ transitionOut: 'fadeOutDown' }, toast, 'button');
                        }]
                    ],
                });
            }
        }
    });

    // Cierra el dropdown si se hace clic fuera de él
    window.addEventListener('click', (e) => {
        if (!e.target.closest('.nav-item')) {
            const dropdowns = document.querySelectorAll('.dropdown-item');
            dropdowns.forEach(d => d.classList.remove('show'));
        }
    });
});

// Hacer las funciones globales para que estén disponibles
window.openUpdateRangeModal = openUpdateRangeModal;
window.updateUserRank = updateUserRank;