<div class="modal micromodal-slide" id="add-employee-modal" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1">
        <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="add-employee-modal-title">
            <header class="modal__header">
                <h6 class="modal__title" id="add-employee-modal-title">Agregar Empleado</h6>
                <button class="modal__close" aria-label="Cerrar modal" data-micromodal-close>&times;</button>
            </header>

            <div class="modal__content">
                <form id="addEmployeeForm" class="enter-data">

                <div class="inputs">
                    <div class="text-inputs">
                        <div class="input-container">
                            <label for="addEmployeeCedula" class="label-input">Cédula</label>
                            <input type="text" class="form-control input" id="addEmployeeCedula" name="cedula" required>
                            <div class="err-box"></div>
                        </div>
                        <div class="input-container">
                            <label for="addEmployeeNombres" class="label-input">Nombres</label>
                            <input type="text" class="form-control input" id="addEmployeeNombres" name="nombres" required>
                            <div class="err-box"></div>
                        </div>
                        <div class="input-container">
                            <label for="addEmployeeApellidos" class="label-input">Apellidos</label>
                            <input type="text" class="form-control input" id="addEmployeeApellidos" name="apellidos" required>
                            <div class="err-box"></div>
                        </div>
                    </div>

                    <div class="text-inputs">
                        <div class="input-container">
                            <label for="addEmployeeTelefono" class="label-input">Teléfono</label>
                            <input type="text" class="form-control input" id="addEmployeeTelefono" name="telefono" required>
                            <div class="err-box"></div>
                        </div>
                        <div class="input-container">
                            <label for="addEmployeeGerencia" class="label-input">Gerencia</label>
                            <select class="form-control input" id="addEmployeeGerencia" name="gerencia" required>
                                <option value="">Seleccione una Gerencia</option>
                            </select>
                            <div class="err-box"></div>
                        </div>
                        <div class="input-container">
                            <label for="addEmployeeCargo" class="label-input">Cargo</label>
                            <select class="form-control input" id="addEmployeeCargo" name="cargo" required>
                                <option value="">Seleccione un Cargo</option>
                            </select>
                            <div class="err-box"></div>
                        </div>
                    </div>
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