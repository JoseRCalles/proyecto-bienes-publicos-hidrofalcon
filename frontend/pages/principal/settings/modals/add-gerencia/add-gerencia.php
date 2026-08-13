<div class="modal micromodal-slide" id="add-gerencia-modal" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1">
        <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="add-gerencia-modal-title">
            <header class="modal__header">
                <h6 class="modal__title" id="add-gerencia-modal-title">Agregar Gerencia</h6>
                <button class="modal__close" aria-label="Cerrar modal" data-micromodal-close>&times;</button>
            </header>

            <div class="modal__content text-inputs">
                <form id="addGerenciaForm" class="enter-data">
                    <div class="input-container input-container__smaller">
                        <label for="addGerenciaNombre" class="label-input">Gerencia</label>
                        <input type="text" class="form-control input" id="addGerenciaNombre" name="gerencia" required>
                        <div class="err-box err-box__gerencia"></div> </div>
                    <div class="input-container input-container__smaller">
                        <label for="addGerenciaEncargado" class="label-input">Encargado</label>
                        <input type="text" class="form-control input" id="addGerenciaEncargado" name="encargado" required>
                        <div class="err-box err-box__gerencia"></div> </div>
                    <div class="modal-actions">
                        <button type="submit" class="button button-success">Guardar</button>
                        <button type="button" class="button button-secondary" data-micromodal-close>Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>