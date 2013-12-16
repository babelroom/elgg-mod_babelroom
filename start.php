<?php

require_once($CONFIG->pluginspath . "babelroom/vendors/BabelRoomV1API/API.php");

define("BABELROOM_DEFAULT_API_KEY", "65dc149155ab4e26d0b207eed9d3c710");
define("BABELROOM_DEFAULT_API_SERVER", "https://api.babelroom.com");

function babelroom_init($event, $object_type, $object) {        
    $tmp = elgg_get_plugin_setting('api_key', 'babelroom');
    if (!isset($tmp))
        elgg_set_plugin_setting('api_key', BABELROOM_DEFAULT_API_KEY, 'babelroom');
    $tmp = elgg_get_plugin_setting('api_server', 'babelroom');
    if (!isset($tmp))
        elgg_set_plugin_setting('api_server', BABELROOM_DEFAULT_API_SERVER, 'babelroom');

    /* derive room and cdn server settings from api server */
    $tmp = elgg_get_plugin_setting('api_server', 'babelroom');
    if (preg_match('/^(https?:\/\/)api.babelroom.com$/i', $tmp, $matches)) {    /* determine is using babelrom.com or a private server */
        /* hosts for babelroom.com service */
        elgg_set_plugin_setting('room_server', "$matches[1]bblr.co", 'babelroom');
        elgg_set_plugin_setting('cdn_server', "$matches[1]cdn.babelroom.com", 'babelroom');
        }
    else {
        /* hosts for babelroom.com service */
        elgg_set_plugin_setting('room_server', $tmp, 'babelroom');
        elgg_set_plugin_setting('cdn_server', $tmp, 'babelroom');
        }

	elgg_register_page_handler('babelroom', 'babelroom_page_handler');

    elgg_register_widget_type('babelroom', elgg_echo('babelroom:widgets:babelroom:title'), elgg_echo('babelroom:widgets:babelroom:description'), /*context*/"all", /*multiple*/true);
}

/* --- */
function _error($so, $msg)
{
    register_error(elgg_echo('babelroom:errors:'.$msg));
    if ($so) {
        echo '<script language="javascript">window.location="' . _THIS_IS_NOT_GOING_TO_WORK . '";</script>'; /* TOOD - comment */
        }
    else {
        forward(REFERRER);
        }
    return false;
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
    $cid = BRAPI_elgg_getOrCreateConference($widget);
    if (!$cid) {  /* error message already given */
        return false;
        }

    $result;
    $rc = BRAPI_elgg_addParticipant($widget, $cid, $result);
    if (!$rc) {
        return _error($so, 'server_error');
        }
    $url = elgg_get_plugin_setting('room_server', 'babelroom') . '/i/' . $cid . '?t=' . $result->token;
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
            return do_join($_REQUEST['widget_guid']);
        }
    return true;
}

/* --- */
function babelroom_object_handler($event, $object_type, $object) {
    $rc = TRUE;
    if (isset($object->babelroom_height)) {
        switch($event) {
            case 'update':
                $rc = BRAPI_elgg_update($object);
                break;
/* no longer used ...
            case 'delete':
                $rc = BRAPI_delete($object);
                break; */
            }
        }
    if (!$rc) {
        register_error(elgg_echo('babelroom:errors:server_error'.$msg));
        }
    return $rc;
}
 
elgg_register_event_handler('init', 'system', 'babelroom_init');       
elgg_register_event_handler('all', 'object', 'babelroom_object_handler');       

