<div class="modal micromodal-slide" id="add-sede-modal" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1">
        <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="sede-add-modal-title">
            <header class="modal__header">
                <h6 class="modal__title" id="sede-add-modal-title">Agregar Sede</h6>
                <button class="modal__close" aria-label="Cerrar modal" data-micromodal-close>&times;</button>
            </header>
            
            <div class="modal__content text-inputs">
                <form id="addSedeForm" class="enter-data">
                    <div class="input-container input-container__smaller">
                        <label for="addSedeNombre" class="label-input">Nombre de la Sede</label>
                        <input type="text" class="form-control input" id="addSedeNombre" name="nombre_sede" required>
                        <div class="err-box"></div>
                    </div>
                    <div class="input-container input-container__smaller">
                        <label for="addSedeDireccion" class="label-input">Dirección</label>
                        <input type="text" class="form-control input" id="addSedeDireccion" name="direccion_sede">
                        <div class="err-box"></div>
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