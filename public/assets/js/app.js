document.querySelectorAll('[data-confirm]').forEach(function (button) {
    button.addEventListener('click', function (event) {
        if (!window.confirm(button.dataset.confirm)) event.preventDefault();
    });
});

document.querySelectorAll('[data-sale-form]').forEach(function (form) {
    form.addEventListener('submit', function () {
        var button = form.querySelector('button[type="submit"]');
        if (button) {
            button.disabled = true;
            button.textContent = 'Saving sale…';
        }
    });
});
