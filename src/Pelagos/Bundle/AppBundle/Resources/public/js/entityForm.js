(function($) {
    "use strict";

    $.fn.entityForm = function(options) {

        $.validator.methods._required = $.validator.methods.required;
        $.validator.methods.required = function(value, element, param)
        {
            if (typeof this.settings.rules[$(element).attr("name")] !== "undefined"
            && typeof this.settings.rules[$(element).attr("name")].remote !== "undefined") {
                return true;
            }
            return $.validator.methods._required.call(this, value, element, param);
        };

        window.onbeforeunload = function () {
            var unsavedChanges = false;
            $(".entityForm").each(function () {
                if ($(this).prop("unsavedChanges")) {
                    unsavedChanges = true;
                }
            });
            if (unsavedChanges) {
                return "You have unsaved changes!\nAre you sure you want to navigate away?";
            }
        };

        return this.each(function() {
            //plug-in
            var thisForm = this;

            //make sure this is of type form
            if (!$(this).is("form")) {
                return false;
            }

            var actionURL = $(this).attr('action');

            $("input,textarea,select", this).each(function() {
                $(this)
                .attr("disabled", true)
                .addClass("formfield");
            });

            $(this).find("input.clickableLink").each(function () {
                $(this).wrap("<div class=\"clickableLink\"></div>")
                .after("<span><a name=\"url\" target=\"_blank\" href=\"" + $(this).val() + "\">" + $(this).val() + "</a></span>");
            });
            $(this).find("input.clickableLink").next().click(function () {
                event.stopPropagation();
            });

            var wrapper = "<div class=\"entityWrapper formReadonly\"></div>";
            var entityType = $(this).attr("entityType");
            var entityId = $(this).find("[name=\"id\"]").val();

            $(this).wrap(wrapper);

            if (entityId === "") {
                if (!$(this).hasAttr("creatable")) {
                    $(this).find('.innerForm').hide()
                    return null;
                }
            } else {
                if (!$(this).hasAttr("editable")) {
                    $(this).find('.innerForm').hide()
                    return null;
                }
            }

            $(this).prop("unsavedChanges", false);

            var formValidator = $(this).validate({
                submitHandler: function(form) {
                    if ($(thisForm).find("[name=\"id\"]").val() === "") {
                        updateEntity(form, "Create");
                    } else {
                        updateEntity(form, "Update");
                    }
                }
            });

            var buttons = "<div style=\"position:relative;\">" +
                          "<div id=\"notycontainer\" style=\"position:absolute;top:0px;bottom:0px;width:600px;\">" +
                          "</div><br><button class=\"entityFormButton\" type=\"submit\">Save</button>" +
                          "&nbsp;<button id=\"cancelButton\" class=\"entityFormButton\" type=\"reset\">Cancel</button></div>";

            $(this).append(buttons);

            $(".entityFormButton").css("visibility", "hidden").button();

            $(".deleteimg", this).button().click(function (event) {
                event.stopPropagation();

                $.when(showConfirmation({
                        title: "Delete Entity",
                        message: "Are you sure?"
                    })).done(function() {
                    $.when(updateEntity($(thisForm), "Delete")).done(function() {
                        var deletedId = $(thisForm).find("[name=\"id\"]").val();
                        var deleteType = $(thisForm).attr("entityType");
                        $(thisForm).trigger("entityDelete", [deletedId, deleteType]);
                        $(".entityWrapper").has(thisForm)
                        .animate({ height: "toggle", opacity: "toggle" }, "slow", function() {
                            $(this).slideUp("fast", function() {
                                $(this)
                                .remove();
                            });
                        });
                    });
                });
            });

            $(this).on("keyup change", function () {
                if ($(".entityWrapper").has(this).hasClass("active")) {
                    $(this).prop("unsavedChanges", true);
                }
            });

            $(".entityWrapper").has(this).on("click", function() {
                if (!$(this).hasClass("active")) {
                    $(this).addClass("active");

                    var url = actionURL + "/validateProperty";

                    $("input:visible,textarea,select", this).each(function() {
                        $(this).attr("disabled", false);
                        if (!$(this).hasAttr("dontvalidate")) {
                            $(this).rules("add", {
                                remote: {
                                    url: url
                                }
                            });
                        }
                    });
                    $(".innerForm", this).hide();
                    $(".entityFormButton,.showOnEdit", this)
                    .css({opacity: 0.0, visibility: "visible"})
                    .animate({opacity: 1.0});
                    $("button", this).button("enable");
                }
            });

            $(this).bind("reset", function() {
                formValidator.resetForm();
                $("input:visible,textarea,select", this).each(function() {
                    $(this)
                    .attr("disabled", true)
                    .removeClass("active")
                    .rules("remove");
                });
                $(".entityWrapper").has(this)
                .removeClass("active")
                .find(".innerForm", this).show();

                $(".entityFormButton,.showOnEdit", this)
                .css({opacity: 1.0, visibility: "visible" })
                .animate({opacity: 0.0});
                $(this).prop("unsavedChanges", false);

                $("button", this).button("disable");
            });

            if (entityId === "") {
                $(".entityWrapper").has(this).click();
            }
        });
    };

    $.fn.hasAttr = function(attribute) {
        var hasAttribute = $(this).attr(attribute);
        return (typeof hasAttribute === typeof undefined || hasAttribute === false) ? false : true;
    };

    $.fn.fillForm = function(Data) {
        //make sure this is of type form
        if (!this.is("form")) {
            return false;
        }
        var Form = $(this);

        if (typeof Data !== "undefined" && Object.keys(Data).length > 0)
        {
            fillElement(Data, Form);
            return true;
        } else {
            return false;
        }
    };

    function fillElement(Data, Form, Parent)
    {
        $.each(Data, function(name, value) {
            if (typeof Parent !== "undefined" && Parent !== "") {
                if (name === "id") {
                    name = Parent;
                } else {
                    name = Parent + "." + name;
                }
            }
            if (typeof value === "object" && value !== null) {
                fillElement(value, Form, name);
            }
            //set value to blank, for default value, to avoid display of null
            value = (value === null) ? "" : value;

            Form.find("a[name=\"" + name + "\"]").attr("href", value).html(value);
            var selector = Form.find("input,textarea,select").filter("[name=\"" + name + "\"]");
            // Set extra property of name for reset purposes.

            if (Parent === name) {
                var childName = name.split(".");
                childName = childName[childName.length - 1];
                selector.attr(childName, value);
            }
            if (typeof value !== "object") {
                selector.prop("defaultValue", value);
            }
            var elementType = selector.prop("type");
            //Check if value is an object, and switch between the case that can handle objects
            if (typeof value !== "object") {
                switch (elementType)
                {
                    case "radio":
                        selector.filter("[value=\"" + value + "\"]").attr("checked", true);
                        break;
                    case "checkbox":
                        selector.attr("checked", value);
                        break;
                    case "select-one":
                        selector.find("option").attr("selected", false);
                        selector.val(value);
                        selector.find("[value=\"" + value + "\"]").attr("selected", true);
                        break;
                    case "textarea":
                        selector.html(value);
                        selector.val(value);
                        break;
                    default:
                        selector.attr("value", value);
                        selector.val(value);
                        break;
                }
            } else {
                switch (elementType)
                {
                    case "file":
                        selector.attr("base64", value.base64);
                        selector.attr("mimeType", value.mimeType);
                        selector.trigger("logoChanged");
                        break;
                    default:
                        break;
                }

            }
        });
    }

    function updateEntity(form, action)
    {
        var data = $(form).serialize(); //new FormData(form);
        var entityType = $(form).attr("entityType");
        var entityId = $(form).find("[name=\"id\"]").val();
        var url;
        var actionURL = $(form).attr('action');

        // $("form input:hidden").each(function(key, input){
            // data.append(input.name, input.value);
        // });

        var type;
        var returnCode;
        var prefixPhrase;

        switch (action)
        {
            case "Create":
                url = actionURL;
                type = "POST";
                returnCode = 201;
                prefixPhrase = "Created";
                break;
            case "Update":
                url = actionURL + "/" + entityId;
                type = "PUT";
                returnCode = 200;
                prefixPhrase = "Updated";
                break;
            case "Delete":
                url = actionURL + "/" + entityId;
                type = "DELETE";
                returnCode = 204;
                break;
            default:
                url = actionURL + "/" + entityId;
                type = "GET";
                returnCode = 200;
                break;
        }

        return $.Deferred(function() {
            var title = "";
            var message = "";
            var mainPromise = this;

            $.ajax({
                type: type,
                data: data,
                url: url,
                // Optionally enforce JSON return, in case a status 200 happens, but no JSON returns
                //dataType: 'json',
                cache: false,
                contentType: "application/x-www-form-urlencoded; charset=UTF-8",
                processData: false,
                statusCode: {
                    201: function(data) {
                        var currentURL = window.location.pathname;
                        var docTitle = document.title;
                        var newID = data.id;
                        var newURL = currentURL + "/" + newID;
                        window.history.pushState(newID, docTitle, newURL);
                    }
                },
                success: function(data, textStatus, jqXHR) {
                    if (jqXHR.status === returnCode) {
                        title = "Success!";
                        var newID = data.id;
                        message = prefixPhrase + " " + entityType + " successfully with ID:" + newID;
                        $(form).fillForm(data);
                        $(form).trigger("reset");
                    } else {
                        title = "Error!";
                        message = "Something went wrong!<br>Didn't receive the correct success message!";
                    }
                },
                error: function(response) {
                    var json = response.responseJSON;
                    if (typeof response.responseJSON === "undefined") {
                        json = {};
                        json.code = response.status;
                        json.message = response.statusText;
                    }
                    title = "Error!";
                    message = json.message;
                }
            })
            .then(function() {
                return $.Deferred(function() {
                    var notyPromise = this;
                    $("#notycontainer", form)
                    .noty({
                        layout: "top",
                        text: message,
                        theme: "relax",
                        animation: {
                            open: "animated bounceIn", // Animate.css class names
                            close: "animated fadeOut", // Animate.css class names
                            easing: "swing", // unavailable - no need
                            speed: 500 // unavailable - no need
                        },
                        timeout: 3000,
                        callback: {
                            afterClose: function() {
                                notyPromise.resolve();
                            }
                        }
                    });
                });
            })
			.fail(function() {
				showDialog(title, message);
				mainPromise.reject();
			})
            .done(function() {
                return mainPromise.resolve();
            });
        });
    }
}(jQuery));
