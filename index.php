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

global $USER, $CFG, $DB, $PAGE, $OUTPUT;

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('reportcatmanreport', '', null, '', array(
    'pagelayout' => 'report'
));

$PAGE->set_url('/local/catman/index.php');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_catman'));

// Allow the user to delay a purge.
$action = optional_param('action', false, PARAM_ALPHA);
if ($action) {
    $action_id = required_param('id', PARAM_INT);

    switch($action) {
        case 'delay':
        	// Grab the course.
			$course = $DB->get_record('catman_expirations', array(
				'id' => $action_id
			), 'id,expiration_time', MUST_EXIST);

            // Delay the given course.
            $DB->set_field('catman_expirations', 'expiration_time', $course->expiration_time + 1209600, array(
                'id' => $action_id
            ));

            // Let the user know.
            echo $OUTPUT->notification(get_string('delay_success', 'local_catman'));
        break;

        default:
            // Do nothing.
        break;
    }
}

// Create a table.
$table = new \html_table();
$table->head = array(
    get_string('course', 'local_catman'),
    get_string('date_deleted', 'local_catman'),
    get_string('date_scheduled', 'local_catman'),
    get_string('status', 'local_catman'),
    get_string('action', 'local_catman')
);
$table->data = array();

// Get all the entries.
$entries = $DB->get_records_sql("
    SELECT ce.id, ce.courseid, ce.deleted_date, ce.expiration_time, ce.status, c.shortname 
        FROM {catman_expirations} ce
    INNER JOIN {course} c
        ON c.id = ce.courseid
    ORDER BY ce.expiration_time DESC
");

// Add all the entries to the table.
foreach ($entries as $entry) {
    $courseid_cell = new \html_table_cell(\html_writer::tag('a', $entry->shortname, array(
        'href' => $CFG->wwwroot . '/course/view.php?id=' . $entry->courseid,
        'target' => '_blank'
    )));
    $action_cell = new \html_table_cell(\html_writer::tag('a', get_string('delay', 'local_catman'), array(
        'href' => $CFG->wwwroot . '/local/catman/index.php?action=delay&id=' . $entry->id
    )));
    $table->data[] = new \html_table_row(array(
        $courseid_cell,
        strftime("%d/%m/%Y %H:%M", $entry->deleted_date),
        strftime("%d/%m/%Y %H:%M", $entry->expiration_time),
        get_string("status_{$entry->status}", 'local_catman'),
        $action_cell
    ));
}

echo $OUTPUT->box_start('contents');
echo \html_writer::table($table);
echo $OUTPUT->box_end();

echo $OUTPUT->footer();