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

echo $OUTPUT->header();

// Link to the trainings list.
echo $OUTPUT->single_button(
        new moodle_url('/blocks/attestoodle/pages/trainings_list.php', array()),
        get_string('trainings_list_btn_text', 'block_attestoodle'),
        'get',
        array('class' => 'attestoodle-button'));

if (!trainings_factory::get_instance()->has_training($trainingid)) {
    $warningunknownid = get_string('training_details_unknown_training_id', 'block_attestoodle') . $trainingid;
    echo $warningunknownid;
} else {
    // Link to the training learners list.
    echo $OUTPUT->single_button(
            new moodle_url('/blocks/attestoodle/pages/training_learners_list.php', array('id' => $trainingid)),
            get_string('training_details_learners_list_btn_text', 'block_attestoodle'),
            'get',
            array('class' => 'attestoodle-button'));

    $training = trainings_factory::get_instance()->retrieve_training($trainingid);

    foreach ($training->get_courses() as $course) {
        echo $OUTPUT->heading($course->get_name());

        $data = $course->get_activities_as_stdclass();
        $table = new html_table();
        $table->head = array('id', 'Type', 'Nom', 'Jalon');
        $table->data = $data;

        echo html_writer::table($table);
    }
}

echo $OUTPUT->footer();
