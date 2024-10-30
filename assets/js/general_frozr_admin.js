( function( $, wp, ajaxurl ) {
	$( function() {

	var route = norsani_general_admin_script_params.help_route;
	var Frozr_General_Admin_Scripts = {
		init: function() {
			var self = this;
			$( document.body )
			/*Default Theme calls*/
			.on( 'click', '.frozr_activate_default_theme', self.activate_default_theme )
			.on( 'click', '.frozr_install_default_theme', self.install_default_theme )
			.on( 'submit', '.frozr_user_ftp_credit_form > form', self.install_default_theme_credits )
			/*help center*/
			.on( 'click', '.frozrhelp_qa_content h2', self.expand_question )
			.on( 'submit', '#frozr_qu_suggest', self.post_suggest_question )
			.on( 'submit', '#frozr_contact_form', self.post_help_comment )
			.on( 'click', '.frozrhelp_fix_pages', self.fix_pages_errors )
			.on( 'click', '.frozr_install_demo_data_btn', self.install_demo_data );
		},
		fix_pages_errors: function(e) {
			e.preventDefault();
			var selfint = $(this);
			var wrapper = selfint.parents('.frozr_admin_box_content');

			$.ajax({
				beforeSend: function() {wrapper.css({'opacity': '0.6','pointer-events':'none'});},
				complete: function() {wrapper.css({'opacity': '1','pointer-events':'auto'});},
				url: norsani_general_admin_script_params.ajax_url,
				data: {
					action: 'frozr_fix_demo_pages',
					security: norsani_general_admin_script_params.frozrhelp_user_data_nonce,
				},
				type: 'POST',
				success: function( response ) {
					location.reload(true);
				},
			});
		},
		expand_question: function(e) {
			e.preventDefault();
			var wrapper = $(this).parent();
			var content = $('div',wrapper).first();
			var content_active = $('div.active',wrapper).first();
			
			if (content_active.length > 0) {
				content_active.removeClass('active');
			} else {
				content.addClass('active');
			}
		},
		post_suggest_question: function(e) {
			e.preventDefault();
			var selfint = $(this);
			var wrapper = selfint.parent();
			var result_wrap = $('.frozrhelp_qa_result');
			var comment = $('input[name="frozr_help_question"]', selfint);
			var data = {};
			
			data.token = window.sessionStorage.getItem('frozrhelp_token');
			data.message = comment.val();
			
			$.ajax({
				beforeSend: function() {selfint.css({'opacity': '0.6','pointer-events':'none'});},
				complete: function() {selfint.css({'opacity': '1','pointer-events':'auto'});},
				url: route+'post_suggestion',
				data: data,
				type: 'GET',
				success: function( response ) {
					if (response.message) {
					result_wrap.html('<div class="frozr updated notice is-dismissible">'+response.message+'</div>');
					}
				},
				error: function(response) {
					if(response.responseJSON.error_data.comment_duplicate > 0) {
					result_wrap.html('<div class="frozr error notice is-dismissible">'+norsani_general_admin_script_params.frozrhelp_duplicated_question+'</div>');
					} else {
					result_wrap.html('<div class="frozr error notice is-dismissible">'+norsani_general_admin_script_params.frozrhelp_gen_error+'</div>');
					}
				}
			});
		},
		post_help_comment: function(e) {
			e.preventDefault();
			var selfint = $(this);
			var comment = $('textarea[name="frozr_help_message"]', selfint);
			var details_wrapper = $('.frozr_contact_box_details');
			var comments_wrapper = $('.frozr_contact_screen');
			var data = {};
			
			data.token = window.sessionStorage.getItem('frozrhelp_token');
			data.message = comment.val();
			
			$.ajax({
				beforeSend: function() {selfint.css({'opacity': '0.6','pointer-events':'none'});},
				complete: function() {selfint.css({'opacity': '1','pointer-events':'auto'});},
				url: route+'post_comment',
				data: data,
				type: 'GET',
				success: function( response ) {
					if (!$.isEmptyObject(response.comments)) {
					comments_wrapper.html('');
					$.each(response.comments, function(key,value) {
						var admin_cmt = '';
						if (value.user_id == '1') {
							admin_cmt = ' admin_reply';
						}
						comments_wrapper.prepend('<div class="frozr_comment_body'+admin_cmt+'">'+value.comment_content+'<span class="frozr_comment_date">'+value.comment_date+'</span></div>');
					});
					}
				},
				error: function(response) {
					if (response.responseJSON) {
					details_wrapper.prepend('<div class="frozr error notice is-dismissible">'+response.responseJSON+'</div>');
					} else {
					details_wrapper.prepend('<div class="frozr error notice is-dismissible">'+norsani_general_admin_script_params.frozrhelp_gen_error+'</div>');
					}
				}
			});
		},
		install_demo_data: function(e) {
			e.preventDefault();
			var selfint = $(this);
			var wrapper = selfint.parents('.frozr_admin_box_content');
			wrapper.parent().prepend('<div class="update-message frozr_demo_inst_data_notice notice inline notice-warning notice-alt updating-message"><p aria-label="'+norsani_general_admin_script_params.installing_demo_data+'">'+norsani_general_admin_script_params.installing_demo_data+'</p></div>');			
			$.ajax({
				beforeSend: function() {wrapper.css({'opacity': '0.6','pointer-events':'none'});},
				complete: function() {wrapper.css({'opacity': '1','pointer-events':'auto'});},
				url: norsani_general_admin_script_params.ajax_url,
				data: {
					action: 'frozr_install_demo_data',
					security: norsani_general_admin_script_params.frozr_install_demo_data_nonce,
				},
				type: 'POST',
				success: function( response ) {
					$('.frozr_demo_inst_data_notice').remove();
					wrapper.prepend('<div class="frozrhelp_demo_installed">'+response.message+'</strong></div>');
				},
				error: function(xh) {
					console.log(xh);
				}
			});
		},
		activate_default_theme: function(e) {
			e.preventDefault();
			var selfint = $(this);
			var wrapper = selfint.parent();
			
			$.ajax({
				beforeSend: function() {wrapper.css({'opacity': '0.6','pointer-events':'none'});},
				complete: function() {wrapper.css({'opacity': '1','pointer-events':'auto'});},
				url: norsani_general_admin_script_params.ajax_url,
				data: {
					action: 'frozr_activate_default_theme',
					security: norsani_general_admin_script_params.frozr_default_theme_activation_nonce,
				},
				type: 'POST',
				success: function( response ) {
					window.location.href = response.redirect;
				},
			});
		},
		install_default_theme_credits: function(e) {
			e.preventDefault();
			var selfint = $(this);
			var data = {};
			
			data = self.serializeJSON();
			data.action = 'frozr_install_default_theme';
			data.security = norsani_general_admin_script_params.frozr_default_theme_installation_nonce;
			
			$.ajax({
				beforeSend: function() {selfint.css({'opacity': '0.6','pointer-events':'none'});},
				complete: function() {selfint.css({'opacity': '1','pointer-events':'auto'});},
				url: norsani_general_admin_script_params.ajax_url,
				data: data,
				type: 'POST',
				success: function( response ) {
					if (response.needcredit || response.unziperror) {
						var from_wrapper = $('.frozr_user_ftp_credit_form');
						if (response.needcredit) {
						var message = response.needcredit;
						} else {
						var message = response.unziperror;
						}
						if (from_wrapper.length > 0) {
							from_wrapper.html(message);
						} else {							
							$('#wpwrap').addClass('frozr_credits_required').prepend('<div class="frozr_user_ftp_credit_form">'+message+'</div>');
						}
					} else {
					window.location.href = response.redirect;
					}
				},
			});
		},
		install_default_theme: function(e) {
			e.preventDefault();
			var selfint = $(this);
			var wrapper = selfint.parents('.notice');
			
			$.ajax({
				beforeSend: function() {wrapper.css({'opacity': '0.6','pointer-events':'none'});},
				complete: function() {wrapper.css({'opacity': '1','pointer-events':'auto'});},
				url: norsani_general_admin_script_params.ajax_url,
				data: {
					action: 'frozr_install_default_theme',
					security: norsani_general_admin_script_params.frozr_default_theme_installation_nonce,
				},
				type: 'POST',
				success: function( response ) {
					if (response.needcredit || response.unziperror) {
						var from_wrapper = $('.frozr_user_ftp_credit_form');
						if (response.needcredit) {
						var message = response.needcredit;
						} else {
						var message = response.unziperror;
						}
						if (from_wrapper.length > 0) {
							from_wrapper.html(message);
						} else {							
							$('#wpwrap').addClass('frozr_credits_required').prepend('<div class="frozr_user_ftp_credit_form">'+message+'</div>');
						}
					} else {
					window.location.href = response.redirect;
					}
				},
			});
		},
	};
		
	Frozr_General_Admin_Scripts.init();

	});
})( jQuery, wp, ajaxurl );