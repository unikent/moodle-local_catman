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

function xmldb_local_catman_install() {
    global $CFG, $DB;

	$catid = get_config("local_catman", "catid");

    if ($catid === false) {
    	// First try and find one.
    	$category = $DB->get_record('course_categories', array(
    		'idnumber' => 'kent_catman_removed'
    	), 'id');

    	if (!$category) {
    		// Try to find the old connect one.
    		$category = $DB->get_record('course_categories', array(
	    		'idnumber' => 'kent_connect_removed'
	    	), 'id');
    	}

    	if ($category) {
			$catid = $category->id;
	    	set_config("catid", $catid, "local_catman");
    	}
    }

    if ($catid === false) {
    	// Still false, we will need to create one.
		require_once("$CFG->libdir/coursecatlib.php");

    	// Create a category.
		$category = new \stdClass();
		$category->parent = 0;
		$category->idnumber = 'kent_catman_removed';
		$category->name = 'Removed';
		$category->description = 'Holding place for removed modules';
		$category->sortorder = 999;
		$category->visible = false;
		$catid = \coursecat::create($category);

    	set_config("catid", $catid, "local_catman");
    }

	// Grab a list of courses in this category.
	$courses = $DB->get_records('course', array(
		'category' => $catid
	), '', 'id');

	// Set expiration times for all courses in this category.
	foreach ($courses as $course) {
		// Set an expiration time for this course.
		$DB->insert_record("catman_dates", array(
			"courseid" => $course->id,
			"deleted_date" => time(),
			"expiration_time" => time() + 1209600 // 14 days
		));
	}
}