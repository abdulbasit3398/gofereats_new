var googleUser = {};
var startApp = function()
{
  gapi.load('auth2', function() {
    // Retrieve the singleton for the GoogleAuth library and set up the client.
    auth2 = gapi.auth2.init({
      client_id: GOOGLE_CLIENT_ID,
      cookiepolicy: 'single_host_origin',
      // Request scopes in addition to 'profile' and 'email'
      //scope: 'additional_scope'
    });
    attachSignin(document.getElementById('google_login'),'');
    attachSignin(document.getElementById('google_signin'),'');
    attachSignin(document.getElementById('google_signin1'),'');
    attachSignin(document.getElementById('google_signin2'),'');
    attachSignin(document.getElementById('pop_google_login'),'');
    attachSignin(document.getElementById('pop_google_signup'),'');
  });
};

function attachSignin(element,value="")
{
  auth2.attachClickHandler(element, {},function(googleUser) {
    var id_token = googleUser.getAuthResponse().id_token;
    console.log(id_token);
    window.location = APP_URL+'/googleAuthenticate?idtoken='+id_token+'&connect='+value;
  }, function(error) {
    // 
  });
}

startApp();
