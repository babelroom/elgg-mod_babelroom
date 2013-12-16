<?php

require_once($CONFIG->pluginspath . "babelroom/vendors/BabelRoomV1API/API.php");

?>
<?php

$id = BRAPI_elgg_getOrCreateConference($vars['entity']);
if ($id) {
    $ht = '<p>';
    $ht .= '<h4>' . elgg_echo("babelroom:widgets:babelroom:edit:conference_settings") . '</h4>';
    $ht .= '<div>';
    $ht .= elgg_echo("babelroom:widgets:babelroom:edit:subwidget");
    $ht .= '</div><div>';
    $ht .= elgg_view("input/dropdown", array("name" => "params[babelroom_subwidget]",
        "options_values" => array(
            "group_chat" => elgg_echo('babelroom:widgets:babelroom:type:group_chat'),
            "conference" => elgg_echo('babelroom:widgets:babelroom:type:conference'),
            "p2p_chat" => elgg_echo('babelroom:widgets:babelroom:type:p2p_chat'),
            "presentation" => elgg_echo('babelroom:widgets:babelroom:type:presentation'),
            ),
        "value" => $vars['entity']->babelroom_subwidget));
    $ht .= '</div><div>';
    $ht .= elgg_echo("babelroom:widgets:babelroom:edit:reset");
    $ht .= '</div><div>';
    $ht .= elgg_view("input/checkbox", array("name" => "params[babelroom_reset]", "value" => $vars['entity']->babelroom_reset));
    $ht .= '</div>';
    echo $ht;
}

if (!isset($vars['entity']->babelroom_height)) {
    /* defaults */
    $vars['entity']->babelroom_height = "365";
    $vars['entity']->babelroom_target = "_blank";
    $vars['entity']->babelroom_subwidget = "group_chat";    # default subwidget
    }

$ht = '';
$ht .= '<h4>' . elgg_echo("babelroom:widgets:babelroom:edit:widget_settings") . '</h4>';
$ht .= elgg_echo("babelroom:widgets:babelroom:edit:target");
$ht .= elgg_view("input/text", array("name" => "params[babelroom_target]", "value" => $vars['entity']->babelroom_target));
$ht .= elgg_echo("babelroom:widgets:babelroom_chat:edit:height");
$ht .= elgg_view("input/text", array("name" => "params[babelroom_height]", "value" => $vars['entity']->babelroom_height));
echo $ht;

