$(document).ready(function() {

    $(document).on('focus', '.js-datepicker', function(e) {
        $(this).datepicker({
            format: 'dd/mm/yyyy',
            language: 'fr'
        });
    });
});