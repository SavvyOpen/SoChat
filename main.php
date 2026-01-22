<html>

<!--
	Copyright (c) 2025 Chung Lim Lee and Savvy Open
	All rights reserved.
-->





<head>
    <title>SO ChatX</title>
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width">
</head>


<body tabindex="-1">

<?php include $_SERVER['DOCUMENT_ROOT']. "/script/lib/php/modal_window.php" ?>
<script src="/script/lib/js/server_request.js"></script>





<!-- CSS LAYOUTS -->

<style>

body {font-family: Arial,sans-serif; font-weight: 500; background-color: #f0f0f8; overflow: hidden; margin: 0px} /* *** margin here must set to zero, default is 8px */

input {font-size: 15px; height: 24px;}

button {box-shadow: 0px 0px 3px #303030; border: none; border-radius: 10px; padding: 5px 10px 5px 10px;}
button:hover {background-color: white; cursor: pointer;}


.contact {width: 92%; max-width: 700px; padding-left: 4%; padding-top: 1%; padding-bottom: 2%; padding-right: 5%; border: 1px solid #f0f0f0}
.contact:hover {background-color: f0f0f0}
.user {float: left; font-size: 1.2em; color: black}
.date_time {float: right; font-size: 1em; color: grey}
.last_message {font-size: 1em; color: grey}
.contact_view_button {font-size: 1em; width: 25%; padding: 8px 0px 8px 0px; float: left;}


#chat_title {float: left; width: 93%; padding: 10px 0px 3px 0px; text-align: center; height: 30px; font-size: 1.2em; color: white; background-color: black}    
#back_button {float: right; width: 7%; padding: 10px 0px 3px 0px; text-align: center; height: 30px; font-size: 1.2em; color: white; background-color: black}    
#back_button:hover {cursor: pointer}

#contact_tabs {padding: 1px; position: absolute; width: 100%; max-width: 700px; color: black; background-color: #e8e8e8; overflow: auto;}
#contact_tabs:hover {cursor: pointer;}

#history_box {position: absolute; width: 99%; max-width: 700px; height: 500px; margin-left: 1%; top: 44px; left: 0px;}

#message_box {box-shadow: 0px 0px 1px #909090; font-size: 25px; position: absolute; bottom: 0px; left: 0px; font-family: Arial,sans-serif; font-size: 1em; border: none; width: 80%; max-width: 560px; height: 100px; padding: 2px; background-color: white; resize: none} 
#message_box:focus {outline: none}

#send_button {box-shadow: 0px 0px 1px #909090; position: absolute; bottom: 0px; right: 0px; text-align: center; width: 20%; max-width: 140px; height: 100px; font-size: 1.2em; color: black; background-color: #f8f8f8}
#send_button:hover {cursor: pointer; background-color: white};


.no_text_select {
    
    -ms-user-select: none;
    -webkit-user-select: none;
    user-select: none;
}

</style>    
    




<!-- HTML LAYOUTS -->

<div id="master_view" style='box-shadow: 0px 1px 1px grey; background-color: white; width: 100%; height: 100%; max-width: 700px; margin-left: auto; margin-right: auto;'>

    <div id="contact_view" style="position: absolute; width: 100%; max-width: 700px; height: 100%; top: 0px;" class="no_text_select">
    
        <div id="logo" style="text-align: center; padding: 10px 0px 3px 0px; width: 100%; height: 30px; background-color: black; color: white; font-size: 1.2em;">SO ChatX
        </div>
        
        <div id="contact_list" style="overflow-y: auto; overflow-x: hidden; height: 80%;"></div>
        
        <div id="contact_tabs">
            <center>
                <span id="chat_button" style="height: 100%; background-color: white" onclick="use_contact_type = 'chat'; get_contact_data(); this.style.backgroundColor = 'white'; group_button.style.backgroundColor = '';" class="contact_view_button">"!"<br>Chats</span>
                <span id="group_button" onclick="use_contact_type = 'group'; get_contact_data(); this.style.backgroundColor = 'white'; chat_button.style.backgroundColor = '';" class="contact_view_button">@<br>Groups</span>
                <span id="add_new_chat" class="contact_view_button">+<br>Add</span>
                <span id="option_button" onclick="modal_window('Options', `<center><br><br>TOTP Authenticator<br><button style='margin-top: 8px;' onclick='totp_setup();'>Setup</button><br><br><br><br>Active Session<br><button style='margin-top: 8px;' onclick='logout();'>Logout</button><br><br></center>`)" class="contact_view_button"><b>···</b><br>Options</span>
            </center>        
        </div>
    
    </div>
    
    
    
    <div id="chat_view" style="position: absolute; display: none; width: 100%; height: 100%; max-width: 700px; top: 0px;" class="no_text_select">
    
        <div id="chat_title"></div><div id="back_button" onclick="contact_view.style.display = 'block'; chat_view.style.display = 'none'; message_box.value = ''">x</div>
        
        <div id="history_box" style="overflow-y: scroll;"></div>
    
        <textarea id="message_box"></textarea>
        <div id="send_button"><br><br>Send</div>

    </div>

</div>





<!-- JS -->

<script>
    
var contact_view = document.getElementById('contact_view');
var contact_list = document.getElementById('contact_list');

var chat_view = document.getElementById('chat_view');
var chat_title = document.getElementById('chat_title');
var history_box = document.getElementById('history_box');
var message_box = document.getElementById('message_box');
var send_button = document.getElementById('send_button');

var all_chat_data = []; // all contacts with their respective messages
var all_chat_data_new;  // all new chat data coming from server
var all_chat_data_version = {};

var use_contact_type = 'chat';  // set the corresponding view for chat or group
var currently_interacting_with_user = '';
var _derived_password = '';



document.getElementById('contact_tabs').style.bottom = '0px';   // set contact tabs always at the bottom of the relative screen size



document.getElementById('add_new_chat').onclick = function() {
    
    modal_window('Add Menu', `
    
        <div style='overflow-y: hidden;'>
            <center>
                <b>User Chat Request</b><br>
                <input type="text" id="request_chat_with" size=18 style='border: 1px solid grey; margin-top: 8px;' placeholder='case-sensitive'><br><br>
                <button id="request_chat_with_button">Request</button>
                <br><br><br>
                
                
                <b>Group</b><br>
                <input type="text" id="group_name_input" size=18 style='border: 1px solid grey; margin-top: 8px;' placeholder='case-sensitive'><br><br>
                <button onclick='if (group_name_input.value !== "") create_group(group_name_input.value);'>Create</button>
                <button onclick='if (group_name_input.value !== "") join_group(group_name_input.value);' style='margin-left: 30px;'>Join</button>
                <br><br>
            </center>
        </div>
    `);
    
    request_chat_with_button.onclick = function() { 
        
        if (request_chat_with.value !== '')
            request_chat(request_chat_with.value);

    };
}





// RESIZE HISTORY BOX WHEN VIEW PORT SIZE CHANGES

window.addEventListener('resize', () => {

    history_box.style.height = window.innerHeight - 30 - 100 - 20;  // minus heading, contact_tabs, adjustment
});





// LOGIN HANDLINGS

function login_modal(force_show) {
    
    if (modal_window_box.style.display === '' || modal_window_box.style.display === 'none' || force_show === true) {

        modal_window('Sign In', `
        
            <center>
            <div style='overflow: hidden'>
            User Name<br>
            <input style='border: 1px solid grey; margin-top: 8px;' type="text" id="_user_name" placeholder="case-sensitive">
            <br><br>
            Password<br>
            <input style='border: 1px solid grey; margin-top: 8px;' type="password" id="_user_password" placeholder="">
            <br><br>
            
            <input id="_totp_code" style='border: 1px solid grey; margin-top: 8px;' type="text" placeholder="TOTP code" size=8>
            <br><br><br>
            <button style='margin-left: 1px;' onclick="pre_login();">Login</button>
            <br><br>
            </div>
            </center>
        `);
    }
}

async function get_key_by_pbkdf2(password, salt) {

    var key = await window.crypto.subtle.importKey("raw", new TextEncoder().encode(password), "PBKDF2", false, ["deriveBits", "deriveKey"]);
    
    var derived_key_bits = await crypto.subtle.deriveBits(
        
        {
            name: 'PBKDF2',
            salt: new TextEncoder().encode(salt),
            iterations: 1_000_000,
            hash: 'SHA-512'
        },
        
        key,
        256
    );

	return new Uint8Array(derived_key_bits).toString();
}

async function pre_login() { // get the password salt for the given user (*** this is for client-side hashing for added security on top of the server side hashing)

    var http_post = new XMLHttpRequest();

    http_post.onreadystatechange = async function() {
      
    	if (this.readyState === this.HEADERS_RECEIVED)
    		res = http_post.getAllResponseHeaders();
    		
    
    	if (this.readyState==4 && this.status==200) {
 
    		var response = JSON.parse(this.responseText);

            if (response.timeout) {
            
                modal_window('Login Timeout', 'Please wait for 5 seconds before retry.');
                return;
            }

            var user_password_salt = response.password_salt;
            var derived_password = await get_key_by_pbkdf2(_user_password.value, user_password_salt);
                
            login(derived_password);
    	}
    }
    
    http_post.open("POST", '/app/so_login/so_login_salt.php');
    http_post.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    http_post.send("user=" + encodeURIComponent(_user_name.value));
}

function login(derived_password) {  // log in when no existing user already logged in with the current session id in the client's browser

    var http_post = new XMLHttpRequest();

    http_post.onreadystatechange=function() {
      
    	if (this.readyState === this.HEADERS_RECEIVED)
    		res = http_post.getAllResponseHeaders();
    		
    
    	if (this.readyState==4 && this.status==200) {
 
    		var response = JSON.parse(this.responseText);

    		if (response.success) {

                // close modal box and remove credential from temporary elements
                
                _user_name.value = '';
                _user_password.value = '';
                _totp_code.vlaue = '';
                
                modal_window_close();
                location.reload();  // *** force page reload
    		}

            else {
                
                modal_window('Login Failed', 'Credentials are incorrect.<br><br><button style="margin-left: 1px;" onclick="login_modal(true);">Retry</button>');
            }
    	}
    }
    
    http_post.open("POST", '/app/so_login/so_login.php');
    http_post.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    http_post.send("user=" + encodeURIComponent(_user_name.value) + "&password=" + encodeURIComponent(derived_password) + "&totp_code=" + encodeURIComponent(_totp_code.value));
}

function totp_setup() {

    
    modal_window('TOTP Setup', `
    
        <center>
        <br>
        User Name<br><input id='totp_user_name' style='margin-top: 8px;' type="text" placeholder='case-sensitive'>
        <br><br><br>
        Approval Code<br><input id='totp_approval_code' style='margin-top: 8px;' type="text" size=10>
        <br><br><br>
        <button style='margin-left: 1px;' onclick='totp_request_qrcode(totp_user_name, totp_approval_code);'>Submit</button>
        </center>
    `);
}

function totp_request_qrcode(for_user_name, approval_code) {

    server_post_request('/app/so_login/totp_setup.php', 'user_name=' + encodeURIComponent(for_user_name.value) + '&approval_code=' + encodeURIComponent(approval_code.value), function(result) {
     
        modal_window('TOTP Setup', result);
    });
}

function logout() { // logout the user that matches the session ID in the MYSQL database
    
    var http_post = new XMLHttpRequest();

    http_post.onreadystatechange = function() {
      
    	if (this.readyState === this.HEADERS_RECEIVED)
    		res = http_post.getAllResponseHeaders();
    

    	if (this.readyState==4 && this.status==200) {

            location.reload();

            /*  page reload above to bypass these
            
                modal_window_close();
                login_modal();
                console.log('User logout'); 		
            */
    	}
    }
    
    http_post.open("POST", '/app/so_login/so_logout.php');
    http_post.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    http_post.send();
}





// SSE HANDLINGS (INCOMING DATA FROM SERVER)

window.addEventListener("beforeunload", function() {    // *** During page reload with refresh button, Firefox will delay in auto calling evtSource.close(), but Edge/Chrome will not delay

    evtSource.close();  // manually close event source before page reloads, else Firefox calls evtSource.close() after the page has been reloaded with SSE connected (resulting the first SSE disconnects right away)
});



var evtSource;

function get_all_data() {

    if (evtSource !== undefined)    // *** need to close any existing SSE, else AJAX below will not send from browser!!!
        evtSource.close();
        

        
    evtSource = new EventSource('/app/so_chat/receive_message.php?version=' + encodeURIComponent(JSON.stringify(all_chat_data_version)) );

    evtSource.addEventListener("no_active_login", function(event) {

        login_modal();
    });     



    evtSource.onopen = function() {
        
        console.log("SSE connected");
    };
    
    evtSource.onmessage = function(event) {

        all_chat_data_new = JSON.parse(event.data);
        console.log("server send:", all_chat_data_new);
        


        if (so_chat_initialized === false) { // when successfully received data from server for the first time then mark so_chat_initalized true

            so_chat_initialized = true;
            all_chat_data = all_chat_data_new;
        }
        
        else {  // when subsequent received data from server then append to all_chat_data
            
            for (var chat_with of Object.keys(all_chat_data_new) ) {
                
                if (all_chat_data_new[chat_with] === "@Requestor@") {  // skip any chat requests or pending responds to any request (a string type is the cue)
                
                    all_chat_data[chat_with] = '@Requestor@';
                    continue;
                }

                else if (all_chat_data_new[chat_with] === "@Acceptor@") {  // skip any chat requests or pending responds to any request (a string type is the cue)
                
                    all_chat_data[chat_with] = '@Acceptor@';
                    continue;
                }

                
                
                if (all_chat_data[chat_with] === "@Acceptor@") // *** when request chat has been accepted (server begins to send an array instead of sending a string = @Acceptor@ for this chat_with)
                    all_chat_data[chat_with] = all_chat_data_new[chat_with];
                else
                    all_chat_data[chat_with] = all_chat_data[chat_with].concat(all_chat_data_new[chat_with]);   // *** merge the incremental new chat history from server to this client
                
                
                
                if (all_chat_data_new[chat_with].length !== 0)
                    var notification = new Notification(chat_with + ': ' + all_chat_data_new[chat_with][all_chat_data_new[chat_with].length - 1].Message.substring(0, 30) + "...");

            }
        }



        // refresh all contact data and all chat data
        
        get_contact_data();
        get_chat_data(currently_interacting_with_user);
    };
    
    evtSource.onerror = function(error) {
        
        console.log("SSE disconnected: ", error);
        get_all_data_delayed();
    };

    evtSource.addEventListener("client_version", function(event) {
            
        console.log("client version:", event.data);
    });     

    evtSource.addEventListener("server_version", function(event) {
            
        console.log("server version:", event.data);

        all_chat_data_version = JSON.parse(event.data);
    });
    
    evtSource.addEventListener("SSE_takeover", function(event) {
            
        console.log("SSE takeover:", event.data);
        modal_window('Auto Timeout', 'Detected user is using this app in another device/session.');
        evtSource.close();
    });
    
    evtSource.addEventListener("debug", function(event) {
            
        console.log("debug:", event.data);
    });
}

function get_cookie(name) { // call by get_all_data during new SSE connection

    var pre_cookie_list = document.cookie.replaceAll(' ', '').split(';');
    var cookie;
    var cookie_list = {};
    
    for (var n = 0; n < pre_cookie_list.length; n++) {
        
        cookie = pre_cookie_list[n].split('=');
        cookie_list[cookie[0] ] = cookie[1];
    }
    
    return cookie_list[name];
}

var get_all_data_delayed = function() { // call by evtSource.onerror listener
    
    var start_time = Date.now(); 
    
    return function() {
    
        if (Date.now() - start_time > 3000) {   // only running get_all_data() every 3 seconds
        
            start_time = Date.now();
            get_all_data();
        }
    }
}();





// CONTACT AND DATA HANDLINGS

function get_contact_data(type) {   // contacts with their related last message

    var chat_data, last_record, contact_data;


    contact_list.innerHTML = '';
    
    for (var chat_with of Object.keys(all_chat_data) ) {

        chat_data = all_chat_data[chat_with];

        if (use_contact_type === 'chat') {
        
            if (chat_data === "@Acceptor@") {   // *** in the perspective of chat_with, for any pending chat requests to chat_with
    
                contact_list.innerHTML += `
                
                    <div class="contact" onclick="modal_window('Pending Request', 'Waiting for ${chat_with} to accept chatting with you!')">
                    <div class="user">${chat_with}</div><br><br>
                    <div class="last_message"><span style='background-color: #aaffaa; padding: 5px;'>requested to chat with ${chat_with}</span></div>
                    </div>
                `;
                
                continue;
            }
            
            else if (chat_data === "@Requestor@") {   // *** in the perspective of chat_with, for any pending chat accepts (for chat_with's chat requests)
    
                contact_list.innerHTML += `
                
                    <div id="accept_${chat_with}" onclick="accept_chat_modal('${chat_with}');" class="contact">
                    <div class="user">${chat_with}</div><br><br>
                    <div class="last_message"><span style='background-color: #aaffaa; padding: 1vw'>${chat_with} requesting to chat with you</span></div>
                    </div>
                `;
    
                continue;
            }
        }



        // skip to next entry when it does not contain contact data
        
        if (use_contact_type === 'chat' && chat_with[0] === '@')
            continue;
        else if (use_contact_type === 'group' && chat_with[0] !== '@')
            continue;
    
    
    
        // *** At the belows will only run when chat_data is neither @Acceptor@ or @Requestor@
        
        contact_data = `
    
            <div class="contact" onclick="contact_view.style.display = 'none'; chat_view.style.display = 'block'; history_box._scrolled = false; get_chat_data('${chat_with}')">
            <div class="user">${chat_with}</div>
        `;
        

        
        if (chat_data.length > 0) { // when there is one or more message(s)

            last_record = chat_data[chat_data.length - 1];  // *** get the most recent message

            contact_data += `

                <div style="font-size: 0.8em" class="date_time">${new Date(last_record.Time + 'Z').toLocaleString()}</div><br><br>
                <div class="last_message">${last_record.Message.substring(0, 30)}...</div>
                </div>
            `;
        }
        
        else {  // No Message yet
            
            contact_data += `
            
                <div class="date_time"></div><br><br>
                <div class="last_message"><i>(No Message)</i></div>
                </div>
            `;
        }
        
        contact_list.innerHTML += contact_data;    
    }
}

function get_chat_data(chat_with) {

	history_box.style.height = window.innerHeight - 30 - 100 - 20;  // minus heading, contact_tabs, adjustment

    

    currently_interacting_with_user = chat_with;
    chat_title.innerHTML = chat_with;
    
    

    var chat_data = all_chat_data[chat_with];

    if (chat_data === undefined)
        return;
        

    
    history_box.innerHTML = '';

    for (var message of chat_data) {
        
        if (message.User === chat_with) {

            history_box.innerHTML += `
            
                <div style='box-shadow: 0px 0px 1px #909090; border-radius: 5px; background-color: #fafafa; float: left; width: 80%; max-width: 700px; margin-bottom: 25px; padding: 15px; margin: 10px;'>
                    <div style='float: left; font-size: 1em; color: grey'>${message.User}</div>
                    <div style='float: right; font-size: 0.8em; color: grey'>${new Date(message.Time + 'Z').toLocaleString()}</div><br><br>
                    <div>${message.Message}</div>
                </div><br>
            `;
        }
        
        else {

            history_box.innerHTML += `
            
                <div style='box-shadow: 0px 0px 1px #909090; border-radius: 5px; background-color: #ddffdd; float: right; width: 80%; max-width: 700px; margin-bottom: 25px; padding: 15px; margin: 10px;'>
                    <div style='float: left; font-size: 1em; color: grey'>${message.User}</div>
                    <div style='float: right; font-size: 0.8em; color: grey'>${new Date(message.Time + 'Z').toLocaleString()}</div><br><br>
                    <div>${message.Message}</div>
                </div><br>
            `;
        }
    }



    if (history_box._scrolled === false)
        history_box.scrollTop = history_box.scrollHeight;

}

history_box.onscroll = function() {

    var scrollable_height = history_box.scrollHeight - history_box.clientHeight;
    
    if (history_box.scrollTop > scrollable_height - 10 && history_box.scrollTop <= scrollable_height)
        history_box._scrolled = false;
    else
        history_box._scrolled = true;
}





// SEND MESSAGE TO SERVER

send_button.onclick = function() {

    if (message_box.value === '')
        return;
        
    server_post_request('/app/so_chat/send_message.php', 'chat_with=' + encodeURIComponent(currently_interacting_with_user) + '&message=' + encodeURIComponent(message_box.value), function(result) {

        // immediate temporary placeholder before get_chat_data() refreshes
        
        history_box.innerHTML += `
        
            <div style='box-shadow: 0px 0px 1px #909090; border-radius: 5px; background-color: #ddffdd; float: right; width: 80%; margin-bottom: 1em; padding: 1em'>
                <div style='float: left; font-size: 1em; color: grey'>---</div>
                <div style='float: right; font-size: 0.8em; color: grey'>${new Date().toLocaleString()}</div><br><br>
                <div>${message_box.value}</div>
            </div><br>
        `;


        message_box.value = '';
    });
}





// REQUEST AND ACCEPT CHATS

function request_chat(chat_with) {

    server_post_request('/app/so_chat/request_chat.php', 'chat_with=' + encodeURIComponent(chat_with), function(result) {

        console.log(result);
        
        if (result === 'valid request') {
            
            all_chat_data[chat_with] = '@Requestor@';
            modal_window_close();
        }
    });
}

function accept_chat_modal(chat_with) {
    
    modal_window('Pending Accept', `Accept chatting request from ${chat_with}?<br><br><button onclick="accept_chat('${chat_with}')">Accept</button> <button style='margin-left: 30px;' onclick='modal_window_close()'>Decline</button>`);
}
            
function accept_chat(chat_with) {

    server_post_request('/app/so_chat/accept_chat.php', 'chat_with=' + encodeURIComponent(chat_with), function(result) {
     
        console.log(result);
        
        if (result === 'valid acceptor')
            all_chat_data[chat_with] = [];
            
        modal_window_close();
    });
}





// GROUP HANDLINGS

function create_group(name) {

    server_post_request('/app/so_chat/create_group.php', 'group_name=' + encodeURIComponent(name), function(result) {
     
        modal_window('Group Creation', `<b>${group_name}</b> created successfully.`);
    });
}

function join_group(name) {

    server_post_request('/app/so_chat/join_group.php', 'group_name=' + encodeURIComponent(name), function(result) {
     
        modal_window('Join Group', result);
    });
}





// MAIN LOADER

var so_chat_initialized = false;    // to indicate has SO_chat initalized or not

server_post_request('/app/so_chat/user_first_login.php', '', function() {});    // *** if this user is the first time using SO Chat then setup this user in SO Chat database
    
get_all_data();
Notification.requestPermission();


</script>


</body>    
</html>