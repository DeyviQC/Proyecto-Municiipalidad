document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!confirm(form.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });

    document.querySelectorAll('.alert.fade.show').forEach(function (alerta) {
        setTimeout(function () {
            var instancia = bootstrap.Alert.getOrCreateInstance(alerta);
            instancia.close();
        }, 6000);
    });
});