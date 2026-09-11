function toast_msg(msg, type) {
    toastr.options = {
        "closeButton": true,
        "progressBar": false,
        "positionClass": "toast-top-right", // Ubicación
        "timeOut": 2000 // Duración en milisegundos
    };

    switch (type) {
        case 'success':
            toastr.success(msg);
            break;
        case 'warning':
            toastr.warning(msg);
            break;
        case 'error':
            toastr.error(msg);
            break;
        case 'info':
            toastr.info(msg);
            break;
        default:
            console.warn("Tipo de mensaje no válido.");
            break;
    }
}

function reload_table() {
    $('#table').DataTable().clear().draw();
}

function block_content(elemento)
{
    $(elemento).block({
        message: `<div class="sk-bounce sk-primary mx-auto">
                    <div class="sk-bounce-dot"></div>
                    <div class="sk-bounce-dot"></div>
                </div>`,
        css: {
            backgroundColor: "transparent",
            color: "#fff",
            border: "0"
        }
    })
}

function close_block(elemento)
{
    $(elemento).unblock();
}

$('body').on('click', '.btn-close-modal-client', function(event) {
    event.preventDefault();
    $('#modalConfirmSale').css('z-index', '');
    $('#modalAddClient').modal('hide');

    // Verifica si la ruta actual es "/pos"
    if (window.location.pathname === "/pos") {
        setTimeout(() => {
            $('#modalConfirmSale').modal('show');
        }, 100);
    }
});


function touch_down(input = '', form = '') {
    if (input == '') {
        toast_msg('Establezca un campo de entrada', 'warning');
        return;
    }
    $('body').on('click', `.bootstrap-touchspin-down-${form}`, function (event) {
        event.preventDefault();
        let contador = 1,
            cantidad_actual = parseInt($(input).val());

        if (cantidad_actual <= 1) {
            toast_msg('Cantidad no puede ser menor a 1', 'warning');
            return;
        }

        $(input).val(cantidad_actual - contador);
    });
}

function touch_up(input = '', form = '') {
    if (input == '') {
        toast_msg('Establezca un campo de entrada', 'warning');
        return;
    }
    $('body').on('click', `.bootstrap-touchspin-up-${form}`, function (event) {
        event.preventDefault();
        let contador = 1,
            cantidad_actual = parseInt($(input).val());
        $(input).val(cantidad_actual + contador);
    });
}