/**
 *  @summary Function to access GET request parameter value.
 *  @since 1.0
 *  @param type $var QueryString parameter name
 *  @return type $string Parameter value
 */
function em_get(name) {
    if (name = (new RegExp('[?&]' + encodeURIComponent(name) + '=([^&]*)')).exec(location.search))
        return decodeURIComponent(name[1]);
}

/**
 * @summary Custom Event dispatcher to fire any type of event on any HTML Element.
 *
 * @since 1.0
 *
 *
 * @param type $var Event Name.
 * @param type $var Element Id.
 */
function em_event_dispatcher(event_name,id){
    
 // Create the event.
var event = document.createEvent('Event');

// Define that the event name is 'build'.
event.initEvent('change', true, true);
var elem= document.getElementById(id);
// Listen for the event.
elem.addEventListener('change', function (e) {
  // e.target matches elem
}, false);

// target can be any Element or other EventTarget.
elem.dispatchEvent(event);

 


}

function CustomEvent ( event, params ) {
   params = params || { bubbles: false, cancelable: false, detail: undefined };
   var evt = document.createEvent( 'CustomEvent' );
   evt.initCustomEvent( event, params.bubbles, params.cancelable, params.detail );
   return evt;
  }

/*
 * @summary Remove element from Array
 * @since 1.0
 * 
 * @param  Array collection
 * @param  mix element
 * @return Array collections
 */
function em_remove_from_array(collection,element){ 

    var index= collection.indexOf(element);
    
    if(index>=0){

            collection.splice(index,1);
    }
 
    return collection;
}


function em_get_url_param(sParam,def)
{
    var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split('&');
    for (var i = 0; i < sURLVariables.length; i++) 
    {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == sParam) 
        {
            return sParameterName[1];
        }
    }
    return def;
}