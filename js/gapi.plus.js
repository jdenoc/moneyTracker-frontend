/**
 * Global variables to hold the profile and email data.
 */
var profile, email;

/*
 * Triggered when the user accepts the sign in, cancels, or closes the
 * authorization dialog.
 */
function loginFinishedCallback(authResult) {
    if (authResult) {
        if (authResult['error'] == undefined){
            toggleElement('signin-button'); // Hide the sign-in button after successfully signing in the user.
            gapi.client.load('plus','v1', loadProfile);  // Trigger request to get the email address.
        } else {
            console.log('An error occurred');
        }
    } else {
        console.log('Empty authResult');  // Something went wrong
    }
}

/**
 * Uses the JavaScript API to request the user's profile, which includes
 * their basic information. When the plus.profile.emails.read scope is
 * requested, the response will also include the user's primary email address
 * and any other email addresses that the user made public.
 */
function loadProfile(){
    var request = gapi.client.plus.people.get( {'userId' : 'me'} );
    request.execute(loadProfileCallback);
}

/**
 * Callback for the asynchronous request to the people.get method. The profile
 * and email are set to global variables. Triggers the user's basic profile
 * to display when called.
 */
function loadProfileCallback(obj) {
    profile = obj;

    // Filter the emails object to find the user's primary account, which might
    // not always be the first in the array. The filter() method supports IE9+.
    email = obj['emails'].filter(function(v) {
        return v.type === 'account'; // Filter out the primary email
    })[0].value; // get the email from the filtered results, should always be defined.
    setSession(profile);
}

/**
 * Display the user's basic profile information from the profile object.
 */
function setSession(profile){
    $.ajax({
        type: 'POST',
        url: 'includes/session.php?x='+nocache(),
        data: {
            name: profile['displayName'],
            pic: profile['image']['url'],
            email: email
        },
        cache: false,
        beforeSend:function(){},
        success:function(data){
//          successful request
            if(parseInt(data)==1){
                window.location = 'main.php';
                exit();
            } else {
                document.getElementById('name').innerHTML = profile['displayName'];
                toggleElement('profile');
            }
        },
        error:function(){
            console.log('*** Error displaying Feed ***');
            endLoading();
        }
    });
}

/**
 * Utility function to show or hide elements by their IDs.
 */
function toggleElement(id) {
    var el = document.getElementById(id);
    if (el.getAttribute('class') == 'hide') {
        el.setAttribute('class', 'show');
    } else {
        el.setAttribute('class', 'hide');
    }
}

function nocache(){
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    for( var i=0; i < 5; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));

    return text;
}