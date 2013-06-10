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
        'name' => $conference->babelroom_name,
        'introduction' => $conference->babelroom_description,
        'origin_data' => "Elgg/" . get_version(true),
        'origin_id' => $conference->getGUID(),
        );
    $result = null;
    if (!_do_api_call('POST', '/api/v1/conferences', $params, $result) || empty($result->data) || empty($result->data->id))
        return false;
    $conference->babelroom_id = $result->data->id;
    $conference->babelroom_url = '/i/'.$result->data->id;
    return true;
}

function BRAPI_update($conference) {
    $params = array('name' => $conference->babelroom_name, 'introduction' => $conference->babelroom_description);
    $result = null;
    return _do_api_call('PUT', '/api/v1/conferences/'.$conference->babelroom_id, $params, $result);
}

function BRAPI_delete($conference) {
    $result = null;
    return _do_api_call('DELETE', '/api/v1/conferences/'.$conference->babelroom_id, array(), $result);
}

