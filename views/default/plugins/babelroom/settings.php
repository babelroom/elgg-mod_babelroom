<?php 

	$plugin = $vars["entity"];

	$api_key  = "<table>";
	$api_key .= "<tr>";
	$api_key .= "<td>" . elgg_echo("babelroom:settings:api_key:api_key") . ":</td>";
	$api_key .= "</tr><tr>";
    $api_key .= "<td>" . elgg_view("input/text", array("name" => "params[api_key]", "value" => $plugin->api_key)) . "</td>";
	$api_key .= "</tr><tr>";
	$api_key .= "<td>" . elgg_echo("babelroom:settings:default") . ": " . BABELROOM_DEFAULT_API_KEY . "</td>";
	$api_key .= "</tr><tr>";
	$api_key .= '<td><span class="elgg-text-help">' . elgg_echo("babelroom:settings:api_key:api_key_description") . "</span></td>";
	$api_key .= "</tr>";
	$api_key .= "</table>";

	echo elgg_view_module("inline", elgg_echo("babelroom:settings:api_key"), $api_key);

	$servers  = "<table>";
	$servers .= "<tr>";
	$servers .= "<td>" . elgg_echo("babelroom:settings:servers:room_server") . ":</td>";
	$servers .= "</tr><tr>";
    $servers .= "<td>" . elgg_view("input/text", array("name" => "params[room_server]", "value" => $plugin->room_server)) . "</td>";
	$servers .= "</tr><tr>";
	$servers .= "<td>" . elgg_echo("babelroom:settings:default") . ": " . BABELROOM_DEFAULT_ROOM_SERVER . "</td>";
	$servers .= "</tr><tr>";
	$servers .= '<td><span class="elgg-text-help">' . elgg_echo("babelroom:settings:servers:room_server_description") . "</span></td>";
	$servers .= "</tr><tr>";
	$servers .= '<td>&nbsp;</td></tr>';

	$servers .= "<tr>";
	$servers .= "<td>" . elgg_echo("babelroom:settings:servers:api_server") . ":</td>";
	$servers .= "</tr><tr>";
    $servers .= "<td>" . elgg_view("input/text", array("name" => "params[api_server]", "value" => $plugin->api_server)) . "</td>";
	$servers .= "</tr><tr>";
	$servers .= "<td>" . elgg_echo("babelroom:settings:default") . ": " . BABELROOM_DEFAULT_API_SERVER . "</td>";
	$servers .= "</tr><tr>";
	$servers .= '<td><span class="elgg-text-help">' . elgg_echo("babelroom:settings:servers:api_server_description") . "</span></td>";
	$servers .= "</tr>";
	$servers .= "</table>";

	echo elgg_view_module("inline", elgg_echo("babelroom:settings:servers"), $servers);

