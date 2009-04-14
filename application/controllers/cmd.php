<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * CMD controller
 * Requires authentication
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Cmd_Controller extends Authenticated_Controller {
	const CMD_NONE = 0;

	const CMD_ADD_HOST_COMMENT = 1;
	const CMD_DEL_HOST_COMMENT = 2;

	const CMD_ADD_SVC_COMMENT = 3;
	const CMD_DEL_SVC_COMMENT = 4;

	const CMD_ENABLE_SVC_CHECK = 5;
	const CMD_DISABLE_SVC_CHECK = 6;

	const CMD_SCHEDULE_SVC_CHECK = 7;

	const CMD_DELAY_SVC_NOTIFICATION = 9;

	const CMD_DELAY_HOST_NOTIFICATION = 10;

	const CMD_DISABLE_NOTIFICATIONS = 11;
	const CMD_ENABLE_NOTIFICATIONS = 12;

	const CMD_RESTART_PROCESS = 13;
	const CMD_SHUTDOWN_PROCESS = 14;

	const CMD_ENABLE_HOST_SVC_CHECKS =  15;
	const CMD_DISABLE_HOST_SVC_CHECKS = 16;

	const CMD_SCHEDULE_HOST_SVC_CHECKS = 17;

	const CMD_DELAY_HOST_SVC_NOTIFICATIONS = 19;  /* currently unimplemented */

	const CMD_DEL_ALL_HOST_COMMENTS = 20;
	const CMD_DEL_ALL_SVC_COMMENTS = 21;

	const CMD_ENABLE_SVC_NOTIFICATIONS = 22;
	const CMD_DISABLE_SVC_NOTIFICATIONS = 23;
	const CMD_ENABLE_HOST_NOTIFICATIONS = 24;
	const CMD_DISABLE_HOST_NOTIFICATIONS = 25;
	const CMD_ENABLE_ALL_NOTIFICATIONS_BEYOND_HOST = 26;
	const CMD_DISABLE_ALL_NOTIFICATIONS_BEYOND_HOST = 27;
	const CMD_ENABLE_HOST_SVC_NOTIFICATIONS = 28;
	const CMD_DISABLE_HOST_SVC_NOTIFICATIONS = 29;

	const CMD_PROCESS_SERVICE_CHECK_RESULT = 30;

	const CMD_SAVE_STATE_INFORMATION = 31;
	const CMD_READ_STATE_INFORMATION = 32;

	const CMD_ACKNOWLEDGE_HOST_PROBLEM = 33;
	const CMD_ACKNOWLEDGE_SVC_PROBLEM = 	34;

	const CMD_START_EXECUTING_SVC_CHECKS = 35;
	const CMD_STOP_EXECUTING_SVC_CHECKS = 36;

	const CMD_START_ACCEPTING_PASSIVE_SVC_CHECKS = 37;
	const CMD_STOP_ACCEPTING_PASSIVE_SVC_CHECKS = 38;

	const CMD_ENABLE_PASSIVE_SVC_CHECKS = 39;
	const CMD_DISABLE_PASSIVE_SVC_CHECKS = 40;

	const CMD_ENABLE_EVENT_HANDLERS = 41;
	const CMD_DISABLE_EVENT_HANDLERS = 42;

	const CMD_ENABLE_HOST_EVENT_HANDLER = 43;
	const CMD_DISABLE_HOST_EVENT_HANDLER = 44;

	const CMD_ENABLE_SVC_EVENT_HANDLER = 45;
	const CMD_DISABLE_SVC_EVENT_HANDLER = 46;

	const CMD_ENABLE_HOST_CHECK = 47;
	const CMD_DISABLE_HOST_CHECK = 48;

	const CMD_START_OBSESSING_OVER_SVC_CHECKS = 49;
	const CMD_STOP_OBSESSING_OVER_SVC_CHECKS = 50;

	const CMD_REMOVE_HOST_ACKNOWLEDGEMENT = 51;
	const CMD_REMOVE_SVC_ACKNOWLEDGEMENT = 52;

	const CMD_SCHEDULE_FORCED_HOST_SVC_CHECKS = 53;
	const CMD_SCHEDULE_FORCED_SVC_CHECK = 54;

	const CMD_SCHEDULE_HOST_DOWNTIME = 55;
	const CMD_SCHEDULE_SVC_DOWNTIME = 56;

	const CMD_ENABLE_HOST_FLAP_DETECTION = 57;
	const CMD_DISABLE_HOST_FLAP_DETECTION = 58;

	const CMD_ENABLE_SVC_FLAP_DETECTION = 59;
	const CMD_DISABLE_SVC_FLAP_DETECTION = 60;

	const CMD_ENABLE_FLAP_DETECTION = 61;
	const CMD_DISABLE_FLAP_DETECTION = 62;

	const CMD_ENABLE_HOSTGROUP_SVC_NOTIFICATIONS = 63;
	const CMD_DISABLE_HOSTGROUP_SVC_NOTIFICATIONS = 64;

	const CMD_ENABLE_HOSTGROUP_HOST_NOTIFICATIONS = 65;
	const CMD_DISABLE_HOSTGROUP_HOST_NOTIFICATIONS = 66;

	const CMD_ENABLE_HOSTGROUP_SVC_CHECKS = 67;
	const CMD_DISABLE_HOSTGROUP_SVC_CHECKS = 68;

	const CMD_CANCEL_HOST_DOWNTIME = 69; /* not internally implemented */
	const CMD_CANCEL_SVC_DOWNTIME = 70; /* not internally implemented */

	const CMD_CANCEL_ACTIVE_HOST_DOWNTIME = 71; /* old - no longer used */
	const CMD_CANCEL_PENDING_HOST_DOWNTIME = 72; /* old - no longer used */

	const CMD_CANCEL_ACTIVE_SVC_DOWNTIME = 73; /* old - no longer used */
	const CMD_CANCEL_PENDING_SVC_DOWNTIME = 74; /* old - no longer used */

	const CMD_CANCEL_ACTIVE_HOST_SVC_DOWNTIME = 75; /* unimplemented */
	const CMD_CANCEL_PENDING_HOST_SVC_DOWNTIME = 76; /* unimplemented */

	const CMD_FLUSH_PENDING_COMMANDS = 77;

	const CMD_DEL_HOST_DOWNTIME = 78;
	const CMD_DEL_SVC_DOWNTIME = 79;

	const CMD_ENABLE_FAILURE_PREDICTION = 80;
	const CMD_DISABLE_FAILURE_PREDICTION = 81;

	const CMD_ENABLE_PERFORMANCE_DATA = 82;
	const CMD_DISABLE_PERFORMANCE_DATA = 83;

	const CMD_SCHEDULE_HOSTGROUP_HOST_DOWNTIME = 84;
	const CMD_SCHEDULE_HOSTGROUP_SVC_DOWNTIME = 85;
	const CMD_SCHEDULE_HOST_SVC_DOWNTIME = 86;

/* new commands in Nagios 2.x found below... */
	const CMD_PROCESS_HOST_CHECK_RESULT = 87;

	const CMD_START_EXECUTING_HOST_CHECKS = 88;
	const CMD_STOP_EXECUTING_HOST_CHECKS = 89;

	const CMD_START_ACCEPTING_PASSIVE_HOST_CHECKS = 90;
	const CMD_STOP_ACCEPTING_PASSIVE_HOST_CHECKS = 91;

	const CMD_ENABLE_PASSIVE_HOST_CHECKS = 92;
	const CMD_DISABLE_PASSIVE_HOST_CHECKS = 93;

	const CMD_START_OBSESSING_OVER_HOST_CHECKS = 94;
	const CMD_STOP_OBSESSING_OVER_HOST_CHECKS = 95;

	const CMD_SCHEDULE_HOST_CHECK = 96;
	const CMD_SCHEDULE_FORCED_HOST_CHECK = 98;

	const CMD_START_OBSESSING_OVER_SVC = 99;
	const CMD_STOP_OBSESSING_OVER_SVC = 100;

	const CMD_START_OBSESSING_OVER_HOST = 101;
	const CMD_STOP_OBSESSING_OVER_HOST = 102;

	const CMD_ENABLE_HOSTGROUP_HOST_CHECKS = 103;
	const CMD_DISABLE_HOSTGROUP_HOST_CHECKS = 104;

	const CMD_ENABLE_HOSTGROUP_PASSIVE_SVC_CHECKS = 105;
	const CMD_DISABLE_HOSTGROUP_PASSIVE_SVC_CHECKS = 106;

	const CMD_ENABLE_HOSTGROUP_PASSIVE_HOST_CHECKS = 107;
	const CMD_DISABLE_HOSTGROUP_PASSIVE_HOST_CHECKS = 108;

	const CMD_ENABLE_SERVICEGROUP_SVC_NOTIFICATIONS = 109;
	const CMD_DISABLE_SERVICEGROUP_SVC_NOTIFICATIONS = 110;

	const CMD_ENABLE_SERVICEGROUP_HOST_NOTIFICATIONS = 111;
	const CMD_DISABLE_SERVICEGROUP_HOST_NOTIFICATIONS = 112;

	const CMD_ENABLE_SERVICEGROUP_SVC_CHECKS = 113;
	const CMD_DISABLE_SERVICEGROUP_SVC_CHECKS = 114;

	const CMD_ENABLE_SERVICEGROUP_HOST_CHECKS = 115;
	const CMD_DISABLE_SERVICEGROUP_HOST_CHECKS = 116;

	const CMD_ENABLE_SERVICEGROUP_PASSIVE_SVC_CHECKS = 117;
	const CMD_DISABLE_SERVICEGROUP_PASSIVE_SVC_CHECKS = 118;

	const CMD_ENABLE_SERVICEGROUP_PASSIVE_HOST_CHECKS = 119;
	const CMD_DISABLE_SERVICEGROUP_PASSIVE_HOST_CHECKS = 120;

	const CMD_SCHEDULE_SERVICEGROUP_HOST_DOWNTIME = 121;
	const CMD_SCHEDULE_SERVICEGROUP_SVC_DOWNTIME = 122;

	const CMD_CHANGE_GLOBAL_HOST_EVENT_HANDLER = 123;
	const CMD_CHANGE_GLOBAL_SVC_EVENT_HANDLER = 124;

	const CMD_CHANGE_HOST_EVENT_HANDLER = 125;
	const CMD_CHANGE_SVC_EVENT_HANDLER = 126;

	const CMD_CHANGE_HOST_CHECK_COMMAND = 127;
	const CMD_CHANGE_SVC_CHECK_COMMAND = 128;

	const CMD_CHANGE_NORMAL_HOST_CHECK_INTERVAL = 129;
	const CMD_CHANGE_NORMAL_SVC_CHECK_INTERVAL = 130;
	const CMD_CHANGE_RETRY_SVC_CHECK_INTERVAL = 131;

	const CMD_CHANGE_MAX_HOST_CHECK_ATTEMPTS = 132;
	const CMD_CHANGE_MAX_SVC_CHECK_ATTEMPTS = 133;

	const CMD_SCHEDULE_AND_PROPAGATE_TRIGGERED_HOST_DOWNTIME = 134;

	const CMD_ENABLE_HOST_AND_CHILD_NOTIFICATIONS = 135;
	const CMD_DISABLE_HOST_AND_CHILD_NOTIFICATIONS = 136;

	const CMD_SCHEDULE_AND_PROPAGATE_HOST_DOWNTIME = 137;

	const CMD_ENABLE_SERVICE_FRESHNESS_CHECKS = 138;
	const CMD_DISABLE_SERVICE_FRESHNESS_CHECKS = 139;

	const CMD_ENABLE_HOST_FRESHNESS_CHECKS = 140;
	const CMD_DISABLE_HOST_FRESHNESS_CHECKS = 141;

	const CMD_SET_HOST_NOTIFICATION_NUMBER = 142;
	const CMD_SET_SVC_NOTIFICATION_NUMBER = 143;

/* new commands in Nagios 3.x found below... */
	const CMD_CHANGE_HOST_CHECK_TIMEPERIOD = 144;
	const CMD_CHANGE_SVC_CHECK_TIMEPERIOD = 145;

	const CMD_PROCESS_FILE = 146;

	const CMD_CHANGE_CUSTOM_HOST_VAR = 147;
	const CMD_CHANGE_CUSTOM_SVC_VAR = 148;
	const CMD_CHANGE_CUSTOM_CONTACT_VAR = 149;

	const CMD_ENABLE_CONTACT_HOST_NOTIFICATIONS = 150;
	const CMD_DISABLE_CONTACT_HOST_NOTIFICATIONS = 151;
	const CMD_ENABLE_CONTACT_SVC_NOTIFICATIONS = 152;
	const CMD_DISABLE_CONTACT_SVC_NOTIFICATIONS = 153;

	const CMD_ENABLE_CONTACTGROUP_HOST_NOTIFICATIONS = 154;
	const CMD_DISABLE_CONTACTGROUP_HOST_NOTIFICATIONS = 155;
	const CMD_ENABLE_CONTACTGROUP_SVC_NOTIFICATIONS = 156;
	const CMD_DISABLE_CONTACTGROUP_SVC_NOTIFICATIONS = 157;

	const CMD_CHANGE_RETRY_HOST_CHECK_INTERVAL = 158;

	const CMD_SEND_CUSTOM_HOST_NOTIFICATION = 159;
	const CMD_SEND_CUSTOM_SVC_NOTIFICATION = 160;

	const CMD_CHANGE_HOST_NOTIFICATION_TIMEPERIOD = 161;
	const CMD_CHANGE_SVC_NOTIFICATION_TIMEPERIOD = 162;
	const CMD_CHANGE_CONTACT_HOST_NOTIFICATION_TIMEPERIOD = 163;
	const CMD_CHANGE_CONTACT_SVC_NOTIFICATION_TIMEPERIOD = 164;

	const CMD_CHANGE_HOST_MODATTR = 165;
	const CMD_CHANGE_SVC_MODATTR = 166;
	const CMD_CHANGE_CONTACT_MODATTR = 167;
	const CMD_CHANGE_CONTACT_MODHATTR = 168;
	const CMD_CHANGE_CONTACT_MODSATTR = 169;

/* custom command introduced in Nagios 3.x */
	const CMD_CUSTOM_COMMAND = 999;

	/**
	*	@name __construct()
	*	@desc Constructor
	*
	*/
	public function __construct()
	{
		parent::__construct();
	}

	/**
	*	@name command
	*	@desc
	*
	*/
	public function command($cmd_typ=false, $host_name=false, $service=false, $force=false)
	{
		$cmd_typ = (int)$cmd_typ;
		$host_name = trim($host_name);

		# decode service description if set
		$service = !empty($service) ? link::decode($service) : false;
		echo $cmd_typ.', '.$host_name.'; '.$service;
	}

	/**
	*	@name 	unauthorized
	*	@desc	Display message to user when they lack proper
	* 			credentials to issue a command
	*/
	public function unauthorized()
	{
		$this->template->content = $this->add_view('cmd/unauthorized');
		$this->template->content->error_message = $this->translate->_('Sorry, but you are not authorized to commit the specified command.');
		$this->template->content->error_description = $this->translate->_('Read the section of the documentation that deals with authentication and authorization in the CGIs for more information.');
		$this->template->content->return_link_lable = $this->translate->_('Return from whence you came');
	}

	/**
	*	@name	use_authentication_off
	*	@desc	Show info to user when use_authentication
	* 			is disabled in cgi.cfg.
	*
	*/
	public function use_authentication_off()
	{
		$this->template->content = $this->add_view('cmd/use_authentication_off');
		$this->template->content->error_msg = $this->translate->_('Error: Authentication is not enabled!');
		$this->template->content->error_description = $this->translate->_("As a safety precaution, commands aren't allowed when authentication is turned off.");
	}
}