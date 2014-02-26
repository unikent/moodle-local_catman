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

require_once("$CFG->libdir/coursecatlib.php");

/**
 * Catman core
 */
abstract class core {

	/**
	 * Creates a category for the manager to use.
	 */
	private static function create_category() {
        // Create a category.
        $category = new \stdClass();
        $category->parent = 0;
        $category->idnumber = 'catman_removed';
        $category->name = 'Removed';
        $category->description = 'Holding place for removed modules.';
        $category->sortorder = 999;
        $category->visible = false;

        return \coursecat::create($category);
	}

    /**
     * Returns the category the category manager is supposed to use.
     */
    public static function get_category() {
    	$catid = get_config("local_catman", "catid");

    	// If this isnt set, create a category.
    	if ($catid === false) {
    		$obj = self::create_category();
    		set_config("catid", $obj->id, "local_catman");
    		return $obj;
	    }

	    return \coursecat::get($catid, true);
    }

    /**
     * Get the period of holding.
     */
    public static function get_holding_period() {
		$period = get_config("local_catman", "period");
		if ($period === false) {
			$period = 1209600; // 14 day default.
		}

		return $period;
    }

    /**
     * Delay the given course.
     */
    public static function delay($id) {
    	global $DB;

    	// Grab the course.
		$course = $DB->get_record('catman_expirations', array(
			'id' => $id
		), 'id,expiration_time', MUST_EXIST);

        // Delay the given course.
        $DB->set_field('catman_expirations', 'expiration_time', $course->expiration_time + self::get_holding_period(), array(
            'id' => $id
        ));
    }
}