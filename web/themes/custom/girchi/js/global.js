console.log(123);
$('#edit-field-politician-value').on('change', (e) => {
    console.log("123");
    console.log(e.target);
    if (e.target.checked) {
        $('.form-checkbox-input').addClass('checked');
    } else {
        $('.form-checkbox-input').removeClass('checked');
    }
})
