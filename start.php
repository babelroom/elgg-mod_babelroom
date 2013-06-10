<?php

require_once($CONFIG->pluginspath . "babelroom/vendors/BabelRoomV1API/API.php");

define("BABELROOM_DEFAULT_API_KEY", "65dc149155ab4e26d0b207eed9d3c710");
define("BABELROOM_DEFAULT_ROOM_SERVER", "https://bblr.co");
define("BABELROOM_DEFAULT_API_SERVER", "https://api.babelroom.com");

function babelroom_init($event, $object_type, $object) {        
/*
*/
    $tmp = elgg_get_plugin_setting('api_key', 'babelroom');
    if (!isset($tmp))
        elgg_set_plugin_setting('api_key', BABELROOM_DEFAULT_API_KEY, 'babelroom');
    $tmp = elgg_get_plugin_setting('room_server', 'babelroom');
    if (!isset($tmp))
        elgg_set_plugin_setting('room_server', BABELROOM_DEFAULT_ROOM_SERVER, 'babelroom');
    $tmp = elgg_get_plugin_setting('api_server', 'babelroom');
    if (!isset($tmp))
        elgg_set_plugin_setting('api_server', BABELROOM_DEFAULT_API_SERVER, 'babelroom');

	elgg_register_page_handler("babelroom", "babelroom_page_handler");

    elgg_register_widget_type('babelroom', 'BabelRoom', 'The BabelRoom widget');
}

/* --- */
function _error($so, $msg)
{
    register_error(elgg_echo('babelroom:errors:'.$msg));
    if ($so) {
        echo '<script language="javascript">window.location="' . _THIS_IS_NOT_GOING_TO_WORK . '";</script>';
        }
    else {
        forward(REFERRER);
        }
    return FALSE;
}
function do_join($widget_guid)
{
    $so = false; /* stream output to browser then redirect using js, or not, for now "not" */
    if ($so) {
        echo "<html><body><pre>Generating invitation...<br />";
        ob_flush();
        flush();
        }
    $widget = get_entity($widget_guid);
    $owner_guid = $widget->getOwnerGUID();
    $elgg_user = elgg_get_logged_in_user_entity();

    $icon = null;
    $is_host = FALSE;
    if ($elgg_user) { # this is null if not logged in
        $user_guid = $elgg_user->getGUID();
        $icon = $elgg_user->getIconURL('large');
        $defaulticon = "defaultlarge.gif";  # this is what we end with if there is no icon
        if (!$icon || (strrpos($icon, $defaulticon)==(strlen($icon)-strlen($defaulticon)))) {
            $icon = null;   # no icon
            }
        /* I'm a host if I'm an administrator on this system or I created the widget */
        if (($owner_guid == $user_guid /* different types, don't use === */) || $elgg_user->isAdmin()) {
            $is_host = TRUE;
            }
        # parse out name?
        $user = array('first'=>$elgg_user->name, 'last'=>'', 'email'=>$elgg_user->email, 'id'=>$user_guid, 'language'=>$elgg->language);
        }
    else {
        $user = array('first'=>'Guest', 'last'=>'User', 'id'=>0, 'language'=>'en');
        }

    $result;
    $rc = BRAPI_create_invitation($widget->babelroom_id, $user, $icon, $is_host, $result);
    if (!$rc) {
        return _error($so, 'server_error');
        }
    $url = elgg_get_plugin_setting('room_server', 'babelroom') . $widget->babelroom_url . '?t=' . $result->token;
    if ($so) {
        echo "Redirecting...<br />";
        echo '<script language="javascript">window.location="' . $url . '";</script>';
        }
    else {
        forward($url);
        }

    return $rc;
}

/* --- */
function babelroom_page_handler($page){
    switch($page[0]) {
        case "join":
            /* technically there's an access control bypass here if somebody knows the id's and manually edits the url */
            return do_join($_REQUEST['widget_guid']);
        }
    return true;
}

/* --- */
function babelroom_object_handler($event, $object_type, $object) {
    $rc = TRUE;
    if (isset($object->babelroom_id)) {
        switch($event) {
            case 'update':
                $rc = BRAPI_update($object);
                break;
            case 'delete':
                $rc = BRAPI_delete($object);
                break;
            }
        }
    if (!$rc) {
        register_error(elgg_echo('babelroom:errors:server_error'.$msg));
        }
    return $rc;
}
 
elgg_register_event_handler('init', 'system', 'babelroom_init');       
elgg_register_event_handler('all', 'object', 'babelroom_object_handler');       
elgg_register_action("babelroom/join", false, $CONFIG->pluginspath . "babelroom/actions/join.php");

