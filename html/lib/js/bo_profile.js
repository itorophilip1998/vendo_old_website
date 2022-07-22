var personalInformation = 1;
var accountinformation = 2;
var personalAdressInfo = 3;
var password = 4;

var readonlyPersonalInformation = 5;
var readonlyAccountInformation = 6;
var readonlyPersonalAdressInfo = 7;

function getCorrectForm(element) {
	var data;
	switch (element) {
		case accountinformation:
			data = {
				loadAccountInformationForm: true
			}
		break;
		case personalInformation:
			data = {
				loadPersonalInformationForm: true
			}
		break;
		case personalAdressInfo:
			data = {
				loadPersonalAdressInfoForm: true
			}
		break;

		case password:
			data = {
				loadPasswordForm: true
			}
		break;
		case readonlyPersonalInformation:
			data = {
				loadPersonalInformation: true
			}
		break;
		case readonlyAccountInformation:
			data = {
				loadAccountInformation: true
			}
		break;
		case readonlyPersonalAdressInfo:
			data = {
				loadPersonalAdress: true
			}
		break;
	}
	return data;
}

function loadReadonlyForm(element, elementToLoadIn, additionalData = new Array()) {
	var data = getCorrectForm(element);

    for (var attrname in additionalData) { data[attrname] = additionalData[attrname]; }
//	data = { ...data, ...additionalData};
	
	$.post("./ajax_profile_element_loader.php", data, function(data, err, e) {
		try {
			var response = JSON.parse(data);
			if(response.code == 200) {
				if(element == readonlyAccountInformation) {
					if(additionalData.updateUser == true) {
						window.location = window.location;
					}
				}
	
				$(elementToLoadIn).hide(300, function() {
					$(elementToLoadIn).html(response.data);
					$(elementToLoadIn).show(300);
				});
	
				$("#greetingText").html(response.new_greeting);
			} else if(response.code == (-10)) {
				window.location = "./bo_login.php?destination=" + encodeURI("./bo_profile.php");
			} else {
				toastr['error'](response.message);
			}
		} catch(error) {
			console.log(error);
		}
	});
}

function loadForm(element, elementToLoadIn) {
	var data = getCorrectForm(element);

	$.post("./ajax_profile_element_loader.php", data, function(data, err, e) {
		try {
			var response = JSON.parse(data);
			if(response.code == (-10)) {
				window.location = "./bo_login.php?destination=" + encodeURI("./bo_profile.php");
			}

			$("#" + elementToLoadIn).hide(300, function() {
				$("#" + elementToLoadIn).html(response.data);
				$("#" + elementToLoadIn).show(300);

				if(element == accountinformation) {
					$("#changePassword").click(function(event) {
						event.preventDefault();
	
						loadForm(password, "passwordForm");
					});
					
					$("#changeAccountInfoForm").submit(function( event ) {
						event.preventDefault();

						loadReadonlyForm(readonlyAccountInformation, $(this).parent(), {
								language: $("#langaugeSelect").val(),
								email: $("#changeAccountInfoForm").find('input[name="email"]').val(),
								mobile_number: $("#changeAccountInfoForm").find('input[name="mobile_number"]').val(),
								phonecode: $("#phoneCodeSelect").val(),
								payout_address: $("#changeAccountInfoForm").find('input[name="payout_address"]').val(),
								updateUser: true
							}
						);
					});
	
					$("#changeAccountInfoForm").on("reset", function(event) {
						event.preventDefault();

						loadReadonlyForm(readonlyAccountInformation, $(this).parent());
					});
				}
	
				if(element == password) {
					$("#divToBlur").addClass("blur");
					$("#showPassword").click(function() {
						console.log($("#newPassword").attr("type"));
						if($("#newPassword").attr("type") != "password") {
							$("#newPassword").attr("type", "password");
						} else {
							$("#newPassword").attr("type", "text");
						}
					});
					$("#changePasswordForm").on("reset", function(event) {
						event.preventDefault();
						$(this).parent().parent().parent().hide(300, function() {
							$(this).html("");
							$("#divToBlur").removeClass("blur");
						});
					});

					$("#changePasswordForm").submit(function(e) {
						e.preventDefault();
						$("#PasswordIsNotTheSameText").hide();
						$("#changePasswordForm").attr("disabled", true);
						$("#loadingMarqueePassword").show();

						var oldPassword = $("#changePasswordForm").find('input[name="oldPassword"]').val();
						var newPassword = $("#changePasswordForm").find('input[name="newPassword"]').val();
						var newPasswordRepeat = $("#changePasswordForm").find('input[name="newPasswordRepeat"]').val();

						if(newPassword != newPasswordRepeat || newPassword.length <= 0 || newPasswordRepeat.length <= 0 || oldPassword.length <= 0) {
							$("#loadingMarqueePassword").hide();
							$("#changePasswordForm").attr("disabled", false);
							$("#PasswordIsNotTheSameText").show();
						} else {
							$.post("./ajax_change_password.php", {
								oldPassword: oldPassword,
								newPassword: newPassword,
								newPasswordRepeat: newPasswordRepeat
							}, function(data) {
								try {
									var response = JSON.parse(data);
									$("#divToBlur").removeClass("blur");
									$("#loadingMarqueePassword").hide();
									if(response.code != (-10)) {
										$("#changePasswordForm").attr("disabled", false);
										if(response.code == 200) {
											toastr['success'](response.message);
											$("#changePasswordForm").parent().parent().parent().hide();
											$("#changePasswordForm").html("");
										} else {
											toastr['error'](response.message);
										}
									} else {
										window.location = "./bo_login.php?destination=" + encodeURI("./bo_profile.php");
									}
								} catch (error) {
									
								}
							});
						}
					});
				}
	
				if(element == personalInformation) {
					$("#PersonalInformationForm").submit(function( event ) {
						event.preventDefault();

						loadReadonlyForm(readonlyPersonalInformation, $(this).parent(), {
							given_name: $("#PersonalInformationForm").find('input[name="given_name"]').val(),
							sur_name: $("#PersonalInformationForm").find('input[name="sur_name"]').val(),
							sex: $("#PersonalInformationForm").find('input[name="sex"]:checked').val(),
							date_of_birth: $("#PersonalInformationForm").find('input[name="date_of_birth"]').val(),
							updateUser: true
						});
					});
	
					$("#PersonalInformationForm").on("reset", function(event) {
						event.preventDefault();

						loadReadonlyForm(readonlyPersonalInformation, $(this).parent());
					});
				}
	
				if(element == personalAdressInfo) {
					$("#changePersonalAdressForm").submit(function( event ) {
						loadReadonlyForm(readonlyPersonalAdressInfo, $(this).parent(), {
							country: $("#countrySelect").val(),
							city: $("#changePersonalAdressForm").find('input[name="city"]').val(),
							postcode: $("#changePersonalAdressForm").find('input[name="postcode"]').val(),
							street: $("#changePersonalAdressForm").find('input[name="street"]').val(),
							housenumber: $("#changePersonalAdressForm").find('input[name="housenumber"]').val(),
							updateUser: true
						});
						event.preventDefault();
					});
	
					$("#changePersonalAdressForm").on("reset", function(event) {
						event.preventDefault();

						loadReadonlyForm(readonlyPersonalAdressInfo, $(this).parent());
					});
				}
	
			});
		} catch (error) {
			console.log(error);
		}
	});
}



$(document).ready(function(){

	$("#editAccountInformation").click(function(e) {
		e.preventDefault();

		loadForm(accountinformation, "accountInformation");
	});

	$("#editPersonalInformation").click(function(e) {
		e.preventDefault();

		loadForm(personalInformation, "personalInformation");
	});

	$("#editPersonalAdress").click(function(e) {
		e.preventDefault();

		loadForm(personalAdressInfo, "profileAdress");
	});

	$("#uploadProfilePictureButton").click(function(e) {	
		e.preventDefault();
		$("#fileToUpload").trigger("click");
	});

	$("#fileToUpload").change(function(e) {
		$("#fileToUpload").attr("disabled", true);

		var newProfilePicture = $(this)[0].files[0];
		var fd = new FormData(); 
		
		$("#profileImage").hide();
		$("#loadingMarquee").show();
		
		fd.append('newProfilePicture', newProfilePicture); 

		$.ajax({ 
			url: './ajax_upload_profile_picture.php', 
			type: 'post', 
			data: fd, 
			contentType: false, 
			processData: false, 
			success: function(response){
				$("#fileToUpload").attr("disabled", false);
				console.log(response);
				var resp = JSON.parse(response);
				if(resp.code == 200) {
					$("#loadingMarquee").hide();
					$("#profileImage").html(resp.data);
					$("#profileImage").show();
				} else {
					$("#loadingMarquee").hide();
					$("#profileImage").show();
					if(resp.code == (-10)) {
						window.location = "./bo_login.php?destination=" + encodeURI("./bo_profile.php");
					}
				}
			}, 
		}); 
				
	})
	

});