var $ = jQuery.noConflict();

var fundingID;

$(document).ready(function()
{
    
    fundingID = $('#fundingOrganizationForm #id').val();
    
    "use strict";
    $('#fundingOrganizationForm').validate({
        submitHandler: function(form) {
            //var data = $(form).getFormJSON();
            var data = new FormData(form);
            var url = pelagosBasePath + "/services/entity/FundingOrganization/" + fundingID;
            updateFundingOrganization(data, fundingID, url);
        }
    });
    
    $('#fundingCycleForm').validate({
        submitHandler: function(form) {
            //var data = $(form).getFormJSON();
            var data = new FormData(form);
            var url = pelagosBasePath + "/services/entity/FundingOrganization/" + fundingID;
            updateFundingOrganization(data, fundingID, url);
        }
    });
    
    var isLoggedIn = JSON.parse($('div[userLoggedIn]').attr('userLoggedIn'));
    if (isLoggedIn) {
        $('#fundingOrganizationForm').editableForm({
            validationURL: pelagosBasePath + '/services/entity/FundingOrganization/validateProperty'
        });
        
        $('#fundingCycleForm').editableForm({
            validationURL: pelagosBasePath + '/services/entity/FundingCycle/validateProperty'
        });
    }
    
    $('#fundingOrganizationFormDialog').dialog({
        autoOpen: false,
        resizable: false,
        minWidth: 300,
        width: 'auto',
        height: 'auto',
        modal: true,
        buttons: {
            Ok: function() {
                $( this ).dialog( "close" );
            }
        }
    });
});

function populateFundingOrganization(FundingOrganizationID)
{
    "use strict";
    $("#fundingOrganizationForm").trigger("reset");
    $("#fundingOrganizationLogo").html("");
    $.get(pelagosBasePath + "/services/entity/FundingOrganization/" + FundingOrganizationID)
    .done(function(data) {
        $("#fundingOrganizationForm").fillForm(data.data);
        $("#fundingOrganizationLogo").html("<img src=\"data:" + data.data.logo.mimeType + ";base64," + data.data.logo.base64 + "\">");
    });
}

function updateFundingOrganization(jsonData,fundingID, url)
{
    var theurl = url; 
    var title = "";
    var messsage = "";
    $.ajax({
        type: 'PUT',
        data: jsonData,
        url: theurl,
        // Optionally enforce JSON return, in case a status 200 happens, but no JSON returns
        //dataType: 'json'
        cache: false,
        contentType: false,
        processData: false
    })
    .done(function(json) {
        if (json.code == 200) {
            title = "Success!";
            message = json.message;
            $('form').editableForm('reset');
            $("#fundingOrganizationForm").fillForm(json.data);
            $("#fundingOrganizationLogo").html("<img src=\"data:" + json.data.logo.mimeType + ";base64," + json.data.logo.base64 + "\">");
        } else {
            title = "Error!";
            message = "Something went wrong!<br>Didn't receive the correct success message!";
            message += '<br>Please contact <a href="mailto:griidc@gomri.org&subject=userland">griidc@gomri.org</a>';
        }
    })
    .fail(function(response) {
        json = response.responseJSON;
        if (typeof response.responseJSON == 'undefined') {
            var json = {};
            json['code'] = response.status;
            json['message'] = response.statusText;
        }
        title = "Error!";
        message = json.message;
    })
    .always(function(json) {
        if (json.code != 200) {
            $('#fundingOrganizationFormDialog').html(message);
            $('#fundingOrganizationFormDialog').dialog( 'option', 'title', title).dialog('open');
        } else {
            //$('.noty_inline_layout_container);
            var n = $('#notycontainer').noty({
            //var n = noty({
                layout: 'top',
                text: message,
                theme: 'relax',
                animation: {
                    open: 'animated bounceIn', // Animate.css class names
                    close: 'animated fadeOut', // Animate.css class names
                    easing: 'swing', // unavailable - no need
                    speed: 500 // unavailable - no need
                },
                timeout: 3000
            });
        }
    })
}
