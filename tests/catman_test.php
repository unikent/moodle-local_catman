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

defined('MOODLE_INTERNAL') || die();

/**
 * Tests the category manager
 */
class local_catman_tests extends \advanced_testcase
{
    /**
     * Create a course, move it to the deleted category and see what happens.
     */
    public function test_observer() {
        global $CFG, $DB;

        require_once($CFG->dirroot . "/course/lib.php");

        $this->resetAfterTest();

        // Enable the plugin for testing.
        set_config("enable", true, "local_catman");

        // First we want to create a new category.
        $category = \local_catman\core::get_category();

        // Now create some courses.
        $c1 = $this->getDataGenerator()->create_course();
        $c2 = $this->getDataGenerator()->create_course();
        $c3 = $this->getDataGenerator()->create_course();

        // Move c1 and c2 into the deleted category.
        $this->assertTrue(move_courses(array($c1->id, $c2->id), $category->id));

        // Make sure they were added to the expirations table.
        $this->assertTrue($DB->record_exists('catman_expirations', array(
            "courseid" => $c1->id
        )));
        $this->assertTrue($DB->record_exists('catman_expirations', array(
            "courseid" => $c2->id
        )));

        // Ensure c3 wasnt.
        $this->assertFalse($DB->record_exists('catman_expirations', array(
            "courseid" => $c3->id
        )));

        // Move c2 back out.
        $this->assertTrue(move_courses(array($c2->id), 1));

        // Make sure it is no longer in the table.
        $this->assertFalse($DB->record_exists('catman_expirations', array(
            "courseid" => $c2->id
        )));

        // And make sure c1 is.
        $this->assertTrue($DB->record_exists('catman_expirations', array(
            "courseid" => $c1->id
        )));
    }

    /**
     * Test the cron.
     */
    public function test_cron() {
        global $CFG, $DB;

        require_once($CFG->dirroot . "/course/lib.php");
        require_once($CFG->dirroot . "/local/catman/lib.php");

        $this->resetAfterTest();

        // Enable the plugin for testing.
        set_config("enable", true, "local_catman");
        set_config("period", 1, "local_catman");

        // First we want to create a new category.
        $category = \local_catman\core::get_category();

        // Now create some courses.
        $c1 = $this->getDataGenerator()->create_course();
        $c2 = $this->getDataGenerator()->create_course();
        $c3 = $this->getDataGenerator()->create_course();

        // Move c2 and c3 into the deleted category.
        $this->assertTrue(move_courses(array($c2->id, $c3->id), $category->id));

        // Sleep.
        sleep(2);

        // Run task.
        ob_start();
        $task = new \local_catman\task\purge();
        $task = \core\task\manager::queue_adhoc_task($task);
        $task = \core\task\manager::get_next_adhoc_task(time());
        $this->assertNotNull($task);
        $task->execute();
        \core\task\manager::adhoc_task_complete($task);
        ob_get_clean();

        // What happened?
        $this->assertTrue($DB->record_exists("course", array(
            "id" => $c1->id
        )));
        $this->assertFalse($DB->record_exists("course", array(
            "id" => $c2->id
        )));
        $this->assertFalse($DB->record_exists("course", array(
            "id" => $c3->id
        )));
    }

    /**
     * Test enrolment deletion observer.
     */
    public function test_enrolments() {
        global $CFG, $DB;

        require_once($CFG->dirroot . "/course/lib.php");

        $this->resetAfterTest();

        // Enable the plugin for testing.
        set_config("enable", true, "local_catman");

        // First we want to create a new category.
        $category = \local_catman\core::get_category();

        // Get some roles.
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $teacherrole = $DB->get_record('role', array('shortname' => 'teacher'));

        // Now create some courses.
        $c1 = $this->getDataGenerator()->create_course();
        $c1ctx = context_course::instance($c1->id);

        // Enrol users.
        $u1 = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($u1->id, $c1->id, $studentrole->id);
        $this->assertCount(1, get_enrolled_users($c1ctx));

        $u2 = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($u2->id, $c1->id, $teacherrole->id);
        $this->assertCount(2, get_enrolled_users($c1ctx));

        // Delete it.
        $this->assertTrue(move_courses(array($c1->id), $category->id));

        // Check!
        $this->assertCount(0, get_enrolled_users($c1ctx));
    }
}