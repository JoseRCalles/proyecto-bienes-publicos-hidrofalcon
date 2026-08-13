 document.addEventListener('DOMContentLoaded', () => {
            MicroModal.init({
                onShow: (modal) => {

                },
                onClose: (modal) => {

                },
                openTrigger: 'data-micromodal-trigger',
                closeTrigger: 'data-micromodal-close',
                disableScroll: true,
                awaitOpenAnimation: true,
                // awaitCloseAnimation: true,
                debugMode: true // Activar modo de depuración para ver mensajes en consola
            });
});



export default function showHide(hide,show){
    if(show.classList.contains('show')){
        show.classList.remove('show');
        hide.classList.add('show')
    }
    else{
        hide.classList.remove('show')
        show.classList.add('show');
    }
}

