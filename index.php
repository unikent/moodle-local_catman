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

// Create a table.
$table = new \html_table();
$table->head = array(
	get_string('course_id'),
	get_string('course_name'),
	get_string('date_deleted'),
	get_string('date_scheduled'),
	get_string('status')
);
$table->data = array();

// Get all the entries.
$entries = $DB->get_records_sql("
	SELECT ce.id, ce.courseid, ce.deleted_date, ce.expiration_time, ce.status, c.shortname 
		FROM {catman_expirations} ce
	INNER JOIN {course} c
		ON c.id = ce.courseid
");

// Add all the entries to the table.
foreach ($entries as $entry) {
	$table->data[] = new \html_table_row(array(
		$entry->courseid,
		$entry->shortname,
		$entry->deleted_date,
		$entry->expiration_time,
		get_string("status_{$entry->status}", 'local_catman')
	));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_catman'));

echo $OUTPUT->box_start('contents');
echo \html_writer::table($table);
echo $OUTPUT->box_end();

echo $OUTPUT->footer();