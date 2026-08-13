<div class="security-questions" style="display: none;">
    <header class="leftSide__header">
        <h5>Ingrese sus preguntas de Seguridad</h5>
    </header>
    <form class="enter-data" id="security-questions-form">
        <div class="select-inputs" id="securityQuestions-1">
            <div class="input-container">
                <p class="question1-box question"></p>
                <input type="text" name="respuesta1" id="respuesta1" class="input">
                <div class="error-r1 error-box"></div>
                <input type="hidden" name="pregunta_id_1" id="pregunta_id_1">
            </div>
        </div>

        <div class="select-inputs" id="securityQuestions-2">
            <div class="input-container">
                <p class="question2-box question"></p>
                <input type="text" name="respuesta2" id="respuesta2" class="input">
                <div class="error-r2 error-box"></div>
                <input type="hidden" name="pregunta_id_2" id="pregunta_id_2">
            </div>
        </div>

        <div class="extra-actions">
            <div class="button-container">
                <button type="button" class="register-button" id="verify-answers-button">Verificar</button>
                <div class="err-boxes"></div>
            </div>
        </div>
    </form>
</div>