$(document).ready(function()
{
    "use strict";

    $("#phoneNumber").val($('form[entityType="Person"] input[name="phoneNumber"]').val());
    $("#phoneNumber").mask("(999) 999-9999");
    $("#phoneNumber").prop("defaultValue", $("#phoneNumber").val());

    $("form[entityType=\"Person\"]").on("presubmit", function() {
        var phoneValue = $("#phoneNumber").val().replace(/[^\d]/g, "");
        $('form[entityType="Person"] input[name="phoneNumber"]').val(phoneValue);
    });

    $("form[entityType=\"Person\"]").on("reset", function() {
        var value = $('form[entityType="Person"] input[name="phoneNumber"]').val();
        $("#phoneNumber").val(value);
        $("#phoneNumber").mask("(999) 999-9999");
        $("#phoneNumber").prop("defaultValue", $("#phoneNumber").val());
    });

    $.ajax({
        url: $("#organization").attr("data-url"),
        dataType: "json",
        success: function(json) {
            var organizationList = json;

            $("#organization").autocomplete({
                source: organizationList
            });
        }
    });

    $.ajax({
        url: $("#position").attr("data-url"),
        dataType: "json",
        success: function(json) {
            var personList = json;

            $("#position").autocomplete({
                source: personList
            });
        }
    });
});
