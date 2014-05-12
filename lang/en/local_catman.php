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

/**
 * Strings for the category manager
 *
 * @package    local_catman
 * @copyright  2014 University of Kent
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Category Manager';
$string['plugindesc'] = 'The category manager was created by UKC to enable the automatic management of the \'removed\' category Moodle places deleted course into prior to purge.';

$string['table_info'] = 'This is a list of courses that will shortly be deleted permanently from Moodle. If you have an issue with anything on the list please get in touch with a developer.';

$string['enable'] = 'Enable the category manager';
$string['limit'] = 'Limits how many items are deleted per cron run';
$string['catid'] = 'The ID of the removed category';
$string['period'] = 'Number of seconds between delete and purge';

$string['course'] = 'Course';
$string['date_deleted'] = 'Date Deleted';
$string['date_scheduled'] = 'Purge Due Date';
$string['status'] = 'Status';

$string['status_0'] = 'Scheduled';
$string['status_1'] = 'Purged';
$string['status_2'] = 'Errored';

$string['delay'] = 'Delay';
$string['delay_success'] = 'Course purge has been delayed by 2 weeks.';
$string['action'] = 'Action';
