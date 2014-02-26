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
 * Category manager
 *
 * @package    local_catman
 * @copyright  2014 University of Kent
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_catman;

defined('MOODLE_INTERNAL') || die();

/**
 * Catman observers
 */
class observers {

    /**
     * Triggered when 'course_updated' event is triggered.
     * Adds a course expiration date if the course has moved category.
     *
     * @param \core\event\course_updated $event
     * @return unknown
     */
    public static function course_updated(\core\event\course_updated $event) {
    	global $DB;

		// Grab the course.
		$course = $DB->get_record('course', array(
			"id" => $event->objectid
		), 'id,category');

		// The ID of the deleted category is stored in config.
		$category = core::get_category();

    	// Does the course exist in the expiration table already?
    	if ($DB->record_exists("catman_expirations", array("courseid" => $event->objectid))) {
    		if ($course->category !== $category->id) {
    			// Delete the record from catman expirations.
    			$DB->delete_records("catman_expirations", array(
    				"courseid" => $event->objectid
    			));
    		}

			return true;
    	}

		// Is this now in the deleted category?
		if ($course->category === $category->id) {
			// Insert a record into the DB
			$DB->insert_record("catman_expirations", array(
				"courseid" => $course->id,
				"deleted_date" => time(),
				"expiration_time" => time() + core::get_holding_period()
			));
		}

    	return true;
    }
}