<?php

?>
<div><center><h3><?php echo $vars['entity']->babelroom_name; ?></h3></center></div>
<p />
<div><?php echo $vars['entity']->babelroom_description; ?></div>
<p />

<?php

$target = '';
if (strlen($vars['entity']->babelroom_target)) {
    # trim helps if a user inadvertly left leading or trailing spaces .. difficult to see, but will break target
    $target = ' target="' . trim($vars['entity']->babelroom_target) . '"';
    }

echo '<center><form action="' . elgg_get_site_url() . 'babelroom/join" method="GET"' . $target . '>';    # could also POST
echo elgg_view('input/hidden', array(
    'name' => 'widget_guid',
    'value' => $vars['entity']->getGUID(),
));
echo elgg_view('input/submit', array('value' => elgg_echo('babelroom:widgets:view:join')));
echo '</form></center>';

?>
<p />

