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

// Importation de la config $CFG qui importe égalment $DB et $OUTPUT.
require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->dirroot.'/blocks/attestoodle/lib.php');

require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/training_factory.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/courses_factory.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/activities_factory.php');

require_once($CFG->dirroot.'/blocks/attestoodle/classes/course.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/activity.php');

use block_attestoodle\factories\training_factory;

echo $OUTPUT->header();
$parameters = array();
$url = new moodle_url('/blocks/attestoodle/pages/courses_list.php', $parameters);
$label = get_string('courses_list_btn_text', 'block_attestoodle');
$options = array('class' => 'attestoodle-button');
echo $OUTPUT->single_button($url, $label, 'get', $options);

echo $OUTPUT->heading('Liste des formations :');
// Print des formations dans un tableau.
training_factory::get_instance()->create_trainings();
$databrut = training_factory::get_instance()->get_trainings();

$data = parse_trainings_as_stdclass($databrut);

$table = new html_table();
$table->head = array('ID', 'Nom', 'Description', '');
$table->data = $data;

echo html_writer::table($table);

/*
echo "<pre>";
foreach (training_factory::get_instance()->get_trainings() as $t) {
    var_dump($t->get_courses());
}
echo "</pre>";
*/

echo $OUTPUT->footer();
