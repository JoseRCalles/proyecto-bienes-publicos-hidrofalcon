<div class="modal micromodal-slide" id="showAll-modal" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1" data-micromodal-close>
        <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="showAll-modal-title">

            <header class="modal__header">
                <h6 class="modal__title" id="showAll-modal-title">Todos los Activos</h6>
                <button class="modal__close" aria-label="Cerrar modal" data-micromodal-close>&times;</button>
            </header>

            <div class="modal__content" id="modal__content-showAll">
                <div class="search-controls">
                    <form action="" method="get" class="allAssets-searchbar-form" id="allAssetsSearchForm">
                        <div class="searchpart">
                            <div class="searchbar__controls searchbar__showAll">
                                <input type="text" placeholder="Buscar" class="allAssets__searchbar properties__searchbar" id="allAssetsSearchInput">
                                <svg class="searchbar-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="allAssets-searchbar-icon">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M5.85593 3.61739C7.1902 2.72585 8.75888 2.25 10.3636 2.25C12.5154 2.25014 14.5791 3.105 16.1006 4.62655C17.6222 6.14811 18.4771 8.21174 18.4772 10.3635C18.4772 11.9683 18.0013 13.537 17.1098 14.8713C16.2183 16.2055 14.9511 17.2455 13.4685 17.8596C11.986 18.4737 10.3546 18.6344 8.78071 18.3213C7.20683 18.0082 5.76113 17.2355 4.62642 16.1008C3.49171 14.9661 2.71897 13.5204 2.86761 7.25866C3.48171 5.77609 4.52165 4.50892 5.85593 3.61739ZM10.3635 3.75C9.05552 3.75001 7.77687 4.13789 6.68928 4.86459C5.60168 5.5913 4.754 6.6242 4.25343 7.83268C3.75287 9.04116 3.6219 10.3709 3.87708 11.6538C4.13227 12.9368 4.76215 14.1152 5.68708 15.0401C6.61201 15.965 7.79044 16.5949 9.07335 16.8501C10.3563 17.1053 11.686 16.9743 12.8945 16.4738C14.103 15.9732 15.1359 15.1255 15.8626 14.0379C16.5893 12.9503 16.9772 11.6717 16.9772 10.3636" fill="#1E293B"/>
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M15.327 15.327C15.6199 15.0341 16.0948 15.0341 16.3877 15.327L21.5303 20.4697C21.8232 20.7626 21.8232 21.2374 21.5303 21.5303C21.2374 21.8232 20.7626 21.8232 20.4697 21.5303L15.327 16.3877C15.0341 16.0948 15.0341 15.6199 15.327 15.327Z" fill="#1E293B"/>
                                </svg>
                            </div>
                            <div class="filter-container">
                                <button class="filter-button button" id="filter-button">
                                    <p class="filter-button__text">Filtros</p>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <g clip-path="url(#clip0_403_3232)">
                                            <path d="M1 4.75032H3.736C3.95064 5.54005 4.41917 6.23721 5.06933 6.73425C5.71948 7.23129 6.51512 7.50058 7.33350 7.50058C8.15188 7.50058 8.94752 7.23129 9.59767 6.73425C10.2478 6.23721 10.7164 5.54005 10.931 4.75032H23C23.2652 4.75032 23.5196 4.64496 23.7071 4.45743C23.8946 4.26989 24 4.01554 24 3.75032C24 3.48510 23.8946 3.23075 23.7071 3.04321C23.5196 2.85568 23.2652 2.75032 23 2.75032H10.931C10.7164 1.96059 10.2478 1.26343 9.59767 0.766389C8.94752 0.269352 8.15188 6.10352e-05 7.33350 6.10352e-05C6.51512 6.10352e-05 5.71948 0.269352 5.06933 0.766389C4.41917 1.26343 3.95064 1.96059 3.736 2.75032H1C0.734784 2.75032 0.480430 2.85568 0.292893 3.04321C0.105357 3.23075 0 3.48510 0 3.75032C0 4.01554 0.105357 4.26989 0.292893 4.45743C0.480430 4.64496 0.734784 4.75032 1 4.75032ZM7.333 2.00032C7.67912 2.00032 8.01746 2.10296 8.30525 2.29525C8.59303 2.48754 8.81734 2.76085 8.94979 3.08062C9.08224 3.40039 9.11690 3.75226 9.04937 4.09173C8.98185 4.43119 8.81518 4.74301 8.57044 4.98776C8.32570 5.23250 8.01388 5.39917 7.67441 5.46669C7.33494 5.53422 6.98307 5.49956 6.66330 5.36711C6.34353 5.23466 6.07022 5.01035 5.87793 4.72257C5.68564 4.43478 5.58300 4.09644 5.58300 3.75032C5.58353 3.28635 5.76807 2.84154 6.09615 2.51347C6.42422 2.18539 6.86903 2.00085 7.33300 2.00032Z" fill="#374957"/>
                                            <path d="M23 10.9991H20.264C20.0497 10.2092 19.5814 9.51177 18.9313 9.01452C18.2812 8.51728 17.4855 8.24786 16.6670 8.24786C15.8485 8.24786 15.0528 8.51728 14.4027 9.01452C13.7526 9.51177 13.2843 10.2092 13.0700 10.9991H1C0.734784 10.9991 0.480430 11.1044 0.292893 11.292C0.105357 11.4795 0 11.7339 0 11.9991C0 12.2643 0.105357 12.5187 0.292893 12.7062C0.480430 12.8937 0.734784 12.9991 1 12.9991H13.0700C13.2843 13.789 13.7526 14.4864 14.4027 14.9837C15.0528 15.4809 15.8485 15.7503 16.6670 15.7503C17.4855 15.7503 18.2812 15.4809 18.9313 14.9837C19.5814 14.4864 20.0497 13.789 20.2640 12.9991H23C23.2652 12.9991 23.5196 12.8937 23.7071 12.7062C23.8946 12.5187 24 12.2643 24 11.9991C24 11.7339 23.8946 11.4795 23.7071 11.292C23.5196 11.1044 23.2652 10.9991 23 10.9991ZM16.667 13.7491C16.3209 13.7491 15.9825 13.6465 15.6948 13.4542C15.4070 13.2619 15.1827 12.9886 15.0502 12.6688C14.9178 12.3490 14.8831 11.9971 14.9506 11.6577C15.0181 11.3182 15.1848 11.0064 15.4296 10.7617C15.6743 10.5169 15.9861 10.3502 16.3256 10.2827C16.6651 10.2152 17.0169 10.2499 17.3367 10.3823C17.6565 10.5148 17.9298 10.7391 18.1221 11.0268C18.3144 11.3146 18.4170 11.6530 18.4170 11.9991C18.4165 12.4631 18.2319 12.9079 17.9039 13.2359C17.5758 13.5640 17.1310 13.7486 16.6670 13.7491Z" fill="#374957"/>
                                            <path d="M23 19.2503H10.931C10.7164 18.4606 10.2478 17.7634 9.59767 17.2664C8.94752 16.7694 8.15188 16.5001 7.33350 16.5001C6.51512 16.5001 5.71948 16.7694 5.06933 17.2664C4.41917 17.7634 3.95064 18.4606 3.736 19.2503H1C0.734784 19.2503 0.480430 19.3557 0.292893 19.5432C0.105357 19.7307 0 19.9851 0 20.2503C0 20.5155 0.105357 20.7699 0.292893 20.9574C0.480430 21.1449 0.734784 21.2503 1 21.2503H3.736C3.95064 22.0400 4.41917 22.7372 5.06933 23.2342C5.71948 23.7313 6.51512 24.0006 7.33350 24.0006C8.15188 24.0006 8.94752 23.7313 9.59767 23.2342C10.2478 22.7372 10.7164 22.0400 10.931 21.2503H23C23.2652 21.2503 23.5196 21.1449 23.7071 20.9574C23.8946 20.7699 24 20.5155 24 20.2503C24 19.9851 23.8946 19.7307 23.7071 19.5432C23.5196 19.3557 23.2652 19.2503 23 19.2503ZM7.333 22.0003C6.98688 22.0003 6.64854 21.8977 6.36075 21.7054C6.07297 21.5131 5.84866 21.2398 5.71621 20.9200C5.58376 20.6002 5.54910 20.2484 5.61663 19.9089C5.68415 19.5694 5.85082 19.2576 6.09556 19.0129C6.34030 18.7681 6.65213 18.6015 6.99159 18.5339C7.33106 18.4664 7.68293 18.5011 8.00270 18.6335C8.32247 18.7660 8.59578 18.9903 8.78807 19.2781C8.98036 19.5658 9.08300 19.9042 9.08300 20.2503C9.08221 20.7142 8.89758 21.1588 8.56956 21.4869C8.24154 21.8149 7.79689 21.9995 7.33300 22.0003Z" fill="#374957"/>
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_403_3232">
                                            <rect width="24" height="24" fill="white"/>
                                            </clipPath>
                                        </defs>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="filter-selectors" id="filter-showall_container">
                            <div class="input-container input__showAll">
                                <label for="allAssets-fecha-adquisicion-start" class="label-input label__showAll">Fecha Adq. Desde:</label>
                                <input type="date" class="input input__showAll" id="allAssets-fecha-adquisicion-start">
                            </div>
                            <div class="input-container input__showAll">
                                <label for="allAssets-fecha-adquisicion-end" class="label-input label__showAll">Hasta:</label>
                                <input type="date" class="input input__showAll" id="allAssets-fecha-adquisicion-end">
                            </div> 
                            <div class="select-input input__showAll">
                                <label for="allAssets-estatus-select" class="label-input label__showAll">Estatus Físico</label>
                                <select class="input input__showAll" name="estatus-filter" id="allAssetsEstatusSelect"></select>
                            </div>
                            <div class="select-input input__showAll">
                                <label for="allAssets-gerencia-select" class="label-input label__showAll">Gerencia Adm</label>
                                <select class="input input__showAll" name="gerencia-filter" id="allAssets-gerencia-select"></select>
                            </div>
                        </div>

                        <div class="filter-selectors">
                            <div class="select-input input__showAll">
                                <label for="allAssets-estatus-adm-select" class="label-input label__showAll">Estatus Administrativo</label>
                                <select class="input input__showAll" name="estatus-adm-filter" id="allAssetsEstatusAdmSelect"></select>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="allAssets-results">
                    <div id="allAssetsErrorMessage" class="error-message" style="display: none;">
                        <p></p>
                    </div>
                    <table id="allAssetsTable">
                        <thead>
                            <tr id="allAssetsTableHeaderRow">
                            </tr>
                        </thead>
                        <tbody id="allAssetsTableBody">
                            </tbody>
                    </table>
                </div>

                <div class="modal-actions">
                    <button class="button button-exportar-excel" id="exportAllAssetsExcelButton">Descargar Excel</button>
                    <button class="button button-show-all" id="showAll-modalResetFiltersBtn">Res. Filtros</button>
                </div>
            </div>
        </div>
    </div>
</div>