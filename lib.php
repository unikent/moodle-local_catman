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
 * Local lib code
 *
 * @package    local_catman
 * @copyright  2014 University of Kent
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Our cron runs through 25 course deletions.
 */
function local_catman_cron() {
	global $CFG, $DB;

	// Dont run if we are disabled.
	if (!isset($CFG->local_catman_enable) || !$CFG->local_catman_enable) {
		return;
	}

	// What is the maximum number of courses we want to delete in one go?
	$limit = isset($CFG->local_catman_limit) ? $CFG->local_catman_limit : 25;

	// Get a list of courses that are due to expire.
	$courses = $DB->get_records_sql("SELECT * FROM {catman_dates} WHERE expiration_time < :time", array(
		"time" => time()
	), 0, $limit);

	// Foreach course in the category.
	foreach ($courses as $id) {
		mtrace(" ");
		mtrace("Deleting course $id....\n");
		$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
		try {
			@delete_course($course);
		} catch (Exception $e) {
			// TODO - set an error
		}
		mtrace(" ");
	}
}