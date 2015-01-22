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

namespace local_catman\task;

/**
 * Purges due courses.
 */
class purge extends \core\task\scheduled_task
{
    public function get_name() {
        return "Deleted Course Purge";
    }

    public function execute() {
        global $DB;

        // Don't run if we are disabled.
        if (!get_config("local_catman", "enable")) {
            return;
        }

        // Get a list of courses that are due to expire.
        $sql = "SELECT * FROM {catman_expirations} WHERE expiration_time < :time AND status = 0";
        $expirations = $DB->get_records_sql($sql, array(
            "time" => time()
        ));

        // Grab the removed category.
        $category = \local_catman\core::get_category();

        // Foreach course in the category.
        foreach ($expirations as $expiration) {
            echo "Deleting course {$expiration->courseid}....\n";

            // Set it to status 2 (error) so we don't keep re-trying this if it fails badly.
            $expiration->status = 2;
            $DB->update_record('catman_expirations', $expiration);

            // Grab the course.
            $course = $DB->get_record('course', array(
                'id' => $expiration->courseid,
                'category' => $category->id
            ));
            $coursectx = \context_course::instance($course->id);

            // Did we succeed?
            if ($course === false) {
                continue;
            }

            try {
                // Attempt to delete the course.
                delete_course($course);

                $expiration->status = 1;
            } catch (\Exception $e) {
                $expiration->status = 2;
                debugging($e->getMessage());
            }

            // Does the course exist?
            // If it does, it didn't work.
            if ($DB->record_exists('course', array('id' => $expiration->courseid))) {
                $expiration->status = 2;
            }

            // Raise an event.
            if ($expiration->status = 1) {
                $event = \local_catman\event\course_purged::create(array(
                    'objectid' => $expiration->courseid,
                    'context' => $coursectx,
                    'other' => array(
                        'shortname' => $course->shortname
                    )
                ));
                $event->trigger();
            }

            $DB->update_record('catman_expirations', $expiration);
        }
    }
} 
