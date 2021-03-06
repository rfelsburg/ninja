<?php
print "<h2>" . $object->get_readable_name() . "</h2>";
if ($result['status']) {
	echo '<div class="alert notice">'.html::specialchars($result['output']);
	echo "</div>\n";
	$this->footer = '<input style="margin-left: 12px" type="button" value="Done" onclick="history.go(-2)" />'."\n";
} else {
	echo '<div class="alert error">'.sprintf(_('There was an error submitting your command to %s.'), Kohana::config('config.product_name'));
	if (!empty($result['output'])) {
		echo '<br /><br />'._('ERROR').': '.html::specialchars($result['output']);
	}
	echo "</div>\n";
	$this->footer = '<input style="margin-left: 12px" type="button" value="Back" onclick="history.go(-1)" />'."\n";
}
