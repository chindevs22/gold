
jQuery(document).ready(function() {
	jQuery('.nav-tab').each(function() {
		var tab = jQuery(this).text();
		if ('Conditional Checkout Fields' == tab) {
			jQuery(this).addClass('nav-tab-active');
			jQuery('.woocommerce-layout__header-heading').html('Conditional Checkout Fields Settings');
		}
	});

	jQuery('body').on('keypress' ,'input[name="fme_ccfw_field_fieldname"]',function( e ) { 
		if(!/[0-9a-zA-Z_-]/.test(String.fromCharCode(e.which)))
			return false;
	});

	jQuery('#fme-cpffw_save_fieldsettings').on('click', function(){
		jQuery('.fme_cpffw_checkoutfields_data tr').each(function( index, val ){


			var additionalfieldlabel = jQuery('#additionalfieldlabel').val();
			var str = jQuery(this).attr('id');
			var id = str.replace("fme_row", "");
			var sortorder = index+1;
			jQuery(this).attr('sort-order',sortorder);
			var getsortorder = jQuery(this).attr('sort-order');
			var type = jQuery(this).attr('field_type');
			var ajaxurl = fme_ccfw_php_vars.admin_url;
			jQuery.ajax({
				url: ajaxurl,
				type: 'post',
				data: {
					action: 'fme_ccfw_fieldsortorder',
					id:id,
					getsortorder:getsortorder,
					type:type,
					additionalfieldlabel:additionalfieldlabel,
					security:fme_ccfw_php_vars.admin_ajax_nonce
				},
				success: function (data) {
					if (data=='success') {
						jQuery('#fme_success_alert').show();
						jQuery('#fme_success_alert').delay(3000).fadeOut('slow');
						jQuery("html, body").animate({ scrollTop: 0 }, "1");              
          				jQuery('html, body').stop(true, true);
						return false;	
					}
					window.onbeforeunload = null;
				}   
			});		
		});

	});


	jQuery('body').on('click','.fme-ccfw-remove-condtions-row', function(){
		jQuery(this).parent().parent().parent().remove();
	});



	jQuery('body').on('click', '.fme-ccfw-add-condtions-ccfw', function(){

		var ajaxurl = fme_ccfw_php_vars.admin_url;
		var thiss = jQuery(this);
		var url      = window.location.href;  
		var type = jQuery(this).attr('data-section');
		jQuery.ajax({
			url: ajaxurl,
			type: 'post',
			data: {
				action: 'fme_ccfw_field_add_condtion',
				type: type,
				security:fme_ccfw_php_vars.admin_ajax_nonce
			},
			success: function (data) {
				jQuery(thiss).parent().parent().parent().before(data);
				window.onbeforeunload = null;
			}   
		});		

	});

	jQuery('body').on('click', '.fme-ccfw-remove-group' , function(){
		jQuery(this).parent().parent().parent().parent().parent().parent().parent().remove();
	});
	

	jQuery(document).ready(function(){
		jQuery("body").on("click", '#fme-ccfw-add_group_cond', function () {
			var ajaxurl = fme_ccfw_php_vars.admin_url;
			var type = "<?php echo esc_attr($type) ?>";
			var url      = window.location.href;  
			var type = '';

			 if(url.includes('section=')) {
			 type = /section=(\w+)/.exec(url)[1];
			} else {
			 type = 'billing';
			}
			jQuery.ajax({
				url: ajaxurl,
				type: 'post',
				data: {
					action: 'fme_ccfw_field_condtion',
					type:type,
					security:fme_ccfw_php_vars.admin_ajax_nonce
				},
				success: function (data) {
					jQuery('#fme_condition_fields_groupdata').after(data);
					window.onbeforeunload = null;
				}   
			});		
		});

	});

	

	jQuery(function () {
		jQuery("body").on("click", '#fme-ccfw-btn-Add', function () {
			var div = jQuery("<tr class=fme-ccfw-optionbtn />");
			div.html(GetDynamicTextBox(""));
			jQuery("#fme-ccfw-optionbtn").after(div);
		});
		jQuery("body").on("click", ".fme-ccfw-remove-option", function () {
			jQuery(this).closest("tr").remove();
		});
	});
	function GetDynamicTextBox(value) {
		return '<td><input name="fme_ccfw_option_name[]" id="fme_ccfw_foption_name" placeholder="Enter option name" type="text" value = "' + value + '" class="form-control" /></td>' + '<td><input name= "fme_ccfw_option_value[]" id="fme_ccfw_foption_value"  placeholder="Enter option value" type="text" value = "' + value + '" class="form-control" /></td>' + '<td><input name= "fme_ccfw_option_price[]" id="fme_ccfw_foption_price" placeholder="Enter option price" type="text" value = "' + value + '" class="form-control" /></td>' + '<td><button type="button" class="btn btn-danger fme-ccfw-remove-option">Remove</button></td>'
	}

	var printCounter = 0;
	var table = jQuery('#fmeccfw_checkout_form_field').DataTable( {
		dom: 'Bfrtip',
		
		buttons: [
		{extend: 'excel', title: '', exportOptions: { columns: [0, 1, 2, 3, 4, 5]}},
		{extend: 'pdf', title: '' , exportOptions: { columns: [0, 1, 2, 3, 4, 5]}},
		{extend: 'csv', title: '' , exportOptions: { columns: [0, 1, 2, 3, 4, 5]}},
		{extend: 'print', title: '', exportOptions: { columns: [0, 1, 2, 3, 4, 5]}},
		],
		"scrollY":        500,
		"scrollCollapse": true,
		"paging":         false,
		rowReorder: true,
		columnDefs: [
		{ orderable: true, className: 'reorder', targets: 0 },
		{ orderable: false, targets: '_all' }
		]
	});

	jQuery('body').on('change', '#fme_ccfw_field_specfic_pc', function(){
		jQuery('.fme-ccfw-products').select2();
		jQuery('.fme-ccfw-category').select2();	
		if (jQuery(this).prop('checked')) {
			jQuery('#fme-ccfw-selectpc-val').show();
			jQuery('#fme-ccfw-selectpc-val').css('display','block');
			if (jQuery('.fme_ccfw_selectpc').val()=='product') {
				jQuery('#product-addons').show();
				jQuery('.select2').css('width','100%');
			} else if (jQuery('.fme_ccfw_selectpc').val()=='category') {
				jQuery('#category-addons').show();
				jQuery('.select2').css('width','100%');
			} else {
				jQuery('#product-addons').hide();
		  		jQuery('#category-addons').hide();
			}
		} else {
			jQuery('#fme-ccfw-selectpc-val').hide();
			jQuery('#fme-ccfw-selectpc-val').css('display','none');
			jQuery('#product-addons').hide();
			jQuery('#category-addons').hide();
		}

	});
		jQuery('.fme-ccfw-user-roles').select2({
			'placeholder' : 'Leave empty to diplay field for all users and ignore radio selection'
		});
	jQuery('body').on('change', '#fme_field_specfic_userrole', function(){
		// jQuery('.fme-ccfw-user-roles').select2();
		// if (jQuery(this).prop('checked')) {
		// 	jQuery('#fme-ccfw-user-role').show();
		// 	jQuery('.select2').css('width','100%');
		// } else {
		// 	jQuery('#fme-ccfw-user-role').hide();
		// 	jQuery('.select2').css('width','100%');
		// }
	});

	jQuery('body').on('change','.fme_ccfw_selectpc',function(){
		"use strict";					 
		var fme_GetVal = jQuery(this).val();

		if(fme_GetVal=='product') {

		  jQuery('#product-addons').show();
		  jQuery('#category-addons').hide();
		  jQuery('.select2').css('width','100%');
		}
		if(fme_GetVal=='category') {

		  jQuery('#product-addons').hide();
		  jQuery('#category-addons').show();
		  jQuery('.select2').css('width','100%');
		}
		if(fme_GetVal=='') {

		  jQuery('#product-addons').hide();
		  jQuery('#category-addons').hide();
		}
		
	});
});

function fme_save_form_field(type) {

	var fme_form_action = jQuery('#fme-ccfw-savefielddata').attr('data-attr');
	var fme_cffw_field_type = jQuery('#fme_cffw_field_type').val();
	var fme_ccfw_field_fieldname = jQuery('#fme_ccfw_field_fieldname').val();
	var fme_ccfw_field_label = jQuery('#fme_ccfw_field_label').val();
	var fme_ccfw_field_ed = jQuery('#fme_ccfw_field_ed').val();
	var fme_ccfw_field_placeholder = jQuery('#fme_ccfw_field_placeholder').val();
	var fme_ccfw_field_class = jQuery('#fme_ccfw_field_class').val();
	var fme_ccfw_field_price = jQuery('#fme_ccfw_field_price').val();
	var fme_ccfw_field_size_type = jQuery('#fme_ccfw_field_sizes').val();
	var fme_ccfw_field_sizes_val = jQuery('#fme_ccfw_field_sizes_val').val();

	var fme_ccfw_uploadsize_array = [];
	fme_ccfw_uploadsize_array.push(fme_ccfw_field_sizes_val);	
	fme_ccfw_uploadsize_array.push(fme_ccfw_field_size_type);	


	var fme_ccfw_field_required;
	if (jQuery('#fme_ccfw_field_required').prop('checked')) {
		fme_ccfw_field_required = jQuery('#fme_ccfw_field_required').val();
	} else {
		fme_ccfw_field_required = '0';
	}
	var fme_ccfw_field_taxable;
	if (jQuery('#fme_ccfw_field_taxable').prop('checked')) {
		fme_ccfw_field_taxable = jQuery('#fme_ccfw_field_taxable').val();
	} else {
		fme_ccfw_field_taxable = '0';
	}
	//haseeb changed
	var fme_ccfw_min_date='0';
	if (jQuery('#fme_ccfw_min_date').prop('checked')) {
		fme_ccfw_min_date = jQuery('#fme_ccfw_min_date').val();
	} else {
		fme_ccfw_min_date = '0';
	}

	var fme_ccfw_field_specfic_pc;
	if (jQuery('#fme_ccfw_field_specfic_pc').prop('checked')) {
		fme_ccfw_field_specfic_pc = jQuery('#fme_ccfw_field_specfic_pc').val();
	} else {
		fme_ccfw_field_specfic_pc = 'off';
	}	

	var fme_ccfw_fselectpc_type = jQuery('#fme-ccfw-fselectpc-type').val();
	var fme_ccfw_fspc;
	if(fme_ccfw_fselectpc_type=='product') {
		fme_ccfw_fspc = jQuery('#fme-ccfw-fproducts').val();
	} else if(fme_ccfw_fselectpc_type=='category') {
		fme_ccfw_fspc = jQuery('#fme-ccfw-fcategory').val();
	} else {
		fme_ccfw_fspc = [];
	}

	var fme_field_specfic_userrole;
	if (jQuery('#fme_field_specfic_userrole').prop('checked')) {
		fme_field_specfic_userrole = jQuery('#fme_field_specfic_userrole').val();
	} else {
		fme_field_specfic_userrole = 'off';
	}	
	var fme_field_specific_userrole_radio=jQuery('#fme_field_specfic_userrole_radio:checked').val();
	
	var fme_ccfw_user_froles_val = jQuery('#fme-ccfw-user-froles').val();
	
	var main_array = [];
	jQuery('.fme-ccfw-conditional_table_set').each(function(){
		var child_array = [];
		var dep = jQuery(this).find('select[name="fmeshowif[]"]');
		jQuery(dep).each(function(index , val){

			var subchild_array = new Array();
			var fme_ccfw_faction_action = jQuery(this).val();
			var fme_ccfw_fvalueof_val = jQuery(this).parent().parent().parent().find('select[name="fmecfield[]"]').val();
			var fme_ccfw_fcondition_con = jQuery(this).parent().parent().parent().find('select[name="fmeccondition[]"]').val();
			var fme_ccfw_fcond_val = jQuery(this).parent().parent().parent().find('input[name="fmeccondition_value[]"]').val();

			if (fme_ccfw_faction_action !='') {
				
				subchild_array.push(fme_ccfw_faction_action);
				subchild_array.push(fme_ccfw_fvalueof_val);
				subchild_array.push(fme_ccfw_fcondition_con);
				subchild_array.push(fme_ccfw_fcond_val);
				subchild_array.push('F');
				child_array.push(subchild_array);

			}
			
		});
		main_array.push(child_array);

	});

	//option field data
	var fme_ccfw_foption_value = jQuery("input[id='fme_ccfw_foption_value']").map(function(){return jQuery(this).val();}).get();
	var fme_ccfw_foption_price = jQuery("input[id='fme_ccfw_foption_price']").map(function(){return jQuery(this).val();}).get();
	var fme_ccfw_foption_name = jQuery("input[id='fme_ccfw_foption_name']").map(function(){return jQuery(this).val();}).get();

	var fme_ccfw_field_extensions = jQuery('#fme_ccfw_field_extension').val();
	var fme_ccfw_field_heading_type = jQuery('#fme-ccfw-heading-value').val();

	if (fme_ccfw_field_fieldname =='') {
		jQuery('.fmeerrorname').show();
		jQuery('.fmeerrorname').delay(2000).fadeOut('slow');
	} if(fme_ccfw_field_label=='') {
		jQuery('.fmeerrorlabel').show();
		jQuery('.fmeerrorlabel').delay(2000).fadeOut('slow');
	} else {
		jQuery("#fme_ccfw_field_fieldname").css('border','1px solid #8c8f94');
		var ajaxurl = fme_ccfw_php_vars.admin_url;
		jQuery.ajax({
			url: ajaxurl,
			type: 'post',
			data: {
				action: 'fme_ccfw_save_fielddata',
				type:type,
				fme_cffw_field_type:fme_cffw_field_type,
				fme_ccfw_field_fieldname:fme_ccfw_field_fieldname,
				fme_ccfw_field_label:fme_ccfw_field_label,
				fme_ccfw_field_ed:fme_ccfw_field_ed,
				fme_ccfw_field_placeholder:fme_ccfw_field_placeholder,
				fme_ccfw_field_class:fme_ccfw_field_class,
				fme_ccfw_field_price:fme_ccfw_field_price,
				fme_ccfw_field_required:fme_ccfw_field_required,
				fme_ccfw_field_taxable:fme_ccfw_field_taxable,
				fme_ccfw_min_date:fme_ccfw_min_date,
				fme_ccfw_field_specfic_pc:fme_ccfw_field_specfic_pc,
				fme_ccfw_fselectpc_type:fme_ccfw_fselectpc_type,
				fme_ccfw_fspc:fme_ccfw_fspc,
				fme_condition_arr: main_array,
				fme_field_specfic_userrole:fme_field_specfic_userrole,
				fme_field_specific_userrole:fme_field_specific_userrole_radio,
				fme_ccfw_user_froles_val:fme_ccfw_user_froles_val,
				fme_ccfw_foption_name:fme_ccfw_foption_name,
				fme_ccfw_foption_value:fme_ccfw_foption_value,
				fme_ccfw_foption_price:fme_ccfw_foption_price,
				fme_form_action:fme_form_action,
				fme_ccfw_field_extensions:fme_ccfw_field_extensions,
				fme_ccfw_field_heading_type:fme_ccfw_field_heading_type,
				fme_ccfw_uploadsize_array:fme_ccfw_uploadsize_array,
				security:fme_ccfw_php_vars.admin_ajax_nonce

			},
			success: function (data) {
				window.onbeforeunload = null;
				jQuery('#fme_success_alert_save').show();
				jQuery('#fme_success_alert_save').delay(2000).fadeOut('slow');
				setTimeout(function(){ location.reload(); }, 3000);
				
			}   
		});
	}
}

function fmeccfwOpenEditFieldForm(id, type) {
	jQuery('#fme-ccfw-savefielddata').attr('data-attr',id);
	var ajaxurl = fme_ccfw_php_vars.admin_url;
	jQuery.ajax({
		url: ajaxurl,
		type: 'post',
		data: {
			action: 'fme_ccfw_store_fielddata',
			id:id,
			type:type,
			security:fme_ccfw_php_vars.admin_ajax_nonce
		},
		success: function (data) {

			jQuery('.modal-body').html(data);
			window.onbeforeunload = null;
		}   
	});
}

function fmeccfw_Delete_fields(id, type) {

	var x = confirm("Are you sure you want to delete this field?");
	if (x == true) {
		var ajaxurl = fme_ccfw_php_vars.admin_url;
		jQuery.ajax({
			url: ajaxurl,
			type: 'post',
			data: {
				action: 'fme_ccfw_delete_fielddata',
				id:id,
				type:type,
				security:fme_ccfw_php_vars.admin_ajax_nonce
			},
			success: function (data) {
				jQuery('#fme_row'+id).slideToggle('slow');
				var fieldname = jQuery('#fme_row'+id).find("td:eq(1)").text();
				alert(fieldname + ' is deleted Successfully!');
				window.onbeforeunload = null;
				location.reload();
			}   
		});
	} else {
		return false;
	}
}


function fmec_ffw_fieldListner() {
	fme_cffw_option_val = jQuery('#fme_cffw_field_type').val();
	if (fme_cffw_option_val=='select' || fme_cffw_option_val=='multiselect' || fme_cffw_option_val=='radio') {
		jQuery('#fme-ccfw-optionbtn').show();
		jQuery('#fme-ccfw-optionbtn').css('visibility','inherit');
		jQuery('.fme-ccfw-option-fields').show();
		jQuery('.fme-ccfw-pricefieldtd').css('visibility','hidden');
		jQuery('#fme_ccfw_field_extensions').hide();
		jQuery('#fme_ccfw_field_file_size').hide();
		jQuery('#fme_ccfw_field_heading_type').hide();
		jQuery('#fme_ccfw_field_extension').val('');
		jQuery('.fme-ccfw-requireds').show();
	}

	if(fme_cffw_option_val=='text' || fme_cffw_option_val=='textarea' || fme_cffw_option_val=='number' || fme_cffw_option_val=='checkbox' || fme_cffw_option_val=='date' || fme_cffw_option_val=='time' || fme_cffw_option_val=='color' || fme_cffw_option_val=='file') {
		jQuery('#fme-ccfw-optionbtn').hide();
		jQuery('.fme-ccfw-option-fields').hide();
		jQuery('.fme-ccfw-pricefieldtd').css('visibility','inherit');
		jQuery('#fme_ccfw_field_extensions').hide();
		jQuery('#fme_ccfw_field_file_size').hide();
		jQuery('#fme_ccfw_field_heading_type').hide();
		jQuery('#fme_ccfw_field_extension').val('');
		jQuery('.fme-ccfw-requireds').show();
	} 

	if( fme_cffw_option_val=='password' || fme_cffw_option_val=='tel') {
		jQuery('#fme-ccfw-optionbtn').hide();
		jQuery('.fme-ccfw-option-fields').hide();
		jQuery('.fme-ccfw-pricefieldtd').css('visibility','hidden');
		jQuery('#fme_ccfw_field_extensions').hide();
		jQuery('#fme_ccfw_field_file_size').hide();
		jQuery('#fme_ccfw_field_heading_type').hide();
		jQuery('#fme_ccfw_field_extension').val('');
		jQuery('.fme-ccfw-requireds').show();
	}

	if(fme_cffw_option_val=='file') {
		jQuery('#fme_ccfw_field_extensions').show();
		jQuery('#fme-ccfw-optionbtn').hide();
		jQuery('.fme-ccfw-option-fields').hide();
		jQuery('#fme_ccfw_field_heading_type').hide();
		jQuery('.fme-ccfw-requireds').show();
		jQuery('#fme_ccfw_field_file_size').show();
	}

	if(fme_cffw_option_val=='paragraph' || fme_cffw_option_val == 'heading') {
		jQuery('#fme_ccfw_field_extensions').hide();
		jQuery('#fme_ccfw_field_file_size').hide();
		jQuery('#fme-ccfw-optionbtn').hide();
		jQuery('.fme-ccfw-option-fields').hide();
		jQuery('#fme_ccfw_field_heading_type').show();
		jQuery('#fme_ccfw_field_extension').val('');
		jQuery('.fme-ccfw-requireds').hide();
		jQuery('.fme-ccfw-pricefieldtd').css('visibility','hidden');
	}

	//haseeb changed to hide show min date chkbox
	if(fme_cffw_option_val=='date') {
		
		jQuery('.fme_ccfw_min_date_class').show();
		jQuery('.fme-ccfw-min-date_chk_box').css("display","inline");
	} else {
		jQuery('.fme_ccfw_min_date_class').hide();
	}

	if (fme_cffw_option_val=='checkbox'){

		thissss=jQuery("label:contains('Placeholder')");
		thissss.text('Value when checked');
		thissss.next('input').attr('placeholder','Yes or No');
	} else {
		thissss=jQuery("label:contains('Value when checked')");
		thissss.text('Placeholder');
		thissss.next('input').attr('placeholder','Enter Placeholder...');
	}
}
