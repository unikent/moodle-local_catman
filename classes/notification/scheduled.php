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

namespace local_catman\notification;

defined('MOODLE_INTERNAL') || die();

class scheduled extends \local_notifications\base {
    /**
     * Returns the component of the notification.
     */
    public static function get_component() {
        return 'local_catman';
    }

    /**
     * Returns the table name the objectid relates to.
     */
    public static function get_table() {
        return 'course';
    }

    /**
     * Returns the level of the notification.
     */
    public function get_level() {
        return \local_notifications\base::LEVEL_WARNING;
    }

    /**
     * Returns the notification.
     */
    public function render() {
        $time = strftime("%H:%M %d/%m/%Y", $this->other['expirationtime']);
        return "This course is scheduled for deletion at $time.";
    }

    /**
     * Checks custom data.
     */
    public function set_custom_data($data) {
        if (!isset($data['expirationtime'])) {
            throw new \moodle_exception('You must set "date"!');
        }

        parent::set_custom_data($data);
    }
}