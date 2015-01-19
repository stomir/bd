jQuery(document).ready(function(){
	jQuery("#use_page_select").change(function(){
		jQuery(".use_page_form_part").hide();
		jQuery("#"+jQuery(this).val()+"_part").show();
	});
	jQuery("#use_page_select").change();
	jQuery("#homework_time").datepicker({dateFormat:'yy-mm-dd 23:59'});
	jQuery("#test_date").datepicker({dateFormat:'yy-mm-dd'});
});
