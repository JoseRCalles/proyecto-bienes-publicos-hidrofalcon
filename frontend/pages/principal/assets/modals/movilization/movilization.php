<div class="modal micromodal-slide" id="movilization-modal" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1">
        <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="movilization-modal-title">

            <header class="modal__header">
                <h6 class="modal__title" id="movilization-modal-title">Movilizar Activo</h6>
                <button class="modal__close" aria-label="Cerrar modal" data-micromodal-close>&times;</button>
            </header>

            <div class="modal__content">
                <form id="movilizar-form">
                    <div class="employee-selection-container">
                        <div class="head-input__container" id="err-em-box__movilization">
                            <div class="employee-input__container">
                                <label for="display_employee_name__movilization" class="label-input employee-label">Trabajador Asignado</label>
                                <input type="text" id="display_employee_name__movilization" class="input input-employee" readonly placeholder="Seleccione un trabajador">
                                <input type="hidden" name="trabajador_id_common__movilization" id="hidden_employee_id_common__movilization">
                                <div class="search-container">
                                    <button type="button" id="toggle-search-employee-btn__movilization" class="btn-search-employee" style="flex-shrink: 0;">Buscar</button>
                                </div>
                            </div>
                            <div class="err-employee err-box" id="err-employee__movilization"></div>
                        </div>
                    </div>

                    <section id="employee-search-section__movilization" class="employee-search-section">
                        <div class="search-controls">
                            <input class='input table-employee__input' type="text" id="employee-search-input__movilization" placeholder="Cédula, nombre o apellido...">
                            <select id="gerencia-filter__movilization" class='input table-employee__input select-employee'>
                                <option value="">Todas las Gerencias</option>
                                </select>
                        </div>
                        <div class="employee-results__container">
                            <table class="employee-table">
                                <thead>
                                    <tr id="employee-table-headers__movilization">
                                        <th>Cédula</th>
                                        <th>Nombres</th>
                                        <th>Apellidos</th>
                                        <th>Teléfono</th>
                                        <th>Cargo</th>
                                        <th>Gerencia</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="employee-table-body__movilization"></tbody>
                            </table>
                            <p id="no-results-message__movilization">No se encontraron trabajadores.</p>
                        </div>
                        <div class="pagination-controls">
                            <button id="prev-page-employee-btn__movilization" class="page-btn" disabled>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <g clip-path="url(#clip0_403_3101)">
                                        <path d="M17.1699 24C17.0383 24.0008 16.9078 23.9756 16.786 23.9258C16.6641 23.876 16.5533 23.8027 16.4599 23.71L8.28989 15.54C7.82426 15.0756 7.45483 14.5238 7.20277 13.9164C6.9507 13.3089 6.82095 12.6577 6.82095 12C6.82095 11.3424 6.9507 10.6912 7.20277 10.0837C7.45483 9.47626 7.82426 8.92451 8.28989 8.46005L16.4599 0.290063C16.5531 0.196825 16.6638 0.122864 16.7856 0.0724035C16.9075 0.0219432 17.038 -0.00402832 17.1699 -0.00402832C17.3018 -0.00402832 17.4323 0.0219432 17.5541 0.0724035C17.676 0.122864 17.7867 0.196825 17.8799 0.290063C17.9731 0.383301 18.0471 0.493991 18.0976 0.615813C18.148 0.737635 18.174 0.868203 18.174 1.00006C18.174 1.13192 18.148 1.26249 18.0976 1.38431C18.0471 1.50613 17.9731 1.61682 17.8799 1.71006L9.70989 9.88005C9.14809 10.4426 8.83253 11.205 8.83253 12C8.83253 12.7951 9.14809 13.5575 9.70989 14.12L17.8799 22.29C17.9736 22.383 18.048 22.4936 18.0988 22.6155C18.1496 22.7373 18.1757 22.868 18.1757 23C18.1757 23.132 18.1496 23.2628 18.0988 23.3846C18.048 23.5065 17.9736 23.6171 17.8799 23.71C17.7865 23.8027 17.6756 23.876 17.5538 23.9258C17.432 23.9756 17.3015 24.0008 17.1699 24Z" fill="#374957"/>
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_403_3101">
                                            <rect width="24" height="24" fill="white"/>
                                        </clipPath>
                                    </defs>
                                </svg>
                            </button>
                            <button id="next-page-employee-btn__movilization" class="page-btn" disabled>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <g clip-path="url(#clip0_403_3214)">
                                        <path d="M6.99985 24C6.86824 24.0008 6.73778 23.9756 6.61594 23.9258C6.4941 23.876 6.38329 23.8027 6.28985 23.71C6.19612 23.6171 6.12172 23.5065 6.07096 23.3846C6.02019 23.2628 5.99405 23.132 5.99405 23C5.99405 22.868 6.02019 22.7373 6.07096 22.6155C6.12172 22.4936 6.19612 22.383 6.28985 22.29L14.4598 14.12C15.0216 13.5575 15.3372 12.7951 15.3372 12C15.3372 11.205 15.0216 10.4426 14.4598 9.88005L6.28985 1.71006C6.10154 1.52176 5.99576 1.26636 5.99576 1.00006C5.99576 0.733761 6.10154 0.478366 6.28985 0.290063C6.47815 0.101759 6.73355 -0.00402832 6.99985 -0.00402832C7.26615 -0.00402832 7.52154 0.101759 7.70985 0.290063L15.8798 8.46005C16.3455 8.92451 16.7149 9.47626 16.967 10.0837C17.219 10.6912 17.3488 11.3424 17.3488 12C17.3488 12.6577 17.219 13.3089 16.967 13.9164C16.7149 14.5238 16.3455 15.0756 15.8798 15.54L7.70985 23.71C7.61641 23.8027 7.50559 23.876 7.38375 23.9258C7.26192 23.9756 7.13145 24.0008 6.99985 24Z" fill="#374957"/>
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_403_3214">
                                            <rect width="24" height="24" fill="white"/>
                                        </clipPath>
                                    </defs>
                                </svg>
                            </button>
                            <span id="page-info__movilization" style="display: none;">Página 1 de 1</span>
                            <div id="page-numbers__movilization" style="display: inline-block;" class="page-numbers"></div>
                        </div>
                    </section>

                    <div class="asset-selection-container">
                        <div class="head-input__container" id="asset-input-container__movilization">
                            <div class="asset-input__container">
                                <label for="display_asset_name__movilization" class="label-input asset-label">Activo Fijo Asignado</label>
                                <input type="text" id="display_asset_name__movilization" name="asset_asignado_display__movilization" class="input input-asset" readonly placeholder="Seleccione un activo fijo">
                                <div class="search-container">
                                    <button type="button" id="toggle-asset-search-btn__movilization" class="btn-search-asset" style="flex-shrink: 0;">Buscar Activo</button>
                                </div>
                            </div>
                            <input type="hidden" name="asset_id__movilization" id="hidden_asset_id__movilization">
                            <div class="err-asset err-box" id="err-asset__movilization"></div>
                        </div>
                    </div>

                    <section id="asset-search-section__movilization" class="asset-search-section">
                        <div class="search-controls">
                            <input class='input table-asset__input' type="text" id="asset-search-input__movilization" placeholder="Código, serial, marca o modelo...">
                            <select id="estatus-filter__movilization" class='input table-asset__input select-asset'>
                                <option value="">Todos los Estatus</option>
                                </select>
                        </div>
                        <div class="asset-results__container">
                            <table class="asset-table">
                                <thead>
                                    <tr id="asset-table-headers__movilization">
                                        <th>Código Activo</th>
                                        <th>Descripcion</th>
                                        <th>Serial</th>
                                        <th>Marca</th>
                                        <th>Modelo</th>
                                        <th>Estatus</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="asset-table-body__movilization"></tbody>
                            </table>
                            <p id="no-asset-results-message__movilization" style="display: none;">No se encontraron activos fijos.</p>
                        </div>
                        <div class="pagination-controls">
                            <button id="prev-page-asset-btn__movilization" type="button" class="page-btn" >
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><g clip-path="url(#clip0_403_3101)"><path d="M17.1699 24C17.0383 24.0008 16.9078 23.9756 16.786 23.9258C16.6641 23.876 16.5533 23.8027 16.4599 23.71L8.28989 15.54C7.82426 15.0756 7.45483 14.5238 7.20277 13.9164C6.9507 13.3089 6.82095 12.6577 6.82095 12C6.82095 11.3424 6.9507 10.6912 7.20277 10.0837C7.45483 9.47626 7.82426 8.92451 8.28989 8.46005L16.4599 0.290063C16.5531 0.196825 16.6638 0.122864 16.7856 0.0724035C16.9075 0.0219432 17.038 -0.00402832 17.1699 -0.00402832C17.3018 -0.00402832 17.4323 0.0219432 17.5541 0.0724035C17.676 0.122864 17.7867 0.196825 17.8799 0.290063C17.9731 0.383301 18.0471 0.493991 18.0976 0.615813C18.148 0.737635 18.174 0.868203 18.174 1.00006C18.174 1.13192 18.148 1.26249 18.0976 1.38431C18.0471 1.50613 17.9731 1.61682 17.8799 1.71006L9.70989 9.88005C9.14809 10.4426 8.83253 11.205 8.83253 12C8.83253 12.7951 9.14809 13.5575 9.70989 14.12L17.8799 22.29C17.9736 22.383 18.048 22.4936 18.0988 22.6155C18.1496 22.7373 18.1757 22.868 18.1757 23C18.1757 23.132 18.1496 23.2628 18.0988 23.3846C18.048 23.5065 17.9736 23.6171 17.8799 23.71C17.7865 23.8027 17.6756 23.876 17.5538 23.9258C17.432 23.9756 17.3015 24.0008 17.1699 24Z" fill="#374957"/></g><defs><clipPath id="clip0_403_3101"><rect width="24" height="24" fill="white"/></clipPath></defs></svg>
                            </button>
                            <button id="next-page-asset-btn__movilization" type="button" class="page-btn" >
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><g clip-path="url(#clip0_403_3214)"><path d="M6.99985 24C6.86824 24.0008 6.73778 23.9756 6.61594 23.9258C6.4941 23.876 6.38329 23.8027 6.28985 23.71C6.19612 23.6171 6.12172 23.5065 6.07096 23.3846C6.02019 23.2628 5.99405 23.132 5.99405 23C5.99405 22.868 6.02019 22.7373 6.07096 22.6155C6.12172 22.4936 6.19612 22.383 6.28985 22.29L14.4598 14.12C15.0216 13.5575 15.3372 12.7951 15.3372 12C15.3372 11.205 15.0216 10.4426 14.4598 9.88005L6.28985 1.71006C6.10154 1.52176 5.99576 1.26636 5.99576 1.00006C5.99576 0.733761 6.10154 0.478366 6.28985 0.290063C6.47815 0.101759 6.73355 -0.00402832 6.99985 -0.00402832C7.26615 -0.00402832 7.52154 0.101759 7.70985 0.290063L15.8798 8.46005C16.3455 8.92451 16.7149 9.47626 16.967 10.0837C17.219 10.6912 17.3488 11.3424 17.3488 12C17.3488 12.6577 17.219 13.3089 16.967 13.9164C16.7149 14.5238 16.3455 15.0756 15.8798 15.54L7.70985 23.71C7.61641 23.8027 7.50559 23.876 7.38375 23.9258C7.26192 23.9756 7.13145 24.0008 6.99985 24Z" fill="#374957"/></g><defs><clipPath id="clip0_403_3214"><rect width="24" height="24" fill="white"/></clipPath></defs></svg>
                            </button>
                            <span id="asset-page-info__movilization" style="display: none;">Página 1 de 1</span>
                            <div id="asset-page-numbers__movilization" style="display: inline-block;" class="page-numbers"></div>
                        </div>
                    </section>

                    <div class="extra-actions" id="extra-actions__movilization">
                        <button class="button submit-button movilization-button" type="submit" id="movilizar-submit-btn">Movilizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>