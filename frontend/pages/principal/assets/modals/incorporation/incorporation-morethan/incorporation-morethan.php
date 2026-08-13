<div class="modal micromodal-slide" id="incorporation-excel-upload-modal" aria-hidden="true">
        <div class="modal__overlay" tabindex="-1">
        <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="modal-title">

            <header class="modal__header">
                <h6 class="modal__title" id="incorporation-modal-title">Ingreso de Activo Fijo y Asignación por Excel</h6>
                <button class="modal__close" aria-label="Cerrar modal" data-micromodal-close>&times;</button>
            </header>

            <div class="modal__content" id="modal__content-incorporation-morethan">
                <form class="enter-data" id="incorporation-excel-upload-form" action="/systemahidrofalcon/api/activos-varios" method="post" enctype="multipart/form-data">
                    <div style="margin-bottom: 15px;">
                        <label for="incorporation-file-input">Selecciona tu archivo Excel:</label>
                        <input class="input" type="file" id="incorporation-file-input" name="excelFile" accept=".xls,.xlsx, .xlsm, .xlsb, .xlam, .xltm, .xltx" required>
                    </div>

                    <div id="incorporation-message-container" style="margin-top: 10px;"></div>
                    <div id="incorporation-results-container" style="margin-top: 20px;"></div>

                    <button class="button" type="submit" id="incorporation-submit-btn">Subir y Procesar</button>
                </form>
                
                <div style="margin-top: 20px;">
                    <table class="table-properties">
                        <thead class="head">
                            <tr class="head" id="propertiesTableHeaderRow"></tr>
                        </thead>
                        <tbody id="propertiesTableBody__incorporation-morethan">
                            <tr>
                                <td colspan="100%">Cargando bienes...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>