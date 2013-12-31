<?php

////////////////////////////////////////////////////////////////////////////////
// BabelRoom API calls                                                        //
////////////////////////////////////////////////////////////////////////////////

/* returns bool(y) */
function _do_api_call($verb, $url, $data, &$result) { 
    $api_key = elgg_get_plugin_setting('api_key', 'babelroom');
    $api_server = elgg_get_plugin_setting('api_server', 'babelroom');
    $server_url = $api_server.$url;
    $rc = false;

    if (!extension_loaded('curl'))
      return false;

    if (
        !(stripos(ini_get('disable_functions'), 'curl_init') !== FALSE) and
        ($ch = @curl_init($server_url)) !== false) {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $verb);                                                                     
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
#        curl_setopt($ch, CURLOPT_HEADER, false); -- for later reference
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $api_key.':');
        if ($data) {
            $data_string = json_encode($data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string)));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
            }
        $tmp_result = curl_exec($ch);
        if (!curl_errno($ch)) {
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($code>=200 and $code<=299) {
                $result = json_decode($tmp_result);
                $rc = true;
                }
            else {
                elgg_log("BRAPI error response code $code, [$server_url]",'ERROR');
                }
            }
        else {
            elgg_log("BRAPI connect error [$server_url]",'ERROR');
            }
        curl_close($ch);
        }
    else {
        elgg_log("BRAPI curl initialization error [$server_url]",'ERROR');
        }

    return $rc;
}

/* status check -- this is currently unused */
function api_status()
{
    $result;
    return _do_api_call('GET', '/api/v1/status', null, $result);
}

/* --- */
function BRAPI_create_invitation($babelroom_id, $user, $avatar_url, $is_host, &$result)
{
    $params = array(
        'return_token' => true,
        'name' => $user['first'],
        'user' => array(
            'name' => $user['first'],
            'last_name' => $user['last'],
            'email' => $user['email'],
            'origin_data' => 'ElggUser(Guid)',
            'origin_id' => $user['id'],
/* other fields of interest ... (in user)
            'phone' => _util_canon_phone($user->phone1),
        enabled
        language
        banned
*/
#            'language' => $user['language'], -- no, server barfs @ 2.37
            ),
        'avatar_url' => $avatar_url,
        'invitation' => array(
            'role' => (($is_host) ? 'Host': null),
            ),
        );
    return _do_api_call('POST', '/api/v1/add_participant/i/'.$babelroom_id, $params, $result);
}

function BRAPI_create(&$conference) {
    $params = array(
        'name' => $conference['name'],
        'introduction' => $conference['description'],
        'origin_data' => "Elgg/" . get_version(true) . "/owner(Guid)",
        'origin_id' => $conference['owner_guid'],
        );
    $result = null;
    if (!_do_api_call('POST', '/api/v1/conferences', $params, $result) || empty($result->data) || empty($result->data->id))
        return false;
    $conference['id'] = $result->data->id;
    return true;
}

/* depreciated -- and no longer compatible
function BRAPI_update($conference) {
    $params = array('name' => $conference->babelroom_name, 'introduction' => $conference->babelroom_description);
    $result = null;
    return _do_api_call('PUT', '/api/v1/conferences/'.$conference->babelroom_id, $params, $result);
}

function BRAPI_delete($conference) {
    $result = null;
    return _do_api_call('DELETE', '/api/v1/conferences/'.$conference->babelroom_id, array(), $result);
}
*/

/* --- higher-level utils --- */
define("BR_MD_KEY", "babelroom_conference_metadata_key");
function BRAPI_elgg_getConference($widget){
    global $_br_conference_id;
    if ($_br_conference_id!==null) {
        return $_br_conference_id;  /* we have it (>0), or we tried and failed (==0) */
        }
    $_br_conference_id = 0; /* we attempted to retrieve it */
    $owner_guid = $widget->getOwnerGUID();
    if (empty($owner_guid)) {
        register_error(elgg_echo('babelroom:errors:unexpected_internal_error',array(__LINE__)));
        return 0;
        }
    $md = elgg_get_metadata(array("metadata_names"=>BR_MD_KEY, "guid"=>$owner_guid));
    if (!empty($md) && count($md) && !empty($md[0]) && isset($md[0]->value) && ($md[0]->value)!=0) { /* overkill? */
        $_br_conference_id = $md[0]->value;
        return $_br_conference_id;
        }
    return -1;
}

function BRAPI_elgg_getOrCreateConference($widget){
    global $_br_conference_id;
    if (BRAPI_elgg_getConference($widget)>=0)
        return $_br_conference_id;
    $owner_guid = $widget->getOwnerGUID();
    $tmp_conference = array(
        'name' => "My New Room",
        'description' => "Description for \"My New Room\"",
        'owner_guid' => $owner_guid,
        );
    if (BRAPI_create($tmp_conference)) {
        system_messages(elgg_echo('babelroom:messages:new_conference', array($tmp_conference['id'])));
        } 
    else {
        register_error(elgg_echo('babelroom:errors:server_error'));
        return 0;
        }
    if (!create_metadata($owner_guid, BR_MD_KEY, $tmp_conference['id'], '', $owner_guid, ACCESS_PUBLIC)) {
        /* returns false on failure, md id on success */
        register_error(elgg_echo('babelroom:errors:unexpected_internal_error',array(__LINE__)));
        return 0;
        }
    return ($_br_conference_id = $tmp_conference['id']);
}

function BRAPI_elgg_update($widget) {
    if(isset($widget->babelroom_reset)) {
        global $_br_conference_id;
        BRAPI_elgg_getConference($widget);  /* put conference id in $_br_conference_id */
        if ($_br_conference_id>0) {
            if (!_do_api_call('DELETE', '/api/v1/conferences/'.$_br_conference_id, array(), $result)) {
                register_error(elgg_echo('babelroom:errors:server_error'));
                /* return false; -- continue any anyhow, possibly leaving an unused conference as cruft */
                }
            else
                system_messages(elgg_echo('babelroom:messages:deleted_conference', array($_br_conference_id)));
            }
        $owner_guid = $widget->getOwnerGUID();
        elgg_delete_metadata(array("metadata_names"=>"babelroom_conference_metadata_key", "guid"=>$owner_guid));
        $_br_conference_id = null;          /* set back to null so subsequent actions will create a new conference */
        unset($widget->babelroom_reset);    /* don't save the reset flag */
        }
    return true;
}

function BRAPI_elgg_addParticipant($widget, $babelroom_id, &$result) {
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

    return BRAPI_create_invitation($babelroom_id, $user, $icon, $is_host, $result);
}

