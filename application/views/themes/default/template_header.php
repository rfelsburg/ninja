<div class="header" id="header">
	<div class="supermenu">
		<ul>

			<!-- Classes are used by javascript navigation -->

			<li class="supermenu-button" id="about-button" title="About">
				<span class="icon-24 x24-info"></span>
			</li>
			<li class="supermenu-button" id="monitoring-button" title="Monitoring">
				<span class="icon-24 x24-link"></span>
			</li>
			<li class="supermenu-button" id="reporting-button" title="Reporting">
				<span class="icon-24 x24-news"></span>
			</li>
			<li class="supermenu-button" id="configuration-button" title="Configuration">
				<span class="icon-24 x24-settings"></span>
			</li>

		</ul>
	</div>
	<?php echo html::image('application/views/themes/default/icons/icon.png', array('style' => 'float: left; margin: 20px 0 0 20px;')); ?>
	<div class="headercontent">

		<?php echo _('Welcome'); ?> <?php echo user::session('username') ?> | <?php echo html::anchor('default/logout', html::specialchars(_('Log out'))) ?><br />

		
		<a onclick="window.location.reload()" class="image-link">
			<span title="Refresh" class="icon-24 x24-refresh"></span>
		</a>
		<a onclick="show_info()" class="image-link">
			<span title="Version Info." class="icon-24 x24-info"></span>
		</a>
		<a class="image-link">
			<span title="Settings" id="settings_icon" class="icon-24 x24-settings"></span>

			
		</a>
		
		<a target="_blank" href="<?php echo '//'.$_SERVER['HTTP_HOST'].'/ninja/css/default/index.html'; ?>" class="header-action">
			<span title="DOJO Dev. Information" class="icon-24 x24-link"></span>
		</a>

		<?php
			$all_host_status_types = nagstat::HOST_PENDING|nagstat::HOST_UP|nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE;
			echo html::anchor('/status/service/all?servicestatustypes='.(nagstat::SERVICE_WARNING|nagstat::SERVICE_CRITICAL|nagstat::SERVICE_UNKNOWN|nagstat::SERVICE_PENDING).'&hostprops='.(nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED).'&service_props='.(nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED).'&hoststatustypes='.$all_host_status_types,
				'<span class="icon-24 x24-shield-warning"></span>', array('title' => 'Unhandled Problems', 'class' => 'image-link'));
		?>
	</div>
</div>

<div id="version_info">
	<ul>
		<li>
		<?php echo  Kohana::config('config.product_name') . ":" . config::get_version_info(); ?>
		</li>
	</ul>
</div>

<div id="page_settings">
	<ul>
		<li id="menu_global_settings" <?php	if (isset($disable_refresh) && $disable_refresh !== false) { ?> style="display:none"<?php } ?>><?php echo _('Global Settings') ?></li>
		<li id="noheader_ctrl" style="display:none">
			<input type="checkbox" id="noheader_chbx" value="1" /><label id="noheader_label" for="noheader_chbx"> <?php echo _('Hide page header')?></label>
		</li>
		<!--<li id="ninja_use_noc">
			<input type="checkbox" id="ninja_noc_control" />
			<label id="ninja_noc_lable" for="ninja_noc_control"> <?php echo _('Use noc (experimental)') ?></label>
		</li>-->
	<?php	if (!isset($disable_refresh) || $disable_refresh === false) { ?>
		<li id="ninja_page_refresh">
			<input type="checkbox" id="ninja_refresh_control" />
			<label id="ninja_refresh_lable" for="ninja_refresh_control"> <?php echo _('Pause refresh') ?></label>
		</li>
		<li id="ninja_refresh_edit">
			<?php echo _('Edit global refresh rate') ?><br />
			<div id="ninja_page_refresh_slider" style="width:200px; margin-top: 8px;">
				<input type="text" maxlength="3" size="3" id="ninja_page_refresh_value" name="ninja_page_refresh_value" style="position: absolute; font-size: 11px; margin-left: 160px; padding: 1px; margin-top:-25px;z-index: 500" /> <div style="position: absolute; margin-left: 192px; margin-top: -23px"></div>
			</div>
		</li>

		<?php
			} # end if disable_refresh

			if (isset($widgets) && is_array($widgets)) {
				echo '<li>'._('Available Widgets').'</li>'."\n";
				foreach($widgets as $widget) {
					$class_name = isset($widget->id) ? 'selected' : 'unselected';
					echo '<li id="li-'.$widget->name.'-'.$widget->instance_id.'" data-name="'.$widget->name.'" data-instance_id="'.$widget->instance_id.'" class="'.$class_name.' widget-selector" onclick="control_widgets(this)">'.$widget->friendly_name.'</li>'."\n";
				}
				echo '<li onclick="restore_widgets();">'._('Restore overview to factory settings').'</li>'."\n";
				if ($authorized === true) {
					echo '<li onclick="widget_upload();">'._('Upload new widget').'</li>'."\n";
				}
				echo '<li id="show_global_widget_refresh">'._('Set widget refresh rate (s.)').'</li>'."\n";
			}
		?>
	</ul>
</div>
