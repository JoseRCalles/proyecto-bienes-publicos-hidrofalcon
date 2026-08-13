<div class="modal micromodal-slide" id="edit-employee-modal" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1">
        <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="edit-employee-modal-title">
            <header class="modal__header">
                <h6 class="modal__title" id="edit-employee-modal-title">Editar Empleado</h6>
                <button class="modal__close" aria-label="Cerrar modal" data-micromodal-close>&times;</button>
            </header>

            <div class="modal__content text-inputs">
                <form id="editEmployeeForm" class="enter-data">
                    <input type="hidden" id="editEmployeeId" name="id">
                    <div class="input-container">
                        <label for="editEmployeeCedula" class="label-input">Cédula</label>
                        <input type="text" class="form-control input" id="editEmployeeCedula" name="cedula" required>
                    </div>
                    <div class="input-container">
                        <label for="editEmployeeNombres" class="label-input">Nombres</label>
                        <input type="text" class="form-control input" id="editEmployeeNombres" name="nombres" required>
                    </div>
                    <div class="input-container">
                        <label for="editEmployeeApellidos" class="label-input">Apellidos</label>
                        <input type="text" class="form-control input" id="editEmployeeApellidos" name="apellidos" required>
                    </div>
                    <div class="input-container">
                        <label for="editEmployeeTelefono" class="label-input">Teléfono</label>
                        <input type="text" class="form-control input" id="editEmployeeTelefono" name="telefono" required>
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