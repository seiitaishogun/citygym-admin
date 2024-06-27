$(function () {
    flatpickr("#datepicker", {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
    });

    $('#sentType').change(function () {
        var type = $('#sentType').val();
        if ( type == 2 ) $('#sltTime').show();
    })
});
