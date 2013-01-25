/*
 * SimpleModal Contact Form
 * http://www.ericmmartin.com/projects/simplemodal/
 * http://code.google.com/p/simplemodal/
 *
 * Copyright (c) 2010 Eric Martin - http://ericmmartin.com
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Revision: $Id: form.js 254 2010-07-23 05:14:44Z emartin24 $
 */

(smform = {
	event_file:{},
	form_name:{},
	message:{},
		
		init: function (form_id, action_file) {
				event_file=action_file;
				form_name=form_id;
							
				
				// load the form using ajax
				$.get(event_file, { form_id: form_id }, function(data){
					// create a modal dialog with the data
					$(data).modal({
						closeHTML: "<a href='#' title='Close' class='modal-close'>x</a>",
						position: ["35%",],
						overlayId: 'form-overlay',
						containerId: 'form-container',
						onOpen: smform.open,
						onShow: smform.show,
						onClose: smform.close					
					});
				});
				
				
			
		},
		open: function (dialog) {
			// add padding to the buttons in firefox/mozilla
			if ($.browser.mozilla) {
				$('#form-container .form-button').css({
					'padding-bottom': '2px'
				});
			}
			// input field font size
			if ($.browser.safari) {
				$('#form-container .form-input').css({
					'font-size': '.9em'
				});
			}

			// dynamically determine height
			var form_name = $("#form-container form").attr('name');
			if(form_name == 'login'){
				var h = 75;
			}
			else { 
				var h = 55;
			}
			hw = $('.form .input').length * 90; 
			h += hw;
			h_top = h + 30;
			
			$('#form-container').css({
					'width': '727px',
					'left':'50%',
					'margin-left':'-350px',
					'height': h_top+55
			});
			
			$('.form-container-top').css({
					'height': h_top
			});

			var title = $('#form-container .form-title').html();
			$('#form-container .form-title').html('Loading...');
			dialog.overlay.fadeIn(200, function () {
				dialog.container.fadeIn(200, function () {
					dialog.data.fadeIn(200, function () {
						$('#form-container .form-content').animate({
							height: h
						}, function () {
							$('#form-container .form-title').html(title);
							$('#form-container form').fadeIn(200, function () {
								$('#form-container #form-name').focus();

								$('#form-container .form-cc').click(function () {
									var cc = $('#form-container #form-cc');
									cc.is(':checked') ? cc.attr('checked', '') : cc.attr('checked', 'checked');
								});

								// fix png's for IE 6
								if ($.browser.msie && $.browser.version < 7) {
									$('#form-container .form-button').each(function () {
										if ($(this).css('backgroundImage').match(/^url[("']+(.*\.png)[)"']+$/i)) {
											var src = RegExp.$1;
											$(this).css({
												backgroundImage: 'none',
												filter: 'progid:DXImageTransform.Microsoft.AlphaImageLoader(src="' +  src + '", sizingMethod="crop")'
											});
										}
									});
								}
							});
						});
					});
				});
			});
		},
		show: function (dialog) {
			$('#form-container .form-send').click(function (e) {
				e.preventDefault();
				
				var event_file = $('#form-container form').attr('action');
				var form_name = $('#form-container form').attr('name');
				
				// validate form
				if (smform.validate()) {
					var msg = $('#form-container .form-message');
					var res = $('#form-container .form-result');
					msg.fadeOut(function () {
						msg.removeClass('form-error').empty();
					});
					$('#form-container .form-title').html('Sending...');
					$('#form-container form').fadeOut(200);
					$('#form-container .form-content').animate({
						height: '80px'
					}, function () {
						$('#form-container .form-loading').fadeIn(200, function () {
							$.ajax({
								url: event_file,
								data: $('#form-container form').serialize()+'&task='+form_name,
								type: 'post',
								cache: false,
								dataType: 'html',
								success: function (data) {
									
									$('#form-container .form-loading').fadeOut(200, function () {
										$('#form-container .form-title').html('');
										if  (/(Спасибо,)/.test(data) ){
											$('#form-container form').css('display','none');
											res.html(data).fadeIn(200);
										}
										else {
											$('#form-container form').css('display','block');
											msg.html(data).fadeIn(200);
										}
										
									});
								},
								error: smform.error
							});
						});
					});
				}
				else {
					if ($('#form-container .form-message:visible').length > 0) {
						var msg = $('#form-container .form-message div');
						msg.fadeOut(200, function () {
							msg.empty();
							smform.showError();
							msg.fadeIn(200);
						});
					}
					else {
						$('#form-container .form-message').animate({
							height: '10px'
						}, smform.showError);
					}
					
				}
			});
		},
		close: function (dialog) {
				$.modal.close();			
		},
		error: function (xhr) {
			alert(xhr.statusText);
		},
		validate: function () {
			smform.message = '';
			
			$('.form .input').attr('requir', function(i, val) {
					if (val == 1){
						if (!$(this).val()) {
							smform.message += 'Поле '+ $(this).prev().html() +' обязательное для заполнения. ';
							
						}
					}					
			});

			$('.form .input').attr('name', function(i, val) {
					if (val == 'email'){
						var email = $(this).val();
						if (!smform.validateEmail(email)) {
							smform.message += 'Не правильно заполнено поле '+ $(this).prev().html();
							
						}
					}					
			});
			
			if (smform.message.length > 0) {
				return false;
			}
			else {
				return true;
			}
		},
		validateEmail: function (email) {
			var at = email.lastIndexOf("@");

			// Make sure the at (@) sybmol exists and  
			// it is not the first or last character
			if (at < 1 || (at + 1) === email.length)
				return false;

			// Make sure there aren't multiple periods together
			if (/(\.{2,})/.test(email))
				return false;

			// Break up the local and domain portions
			var local = email.substring(0, at);
			var domain = email.substring(at + 1);

			// Check lengths
			if (local.length < 1 || local.length > 64 || domain.length < 4 || domain.length > 255)
				return false;

			// Make sure local and domain don't start with or end with a period
			if (/(^\.|\.$)/.test(local) || /(^\.|\.$)/.test(domain))
				return false;

			// Check for quoted-string addresses
			// Since almost anything is allowed in a quoted-string address,
			// we're just going to let them go through
			if (!/^"(.+)"$/.test(local)) {
				// It's a dot-string address...check for valid characters
				if (!/^[-a-zA-Z0-9!#$%*\/?|^{}`~&'+=_\.]*$/.test(local))
					return false;
			}

			// Make sure domain contains only valid characters and at least one period
			if (!/^[-a-zA-Z0-9\.]*$/.test(domain) || domain.indexOf(".") === -1)
				return false;	

			return true;
		},
		showError: function () {
			$('#form-container .form-message')
				.html($('<div class="form-error"></div>').append(smform.message))
				.fadeIn(200);
		}


	//smform.init(event_file, form_id);

});