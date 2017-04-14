$(function () {
    if ($('#plans_container').length > 0) {
        $('label[for="plan_0"]').parent().remove();
        $('input[name="plan_id]:first').click();
    }
});