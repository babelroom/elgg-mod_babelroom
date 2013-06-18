<?php

    $english = array(
        'babelroom:errors:server_error' => "BabelRoom API failed. Please verify settings.",
        
        'babelroom:widgets:edit:name' => "Name",
        'babelroom:widgets:edit:description' => "Description",
        'babelroom:widgets:edit:target' => "Target<br /><table><tr><td><b>_blank</b></td><td>&nbsp;Open meeting in new window</td></tr><tr><td><b>_self|</b><em>empty</em></td><td>&nbsp;Open in current window</td></tr><tr><td><em>framename</em></td><td>&nbsp;Open in specified iframe</td></tr><tr><td><b>...</b></td><td>&nbsp;For more examples, see <a href=\"http://www.w3schools.com/tags/att_form_target.asp\" target=\"_blank\">reference.</a></td></tr></table>",

        'babelroom:widgets:view:join' => "Join Room ...",

        'babelroom:settings:default' => "Default",

        'babelroom:settings:api_key' => "API Key - You should change the API key",
        'babelroom:settings:api_key:api_key' => "API Key",
        'babelroom:settings:api_key:api_key_description' => 'The API key for your BabelRoom account. For conferences hosted on <a href="https://babelroom.com/">babelroom.com</a> create an API key by clicking the <em>Enable API Access</em> button on the BabelRoom API Credentials page here: <a href="https://my.babelroom.com/home?nav=api">https://my.babelroom.com/home?nav=api</a>. You can signup for a free account at <a href="https://babelroom.com/signup/">https://babelroom.com/signup/</a>. If you are using a private BabelRoom server please contact the server administrator for assistance. The default API key will not work with private BabelRoom servers.',

        'babelroom:settings:servers' => "Servers - Change these only if you are using a private server",
        'babelroom:settings:servers:room_server' => "Room Server",
        'babelroom:settings:servers:room_server_description' => 'The Room server. Leave the default value to use <a href="https://babelroom.com/">babelroom.com</a>. If you are using a private BabelRoom server please contact the server administrator for assistance.',

        'babelroom:settings:servers:api_server' => "API Server",
        'babelroom:settings:servers:api_server_description' => 'The API server. Leave the default value to use <a href="https://babelroom.com/">babelroom.com</a>. If you are using a private BabelRoom server please contact the server administrator for assistance.',
    );
    add_translation("en", $english);
