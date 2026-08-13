<div class="text-inputs" id="personalInfo">
    <div class="input-container">
        <label for="nombres" class="label-input">Nombres</label>
        <input type="text" name="nombres" id="nombres" class="input">
        <div class="error-nombres error-box"></div>
    </div>

    <div class="input-container">
        <label for="cedula" class="label-input">Cédula</label>
        <input type="text" name="cedula" id="cedula" class="input">
        <div class="error-cedula error-box"></div>
    </div>

    <div class="input-container">
        <label for="gerencia" class="label-input">Gerencia</label>
        <select name="gerencia" id="gerencia" class="dinamic-input">
            <option value="" disabled selected>Seleccione una Gerencia</option>
        </select>
        <div class="error-gerencia error-box"></div>
    </div>

    <div class="input-container">
        <label for="contrasena" class="label-input">Contraseña</label>
        <input type="password" name="contrasena" id="contrasena" class="input">
        <div class="error-contrasena error-box"></div>
    </div>
</div>

<div class="text-inputs" id="confirmPasswordSection">
    <div class="input-container">
        <label for="apellidos" class="label-input">Apellidos</label>
        <input type="text" name="apellidos" id="apellidos" class="input">
        <div class="error-apellidos error-box"></div>
    </div>

    <div class="input-container">
        <label for="cargo" class="label-input">Cargo</label>
        <select name="cargo" id="cargo" class="dinamic-input">
            <option value="" disabled selected>Seleccione un Cargo</option>
        </select>
        <div class="error-cargo error-box"></div>
    </div>

    <div class="input-container">
        <label for="usuario" class="label-input">Usuario:</label>
        <input type="text" id="usuario" name="usuario" class="input">
        <div class="error-usuario error-box"></div>
    </div>

    <div class="input-container">
        <label for="confirmar_contrasena" class="label-input">Confirma Contraseña</label>
        <input type="password" name="confirm_password" id="confirmar_contrasena" class="input">
        <div class="error-confirmar_contrasena error-box"></div>
    </div>

    <div class="continue-container">
        <input type="button" class="continue" value="Continuar">
    </div>
</div>