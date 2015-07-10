var $ = jQuery.noConflict();

var base_path;

$(document).ready(function()
{
    base_path = $('div[base_path]').attr('base_path');
    
    $.validator.methods._required = $.validator.methods.required;
    $.validator.methods.required = function( value, element, param )
    {
        if (typeof this.settings.rules[ $(element).attr('name') ] != 'undefined' 
            && typeof this.settings.rules[ $(element).attr('name') ].remote != 'undefined') {
                return true;
            }
        return  $.validator.methods._required.call( this, value, element, param );
    }

    formValidator = $("#fundingOrgForm").validate({
        submitHandler: function(form) {
            var data = new FormData($('form')[0]);
            console.log(data);
            //var data = getFormJSON($(form));
            saveFundingOrg(data)
        }
    });
    
    $("input,textarea").each(function() {
        var url = base_path + "/services/fundingOrganization/validateProperty";
        $(this).rules( "add", {
            remote: {
                url: url,
            }
        })
    });

    $('button[type="submit"]').button();

    $('button[type="reset"]').button().click(function() {
        formValidator.resetForm();
    });

    $('#fundingOrgDialog').dialog({
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

    //minWidth does not work properly with 'auto' width, so hack
    $('.ui-dialog').css({'min-width': '300px'});
});

/**
 *  savePerson function
 *
 *  This function will send the person data to the webservice
 *
 *  @param [json] data Contains a JSON of firstName,lastName,eMailAddress
 *
 */
function saveFundingOrg(jsonData)
{
    //var url = base_path + "/services/fundingOrganization";
    var url = "https://proteus.tamucc.edu/~mvandeneijnden/test/upload.php";
    var title = "";
    var messsage = "";
    $.ajax({
        type: 'POST',
        data: jsonData,
        url: url,
        // Optionally enforce JSON return, in case a status 200 happens, but no JSON returns
        //dataType: 'json'
        cache: false,
        contentType: false,
        processData: false
    })
    .done(function(json) {
        if (json.code == 201) {
            title = "Success!";
            message = json.message;
            $('#btnReset').click();
        } else {
            title = "Error!";
            message = "Something went wrong!<br>Didn't receive the correct success message!";
            message += '<br>Please contact <a href="mailto:griidc@gomri.org&subject=Person%20Form">griidc@gomri.org</a>';
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
        $('#fundingOrgDialog').html(message);
        $('#fundingOrgDialog').dialog( 'option', 'title', title).dialog('open');
    })
}

/**
 *  getFormJSON function
 *
 *  This function will return the form fields/data as JSON
 *  it takes a jQuery selector of a Form
 *
 *  @param [selector] formSelector jQuery selector of the Form
 *  @return JSON
 */
function getFormJSON(formSelector)
{
    var data = {};
    formSelector.serializeArray().map(function(x){data[x.name] = x.value;});
    return data;
}