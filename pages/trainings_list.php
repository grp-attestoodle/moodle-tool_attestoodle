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

// importation de la config $CFG qui importe égalment $DB et $OUTPUT
require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->dirroot.'/blocks/attestoodle/lib.php');

//require_once($CFG->dirroot.'/blocks/attestoodle/classes/utils/singleton.php');
//require_once($CFG->dirroot.'/blocks/attestoodle/classes/utils/db_accessor.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/training_factory.php');

//use block_attestoodle\utils\singleton;
//use block_attestoodle\utils\db_accessor;
use block_attestoodle\factories\training_factory;
//use block_attestoodle\training;

echo $OUTPUT->header();
$parameters = array();
$url = new moodle_url('/blocks/attestoodle/course_list_page.php', $parameters);
$label = get_string('course_list_btn_text', 'block_attestoodle');
$options = array('class' => 'attestoodle-button');
echo $OUTPUT->single_button($url, $label, 'get', $options);

echo $OUTPUT->heading('Liste des formations :');
// print des formations dans un tableau
training_factory::get_instance()->create_trainings();
$data = training_factory::get_instance()->get_trainings_as_stdClass();
echo "<pre>";
var_dump($data);
echo "</pre>";
$datatest = [];
for($i = 1; $i < 3; $i++) {
    $obj = new stdClass();
    $obj->truc = "truc".$i;
    $obj->much = "much".$i;
    $obj->machin = "machin".$i;
    $datatest[] = $obj;
}
echo "<pre>";
var_dump($datatest);
echo "</pre>";

$table = new html_table();
$table->head = array('ID', 'Nom', 'Description');
$table->data = $data;

//foreach($data as $d) {
//    $table->data[] = $d->get_data_as_table();
//}
echo html_writer::table($table);

$tabletest = new html_table();
$tabletest->head = array('Truc', 'Much', 'Machin');
$tabletest->data = $datatest;

echo html_writer::table($tabletest);

echo $OUTPUT->footer();
