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
 * Local stuff for Moodle Catman
 *
 * @package    local_catman
 * @copyright  2014 Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_catman\nagios;

/**
 * Checks there are no errors.
 * Relies on local_nagios.
 */
class error_check extends \local_nagios\base_check
{
    public function execute() {
        global $DB;

        $count = $DB->count_records('catman_expirations', array(
            'status' => 2
        ));

        if ($count > 0) {
            $this->error("{$count} failed course purges");
        }
    }
}