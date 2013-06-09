<?php

require_once($CONFIG->pluginspath . "babelroom/vendors/BabelRoomV1API/API.php");

if (!isset($vars['entity']->babelroom_id)) {
    $vars['entity']->babelroom_name = "My New Room";
    $vars['entity']->babelroom_description = "Description for \"My New Room\"";
    BRAPI_create($vars['entity']);
    }
?>
<div>
    <?php echo elgg_echo("babelroom:widgets:edit:name"); ?><br />
    <?php echo elgg_view("input/text", array("name" => "params[babelroom_name]", "value" => $vars['entity']->babelroom_name)); ?>
</div>
<div>
    <?php echo elgg_echo("babelroom:widgets:edit:description"); ?><br />
    <?php echo elgg_view("input/text"/*"/longtext"*/, array("name" => "params[babelroom_description]", "value" => $vars['entity']->babelroom_description)); ?>
</div>

