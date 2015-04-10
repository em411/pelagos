var $ = jQuery.noConflict();

var valid_publication = false;
var valid_dataset = false;

$(document).ready(function() {
    $('#retrieve_publication').button().click(function () {
        retrieveCitation('publication');
    });
    $('#retrieve_dataset').button().click(function () {
        retrieveCitation('dataset');
    });
    $('#link').button().click(function () {
        $.ajax({
            url: pelagos_base_path + '/services/publink/' + $('#udi').val() + '/' + $('#doi').val(),
            method: 'LINK'
        }).done(function (data) {
            $('#udi').val('');
            $('#doi').val('')
            $('#publication .pelagos-citation').html('');
            $('#dataset .pelagos-citation').html('');
            $('#link').button("option", "disabled", true);
            alert('Success: ' + data.message);
        }).fail(function (data) {
            alert('Error: ' + data.responseJSON.message);
        });
    });
    initSpinners();
});

function retrieveCitation(type) {
    $('#' + type + ' .pelagos-spinner').show();
    $('#' + type + ' .pelagos-citation').html('');
    $.ajax({
        url: pelagos_base_path + '/services/citation/' + type + '/' + $('#' + type + ' .id').val()
    }).done(function (data) {
        $('#' + type + ' .pelagos-citation').html(data.text);
        $('#' + type + ' .pelagos-citation').removeClass('pelagos-error');
        if (type == 'dataset') {
            valid_dataset = true;
        }
        if (type == 'publication') {
            valid_publication = true;
        }
        if (valid_dataset && valid_publication) {
            $('#link').button("option", "disabled", false);
        }
    }).fail(function (data) {
        if (data.responseJSON) {
            $('#' + type + ' .pelagos-citation').html(data.responseJSON.message);
        } else {
            $('#' + type + ' .pelagos-citation').html(data.statusText);
        }
        $('#' + type + ' .pelagos-citation').addClass('pelagos-error');
        if (type == 'dataset') {
            valid_dataset = false;
        }
        if (type == 'publication') {
            valid_publication = false;
        }
        $('#link').button("option", "disabled", true);
    }).always(function () {
        $('#' + type + ' .pelagos-spinner').hide();
    });
}

function initSpinners()
{
    var opts = {
        lines: 11, // The number of lines to draw
        length: 10, // The length of each line
        width: 5, // The line thickness
        radius: 15, // The radius of the inner circle
        corners: 1, // Corner roundness (0..1)
        rotate: 0, // The rotation offset
        direction: 1, // 1: clockwise, -1: counterclockwise
        color: '#000', // #rgb or #rrggbb or array of colors
        speed: 1, // Rounds per second
        trail: 60, // Afterglow percentage
        shadow: true, // Whether to render a shadow
        hwaccel: true, // Whether to use hardware acceleration
        className: 'spinner', // The CSS class to assign to the spinner
        zIndex: 2000000000, // The z-index (defaults to 2000000000)
        top: '20px', // Top position relative to parent
        left: '40px' // Left position relative to parent
    };
    
    target = document.getElementById('publication_spinner');
    publication_spinner = new Spinner(opts).spin(target);
    target = document.getElementById('dataset_spinner');
    dataset_spinner = new Spinner(opts).spin(target);
}
