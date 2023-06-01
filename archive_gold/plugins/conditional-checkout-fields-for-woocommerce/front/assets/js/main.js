jQuery(document).ready(function() {
     // var thiss=jQuery('.woocommerce-additional-fields__field-wrapper');

     
  setInterval(function(){
    //don't use settimeout instead of setInterval
      jQuery('.blockOverlay').remove();
     },2000);
     setTimeout(function(){ 
         var thiss=jQuery('.woocommerce-additional-fields__field-wrapper');
         var o_element= thiss.find('.fme-ccfw_class_main')[0];
         o_element_id=jQuery(o_element).attr('id');
         if (o_element_id=='order_comments' && fme_ccfw_php_front_vars.additional_field_exist == 1){
          jQuery('#order_comments').after('<h2 style="margin-top:5%;">'+ fme_ccfw_php_front_vars.additionalLabelvalue +'</h2>');
         } else if(fme_ccfw_php_front_vars.additional_field_exist == 1 ) {
          jQuery('.woocommerce-additional-fields__field-wrapper').before('<h2 style="margin-top:5%;">'+ fme_ccfw_php_front_vars.additionalLabelvalue +'</h2>');
          }
    }, 100);

	jQuery("body").on('keypress','.fme-ccfw_class_main',function(e){
     
      if (e.which === 32 && !this.value.length) {
           e.preventDefault();
       }
        
 });
	
});