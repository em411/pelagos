var $ = jQuery.noConflict();

$(document).ready(function()
{
    "use strict";
    
    $("#get-versions-button").click(function (){
        var udi = $("input[name=udi]").val();
        jQuery.ajax({
            url: Routing.generate("pelagos_app_ui_sidebyside_getversions", {udi: udi}),
            type: "POST",
            data: {udi: udi},
            context: document.body
        })
        .done(function(data) {
            var select = $("select.version-select");
            select.find("option").remove();
            
            $.each(data, function(index, item) {
                var option = new Option(item.sequence, item.sequence);
                $(option).data("udi", item.udi);
                $(option).data("modificationtimestamp", item.modificationtimestamp);
                $(option).data("status", item.status);
                $(option).data("version", item.version);
                $(option).data("modifier", item.modifier);
                select.append(option);
            });
            
            $(".right-version").find("select option:selected")
                .prop("selected", false)
                .next()
                .prop("selected", "selected");
            select.change();
        });
    });
    
    $(".left-version").find("select").change(function() {
        var version = $(this).find("option:selected").data("version");
        var udi = $(this).find("option:selected").data("udi");
        
        $("#left").html("<h1>LOADING</h1>");
        $(".udi-title").text(udi);
        
        $(this).parents("div.left-version")
            .find(".submission-status")
            .text($(this).find("option:selected").data("status"));
        $(this).parents("div.left-version")
            .find(".submission-modificationtimestamp")
            .text($(this).find("option:selected").data("modificationtimestamp"));
        $(this).parents("div.left-version")
            .find(".submission-modifier")
            .text($(this).find("option:selected").data("modifier"));
        
        $("#left").load("/pelagos-symfony/dev/mvde/sidebyside/getForm/" + udi + "/" + version, function() {
            $(".smallmap", this).gMap();
        });
    });
    
    $(".right-version").find("select").change(function() {
        var version = $(this).find("option:selected").data("version");
        var udi = $(this).find("option:selected").data("udi");
        
        $("#right").html("<h1>LOADING</h1>");
        $(".udi-title").text(udi);
        
        $(this).parents("div.right-version")
            .find(".submission-status")
            .text($(this).find("option:selected").data("status"));
        $(this).parents("div.right-version")
            .find(".submission-modificationtimestamp")
            .text($(this).find("option:selected").data("modificationtimestamp"));
        $(this).parents("div.right-version")
            .find(".submission-modifier")
            .text($(this).find("option:selected").data("modifier"));
        
        $("#right").load("/pelagos-symfony/dev/mvde/sidebyside/getForm/" + udi + "/" + version, function() {
            $(".smallmap", this).gMap();
        });
    });
});