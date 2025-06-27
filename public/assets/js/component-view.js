const client = ZoomMtgEmbedded.createClient()

let meetingSDKElement = document.getElementById('meetingSDKElement')

var authEndpoint = 'https://meeting-zoom-nvt-aa9f9971c3ae.herokuapp.com'
var sdkKey = 'wnNV90WsQeipWxvEh_cg'
var meetingNumber = '81394981517'
var passWord = ''
var role = 0
var userName = 'LongPK'
var userEmail = ''
var registrantToken = ''
var zakToken = ''
var leaveUrl = 'https://zoom.longpk.info'


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

  client.init({zoomAppRoot: meetingSDKElement, language: 'en-US', patchJsMedia: true}).then(() => {
    client.join({
      signature: signature,
      sdkKey: sdkKey,
      meetingNumber: meetingNumber,
      password: passWord,
      userName: userName,
      userEmail: userEmail,
      tk: registrantToken,
      zak: zakToken
    }).then(() => {
      console.log('joined successfully')
    }).catch((error) => {
      console.log(error)
    })
  }).catch((error) => {
    console.log(error)
  })
}
