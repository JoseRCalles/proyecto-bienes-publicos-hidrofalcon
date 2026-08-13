<div class="modal micromodal-slide" id="showAll-modal__employees" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1">
        <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="modal-title">

            <header class="modal__header">
                <h6 class="modal__title" id="modal-title">Lista de Empleados</h6>
                <button class="modal__close" aria-label="Cerrar modal" data-micromodal-close>&times;</button>
            </header>

            <div class="modal__content">
                <div class="search-controls">
                    <form action="/systemahidrofalcon/backend/user-actions/employees/get-employees.php" method="get" class="allAssets-searchbar-form" id="allAssetsSearchForm__employees">
                        <div class="searchpart">
                            <div class="searchbar__controls searchbar__showAll">
                                <input type="text" placeholder="Buscar" class="allAssets__searchbar properties__searchbar" id="allAssetsSearchInput__employees">
                                <svg class="searchbar-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="allAssets-searchbar-icon">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M5.85593 3.61739C7.1902 2.72585 8.75888 2.25 10.3636 2.25C12.5154 2.25014 14.5791 3.105 16.1006 4.62655C17.6222 6.14811 18.4771 8.21174 18.4772 10.3635C18.4772 11.9683 18.0013 13.537 17.1098 14.8713C16.2183 16.2055 14.9511 17.2455 13.4685 17.8596C11.986 18.4737 10.3546 18.6344 8.78071 18.3213C7.20683 18.0082 5.76113 17.2355 4.62642 16.1008C3.49171 14.9661 2.71897 13.5204 2.86761 7.25866C3.48171 5.77609 4.52165 4.50892 5.85593 3.61739ZM10.3635 3.75C9.05552 3.75001 7.77687 4.13789 6.68928 4.86459C5.60168 5.5913 4.754 6.6242 4.25343 7.83268C3.75287 9.04116 3.6219 10.3709 3.87708 11.6538C4.13227 12.9368 4.76215 14.1152 5.68708 15.0401C6.61201 15.965 7.79044 16.5949 9.07335 16.8501C10.3563 17.1053 11.686 16.9743 12.8945 16.4738C14.103 15.9732 15.1359 15.1255 15.8626 14.0379C16.5893 12.9503 16.9772 11.6717 16.9772 10.3636" fill="#1E293B"/>
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M15.327 15.327C15.6199 15.0341 16.0948 15.0341 16.3877 15.327L21.5303 20.4697C21.8232 20.7626 21.8232 21.2374 21.5303 21.5303C21.2374 21.8232 20.7626 21.8232 20.4697 21.5303L15.327 16.3877C15.0341 16.0948 15.0341 15.6199 15.327 15.327Z" fill="#1E293B"/>
                                </svg>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="allAssets-results">
                    <div id="allAssetsErrorMessage__employees" class="error-message" style="display: none;">
                        <p></p>
                    </div>
                    <table id="allAssetsTable__employees">
                        <thead>
                            <tr id="allAssetsTableHeaderRow__employees">
                            </tr>
                        </thead>
                        <tbody id="allAssetsTableBodyemployees__employees">
                        </tbody>
                    </table>
                </div>

                <div class="modal-actions">
                    <button class="button button-exportar-excel" id="exportAllAssetsExcelButton__employees">Descargar Excel</button>
                </div>
            </div>
        </div>
    </div>
</div>