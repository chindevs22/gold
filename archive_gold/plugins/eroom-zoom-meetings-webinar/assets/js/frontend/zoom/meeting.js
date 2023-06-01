window.addEventListener('DOMContentLoaded', function(event) {
  console.log('DOM fully loaded and parsed');
  websdkready();
});

function websdkready() {
  var testTool = window.testTool;
  var meetingConfig = {
    apiKey: API_KEY,
    secretKey: SECRET_KEY,
    meetingNumber: meeting_id,
    userName: username,
    passWord: meeting_password,
    leaveUrl: leaveUrl,
    role: 0, //0-Attendee,1-Host,5-Assistant
    userEmail: email,
    lang: lang,
    signature: "",
    china: 0,//0-GLOBAL, 1-China
  };

  if (testTool.isMobileDevice()) {
    vConsole = new VConsole();
  }

  ZoomMtg.preLoadWasm();
  ZoomMtg.prepareJssdk();


  ZoomMtg.inMeetingServiceListener('onUserJoin', function (data) {
    console.log('inMeetingServiceListener onUserJoin', data);
  });

  ZoomMtg.inMeetingServiceListener('onUserLeave', function (data) {
    console.log('inMeetingServiceListener onUserLeave', data);
  });

  ZoomMtg.inMeetingServiceListener('onUserIsInWaitingRoom', function (data) {
    console.log('inMeetingServiceListener onUserIsInWaitingRoom', data);
  });

  ZoomMtg.inMeetingServiceListener('onMeetingStatus', function (data) {
    console.log('inMeetingServiceListener onMeetingStatus', data);
  });

  ZoomMtg.preLoadWasm();
  ZoomMtg.prepareJssdk();
  function beginJoin() {
    var signature = ZoomMtg.generateSDKSignature({
      meetingNumber: meetingConfig.meetingNumber,
      sdkKey: meetingConfig.apiKey,
      sdkSecret: meetingConfig.secretKey,
      role: meetingConfig.role,
      success: function (res) {
        console.log(res.result);
        meetingConfig.signature = res.result;
        meetingConfig.sdkKey = meetingConfig.apiKey;
      },
    });
    ZoomMtg.init({
      leaveUrl: meetingConfig.leaveUrl,
      disableCORP: !window.crossOriginIsolated,
      webEndpoint: meetingConfig.webEndpoint,
      success: function () {
        ZoomMtg.i18n.load(meetingConfig.lang);
        ZoomMtg.i18n.reload(meetingConfig.lang);
        ZoomMtg.join({
          meetingNumber: meetingConfig.meetingNumber,
          userName: meetingConfig.userName,
          signature: signature,
          sdkKey: meetingConfig.apiKey,
          userEmail: meetingConfig.userEmail,
          passWord: meetingConfig.passWord,
          success: function (res) {
            console.log("join meeting success");
            ZoomMtg.getAttendeeslist({});
            ZoomMtg.getCurrentUser({
              success: function (res) {
                console.log("success getCurrentUser", res.result.currentUser);
              },
            });
          },
          error: function (res) {
            console.log(res);
          },
        });
      },
      error: function (res) {
        console.log(res);
      },
    });
  }
  beginJoin();
};
