var $ = jQuery.noConflict();

$(document).ready(function() {
    $('#linkList').dataTable( {
        "ajax":"GetLinksJSON/",
        "aoColumns": [
            { "mDataProp": "fc" },
            { "mDataProp": "proj" },
            { "mDataProp": "udi" },
            { "mDataProp": "doi" },
            { "mDataProp": "username" },
            { "mDataProp": "created" },
        ],
        "deferRender": true
        });

    var table = $('#linkList').DataTable();

    $('#linkList tbody').on( 'click', 'tr', function () {
        if ( $(this).hasClass('selected') ) {
            $(this).removeClass('selected');
            $('#delete_button').attr('disabled', 'disabled');
        }
        else {
            table.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
            $('#delete_button').removeAttr('disabled');
        }
    });

    $('#delete_button').click( function ( ) {
        var doi = table.row('.selected').data().doi;
        var udi = table.row('.selected').data().udi;
        $.when(confirmDialog(doi, udi)).done(function() {
            var request = $.ajax({
                url: pelagos_base_path + "/services/plinker/" + udi + "/" + doi,
                method: "DELETE"
            });

            request.success(function () {
                $('.selected').fadeOut('slow', function () {
                    table.row('.selected').remove().draw( true );
                    $('#delete_button').attr('disabled', 'disabled');
                });
            });

            request.fail( function ( xhr, textStatus, errorThrown) {
                alert("Deletion failed.  Details: " + xhr.responseText);
            });
        });
    });
});

function confirmDialog(doi, udi)
{
    return $.Deferred(function() {
        var self = this;
        $( '<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>This UDI/DOI association will be permanently deleted. Are you sure?</p>' ).dialog({
            resizable: false,
            height:'auto',
            modal: true,
            title: 'Please Confirm',
            buttons: {
                "Delete?": function() {
                    console.log(doi, udi);
                    $( this ).dialog( "close" );
                    self.resolve();
                },
                "Cancel": function() {
                    $( this ).dialog( "close" );
                    self.reject();
                }
            }
        });
    });
}
