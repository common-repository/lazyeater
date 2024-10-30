/*global frozr */
jQuery( function( $ ) {
	
	/**
	 * Product update ajax methods
	 */
	var file_frame_two,file_frame;
	var dashboard_scripts = {
		/**
		 * Initialize product update ajax methods
		 */
		init: function() {
			var self = this;
			
			$( document.body )
			.on("click", self.general_click)
			/*Dashboard products page*/
			.on("change", "textarea.attrs_vals:not(.new_attr)", self.edit_addtibutes_name)
			.on("change", "textarea.attrs_vals.new_attr, input.attribute_name.new_attr", self.add_attributes_to_options)
			.on("focusout", "input.attribute_name", self.attribute_names)
			.on("change", ".option_multiple", self.option_multiple)
			.on("click", ".remove_option_group", self.remove_option_group)
			.on("click", ".add_option_form", self.add_option_form)
			.on("click", ".remove_option_field", self.remove_option_field)
			.on("change", ".product_has_variations", self.has_variations)
			.on("change", ".item_fat", self.item_fat)
			.on("change", ".item_promotions_get", self.promotions_get)
			.on("click", ".sale_schedule", self.sale_schedule )
			.on("click", ".cancel_sale_schedule", self.cancel_sale_schedule )
			.on("click", ".update_product_new,.update_product", self.save_changes )
			.on("click", ".delete_item", self.delete_item)
			.on("click", ".content-area-product-edit form a.frozr-remove-feat-image", self.remove_feat_image)
			.on("click", ".content-area-product-edit form a.frozr-feat-image-btn", self.upload_feat_image)
			.on("change", ".instruction-inside, .content-area-product-edit input, .content-area-product-edit textarea", self.input_changed )
			.on("change", ".content-area-product-edit select", self.defaults_changed )
			/*Dashboard vendor settings page*/
			.on("click", "a.frozr-banner-drag, .src_adv_wrp_img", self.imageUpload)
			.on("keyup", "#frozr_store_name", self.change_store_name_display)
			.on("click", "a.frozr-remove-banner-image", self.removeBanner)
			.on("click", "a.frozr-gravatar-drag", self.gragatarImageUpload)
			.on("click", "a.frozr-remove-gravatar-image", self.removeGravatar)
			.on("click", "#frozr_update_profile", self.save_vendor_settings)
			.on("click", "input#show_rest_tables", self.show_tables_settings)
			/*Dashboard admin sellers page*/
			.on("submit", "form.seller_edit_form", self.save_seller_settings)
			.on("submit", "#rest_invit_form", self.rest_invitation)
			/*Dashboard orders page*/
			.on("click", ".order_print_butn", self.frozr_print_order)
			.on("click", "a.order_status_butn", self.update_order_status)
			.on('click', '.or_notes a.add_note', self.add_order_note )
			.on('click', '.or_notes a.delete_note', self.delete_order_note )
			/*Dashboard home*/
			.on("click", ".print_summary_report", self.print_summary_report)
			.on("click", ".show_resutl", self.dash_totals)
			.on("click", ".show_custom", self.show_dash_totals_inputs)
			.on("submit", ".custom_start_end", self.dash_totals)
			.on("change", "#seller_summary_select", self.dash_totals)
			/*Dashboard coupons*/
			.on('submit', '#coupons_form', self.save_coupons)
			.on('click', '.delete_coupon a', self.delete_coupon)
			/*Dashboard withdraw*/
			.on('click', '.frozr-wid-image-btn', self.upload_wid_invoice)
			.on('click', '.frozr-remove-wid-image', self.remove_wid_invoice)
			.on("change", "form.withdraw .ui-radio", self.wid_req_pen)
			.on('submit', 'form.withdraw', self.save_withdraw)
			.on('click', '.delete_wid a', self.delete_withdraw)
			.on('click', '.pay_wid a', self.pay_withdraw)
			.on('click', '.cancel_wid a', self.cancel_withdraw); 

			if ($('.orders_list_table').length) {
				var refresh = setInterval(this.refresh_orders, 150000);//update orders every 2.5 minutes
			}
		},
		wid_req_pen: function() {
			var wrapper = $(this).parent().parent().parent().parent();
			if ($(".pend_wid_req", this).prop("checked")) {
				$(".withdraw_invoice, .wid_reject_div", wrapper).addClass("frozr_hide");
				$(".wid_gen_info", wrapper).removeClass("frozr_hide");
				$( ".edit_wid" ).popup( "reposition", {positionTo: "window"} );
			} else if ($(".reject_wid_req", this).prop("checked")) {
				$(".withdraw_invoice, .wid_gen_info", wrapper).addClass("frozr_hide");
				$(".wid_reject_div", wrapper).removeClass("frozr_hide");
				$( ".edit_wid" ).popup( "reposition", {positionTo: "window"} );
			} else if ($(".com_wid_req", this).prop("checked")) {
				$(".wid_reject_div, .wid_gen_info", wrapper).addClass("frozr_hide");
				$(".withdraw_invoice", wrapper).removeClass("frozr_hide");
				$( ".edit_wid" ).popup( "reposition", {positionTo: "window"} );
			}
		},
		upload_wid_invoice: function(e) {
        e.preventDefault();

        var self = $(this);

        if ( file_frame ) {
            file_frame.open();
            return;
        }

        file_frame = wp.media({
            /* Set the title of the modal.*/
            title: 'Upload Invoice',
            button: {
                text: 'Set Invoice',
            }
        });

        file_frame.on('select', function() {
            var selection = file_frame.state().get('selection');

            selection.map( function( attachment ) {
                attachment = attachment.toJSON();

                console.log(attachment, self);
               
			   /* set the image*/
                var instruction = self.closest('.instruction-inside');
                var wrap = instruction.siblings('.image-wrap');
               
			   /* set the image hidden id*/
                wrap.find('input.frozr-wid-image-id').val(attachment.id).change();

                /* wrap.find('img').attr('src', attachment.sizes.thumbnail.url);*/
                wrap.find('div.withdraw_img').css("background-image","url(" + attachment.url + ")");

                instruction.addClass('frozr_hide');
                wrap.removeClass('frozr_hide');
            });
        });

        file_frame.open();

		},
		remove_wid_invoice: function(e) {
			e.preventDefault();

			var self = $(this);
			var wrap = self.closest('.image-wrap');
			var instruction = wrap.siblings('.instruction-inside');

			wrap.find('input.frozr-wid-image-id').val('0');
			wrap.addClass('frozr_hide');
			instruction.removeClass('frozr_hide');
		},
		pay_withdraw: function(e) {
			e.preventDefault();
			if ( window.confirm( norsani_dashboard_script_params.withdraw_pay ) ) {
			var data = {},
				wrapper = $('.frozr_dash_withdraw_list'),
				req_id = $(this).attr('req_id');
				payout_row = $( 'tr[data-id="'+req_id+'"]',wrapper ),

			payout_row.css('opacity', '0.6');

			data.action		= 'frozr_process_payout';
			data.security	= norsani_dashboard_script_params.frozr_process_inst_payment_payout;
			data.wid_id		= req_id;
			$.ajax({
				beforeSend: function() {$( document.body ).trigger('frozr_body_loading');},
				complete: function() {$( document.body ).trigger('frozr_body_loading_complete');},
				url: norsani_dashboard_script_params.ajax_url,
				data: data,
				type: 'POST',
				success: function( response ) {
				payout_row.css('opacity', '1');
				if(response.error) {
				$( document.body ).trigger('frozr_wid_error',[response]);
				} else {
				$( document.body ).trigger('frozr_pay_wid_success',[response,req_id]);
				}
				},
				error: function(response) {
				$( document.body ).trigger('frozr_wid_error',[response]);
				},
			});
			}
		},
		cancel_withdraw: function(e) {
			e.preventDefault();
			if ( window.confirm( norsani_dashboard_script_params.withdraw_cancel ) ) {
			var data = {},
				wrapper = $('.frozr_dash_withdraw_list'),
				req_id = $(this).attr('req_id');
				payout_row = $( 'tr[data-id="'+req_id+'"]',wrapper ),

			payout_row.css('opacity', '0.6');

			data.action			= 'frozr_cancel_payout';
			data.security		= norsani_dashboard_script_params.cancel_fro_withdraw;
			data.payout_id		= req_id;
			$.ajax({
				beforeSend: function() {$( document.body ).trigger('frozr_body_loading');},
				complete: function() {$( document.body ).trigger('frozr_body_loading_complete');},
				url: norsani_dashboard_script_params.ajax_url,
				data: data,
				type: 'POST',
				success: function( response ) {
				payout_row.css('opacity', '1');
				if(response.error) {
				$( document.body ).trigger('frozr_wid_error',[response]);
				} else {
				$( document.body ).trigger('frozr_cancel_wid_success',[response,req_id]);
				}
				},
				error: function(response) {
				$( document.body ).trigger('frozr_wid_error',[response]);
				},
			});
			}
		},
		delete_withdraw: function(e) {
			e.preventDefault();
			if ( window.confirm( norsani_dashboard_script_params.withdraw_delete ) ) {
			var data = {},
				req_id = $(this).attr('req_id');

			$( 'form.withdraw' ).css('opacity', '0.6');

			data.action			= 'frozr_delete_withdraw';
			data.security		= norsani_dashboard_script_params.delete_fro_withdraw;
			data.withdraw_id	= req_id;
			$.ajax({
				beforeSend: function() {$( document.body ).trigger('frozr_body_loading');},
				complete: function() {$( document.body ).trigger('frozr_body_loading_complete');},
				url: norsani_dashboard_script_params.ajax_url,
				data: data,
				type: 'POST',
				success: function( response ) {
					$( 'form.withdraw' ).css('opacity', 'initial');
					location.reload(true);
				}
			});
			}
		},
		save_withdraw: function(e) {
			e.preventDefault();
			if ( window.confirm( norsani_dashboard_script_params.forzr_save_withdraw ) ) {
				var wrapper		= $(this),
					data		= {};

				$( 'form.withdraw' ).css('opacity', '0.6');
				
				data                 = wrapper.serializeJSON();
				data.action          = 'frozr_save_withdraw';
				data.security        = norsani_dashboard_script_params.frozr_save_withdraw;

				$.ajax({
					beforeSend: function() {$( document.body ).trigger('frozr_body_loading');},
					complete: function() {$( document.body ).trigger('frozr_body_loading_complete');},
					url: norsani_dashboard_script_params.ajax_url,
					data: data,
					type: 'POST',
					success: function( response ) {
						$( 'form.withdraw' ).css('opacity', 'initial');
						location.reload(true);
					}
				});
			}
		},
		add_order_note: function() {
			if ( ! $( 'textarea#add_order_note' ).val() ) {
				return;
			}

			$( '.or_notes' ).css('opacity', '0.6');

			var orderid = $('#orders').data('orderid'),
				data = {
					action:    'frozr_add_order_note',
					post_id:   orderid,
					note:      $( 'textarea#add_order_note' ).val(),
					note_type: $( 'select#order_note_type' ).val(),
					security:  norsani_dashboard_script_params.add_order_note
				};

			$.post( norsani_dashboard_script_params.ajax_url, data, function( response ) {
				$( 'ul.order_notes' ).prepend( response );
				$( '.or_notes' ).css('opacity', 'initial');
				$( '#add_order_note' ).val( '' );
			});

			return false;
		},
		delete_order_note: function() {
			var note = $( this ).closest( 'li.note' );

			$( note ).css('opacity', '0.6');

			var data = {
				action:   'frozr_delete_order_note',
				note_id:  $( note ).attr( 'rel' ),
				security: norsani_dashboard_script_params.delete_order_note_nonce
			};

			$.post( norsani_dashboard_script_params.ajax_url, data, function() {
				$( note ).remove();
			});

			return false;
		},
		update_order_status: function(e) {
			if (window.confirm('Sure?')) {
			e.preventDefault();
			var new_status = $(this).data('status'),
				order_id = $(this).data('orderid'),
				wrapper = $(this).closest('table'),
				data        = {};

			wrapper.css('opacity', '0.6');

			data.action		= 'frozr_set_order_status';
			data.security	= norsani_dashboard_script_params.set_order_status;
			data.order_id	= order_id;
			data.order_sts	= new_status;

			$.ajax({
				beforeSend: function() {$( document.body ).trigger('frozr_body_loading');},
				complete: function() {$( document.body ).trigger('frozr_body_loading_complete');},
				url: norsani_dashboard_script_params.ajax_url,
				data: data,
				type: 'POST',
				success: function( response ) {
					location.reload(true);
				}, error: function(err) {
					console.log(err);
				}
			});
			}
		},
		refresh_orders: function() {
			location.reload(true);
		},
		save_coupons: function(e) {
			e.preventDefault();
			var wrapper		= $(this),
				data		= {};
			
			wrapper.css('opacity', '0.6');
			
			data                 = wrapper.serializeJSON();
			data.action          = 'frozr_coupons_create';
			data.security        = norsani_dashboard_script_params.coupon_nonce_field;
			
			$.ajax({
				beforeSend: function() {$( document.body ).trigger('frozr_body_loading');},
				complete: function() {$( document.body ).trigger('frozr_body_loading_complete');},
				url: norsani_dashboard_script_params.ajax_url,
				data: data,
				type: 'POST',
				success: function( response ) {
					wrapper.css('opacity','1');
					if (response.data) {
					$( document.body ).trigger('norsani_display_msg',[response.data]);
					} else {
					$( document.body ).trigger('frozr_coupon_updated',[response.dlink]);
					$( document.body ).trigger('norsani_display_msg',[response.message]);
					}
				}, error: function(err) {
					console.log(err);
				}
			});
		},
		delete_coupon: function(e) {
			e.preventDefault();
			if ( window.confirm( norsani_dashboard_script_params.coupon_delete ) ) {
			var data = {},
				wrapper = $(this).parent().parent().parent().parent(),
				req_id = $(this).parent().data('coupid');

			data.action			= 'frozr_coupun_delete';
			data.security		= norsani_dashboard_script_params.coupon_del_nonce;
			data.post_id		= req_id;
			$.ajax({
				beforeSend: function() {$( document.body ).trigger('frozr_body_loading');},
				complete: function() {$( document.body ).trigger('frozr_body_loading_complete');},
				url: norsani_dashboard_script_params.ajax_url,
				data: data,
				type: 'POST',
				success: function( response ) {
					if (response.success) {
						$( document.body ).trigger('norsani_display_msg',[response.data]);
						wrapper.remove();
					} else {
						$( document.body ).trigger('norsani_display_msg',[response.data]);						
					}
				}, error: function(err) {
					console.log(err);
				}
			});
			}
		},
		edit_addtibutes_name: function(e) {
			var content = $(this).val();
			var options = content.split(norsani_dashboard_script_params.woo_sep);
			var currosponding = $(this).attr('data-attropt');
			$('select[data-attropt="'+currosponding+'"').each(function() {
				var selecter = $(this);
				var selval = selecter.val();
				var empty_selected = '';
				selecter.html('');
				$.each( options, function( key, value ) {
					var opt = $.trim(value);
					if (opt) {
					selecter.append('<option value="'+opt+'">'+opt+'</option>');
					}
				});
				selecter.prepend('<option selected="selected" class="default_option" value="">'+norsani_dashboard_script_params.any_attr+' ' +currosponding+'</option>');
			});
		},
		add_attributes_to_options: function(e) {
			var wrapper = $(this).parents('.option_group');
			var main_wrapper = $(this).parents('.item_variation_wrapper');
			main_wrapper.attr('style','pointer-events:none;opacity:0.8');
			var attr_name = $('input', wrapper).val();
			var attr_name_sanitized = dashboard_scripts.wpFeSanitizeTitle(attr_name);
			var attr_vals = $('textarea', wrapper).val();
			var opts = $('.multi-fields', main_wrapper);
			var opts_wrap = $('.multi-field .options_group_wrapper', main_wrapper);
			if (attr_name_sanitized && attr_vals && $('div',opts).length > 1) {
				var options = attr_vals.split(norsani_dashboard_script_params.woo_sep);
				opts_wrap.each( function() {
					$('.option_group:last-child', this).clone(true).appendTo(this);
					var selecter = $('.option_group:last-child select.item_options', this);
					$('.option_group:last-child input.item_option_attribute', this).val(attr_name).attr({'name': 'var_'+attr_name_sanitized, 'data-attrname': attr_name_sanitized});
					$('.option_group:last-child .attr_name', this).text(attr_name);
					$('.option_group:last-child span.item_options ', this).text(norsani_dashboard_script_params.any_attr+' ' +attr_name);
					selecter.html('').attr({'name': 'item_options[]['+attr_name_sanitized+']', 'data-attropt': attr_name_sanitized});
					$.each( options, function( key, value ) {
						var opt = $.trim(value);
						if (opt) {
						selecter.append('<option value="'+opt+'">'+opt+'</option>');
						}
					});
					selecter.prepend('<option selected="selected" class="default_option" value="">'+norsani_dashboard_script_params.any_attr+' ' +attr_name+'</option>');
				});
			$('input', wrapper).removeClass('new_attr');
			$('textarea', wrapper).removeClass('new_attr');
			}
			main_wrapper.attr('style','');
		},
		attribute_names: function(e) {
			var wrapper = $(this).parents('.item_variation_wrapper');
			wrapper.attr('style','pointer-events:none;opacity:0.8');
			var main_wrapper = $(this).parents('.option_group');
			var content = $(this).val();
			var attr_name_sanitized = dashboard_scripts.wpFeSanitizeTitle(content);
			$(this).attr('data-attrname', attr_name_sanitized);
			var currosponding = $(this).attr('data-attrname');
			var input_name = $(this).attr('name');

			if (input_name == 'attribute_names[]') {
				$(this).attr('name', 'attribute_names['+attr_name_sanitized+']');
				$('textarea', main_wrapper).attr('name', 'attribute_values['+attr_name_sanitized+']');
			}

			$('textarea', main_wrapper).attr('data-attropt', attr_name_sanitized);

			$('select[data-attropt="'+currosponding+'"').each(function() {
				$(this).attr('data-attropt',attr_name_sanitized);
				$(this).attr('name', 'item_options[]['+attr_name_sanitized+']');
				$('option.default_option', this).html(norsani_dashboard_script_params.any_attr+' ' +content);
			});
			$('input[data-attrname="'+currosponding+'"').each(function() {
				var wrapper = $(this).parent();
				$(this).attr('data-attrname',attr_name_sanitized);
				$('.attr_name',wrapper).text(content);
				$(this).val(content);
			});
			wrapper.attr('style','');
		},
		option_multiple: function(e) {
			var wrapper = $(this).parents('.item_variation_wrapper');
			var attr_name = $('.attribute_name', this).val();
			var attr_vals = $('.attrs_vals', this).val();
			var opts = $('.multi-fields', wrapper);
			if (attr_name && attr_vals && $('div',opts).length < 1) {
				var data = {};
				data.action		= 'frozr_add_product_variation';
				data.varname	= $.trim(attr_name);
				data.varopts	= attr_vals.split(norsani_dashboard_script_params.woo_sep);
				data.security	= norsani_dashboard_script_params.frozr_add_variation_nonce;

				$.ajax({
					beforeSend: function() {$( document.body ).trigger('frozr_body_loading');},
					complete: function() {$( document.body ).trigger('frozr_body_loading_complete');},
					url: norsani_dashboard_script_params.ajax_url,
					data: data,
					type: 'POST',
					success: function( response ) {
						opts.append(response);
						$('.add-field',wrapper).show();
					}
				});
			$('input', this).removeClass('new_attr');
			$('textarea', this).removeClass('new_attr');
			}
		},
		remove_option_group: function(e) {
			e.preventDefault();
			var opt_wrapper = $(this).parent();
			var wrapper = opt_wrapper.parent();
			var main_wrapper = opt_wrapper.parent().parent().parent();
			var attr_name = $('.attribute_name', opt_wrapper).val();
			$('input[data-attrname="'+attr_name+'"]:not(.attribute_name)').each(function() {
				$(this).parent().parent().remove();
			});
			if ($('.option_group', wrapper).length > 1) {
				$(this).parent('.option_group').remove();
			} else {
				$('.option_group:last-child', wrapper).clone(true).appendTo(wrapper).focus();
				$('.option_group:last-child input', wrapper).addClass('new_attr').val('').attr({'name': 'attribute_names[]', 'data-attrname': ''}).focus();
				$('.option_group:last-child textarea', wrapper).addClass('new_attr').html('').attr({'name': 'attribute_values[]', 'data-attropt': ''});
				$(this).parent('.option_group').remove();
				var main_wrapper = wrapper.parent().parent();
				$('.multi-fields',main_wrapper).html('');
				$('.add-field',main_wrapper).hide();
			}
		},
		add_option_form: function(e) {
			var wrapper = $(this).parent();
			var opt_wrapper = $('.option_multiple',wrapper);
			$('.option_group:last-child', opt_wrapper).clone(true).appendTo(opt_wrapper).focus();
			$('.option_group:last-child input', opt_wrapper).addClass('new_attr').val('').attr({'name': 'attribute_names[]', 'data-attrname': ''});
			$('.option_group:last-child textarea', opt_wrapper).addClass('new_attr').html('').attr({'name': 'attribute_values[]', 'data-attropt': ''});
		},
		remove_option_field: function(e) {
			e.preventDefault();
			var wrapper = $(this).parent();
			var wrapper_two = wrapper.parent();
			if ($('.multi-field', wrapper_two).length > 1) {
				wrapper.remove();
			} else {
				$('.multi-field:last-child', wrapper_two).clone(true).appendTo(wrapper_two).find('input').val('').focus();
				wrapper.remove();
			}
		},
		rest_invitation: function (e) {
			e.preventDefault();
			var wrapper		= $(this).parent(),
				self		= $(this),
				data		= {};

			wrapper.css('opacity', '0.6');

			data			= self.serializeJSON();
			data.action		= 'frozr_send_rest_invitation';
			data.security	= norsani_dashboard_script_params.frozr_rest_invitation_nonce;

			$.ajax({
				beforeSend: function() {$( document.body ).trigger('frozr_body_loading');},
				complete: function() {$( document.body ).trigger('frozr_body_loading_complete');},
				url: norsani_dashboard_script_params.ajax_url,
				data: data,
				type: 'POST',
				success: function( response ) {
					$( document.body ).trigger('norsani_display_msg',[response.message]);
					wrapper.css('opacity', 'initial');
				}
			});
		},
		show_tables_settings: function() {
			if ($(this).prop("checked")) {
				$('#usr_tables_opts .multi-field-wrapper').removeClass('frozr_hide');
			} else {
				$('#usr_tables_opts .multi-field-wrapper').addClass('frozr_hide');
			}
		},
		change_store_name_display: function(e) {
			e.preventDefault();
			var self = $(this),value = self.val();
			$('.settings-store-name').text(value);
		},
		save_seller_settings: function(e) {
			e.preventDefault();
			var wrapper		= $(this),
				td_wrapper	= wrapper.parents('td');
				data		= {};
		
			wrapper.css('opacity', '0.6');
			
			data				= wrapper.serializeJSON();
			data.action			= 'frozr_seller_settings';
			data.security		= norsani_dashboard_script_params.frozr_seller_settings_nonce;

			$.ajax({
				beforeSend: function() {$( document.body ).trigger('frozr_body_loading');},
				complete: function() {$( document.body ).trigger('frozr_body_loading_complete');},
				url: norsani_dashboard_script_params.ajax_url,
				data: data,
				type: 'POST',
				success: function( response ) {
					$( document.body ).trigger('norsani_display_msg',[response.message]);
					if (response.sts) {
						$('.frozr_vendor_sts',td_wrapper).html(response.sts);
					}
					wrapper.css('opacity', 'initial');
				}
			});
		},
		imageUpload: function(e) {
			e.preventDefault();

			var self = $(this),
				wrapper = self.parents('.frozr-banner');

			/* If the media frame already exists, reopen it.*/
			if ( file_frame_two ) {
				file_frame_two.open();
				return;
			}

			/* Create the media frame.*/
			file_frame_two = wp.media({
				title: self.data( 'uploader_title' ),
				button: {
					text: self.data( 'uploader_button_text' )
				},
				multiple: false
			});

			/* When an image is selected, run a callback.*/
			file_frame_two.on( 'select', function(e) {
				var attachment = file_frame_two.state().get('selection').first().toJSON();
				
				$('.frozr-banner-img',wrapper).attr('data-imageid', attachment.id).css('background-image', 'url('+attachment.url+')').trigger('change');
				jQuery('input.frozr-banner-field', wrapper).val(attachment.id);
				jQuery('.image-wrap', wrapper).removeClass('frozr_hide');

				jQuery('.button-area', wrapper).addClass('frozr_hide');
			});

			/* Finally, open the modal*/
			file_frame_two.open();

		},
		gragatarImageUpload: function(e) {
			e.preventDefault();

			var self = $(this),
				wrapper = self.parent().parent();

			/* If the media frame already exists, reopen it.*/
			if ( file_frame ) {
				file_frame.open();
				return;
			}

			/* Create the media frame.*/
			file_frame = wp.media({
				title: jQuery( this ).data( 'uploader_title' ),
				button: {
					text: jQuery( this ).data( 'uploader_button_text' )
				},
				multiple: false
			});
			
			/* When an image is selected, run a callback.*/
			file_frame.on( 'select', function() {
				var attachment = file_frame.state().get('selection').first().toJSON();
				
				jQuery('input.frozr-gravatar-field', wrapper).val(attachment.id);
				jQuery('.frozr-gravatar-img', wrapper).css('background-image', 'url('+attachment.url+')');
				jQuery('.gravatar-wrap', wrapper).removeClass('frozr_hide');
				jQuery('.gravatar-button-area', wrapper).addClass('frozr_hide');
			});

			/* Finally, open the modal*/
			file_frame.open();

		},
		removeBanner: function(e) {
			e.preventDefault();

			var self = $(this);
			var wrap = self.closest('.image-wrap');
			var instruction = wrap.siblings('.button-area');
			$('.frozr-banner').css('background-image', 'url()');
			wrap.find('input.frozr-banner-field').val('0');
			wrap.addClass('frozr_hide');
			instruction.removeClass('frozr_hide');
		},
		removeGravatar: function(e) {
			e.preventDefault();
	
			var self = $(this);
			var wrap = self.closest('.gravatar-wrap');
			var instruction = wrap.siblings('.gravatar-button-area');

			wrap.find('input.frozr-gravatar-field').val('0');
			wrap.addClass('frozr_hide');
			instruction.removeClass('frozr_hide');
		},
		get_vendor_fields: function() {
			var data = $( 'form#settings-form' ).serializeJSON();

			$( 'form#settings-form select' ).each( function( index, element ) {
				var select = $( element );
				data[ select.attr( 'name' ) ] = select.val();
			});

			return data;
		},
		save_vendor_settings: function(e) {
			e.preventDefault();
			var wrapper		= $( '#settings-form' ),
				address_geo	= $('#setting_address', wrapper).attr('data-geo'),
				data		= {};

			if (address_geo) {
				var restaddgeo = address_geo;
			} else {
				var restaddgeo = '';
			}

			wrapper.css('opacity', '0.6');
			
			data				= dashboard_scripts.get_vendor_fields();
			data.delivery_locs	= $('#delivery_locations_map').data('poly');
			data.addressgeo		= restaddgeo;
			data.delivery_locs_filtered	= $('#delivery_locations_map').data('polyfilterd');
			data.action			= 'frozr_save_vendor_settings';
			data.security		= norsani_dashboard_script_params.vendor_settings_nonce;
			
			$.ajax({
				beforeSend: function() {$( document.body ).trigger('frozr_body_loading');},
				complete: function() {$( document.body ).trigger('frozr_body_loading_complete');},
				url: norsani_dashboard_script_params.ajax_url,
				data: data,
				type: 'POST',
				success: function( response ) {
					if (response.success) {
						$( document.body ).trigger('norsani_display_msg',[response.data]);
					}
					wrapper.css('opacity', 'initial');
				},
				error: function(erre) {
					console.log(erre);
				}
			});
		},
		frozr_print_order: function(e) {
			e.preventDefault();
			var btn     	= $(this),
				wrapper     = btn.parent(),
				data		= {};

			wrapper.css('opacity', '0.6');
			
			data.action		= 'frozr_print_order';
			data.order_id	= btn.data('orderid');
			data.security	= norsani_dashboard_script_params.frozr_dash_print;

			$.ajax({
				beforeSend: function() {$( document.body ).trigger('frozr_body_loading');},
				complete: function() {$( document.body ).trigger('frozr_body_loading_complete');},
				url: norsani_dashboard_script_params.ajax_url,
				data: data,
				type: 'POST',
				success: function( response ) {
					wrapper.css('opacity', 'initial');
					window.location.href=response.url;
				}
			});
		},
		print_summary_report: function(e) {
			e.preventDefault();
			var btn     	= $(this),
				wrapper     = btn.parent().closest('.dash_totals'),
				data		= {};

			wrapper.css('opacity', '0.6');
			
			data.action		= 'frozr_print_summary_report';
			data.rtype		= $('.show_resutl.active', wrapper).data('rtype');
			data.auser		= $('#seller_summary_select', wrapper).val();
			data.startd		= $('.dast_totals_start', wrapper).val();
			data.endd		= $('.dast_totals_end', wrapper).val();
			data.security	= norsani_dashboard_script_params.frozr_dash_print;

			$.ajax({
				beforeSend: function() {$( document.body ).trigger('frozr_body_loading');},
				complete: function() {$( document.body ).trigger('frozr_body_loading_complete');},
				url: norsani_dashboard_script_params.ajax_url,
				data: data,
				type: 'POST',
				success: function( response ) {
					wrapper.css('opacity', 'initial');
					window.location.href=response.url;
				}
			});
		},
		dash_totals: function(e) {
			e.preventDefault();
			var btn     	= $(this),
				wrapper     = btn.parent().closest('.dash_totals'),
				rrtype		= btn.data('rtype'),
				data		= {};

			wrapper.css('opacity', '0.6');
			
			/*Close custom filter if open*/
			if ($('.custom_start_end_opened').length > 0) {
				$('.custom_start_end').removeClass('custom_start_end_opened').hide();
			}
console.log(rrtype);
			$('.show_resutl', wrapper).removeClass('active');
			data.action		= 'frozr_get_totals_data';
			data.rtype		= rrtype;
			data.auser		= $('#seller_summary_select', wrapper).val();
			data.startd		= $('.dast_totals_start', wrapper).val();
			data.endd		= $('.dast_totals_end', wrapper).val();
			data.security	= norsani_dashboard_script_params.get_total_dash_rep;

			$.ajax({
				beforeSend: function() {$( document.body ).trigger('frozr_body_loading');},
				complete: function() {$( document.body ).trigger('frozr_body_loading_complete');},
				url: norsani_dashboard_script_params.ajax_url,
				data: data,
				type: 'POST',
				success: function( response ) {
					wrapper.css('opacity', 'initial');
					btn.addClass('active');
					$('.dash_totals_results', wrapper).html(response);
				},
				error: function (xhr) {
					console.log(xhr);
				}
			});
		},
		general_click: function(e) {
			if (!$(e.target).is('.custom_start_end, .custom_start_end *,.show_custom, .show_custom *') && $('.custom_start_end_opened').length > 0) {
				$('.custom_start_end').removeClass('custom_start_end_opened').hide();
			}
		},
		show_dash_totals_inputs: function(e) {
			e.preventDefault();
			$(this).next('form').addClass('custom_start_end_opened').show();
		},
		delete_item: function(e) {
		if ( window.confirm( norsani_dashboard_script_params.delete_item ) ) {
			e.preventDefault();
			var btn     	= $(this),
				wrapper     = btn.parent().closest('tr'),
				data		= {};
				 

			wrapper.css('opacity', '0.6');

			data.action		= 'frozr_delete_item';
			data.itemid		= btn.data('item');
			data.security	= norsani_dashboard_script_params.frozr_delete_item_nonce;

			$.ajax({
				beforeSend: function() {$( document.body ).trigger('frozr_body_loading');},
				complete: function() {$( document.body ).trigger('frozr_body_loading_complete');},
				url: norsani_dashboard_script_params.ajax_url,
				data: data,
				type: 'POST',
				success: function( response ) {
					if (response.success) {
						$( document.body ).trigger('norsani_display_msg',[response.data]);
						wrapper.remove();
					} else {
						$( document.body ).trigger('norsani_display_msg',[response.data]);					
					}
				}
			});
		}
		},
		has_variations: function() {
			var wrapper = $(this).parents('.woocommerce_options_panel');
			var wrapper_two = $(this).parents('.frozr_product_edit_collapsible ');
			if ($(this).prop("checked")) {
				$('#_regular_price, #_sale_price, #_sale_price_dates_from, #_sale_price_dates_to', wrapper_two).prop("disabled", true).parents('.options_group.pricing').addClass('frozr_reg_price_dis');
				$('.item_variation_wrapper', wrapper).removeClass('frozr_hide');
				$(this).val('yes');
			} else {
				$('#_regular_price, #_sale_price, #_sale_price_dates_from, #_sale_price_dates_to', wrapper_two).prop("disabled", false).parents('.options_group.pricing').removeClass('frozr_reg_price_dis');
				$('.item_variation_wrapper', wrapper).addClass('frozr_hide');
				$(this).val('no');
			}
			if ($('.multi-fields *', wrapper).length == 0) {
				$('.add-field',wrapper).hide();
			}
		},
		item_fat: function() {
			var wrapper = $(this).parents('.form-group');
			if ($(this).prop("checked")) {
				$('.item_fat_rate', wrapper).removeClass('frozr_hide');
			} else {
				$('.item_fat_rate', wrapper).addClass('frozr_hide');
			}
		},
		sale_schedule: function() {
			var self = $(this);
			var wrap_no_opt = self.parents( '.options_group.pricing' );
			var wrap_in_opt = self.parents( '.multi-field.item_variation' );
			var wrap = wrap_no_opt.length > 0 ? wrap_no_opt : wrap_in_opt;

			$( this ).addClass('frozr_hide');
			wrap.find( '.cancel_sale_schedule' ).removeClass('frozr_hide');
			wrap.find( '.sale_price_dates_fields' ).removeClass('frozr_hide');

			return false;
		},
		cancel_sale_schedule: function() {
			var self = $(this);
			var wrap_no_opt = self.parents( '.options_group.pricing' );
			var wrap_in_opt = self.parents( '.multi-field.item_variation' );
			var wrap = wrap_no_opt.length > 0 ? wrap_no_opt : wrap_in_opt;

			$( this ).addClass('frozr_hide');
			wrap.find( '.sale_schedule' ).removeClass('frozr_hide');
			wrap.find( '.sale_price_dates_fields' ).addClass('frozr_hide');
			wrap.find( '.sale_price_dates_fields' ).find( 'input' ).val('');

			return false;
		},
		promotions_get: function() {
			var wrapper = $(this).parent().parent(),
				val = $( this ).val();
			
			if (val == 'discount') {
				$('.discount-form-control', wrapper).show().prop( "disabled", false );
				$('.item-form-control', wrapper).hide().prop( "disabled", true );
				
			} else if (val == 'free_item') {
				$('.discount-form-control', wrapper).hide().prop( "disabled", true );
				$('.item-form-control', wrapper).show().prop( "disabled", false );
			}
			return false;
		},
		upload_feat_image: function(e) {
			e.preventDefault();

			var self = $(this);
			var product_featured_frame;

			if ( product_featured_frame ) {
				product_featured_frame.open();
				return;
			}

			product_featured_frame = wp.media({
				/* Set the title of the modal.*/
				title: 'Upload featured image',
				button: {
					text: 'Set featured image',
				}
			});

			product_featured_frame.on('select', function() {
				var selection = product_featured_frame.state().get('selection');

				selection.map( function( attachment ) {
					attachment = attachment.toJSON();

					/* set the image hidden id*/
					self.siblings('input.frozr-feat-image-id').val(attachment.id).change();

					/* set the image*/
					var instruction = self.closest('.instruction-inside');
					var wrap = instruction.siblings('.image-wrap');

					/* wrap.find('img').attr('src', attachment.sizes.thumbnail.url);*/
					wrap.find('div.product-photo').css("background-image","url(" + attachment.url + ")");

					instruction.addClass('frozr_hide');
					wrap.removeClass('frozr_hide');
				});
			});

			product_featured_frame.open();
		},
		remove_feat_image: function(e) {
			e.preventDefault();

			var self = $(this);
			var wrap = self.closest('.image-wrap');
			var instruction = wrap.siblings('.instruction-inside');

			instruction.find('input.frozr-feat-image-id').val('0').change();
			wrap.addClass('frozr_hide');
			instruction.removeClass('frozr_hide');
		},
		save_changes: function(e) {
			e.preventDefault();
			var wrapper = $(this).parents('.product_form');
			var new_item = $(this).data('new');
			if (new_item){
			var newi = 1;
			} else {
			var newi = 0;
			}
			var id			= $( 'input.pid', wrapper ).val(),
				product_cat = $('input[name="product_cat"]',wrapper).val(),
				need_update	= $( '.popt-needs-update', wrapper ),
				data		= {};
			
			/* Save only with products need update.*/
			if ( 0 < need_update.length ) {
				
				if (product_cat.length == 0) {
					window.confirm( norsani_dashboard_script_params.item_category_not_set );
					return false;
				}

				data				= wrapper.serializeJSON();
				data.action			= 'frozr_update_product';
				data.security		= norsani_dashboard_script_params.update_wc_product_nonce;
				data.product_id		= id;
				data.newitem		= newi;

				$.ajax({
					beforeSend: function() {$( document.body ).trigger('frozr_body_loading');},
					complete: function() {$( document.body ).trigger('frozr_body_loading_complete');},
					url: norsani_dashboard_script_params.ajax_url,
					data: data,
					type: 'POST',
					success: function( response ) {
		
						need_update.removeClass( 'popt-needs-update' );
						
						if (newi) {
							wrapper.html(response).enhanceWithin();
						} else {
							$( document.body ).trigger('norsani_display_msg',[response.msg]);
						}
					}
				}).done(function(response) {
					$( document.body ).trigger('frozr_item_updated',[response]);					
				});
			} else {
				window.confirm( norsani_dashboard_script_params.item_form_not_changed );
			}
		},
		input_changed: function() {
			$( this )
				.closest( 'div' )
				.addClass( 'popt-needs-update' );
		},
		defaults_changed: function() {
			$( this )
				.closest( 'div' )
				.addClass( 'popt-needs-update' );
		},
		/*Original Source: https://salferrarello.com/wordpress-sanitize-title-javascript/*/
		wpFeSanitizeTitle: function(title) {
		var diacriticsMap;

		return removeSingleTrailingDash(
			replaceSpacesWithDash(
				removeAccents(
					// Strip any HTML tags.
					title.replace( /<[^>]+>/ig, '' )
				).toLowerCase()
				// Replace anything that is not a:
					// word character
					// space
					// nor a dash (-)
				// with an empty string (i.e. remove it).
				.replace(/[^\w\s-]+/g, '')
			)
		);
		function replaceSpacesWithDash( str ) {
			return str
				// Replace one or more blank spaces with a single dash (-)
				.replace(/ +/g,'-')
				// Replace two or more dashes (-) with a single dash (-).
				.replace(/-{2,}/g, '-');
		}
		function removeSingleTrailingDash( str ) {
			if ( '-' === str.substr( str.length - 1 ) ) {
				return str.substr( 0, str.length - 1 );
			}
			return str;
		}
		function getDiacriticsRemovalMap() {
			if ( diacriticsMap ) {
				return diacriticsMap;
			}
			var defaultDiacriticsRemovalMap = [
				{'base':'A', 'letters':'\u0041\u24B6\uFF21\u00C0\u00C1\u00C2\u1EA6\u1EA4\u1EAA\u1EA8\u00C3\u0100\u0102\u1EB0\u1EAE\u1EB4\u1EB2\u0226\u01E0\u00C4\u01DE\u1EA2\u00C5\u01FA\u01CD\u0200\u0202\u1EA0\u1EAC\u1EB6\u1E00\u0104\u023A\u2C6F'},
				{'base':'AA','letters':'\uA732'},
				{'base':'AE','letters':'\u00C6\u01FC\u01E2'},
				{'base':'AO','letters':'\uA734'},
				{'base':'AU','letters':'\uA736'},
				{'base':'AV','letters':'\uA738\uA73A'},
				{'base':'AY','letters':'\uA73C'},
				{'base':'B', 'letters':'\u0042\u24B7\uFF22\u1E02\u1E04\u1E06\u0243\u0182\u0181'},
				{'base':'C', 'letters':'\u0043\u24B8\uFF23\u0106\u0108\u010A\u010C\u00C7\u1E08\u0187\u023B\uA73E'},
				{'base':'D', 'letters':'\u0044\u24B9\uFF24\u1E0A\u010E\u1E0C\u1E10\u1E12\u1E0E\u0110\u018B\u018A\u0189\uA779\u00D0'},
				{'base':'DZ','letters':'\u01F1\u01C4'},
				{'base':'Dz','letters':'\u01F2\u01C5'},
				{'base':'E', 'letters':'\u0045\u24BA\uFF25\u00C8\u00C9\u00CA\u1EC0\u1EBE\u1EC4\u1EC2\u1EBC\u0112\u1E14\u1E16\u0114\u0116\u00CB\u1EBA\u011A\u0204\u0206\u1EB8\u1EC6\u0228\u1E1C\u0118\u1E18\u1E1A\u0190\u018E'},
				{'base':'F', 'letters':'\u0046\u24BB\uFF26\u1E1E\u0191\uA77B'},
				{'base':'G', 'letters':'\u0047\u24BC\uFF27\u01F4\u011C\u1E20\u011E\u0120\u01E6\u0122\u01E4\u0193\uA7A0\uA77D\uA77E'},
				{'base':'H', 'letters':'\u0048\u24BD\uFF28\u0124\u1E22\u1E26\u021E\u1E24\u1E28\u1E2A\u0126\u2C67\u2C75\uA78D'},
				{'base':'I', 'letters':'\u0049\u24BE\uFF29\u00CC\u00CD\u00CE\u0128\u012A\u012C\u0130\u00CF\u1E2E\u1EC8\u01CF\u0208\u020A\u1ECA\u012E\u1E2C\u0197'},
				{'base':'J', 'letters':'\u004A\u24BF\uFF2A\u0134\u0248'},
				{'base':'K', 'letters':'\u004B\u24C0\uFF2B\u1E30\u01E8\u1E32\u0136\u1E34\u0198\u2C69\uA740\uA742\uA744\uA7A2'},
				{'base':'L', 'letters':'\u004C\u24C1\uFF2C\u013F\u0139\u013D\u1E36\u1E38\u013B\u1E3C\u1E3A\u0141\u023D\u2C62\u2C60\uA748\uA746\uA780'},
				{'base':'LJ','letters':'\u01C7'},
				{'base':'Lj','letters':'\u01C8'},
				{'base':'M', 'letters':'\u004D\u24C2\uFF2D\u1E3E\u1E40\u1E42\u2C6E\u019C'},
				{'base':'N', 'letters':'\u004E\u24C3\uFF2E\u01F8\u0143\u00D1\u1E44\u0147\u1E46\u0145\u1E4A\u1E48\u0220\u019D\uA790\uA7A4'},
				{'base':'NJ','letters':'\u01CA'},
				{'base':'Nj','letters':'\u01CB'},
				{'base':'O', 'letters':'\u004F\u24C4\uFF2F\u00D2\u00D3\u00D4\u1ED2\u1ED0\u1ED6\u1ED4\u00D5\u1E4C\u022C\u1E4E\u014C\u1E50\u1E52\u014E\u022E\u0230\u00D6\u022A\u1ECE\u0150\u01D1\u020C\u020E\u01A0\u1EDC\u1EDA\u1EE0\u1EDE\u1EE2\u1ECC\u1ED8\u01EA\u01EC\u00D8\u01FE\u0186\u019F\uA74A\uA74C'},
				{'base':'OI','letters':'\u01A2'},
				{'base':'OO','letters':'\uA74E'},
				{'base':'OU','letters':'\u0222'},
				{'base':'OE','letters':'\u008C\u0152'},
				{'base':'oe','letters':'\u009C\u0153'},
				{'base':'P', 'letters':'\u0050\u24C5\uFF30\u1E54\u1E56\u01A4\u2C63\uA750\uA752\uA754'},
				{'base':'Q', 'letters':'\u0051\u24C6\uFF31\uA756\uA758\u024A'},
				{'base':'R', 'letters':'\u0052\u24C7\uFF32\u0154\u1E58\u0158\u0210\u0212\u1E5A\u1E5C\u0156\u1E5E\u024C\u2C64\uA75A\uA7A6\uA782'},
				{'base':'S', 'letters':'\u0053\u24C8\uFF33\u1E9E\u015A\u1E64\u015C\u1E60\u0160\u1E66\u1E62\u1E68\u0218\u015E\u2C7E\uA7A8\uA784'},
				{'base':'T', 'letters':'\u0054\u24C9\uFF34\u1E6A\u0164\u1E6C\u021A\u0162\u1E70\u1E6E\u0166\u01AC\u01AE\u023E\uA786'},
				{'base':'TZ','letters':'\uA728'},
				{'base':'U', 'letters':'\u0055\u24CA\uFF35\u00D9\u00DA\u00DB\u0168\u1E78\u016A\u1E7A\u016C\u00DC\u01DB\u01D7\u01D5\u01D9\u1EE6\u016E\u0170\u01D3\u0214\u0216\u01AF\u1EEA\u1EE8\u1EEE\u1EEC\u1EF0\u1EE4\u1E72\u0172\u1E76\u1E74\u0244'},
				{'base':'V', 'letters':'\u0056\u24CB\uFF36\u1E7C\u1E7E\u01B2\uA75E\u0245'},
				{'base':'VY','letters':'\uA760'},
				{'base':'W', 'letters':'\u0057\u24CC\uFF37\u1E80\u1E82\u0174\u1E86\u1E84\u1E88\u2C72'},
				{'base':'X', 'letters':'\u0058\u24CD\uFF38\u1E8A\u1E8C'},
				{'base':'Y', 'letters':'\u0059\u24CE\uFF39\u1EF2\u00DD\u0176\u1EF8\u0232\u1E8E\u0178\u1EF6\u1EF4\u01B3\u024E\u1EFE'},
				{'base':'Z', 'letters':'\u005A\u24CF\uFF3A\u0179\u1E90\u017B\u017D\u1E92\u1E94\u01B5\u0224\u2C7F\u2C6B\uA762'},
				{'base':'a', 'letters':'\u0061\u24D0\uFF41\u1E9A\u00E0\u00E1\u00E2\u1EA7\u1EA5\u1EAB\u1EA9\u00E3\u0101\u0103\u1EB1\u1EAF\u1EB5\u1EB3\u0227\u01E1\u00E4\u01DF\u1EA3\u00E5\u01FB\u01CE\u0201\u0203\u1EA1\u1EAD\u1EB7\u1E01\u0105\u2C65\u0250'},
				{'base':'aa','letters':'\uA733'},
				{'base':'ae','letters':'\u00E6\u01FD\u01E3'},
				{'base':'ao','letters':'\uA735'},
				{'base':'au','letters':'\uA737'},
				{'base':'av','letters':'\uA739\uA73B'},
				{'base':'ay','letters':'\uA73D'},
				{'base':'b', 'letters':'\u0062\u24D1\uFF42\u1E03\u1E05\u1E07\u0180\u0183\u0253'},
				{'base':'c', 'letters':'\u0063\u24D2\uFF43\u0107\u0109\u010B\u010D\u00E7\u1E09\u0188\u023C\uA73F\u2184'},
				{'base':'d', 'letters':'\u0064\u24D3\uFF44\u1E0B\u010F\u1E0D\u1E11\u1E13\u1E0F\u0111\u018C\u0256\u0257\uA77A'},
				{'base':'dz','letters':'\u01F3\u01C6'},
				{'base':'e', 'letters':'\u0065\u24D4\uFF45\u00E8\u00E9\u00EA\u1EC1\u1EBF\u1EC5\u1EC3\u1EBD\u0113\u1E15\u1E17\u0115\u0117\u00EB\u1EBB\u011B\u0205\u0207\u1EB9\u1EC7\u0229\u1E1D\u0119\u1E19\u1E1B\u0247\u025B\u01DD'},
				{'base':'f', 'letters':'\u0066\u24D5\uFF46\u1E1F\u0192\uA77C'},
				{'base':'g', 'letters':'\u0067\u24D6\uFF47\u01F5\u011D\u1E21\u011F\u0121\u01E7\u0123\u01E5\u0260\uA7A1\u1D79\uA77F'},
				{'base':'h', 'letters':'\u0068\u24D7\uFF48\u0125\u1E23\u1E27\u021F\u1E25\u1E29\u1E2B\u1E96\u0127\u2C68\u2C76\u0265'},
				{'base':'hv','letters':'\u0195'},
				{'base':'i', 'letters':'\u0069\u24D8\uFF49\u00EC\u00ED\u00EE\u0129\u012B\u012D\u00EF\u1E2F\u1EC9\u01D0\u0209\u020B\u1ECB\u012F\u1E2D\u0268\u0131'},
				{'base':'j', 'letters':'\u006A\u24D9\uFF4A\u0135\u01F0\u0249'},
				{'base':'k', 'letters':'\u006B\u24DA\uFF4B\u1E31\u01E9\u1E33\u0137\u1E35\u0199\u2C6A\uA741\uA743\uA745\uA7A3'},
				{'base':'l', 'letters':'\u006C\u24DB\uFF4C\u0140\u013A\u013E\u1E37\u1E39\u013C\u1E3D\u1E3B\u017F\u0142\u019A\u026B\u2C61\uA749\uA781\uA747'},
				{'base':'lj','letters':'\u01C9'},
				{'base':'m', 'letters':'\u006D\u24DC\uFF4D\u1E3F\u1E41\u1E43\u0271\u026F'},
				{'base':'n', 'letters':'\u006E\u24DD\uFF4E\u01F9\u0144\u00F1\u1E45\u0148\u1E47\u0146\u1E4B\u1E49\u019E\u0272\u0149\uA791\uA7A5'},
				{'base':'nj','letters':'\u01CC'},
				{'base':'o', 'letters':'\u006F\u24DE\uFF4F\u00F2\u00F3\u00F4\u1ED3\u1ED1\u1ED7\u1ED5\u00F5\u1E4D\u022D\u1E4F\u014D\u1E51\u1E53\u014F\u022F\u0231\u00F6\u022B\u1ECF\u0151\u01D2\u020D\u020F\u01A1\u1EDD\u1EDB\u1EE1\u1EDF\u1EE3\u1ECD\u1ED9\u01EB\u01ED\u00F8\u01FF\u0254\uA74B\uA74D\u0275'},
				{'base':'oi','letters':'\u01A3'},
				{'base':'ou','letters':'\u0223'},
				{'base':'oo','letters':'\uA74F'},
				{'base':'p','letters':'\u0070\u24DF\uFF50\u1E55\u1E57\u01A5\u1D7D\uA751\uA753\uA755'},
				{'base':'q','letters':'\u0071\u24E0\uFF51\u024B\uA757\uA759'},
				{'base':'r','letters':'\u0072\u24E1\uFF52\u0155\u1E59\u0159\u0211\u0213\u1E5B\u1E5D\u0157\u1E5F\u024D\u027D\uA75B\uA7A7\uA783'},
				{'base':'s','letters':'\u0073\u24E2\uFF53\u00DF\u015B\u1E65\u015D\u1E61\u0161\u1E67\u1E63\u1E69\u0219\u015F\u023F\uA7A9\uA785\u1E9B'},
				{'base':'t','letters':'\u0074\u24E3\uFF54\u1E6B\u1E97\u0165\u1E6D\u021B\u0163\u1E71\u1E6F\u0167\u01AD\u0288\u2C66\uA787'},
				{'base':'tz','letters':'\uA729'},
				{'base':'u','letters': '\u0075\u24E4\uFF55\u00F9\u00FA\u00FB\u0169\u1E79\u016B\u1E7B\u016D\u00FC\u01DC\u01D8\u01D6\u01DA\u1EE7\u016F\u0171\u01D4\u0215\u0217\u01B0\u1EEB\u1EE9\u1EEF\u1EED\u1EF1\u1EE5\u1E73\u0173\u1E77\u1E75\u0289'},
				{'base':'v','letters':'\u0076\u24E5\uFF56\u1E7D\u1E7F\u028B\uA75F\u028C'},
				{'base':'vy','letters':'\uA761'},
				{'base':'w','letters':'\u0077\u24E6\uFF57\u1E81\u1E83\u0175\u1E87\u1E85\u1E98\u1E89\u2C73'},
				{'base':'x','letters':'\u0078\u24E7\uFF58\u1E8B\u1E8D'},
				{'base':'y','letters':'\u0079\u24E8\uFF59\u1EF3\u00FD\u0177\u1EF9\u0233\u1E8F\u00FF\u1EF7\u1E99\u1EF5\u01B4\u024F\u1EFF'},
				{'base':'z','letters':'\u007A\u24E9\uFF5A\u017A\u1E91\u017C\u017E\u1E93\u1E95\u01B6\u0225\u0240\u2C6C\uA763'}
			];

			diacriticsMap = {};
			for (var i=0; i < defaultDiacriticsRemovalMap .length; i++){
				var letters = defaultDiacriticsRemovalMap [i].letters;
				for (var j=0; j < letters.length ; j++){
					diacriticsMap[letters[j]] = defaultDiacriticsRemovalMap [i].base;
				}
			}
			return diacriticsMap;
		}

		// Remove accent characters/diacritics from the string.
		function removeAccents (str) {
			diacriticsMap = getDiacriticsRemovalMap();
			return str.replace(/[^\u0000-\u007E]/g, function(a) {
				return diacriticsMap[a] || a;
			});
		}
		},
	};
	dashboard_scripts.init();
});