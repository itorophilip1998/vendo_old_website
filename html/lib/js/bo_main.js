var durationShowHide = 0;

function closeModals() {
    $("#modal_warning_automatic_trading").each(function() {
        $(this).hide(durationShowHide);
    })
    $("#modal_email_info").each(function() {
        $(this).hide(durationShowHide);
    })
    $("#divToBlur").removeClass("blur");
    $("#buttonChangeAutomatic").off();
    $("#confirmSendMail").off();
    $("#checkboxConfirmAGB").prop("checked", false);
}

function showEmailDialog() {

    $("#modal_warning_automatic_trading").each(function() {
        $(this).hide(durationShowHide);
    });
    $("#modal_email_info").each(function() {
        $(this).show(durationShowHide);
    });

    $("#confirmSendMail").click(function() {
        var checkboxChecked = $("#checkboxConfirmAGB").is(":checked");
        if(checkboxChecked) {
            var data = {
                checked: $("#automaticOnOff").is(":checked")
            }
            
            $.post("./ajax_send_mail_automation.php", data, function(data, err, e) {
                try {
                    var jsonEncode = JSON.parse(data);
                    if(jsonEncode.code == 200) {
                        var checked = $("#automaticOnOff").is(":checked");
                        
                        if(checked) {
                            $("#automaticOnOff").prop("checked", false);
                        } else {
                            $("#automaticOnOff").prop("checked", true);
                        }
                        checked = $("#automaticOnOff").is(":checked");
                    
                        // cosmetic - change color and text
                        if(checked) {
                            $("#text_automaticOnOff").removeClass("automatic_red");
                            $("#text_automaticOnOff").addClass("automatic_green");
                            $("#text_automaticOnOff").html("ON");
    
                            $("#subjectInfoTextOn").addClass("hidden");
                            $("#divInfoTextOn").addClass("hidden");
    
                            $("#subjectInfoTextOff").removeClass("hidden");
                            $("#divInfoTextOff").removeClass("hidden");
                        } else {
                            $("#text_automaticOnOff").removeClass("automatic_green");
                            $("#text_automaticOnOff").addClass("automatic_red");
                            $("#text_automaticOnOff").html("OFF");
    
                            $("#subjectInfoTextOn").removeClass("hidden");
                            $("#divInfoTextOn").removeClass("hidden");
    
                            $("#subjectInfoTextOff").addClass("hidden");
                            $("#divInfoTextOff").addClass("hidden");
                        }

                        $("#automationCheckbox").html(jsonEncode.data);
                    } else {
                        window.location = encodeURI("./bo_login.php?destination=./bo_main.php");
                    }
                } catch (error) {
                    console.log(error);
                }
            })
            .fail(function(data) {
                console.log(data);
            })
            .always(function() {
                closeModals();
            });
        } else {
            $("#notacceptAGB").removeClass("hidden");
        }
    });
}

$(document).ready(function() {
    $("#CopyButton").click(function() {
        var copyText = document.getElementById("inputCode");
        console.log(copyText.getAttribute("disabled"));
        copyText.disabled = false;
        copyText.select();
        copyText.setSelectionRange(0, 99999); /*For mobile devices*/
        
        document.execCommand("copy");
        copyText.disabled = true;
    });

    $("#checkboxConfirmAGB").click(function() {
        $("#notacceptAGB").addClass("hidden");
    })

    $("#automaticOnOff").click(function(e) {
        e.preventDefault();
        $("#divToBlur").addClass("blur");

        var checked = !$("#automaticOnOff").is(":checked");
        if(checked == true) {
            // load info for email
            $("#modal_warning_automatic_trading").each(function() {
                $(this).show(durationShowHide);
            });

            $("#buttonChangeAutomatic").click(function() {
               showEmailDialog();
            });
        } else {
            showEmailDialog();
        }
        
    });

    $(".div_modal_warning_close").click(function() {
        closeModals();
    });
});