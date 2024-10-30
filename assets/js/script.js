/*frozr settings*/
(function($) {

	var file_frame, file_frame_two, product_featured_frame, geocoder, geo_not_loaded = false, location_search_time;
	var Norsani_Scripts = {
		init: function() {
			var self = this;
			
			/*hide some elements*/
			$(".front_inputs_wrap, .item_special_comments_field textarea, .order_ppl_num_field, .order_car_info_field, .lepop_rest_address, .closed_order_notice, .open_closed_order_notice, .delivery_notice").hide();
			
			$( document.body )
			/*General*/
			.on("change", "input", self.update_inputs)		
			.on("click", ".ui-checkbox-off", self.off_checkbox_input)		
			.on("click", ".ui-checkbox-on", self.on_checkbox_input)		
			.on("click", ".f_go_back", self.page_go_back)		
			.on("click", ".add-field", self.add_field)
			.on("click",".remove-field", self.remove_field)
			/*User Location*/
			.on("frozr_location_form_active", self.get_loc_input_autocomplete)
			/*Registration Page*/
			.on("change", ".rest_shifts", self.show_hide_rest_shift_settings)
			.on("change", ".rest_open", self.show_hide_rest_opening_settings)
			/*Vendor Page*/
			.on("submit", "#frozr-form-contact-seller", self.contact_vendor)
			.on("submit", ".rest_rating_form", self.save_vendor_rating)
			.on("submit", "form.rest_rating_login", self.rating_login)
			/*Checkout*/
			.on("change", ".frozr_pretime_form input,.frozr_pretime_form select", self.checkout_order_time_changed);
		},
		checkout_order_time_changed: function(e) {
			var self = $(this);
			var vendor = self.attr('data-vendor');
			$('.frozr_order_time_changed_'+vendor).val(1);
		},
		update_inputs: function(e) {
			var input = $(this)
			if(!input[0].files) {
			$(this).attr('value', $(this).val());
			}
		},
		off_checkbox_input: function(e) {
			var wrapper = $(this).parent();
			$('input[type="checkbox"]', wrapper).val("1");
		},
		on_checkbox_input: function(e) {
			var wrapper = $(this).parent();
			$('input[type="checkbox"]', wrapper).val("0");
		},
		page_go_back: function(e) {
			window.history.back();
		},
		get_loc_input_autocomplete: function(e,inputfield) {
			/*add auto complete for vendor address*/
			var autocomplete,
				input = document.getElementById(inputfield),
				options = {
				types: ['address'],
				componentRestrictions: {country: norsani_scripts_params.geo_default_country},
				};

			autocomplete = new google.maps.places.Autocomplete(input, options);
			autocomplete.addListener('place_changed', function() {
				var place_obj = autocomplete.getPlace();
				var latlng = place_obj.geometry.location;
				Norsani_Scripts.location_clicked(latlng,place_obj.formatted_address);
			});
		},
		location_clicked: function(user_location,location_label) {
			var resultPath		= '',
				sellerspos		= norsani_scripts_params.frozr_sellers_locs,
				sellersArray	= [],
				related_item	= '',
				item_id			= 0,
				data			= {};
			
			$.ajax({
				beforeSend: function() {$( document.body ).trigger('frozr_body_loading');},
				url: norsani_scripts_params.ajax_url,
				data: {action:'frozr_get_sellers_locs',security:norsani_scripts_params.frozr_set_user_loc_nonce},
				type: 'POST',
				success: function( response ) {
					sellerspos = response;
				}
			}).always(function(response) {
			/*Get delivery sellers*/
			jQuery.each(sellerspos, function (index, val) {
				var deliveryCoords = [];
				jQuery.each(val, function (indexs, vals) {
					if (vals[0] != null && vals[1] != null) {
						deliveryCoords.push(new google.maps.LatLng(parseFloat(vals[0]), parseFloat(vals[1])));
					}
				});
				if (deliveryCoords) {
					var sellerspoly = new google.maps.Polygon({paths: deliveryCoords});
					resultPath = google.maps.geometry.poly.containsLocation(user_location, sellerspoly) ? index :null;
					if (resultPath != null) {
						sellersArray.push(resultPath);
					}
				}
			});
			if($('.cart.ajax_lazy_submit').length > 0) {
				item_id = $('.cart.ajax_lazy_submit').attr('data-product_id');
			}

			$( document.body ).trigger( 'lylocbeforesave', [user_location,sellerspos,location_label,sellersArray,data] );

			data.action		= 'frozr_user_loc_cookie';
			data.sellers	= sellersArray;
			data.ritem		= item_id;
			data.userloc	= location_label;
			data.locgeo		= user_location.lat()+','+user_location.lng();
			data.security	= norsani_scripts_params.frozr_set_user_loc_nonce;

			$.ajax({
				complete: function() {$( document.body ).trigger('frozr_body_loading_complete');},
				url: norsani_scripts_params.ajax_url,
				data: data,
				type: 'POST',
				success: function( response ) {
					window.localStorage.setItem("frozr_del_sellers", JSON.stringify(sellersArray));
					window.localStorage.setItem("frozr_user_location", response.usr_loc);
					window.localStorage.setItem("frozr_user_location_unslashed", response.usr_loc_un);
					$( document.body ).trigger( 'lylocaftersave', [user_location,sellerspos,location_label,sellersArray,response] )
				}, error: function( erre ) {
					console.log(erre);
				}
			});
			});
		},
		contact_vendor: function(e) {
			e.preventDefault();
			var self		= $(this),
				data        = {};
				
			self.css('opacity', '0.6');

			data				= self.serializeJSON();
			data.action			= 'frozr_contact_seller';
			data.security		= norsani_scripts_params.frozr_contact_seller;

			$.ajax({
				beforeSend: function() {$( document.body ).trigger('frozr_body_loading');},
				complete: function() {$( document.body ).trigger('frozr_body_loading_complete');},
				url: norsani_scripts_params.ajax_url,
				data: data,
				type: 'POST',
				success: function( response ) {
					$( document.body ).trigger('norsani_display_msg',[response.message]);
					self.css('opacity', 'initial');
				}, error: function(erre) {
					console.log(erre);
				}
			});
		},
		rating_login: function(e) {
			e.preventDefault();
			var wrapper	= $( '.rest_rating_form_wrapper' ),
				self	= $(this),
				data	= {};

			wrapper.css('opacity', '0.6');

			data			= self.serializeJSON();
			data.action		= 'frozr_rating_login';
			data.security	= norsani_scripts_params.rating_user_login;

			$.ajax({
				beforeSend: function() {$( document.body ).trigger('frozr_body_loading');},
				complete: function() {$( document.body ).trigger('frozr_body_loading_complete');},
				url: norsani_scripts_params.ajax_url,
				data: data,
				type: 'POST',
				success: function( response ) {
					if (response.message) {
					$( document.body ).trigger('norsani_display_msg',[response.message]);
					} else {
					self.hide();
					$( 'form.rest_rating_form' ).show();
					}
					wrapper.css('opacity', 'initial');
				}, error: function(erre) {
					console.log(erre);
				}
			});
		},
		save_vendor_rating: function(e) {
			e.preventDefault();
			var wrapper		= $('.rest_rating_form_wrapper'),
				self		= $(this),
				restid		= $('.rest_rating_submit', wrapper).attr('data-restid'),
				orderid		= $('.rest_rating_submit', wrapper).attr('data-orderid'),
				data		= {};

			wrapper.css('opacity', '0.6');

			data			= self.serializeJSON();
			data.action		= 'frozr_save_rest_rating';
			data.order_id	= orderid;
			data.seller_id	= restid;
			data.security	= norsani_scripts_params.add_rest_review;

			$.ajax({
				beforeSend: function() {$( document.body ).trigger('frozr_body_loading');},
				complete: function() {$( document.body ).trigger('frozr_body_loading_complete');},
				url: norsani_scripts_params.ajax_url,
				data: data,
				type: 'POST',
				success: function( response ) {
					$( document.body ).trigger('norsani_display_msg',[response.message]);
					window.history.replaceState(null, null, window.location.pathname);
					wrapper.remove();
					if (response.rating) {
					$('.frozr_vendor_rating_info').html(response.rating);
					}
					wrapper.css('opacity', 'initial');
				}
			});
		},
		show_hide_rest_shift_settings: function(e) {
			var wrapper = $(this).parents('.frozr_reg_opcl_settings,.opcl_settings');
			$('.rest_two', wrapper).removeClass('frozr_hide').toggle(this.checked);
		},
		show_hide_rest_opening_settings: function(e) {
			var wrapper = $(this).parent().parent().parent();
			$('.rest_time_inputs, .rest_shifts_cont', wrapper).removeClass('frozr_hide').toggle(this.checked);
		},
		add_field: function(e) {
			var wrapper = $(this).parents('.multi-field-wrapper');
			var wrapper_child = $('.multi-fields', wrapper);
			$('.multi-field:last-child', wrapper_child).clone(true).appendTo(wrapper_child).find('input:not(.item_option_attribute)').val('').focus();
		},
		remove_field: function(e) {
			var wrapper = $(this).parents('.multi-fields');
			if ($('.multi-field', wrapper).length > 1) {
				$(this).parent('.multi-field').remove();
			} else {
				$('.multi-field:last-child', wrapper).clone(true).appendTo(wrapper).find('input').val('').focus();
				$(this).parent('.multi-field').remove();
			}
		}
	};
	Norsani_Scripts.init();

})(jQuery);