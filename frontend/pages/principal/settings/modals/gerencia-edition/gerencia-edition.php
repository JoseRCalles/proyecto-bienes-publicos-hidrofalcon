<div class="modal micromodal-slide" id="edit-gerencia-modal" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1">
        <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="gerencia-edit-modal-title">
            <header class="modal__header">
                <h6 class="modal__title" id="gerencia-edit-modal-title">Editar Gerencia</h6>
                <button class="modal__close" aria-label="Cerrar modal" data-micromodal-close>&times;</button>
            </header>
            
            <div class="modal__content text-inputs">
                <form id="editGerenciaForm" class="enter-data">
                    <input type="hidden" id="editGerenciaId" name="id">
                    <div class="input-container">
                        <label for="editGerenciaNombre" class="label-input">Nombre de la Gerencia</label>
                        <input type="text" class="form-control input" id="editGerenciaNombre" name="gerencia" required>
                    </div>
                    <div class="input-container">
                        <label for="editGerenciaEncargado" class="label-input">Encargado</label>
                        <input type="text" class="form-control input" id="editGerenciaEncargado" name="encargado">
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