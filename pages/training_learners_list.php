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

require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/trainings_factory.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/courses_factory.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/activities_factory.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/learners_factory.php');

require_once($CFG->dirroot.'/blocks/attestoodle/classes/course.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/activity.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/validated_activity.php');

use block_attestoodle\factories\trainings_factory;

$PAGE->set_url(new moodle_url('/blocks/attestoodle/pages/training_learners_list.php', array('id' => $trainingid)));
// @todo May be replaced by "require_login(...)"
$PAGE->set_context(context_coursecat::instance($trainingid));
// @todo Make a translation string.
$PAGE->set_title("Moodle - Attestoodle - Liste des étudiants");

$trainingexist = trainings_factory::get_instance()->has_training($trainingid);

if ($trainingexist) {
    $training = trainings_factory::get_instance()->retrieve_training($trainingid);
    // @todo Make a translation string.
    $PAGE->set_heading("Etudiants de la formation {$training->get_name()}");
} else {
    // @todo Make a translation string.
    $PAGE->set_heading("Erreur !");
}

echo $OUTPUT->header();

// Link to the trainings list.
echo html_writer::link(
        new moodle_url('/blocks/attestoodle/pages/trainings_list.php', array()),
        get_string('trainings_list_btn_text', 'block_attestoodle'),
        array('class' => 'attestoodle-button'));

if (!$trainingexist) {
    $warningunknownid = get_string('training_details_unknown_training_id', 'block_attestoodle') . $trainingid;
    echo $warningunknownid;
} else {
    // Link to the training details.
    echo html_writer::link(
            new moodle_url('/blocks/attestoodle/pages/training_details.php', array('id' => $trainingid)),
            get_string('edit_training_link_text', 'block_attestoodle'),
            array('class' => 'attestoodle-button'));

    $data = parse_learners_as_stdclass($training->get_learners(), $trainingid);
    $table = new html_table();
    // @todo translations
    $table->head = array('ID', 'Prénom', 'Nom', 'Activités validées', 'Total jalons', '');
    $table->data = $data;

    echo $OUTPUT->heading(get_string('training_learners_list_heading', 'block_attestoodle', count($data)));
    echo html_writer::table($table);
}

echo $OUTPUT->footer();
