<?php

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
	$settings = new admin_settingpage('local_catman', get_string('pluginname', 'local_catman'));
	$ADMIN->add('localplugins', $settings);

	$settings->add(new admin_setting_configcheckbox(
		'local_catman/enable',
		get_string('enable', 'local_catman'),
		'',
		0
	));

	$settings->add(new admin_setting_configtext(
		'local_catman/limit',
		get_string('limit', 'local_catman'),
		'',
		25,
		PARAM_INT
	));

	$settings->add(new admin_setting_configtext(
		'local_catman/catid',
		get_string('catid', 'local_catman'),
		'',
		0,
		PARAM_INT
	));
}
