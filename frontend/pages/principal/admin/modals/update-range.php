<div class="modal micromodal-slide" id="update-range-modal" aria-hidden="true">
  <div class="modal__overlay" tabindex="-1" data-micromodal-close>
    <div class="modal__container" role="dialog" aria-modal="true">
      <header class="modal__header">
        <h2 class="modal__title">Asignar Rango</h2>
        <button class="modal__close" aria-label="Cerrar" data-micromodal-close>x</button>
      </header>
      <main class="modal__content">
        <form id="updateRangeForm" class="enter-data">
          <div class="form-group input-container">
            <label for="newRange" class="label-input">Selecciona el nuevo rango:</label>
            <select id="newRange" name="newRange" class="input" required>
              <!-- Las opciones se llenarán dinámicamente -->
            </select>
          </div>
        </form>
      </main>
      <footer class="modal__footer">
        <button type="button" onclick="submitRangeUpdate()" class="button submit-button">Guardar</button>
        <button class="button goback-button" data-micromodal-close aria-label="Cerrar">Cancelar</button>
      </footer>
    </div>
  </div>
</div>