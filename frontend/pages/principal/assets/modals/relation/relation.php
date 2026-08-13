<div class="modal micromodal-slide" id="relation-modal" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1" >
        <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="modal-title">

            <header class="modal__header">
                <h6 class="modal__title" id="modal-title">Ingreso de Activo Fijo y Asignación</h6>
                <button class="modal__close" aria-label="Cerrar modal" data-micromodal-close>&times;</button>
            </header>

            <div class="modal__content">
                <form id="asset-form" action="/systemahidrofalcon/api/activo?action=addAsset" method="POST" class="enter-data show">
                    <input type="hidden" name="trabajador_id" id="trabajador_id_new_asset_form">
                    <div class="err-trabajador_id"></div>
                    <div class="frstpart-container show">

                        <div class="text-inputs">
                            <div class="input-container">
                                <label for="cod_act_f" class="label-input">Codigo Activo Fijo</label>
                                <input type="text" name="cod_act_f" id="cod_act_f__relation" class="input">
                                <div class="err-cod_act_f err-box"></div>
                            </div>

                            <div class="input-container">
                                <label for="color" class="label-input">Color</label>
                                <input type="text" name="color" id="color__relation" class="input">
                                <div class="err-color err-box"></div>
                            </div>

                            <div class="input-container">
                                <label for="marca" class="label-input">Marca</label>
                                <input type="text" name="marca" id="marca__relation" class="input">
                                <div class="err-marca err-box"></div>
                            </div>

                            <div class="input-container">
                                <label for="modelo" class="label-input">Modelo</label>
                                <input type="text" name="modelo" id="modelo__relation" class="input">
                                <div class="err-modelo err-box"></div>
                            </div>
                        </div>

                        <div class="text-inputs">
                            <div class="input-container">
                                <label for="sede_adm" class="label-input">Sede Administrativa</label>
                                <select type="text" name="sede_adm" id="sede_adm__relation" class="input">
                                    <option value="">Seleccione un estatus</option>
                                </select>
                                <div class="err-sede_adm err-box"></div>
                            </div>

                            <div class="input-container">
                                <label for="descripcion" class="label-input">Descripcion</label>
                                <input type="text" name="descripcion" id="descripcion__relation" class="input">
                                <div class="err-descripcion err-box"></div>
                            </div>

                            <div class="input-container">
                                <label for="serial" class="label-input">Serial</label>
                                <input type="text" name="serial" id="serial__relation" class="input">
                                <div class="err-serial err-box"></div>
                            </div>

                            <div class="input-container">
                                <label for="estatus_new" class="label-input">Estatus</label>
                                <select name="estatus" id="estatus_new__relation" class="input status-newIncorporation">
                                    <option value="">Seleccione un estatus</option>
                                </select>
                                <div class="err-estatus_new err-box"></div>
                            </div>

                        </div>
                        
                        <div class="button-container next-container">
                            <button type="button" class="button next-button">Siguiente</button>
                        </div>
                    </div>
                    
                    <div class="scndpart-container">
                        <div class="text-inputs">

                            <div class="input-container">
                                <label for="observacion" class="label-input">Observacion</label>
                                <input type="text" name="observacion" id="observacion__relation" class="input">
                                <div class="err-observacion err-box"></div>
                            </div>

                            <div class="input-container">
                                <label for="doc" class="label-input">Documento</label>
                                <input type="text" name="doc" id="documento__relation" class="input">
                                <div class="err-doc err-box"></div>
                            </div>

                            <div class="input-container">
                                <label for="monto" class="label-input">Monto</label>
                                <input type="text" name="monto" id="monto__relation" class="input">
                                <div class="err-monto err-box"></div>
                            </div>
                        </div>

                        <div class="text-inputs">
                            <div class="input-container">
                                <label for="unidad_relation" class="label-input">Unidad</label>
                                <input type="text" name="codigo_u_u" id="unidad__relation" class="input">
                                <div class="err-codigo_u_u err-box"></div>
                            </div>

                            <div class="input-container">
                                <label for="fecha_relation" class="label-input">Fecha</label>
                                <input type="text" name="fecha" id="fecha__relation" class="input">
                                <div class="err-fecha err-box"></div>
                            </div>
                        </div>

                        <div class="extra-actions" id="extra-actions__relation">
                            <button type="button" class="button goback-button">
                                Volver
                            </button>
                            <input class="button submit-button" id="submit-button__relation" type="submit" value="Registrar Nuevo Activo">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>