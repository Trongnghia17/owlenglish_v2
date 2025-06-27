ZoomMtg.preLoadWasm()
ZoomMtg.prepareWebSDK()

var authEndpoint = 'https://meeting-zoom-nvt-aa9f9971c3ae.herokuapp.com'
var sdkKey = 'wnNV90WsQeipWxvEh_cg'
const meetingNumber = zoom_id;
// console.log("Meeting Number thành công rồi:", meetingNumber);
var passWord = ''
var role = 0
var userName = name_user;
// console.log("user name thành công rồi:", userName);
var userEmail = ''
var registrantToken = ''
var zakToken = ''
var leaveUrl = 'https://novateen.vn/'

function getSignature() {
  fetch(authEndpoint, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      meetingNumber: meetingNumber,
      role: role
    })
  }).then((response) => {
    return response.json()
  }).then((data) => {
    console.log(data)
    startMeeting(data.signature)
  }).catch((error) => {
  	console.log(error)
  })
}

function startMeeting(signature) {

  document.getElementById('zmmtg-root').style.display = 'block'

  ZoomMtg.init({
    leaveUrl: leaveUrl,
    patchJsMedia: true,
    success: (success) => {
      console.log(success)
      ZoomMtg.join({
        signature: signature,
        sdkKey: sdkKey,
        meetingNumber: meetingNumber,
        passWord: passWord,
        userName: userName,
        userEmail: userEmail,
        tk: registrantToken,
        zak: zakToken,
        success: (success) => {
          console.log(success)
        },
        error: (error) => {
          console.log(error)
        },
      })
    },
    error: (error) => {
      console.log(error)
    }
  })
}
