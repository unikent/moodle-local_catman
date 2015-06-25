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
 * Upgrade code
 *
 * @package    local_catman
 * @copyright  2014 University of Kent
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Catman upgrade task
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool always true
 */
function xmldb_local_catman_upgrade($oldversion) {
    global $CFG, $DB;

    if ($oldversion < 2014050200) {
        // Check all courses that "have" been purged, that actually
        // still exist and set them to errored.

        $entries = $DB->get_records_sql("
            SELECT ce.id
                FROM {catman_expirations} ce
            INNER JOIN {course} c
                ON c.id = ce.courseid
            WHERE ce.status=1
        ");

        foreach ($entries as $entry) {
            $entry->status = 2;
            $DB->update_record('catman_expirations', $entry, true);
        }

        upgrade_plugin_savepoint(true, 2014050200, 'local', 'catman');
    }

    if ($oldversion < 2014062700) {
        require_once($CFG->libdir . '/enrollib.php');

        $category = \local_catman\core::get_category();

        $courses = $DB->get_records('course', array(
            'category' => $category->id
        ));

        foreach ($courses as $course) {
            enrol_course_delete($course);
        }

        upgrade_plugin_savepoint(true, 2014062700, 'local', 'catman');
    }

    return true;
}
