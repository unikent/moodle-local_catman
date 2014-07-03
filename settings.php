<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add('reports', new admin_externalpage(
        'reportcatmanreport',
        get_string('pluginname', 'local_catman'),
        "$CFG->wwwroot/local/catman/index.php",
        'moodle/site:config'
    ));

    $settings = new admin_settingpage('local_catman', get_string('pluginname', 'local_catman'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configcheckbox(
        'local_catman/enable',
        get_string('enable', 'local_catman'),
        '',
        0
    ));

    $settings->add(new admin_setting_configtext(
        'local_catman/catid',
        get_string('catid', 'local_catman'),
        '',
        0,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'local_catman/period',
        get_string('period', 'local_catman'),
        '',
        1209600,
        PARAM_INT
    ));
}
