<div class="modal micromodal-slide" id="edit-sede-modal" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1">
        <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="sede-edit-modal-title">
            <header class="modal__header">
                <h6 class="modal__title" id="sede-edit-modal-title">Editar Sede</h6>
                <button class="modal__close" aria-label="Cerrar modal" data-micromodal-close>&times;</button>
            </header>

            <div class="modal__content text-inputs">
                <form id="editSedeForm" class="enter-data">
                    <input type="hidden" id="editSedeId" name="id">
                    <div class="input-container">
                        <label for="editSedeNombre" class="label-input">Nombre de la Sede</label>
                        <input type="text" class="form-control input" id="editSedeNombre" name="nombre_sede" required>
                    </div>
                    <div class="input-container">
                        <label for="editSedeMunicipio" class="label-input">Municipio</label>
                        <input type="text" class="form-control input" id="editSedeMunicipio" name="municipio">
                    </div>
                    <div class="modal-actions">
                        <button type="submit" class="button button-success">Guardar</button>
                        <button type="button" class="button button-secondary" data-micromodal-close>Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>