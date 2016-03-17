$(document).ready(function()
{
    "use strict";
    $(".entityForm[entityType=\"ResearchGroup\"] [name=\"logo\"]").on("logoChanged", function ()
    {
        if ($(this).attr("mimeType") !== "application/x-empty") {
            $("#researchGroupLogo").html("<img src=\"data:" + $(this).attr("mimeType") + ";base64," + $(this).attr("base64") + "\">");
        }
    });

    $(".entityForm[entityType=\"PersonResearchGroup\"]").on("entityDelete", function (event, deleteId)
    {
        $("#leadership tr[PersonResearchGroupId=\"" + deleteId + "\"]")
        .animate({ height: "toggle", opacity: "toggle" }, "slow", function() {
            $(this).slideUp("fast", function() {
                $(this)
                .remove();
            });
        });

    });

    $("#tabs")
        .tabs({ heightStyle: "content" })
        .tabs("disable", 1);
        
        
    //Special AddForm stuff HERE:
    
    // Special stuff for Addform
    if ($(document).has(".addimg").length ? true : false) {
        
        console.log('found add img');
        
        var newForm = $("form[newform]");
        
        newForm.fadeOut();
        
        $(".addimg").button().click(function() {
            var addImg = $(this).fadeOut();
            var lastTr = $(newForm).closest("table").find("tr:last");
            
            var cloneForm = newForm
                .clone(false)
                .insertBefore(lastTr)
                .removeAttr("newform")
                .fadeIn()
                .entityForm()
                .off()
                ;
            
            $(cloneForm).find("#cancelButton").click(function() {
                addImg.fadeIn();
                if ($(cloneForm).closest("form").find("input[name='id']").val() === "") {
                    cloneForm
                        .fadeOut()
                        .unwrap()
                        .remove();
                }
            });
            
            $(cloneForm).find('button[type="submit"]').click(function() {
                $(cloneForm).one("reset", function() {
                    debugger;
                    if ($(this).find("input[name='id']").val() !== undefined) {
                        var newEntityForm = $(this);
                        var newEntity = newEntityForm
                            .parent()
                            .wrap("<tr><td><div><p></p><p></p></div></td></tr>")
                            ;
                        
                        addImg.fadeIn();
                    }
                });
            });
            
            // TODO: Need code for when form is persisted and
            // TODO: Catch submit button to wrap this form in table row
        });
        
        // var addimg = $(".addimg", this).detach();
        
        // addimg.insertAfter($(this));
    }
});
