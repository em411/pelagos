var $ = jQuery.noConflict();

//FOUC preventor
$("html").hide();

$(document).ready(function () {
    "use strict";

    $("html").show();

    $(".pelagosNoty").pelagosNoty({timeout: 0, showOnTop:false});

});


