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

	$server .= "<tr>";
	$server .= "<td>" . elgg_echo("babelroom:settings:server:api_server") . ":</td>";
	$server .= "</tr><tr>";
    $server .= "<td>" . elgg_view("input/text", array("name" => "params[api_server]", "value" => $plugin->api_server)) . "</td>";
	$server .= "</tr><tr>";
	$server .= "<td>" . elgg_echo("babelroom:settings:default") . ": " . BABELROOM_DEFAULT_API_SERVER . "</td>";
	$server .= "</tr><tr>";
	$server .= '<td><span class="elgg-text-help">' . elgg_echo("babelroom:settings:server:api_server_description") . "</span></td>";
	$server .= "</tr>";
	$server .= "</table>";

	echo elgg_view_module("inline", elgg_echo("babelroom:settings:server"), $server);

