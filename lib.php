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

defined('MOODLE_INTERNAL') || die;

/**
 * Our cron runs through 25 course deletions.
 */
function local_catman_cron() {
    global $DB;

    // Dont run if we are disabled.
    if (!get_config("local_catman", "enable")) {
        return;
    }

    // What is the maximum number of courses we want to delete in one go?
    $limit = get_config("local_catman", "limit");
    if ($limit === false) {
        $limit = 25;
    }

    // Get a list of courses that are due to expire.
    $expirations = $DB->get_records_sql("SELECT * FROM {catman_expirations} WHERE expiration_time < :time AND status = 0", array(
        "time" => time()
    ), 0, $limit);

    // Foreach course in the category.
    foreach ($expirations as $expiration) {
        mtrace(" ");
        mtrace("Deleting course {$expiration->courseid}....\n");

        // Grab the course.
        $course = $DB->get_record('course', array(
            'id' => $expiration->courseid
        ));

        $hipchat = get_config("local_catman", "enable_hipchat");
        if ($hipchat != false) {
            if ($course !== false) {
                $msg = "Deleting '{$course->shortname}' ({$expiration->courseid})...";
                \local_hipchat\Message::send($msg, "red", false, "text", "CatMan");
            }
        }

        // Set it to errored so we dont keep re-trying this if it fails badly.
        $expiration->status = 2;
        $DB->update_record('catman_expirations', $expiration);

        try {
            // Attempt to delete the course.
            if ($course !== false) {
                @delete_course($course);
            }

            $expiration->status = 1;
        } catch (Exception $e) {
            $expiration->status = 2;
        }

        $DB->update_record('catman_expirations', $expiration);

        mtrace(" ");
    }
}
