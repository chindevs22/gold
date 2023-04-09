/* Function invoked when the client javascript library is loaded */
function em_gcal_handle() {
  var apiKey = em_local_gcal_objects.g_api_key;  
  gapi.client.setApiKey(apiKey);
  window.setTimeout(em_gcal_check_auth,100);
}

/* API function to check whether the app is authorized. */
function em_gcal_check_auth() {
  var clientId = em_local_gcal_objects.gc_id;
  var scopes = 'https://www.googleapis.com/auth/calendar';
  gapi.auth.authorize({client_id: clientId, scope: scopes, immediate: true}, 
                      em_gcal_handle_auth_result);
                      
}

/* Invoked by different functions to handle the result of authentication checks.*/
var authData;
function em_gcal_handle_auth_result(authResult) {
    
   
   
    if (authResult && !authResult.error) {
          jQuery("[id=authorize-button]").css('display','none');
          jQuery("[id=addToCalendar]").css('display','block');
          //load the calendar client library
          gapi.client.load('calendar', 'v3', function(){ 
           
          });
    } else {
            jQuery("[id=authorize-button]").css('display','block');
        }
}


/* Event handler that deals with clicking on the Authorize button.*/
function em_gcal_handle_auth_click(event) {
    var clientId = em_local_gcal_objects.gc_id;
    var scopes = 'https://www.googleapis.com/auth/calendar';
    gapi.auth.authorize({client_id: clientId, scope: scopes, immediate: false}, 
                        em_gcal_handle_auth_result);
    return false;
}

/* End of PART 1 - Authentication Process. */

/* Start of PART 2 - dealing with events from the user interface and 
performing API calls. */
/*
   window.onload = function(){
var addButton = document.getElementById('addToCalendar');
addButton.onclick = function(){
  var userChoices = em_gcal_get_user_input();
  if (userChoices) 
    em_gcal_create_event(userChoices);
}



};*/

function em_add_to_calendar(event_id)
{
    var s_date = document.querySelector("#s_date_" + event_id).value;
    var e_date = document.querySelector("#e_date_" + event_id).value;
    var eventDesc = document.querySelector("#event_" + event_id).value;
    var userChoices = em_gcal_get_user_input(s_date,e_date,eventDesc);
    if (userChoices) {
        userChoices.event_id= event_id;
        em_gcal_create_event(userChoices);
    }
     
}

function em_gcal_get_user_input(s_date,e_date,eventDesc){
  // check input values, they should not be empty
  if (s_date=="" || e_date=="" || eventDesc==""){
    alert("All your input fields should have a meaningful value.");
    return
  }
  else return {'s_date': s_date, 'e_date': e_date,
               'eventTitle': eventDesc}
}


// Make an API call to create an event.  Give feedback to user.
function em_gcal_create_event(eventData) {
   
  // First create resource that will be send to server.
    var resource = {
        "summary": eventData.eventTitle,
        "start": {
          "dateTime": new Date(eventData.s_date).toISOString()
        },
        "end": {
          "dateTime": new Date(eventData.e_date).toISOString()
          }
        };
    // create the request
    var request = gapi.client.calendar.events.insert({
      'calendarId': 'primary',
      'resource': resource
    });
  
    // execute the request and do something with response
    request.execute(function(resp) {
      if (typeof resp.htmlLink !== 'undefined') {
          jQuery(".GCal-confirm-message").html("The Event is now added to your Google Calendar. Please click <a href='"+resp.htmlLink+"' target=_blank>here</a>.");
          //jQuery("#pm_reset_passerror").html(resp.htmlLink);
          jQuery("#pm-change-password-dialog, .pm-popup-mask").css('display','block');
      } else {
          jQuery(".pm-popup-title .title").text("Error");
          jQuery(".GCal-confirm-message").html("Unknown error. Event not added to your Google Calendar.");
          //jQuery("#pm_reset_passerror").html(resp.htmlLink);
          jQuery("#pm-change-password-dialog, .pm-popup-mask").css('display','block');
      }
    });
}