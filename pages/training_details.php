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

$trainingid = required_param('id', PARAM_INT);

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

if (!training_factory::get_instance()->has_training($trainingid)) {
    echo "Aucune formation ayant l'ID : " . $trainingid;
} else {
    $training = training_factory::get_instance()->retrieve_training($trainingid);

    foreach ($training->get_courses() as $course) {
        echo $OUTPUT->heading($course->get_name());

        $data = $course->get_activities_as_stdclass();
        $table = new html_table();
        $table->head = array('Nom', 'Description', 'Jalon');
        $table->data = $data;

        echo html_writer::table($table);
    }

//    echo $OUTPUT->heading('Liste des formations :');
//    // Print des formations dans un tableau.
//    training_factory::get_instance()->create_trainings();
//    $data = training_factory::get_instance()->get_trainings_as_stdClass();
//
//    $table = new html_table();
//    $table->head = array('ID', 'Nom', 'Description');
//    $table->data = $data;
//
//    echo html_writer::table($table);
}
echo $OUTPUT->footer();
