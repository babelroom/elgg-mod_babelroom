<?php

    $english = array(
        'babelroom:messages:new_conference' => "New BabelRoom Conference [%d] Created.",
        'babelroom:messages:deleted_conference' => "BabelRoom Conference [%d] Deleted.",

        'babelroom:errors:server_error' => "BabelRoom API failed. Please verify settings.",
        'babelroom:errors:unexpected_internal_error' => "An unexpected internal error occurred.",

        /* settings */
        'babelroom:settings:default' => "Default",

        'babelroom:settings:api_key' => "API Key - You should change the API key",
        'babelroom:settings:api_key:api_key' => "API Key",
        'babelroom:settings:api_key:api_key_description' => 'The API key for your BabelRoom account. For conferences hosted on <a href="https://babelroom.com/">babelroom.com</a> create an API key by clicking the <em>Enable API Access</em> button on the BabelRoom API Credentials page here: <a href="https://my.babelroom.com/home?nav=api">https://my.babelroom.com/home?nav=api</a>. You can signup for a free account at <a href="https://babelroom.com/signup/">https://babelroom.com/signup/</a>. If you are using a private BabelRoom server please contact the server administrator for assistance. The default API key will not work with private BabelRoom servers.',

        'babelroom:settings:server' => "Server - Change this only if you are using a private server",
        'babelroom:settings:server:api_server' => "API Server",
        'babelroom:settings:server:api_server_description' => 'The API server. Leave the default value to use <a href="https://babelroom.com/">babelroom.com</a>. If you are using a private BabelRoom server please contact the server administrator for assistance.',
        
        /* widget */
        'babelroom:widgets:babelroom:title' => "BabelRoom",
        'babelroom:widgets:babelroom:description' => "BabelRoom Widget",

        'babelroom:widgets:babelroom:edit:widget_settings' => 'Widget Settings',
        'babelroom:widgets:babelroom:edit:target' => "Target (for full conference page)<br /><table><tr><td><b>_blank</b></td><td>&nbsp;Open meeting in new window</td></tr><tr><td><b>_self|</b><em>empty</em></td><td>&nbsp;Open in current window</td></tr><tr><td><em>framename</em></td><td>&nbsp;Open in specified iframe</td></tr><tr><td><b>...</b></td><td>&nbsp;For more examples, see <a href=\"http://www.w3schools.com/tags/att_form_target.asp\" target=\"_blank\">reference.</a></td></tr></table>",

        'babelroom:widgets:babelroom_chat:edit:height' => 'Height',

        'babelroom:widgets:babelroom:edit:conference_settings' => 'Conference Settings',
        'babelroom:widgets:babelroom:edit:subwidget' => 'Select BabelRoom subwidget type.',
        'babelroom:widgets:babelroom:edit:reset' => 'Check to reset conference. This will delete the existing conference and create a new one.',


        'babelroom:widgets:babelroom:view:join' => "Join Room ...",
        'babelroom:widgets:babelroom:view:send' => "Send",
        'babelroom:widgets:babelroom:view:room_link' => "Go to Full Room",
        'babelroom:widgets:babelroom:view:access_number' => "Access Number: <b>%s</b>",
        'babelroom:widgets:babelroom:view:pin' => "PIN: <b>%s</b>",
        'babelroom:widgets:babelroom:view:user_count' => "%s User(s)",
        'babelroom:widgets:babelroom:view:no_users' => "<center><em>No Other Users Currently Online</em></center>",
        'babelroom:widgets:babelroom:view:call' => "Call",
        'babelroom:widgets:babelroom:view:busy' => "Busy",
        'babelroom:widgets:babelroom:view:no_presentation_loaded' => "-- No Presentation Loaded --",
        'babelroom:widgets:babelroom:view:select_presentation_text' => "-- Select a Presentation --",
        'babelroom:widgets:babelroom:view:upload' => "Upload",
        'babelroom:widgets:babelroom:view:first' => "First",
        'babelroom:widgets:babelroom:view:prev' => "Prev",
        'babelroom:widgets:babelroom:view:show' => "Show",
        'babelroom:widgets:babelroom:view:hide' => "Hide",
        'babelroom:widgets:babelroom:view:next' => "Next",
        'babelroom:widgets:babelroom:view:end' => "End",
        'babelroom:widgets:babelroom:view:make_me_presenter' => "Make Me Presenter",
        'babelroom:widgets:babelroom:view:close' => "Close",
        'babelroom:widgets:babelroom:view:calling' => "<span style=\"font-size: 1.5em;\">Calling %s ...</span><br><a href=\"#\" class=\"br-cancel\">Cancel</a>",
        'babelroom:widgets:babelroom:view:ringing' => "<span style=\"font-size: 1.5em;\">Incoming Call from %s ...</span><br><a href=\"#\" class=\"br-accept\">Accept</a> <a href=\"#\" class=\"br-reject\">Reject</a>",
        'babelroom:widgets:babelroom:view:permission_denied_msg' => "You have denied permission to use microphone and camera. The call will now be terminated.",
        'babelroom:widgets:babelroom:view:grant_permission' => "You must grant microphone and webcam permission to continue. Follow directions in browser top-bar or pop-up to allow.",
        'babelroom:widgets:babelroom:view:indicators' => "<span>[Muted]</span><span>[Video Off]</span>",
        'babelroom:widgets:babelroom:view:controls' => "<center><a href=\"#\">Mute &nbsp; </a><a href=\"#\">Unmute &nbsp; </a><a href=\"#\">Video Off &nbsp; </a><a href=\"#\">Video On &nbsp; </a><a href=\"#\">Fullscreen &nbsp; </a><a href=\"#\">Exit Fullscreen &nbsp; </a><a href=\"#\">End Call &nbsp; </a></center>",

        'babelroom:widgets:babelroom:view:error_msg' => "A browser/WebRTC error occurred",
        'babelroom:widgets:babelroom:type:group_chat' => "Group Chat",
        'babelroom:widgets:babelroom:type:conference' => "Conference",
        'babelroom:widgets:babelroom:type:p2p_chat' => "Peer-to-Peer",
        'babelroom:widgets:babelroom:type:presentation' => "Presentation",
    );
    add_translation("en", $english);
