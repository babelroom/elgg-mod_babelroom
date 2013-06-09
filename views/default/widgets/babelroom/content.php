<?php

?>
<div><center><h3><?php echo $vars['entity']->babelroom_name; ?></h3></center></div>
<p />
<div><?php echo $vars['entity']->babelroom_description; ?></div>
<p />

<?php

echo '<center><form action="/babelroom/join" method="GET">';    # could also POST
echo elgg_view('input/hidden', array(
    'name' => 'widget_guid',
    'value' => $vars['entity']->getGUID(),
));
echo elgg_view('input/submit', array('value' => elgg_echo('babelroom:widgets:view:join')));
echo '</form></center>';

?>
<p />

