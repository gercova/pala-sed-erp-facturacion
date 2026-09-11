<script>
    $('body').on('click', '#modalAddCategory .btn-save', function() {
        event.preventDefault();
        let form = $('#modalAddCategory #form_save').serialize(),
            descripcion = $('#modalAddCategory #form_save input[name="descripcion"]');

        if (descripcion.val() == '')
            descripcion.addClass('is-invalid');
        else
            descripcion.removeClass('is-invalid');

        if (descripcion.val().trim() != '') {
            $.ajax({
                url: "{{ route('categories.save') }}",
                method: 'POST',
                data: form,
                beforeSend: function() {
                    $('#modalAddCategory .btn-save').prop('disabled', true);
                    $('#modalAddCategory .text-saving').removeClass('d-none');
                    $('#modalAddCategory .text-save').addClass('d-none');
                },
                success: function(r) {
                    $('#modalAddCategory .btn-save').prop('disabled', false);
                    $('#modalAddCategory .text-saving').addClass('d-none');
                    $('#modalAddCategory .text-save').removeClass('d-none');
                    toast_msg(r.msg, r.type);
                    if (!r.status) {
                        return;
                    }

                    $('#modalAddCategory').modal('hide');
                    $('#modalAddCategory #form_save').trigger('reset');
                    reload_table();
                },
                dataType: 'json'
            });
            return;
        }
    });
</script>