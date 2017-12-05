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

$trainingid = required_param('training', PARAM_INT);
$userid = required_param('user', PARAM_INT);

require_once($CFG->dirroot.'/blocks/attestoodle/lib.php');

require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/trainings_factory.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/courses_factory.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/activities_factory.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/learners_factory.php');

require_once($CFG->dirroot.'/blocks/attestoodle/classes/course.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/activity.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/validated_activity.php');

use block_attestoodle\factories\trainings_factory;
use block_attestoodle\factories\learners_factory;

echo $OUTPUT->header();

if (!trainings_factory::get_instance()->has_training($trainingid)) {
    // Link to the trainings list.
    echo $OUTPUT->single_button(
            new moodle_url('/blocks/attestoodle/pages/trainings_list.php', array()),
            get_string('trainings_list_btn_text', 'block_attestoodle'),
            'get',
            array('class' => 'attestoodle-button'));

    $warningunknowntrainingid = get_string('learner_details_unknown_training_id', 'block_attestoodle') . $trainingid;
    echo $warningunknowntrainingid;
} else {
    // Link to the training learners list.
    echo $OUTPUT->single_button(
            new moodle_url('/blocks/attestoodle/pages/training_learners_list.php', array('id' => $trainingid)),
            get_string('backto_training_learners_list_btn_text', 'block_attestoodle'),
            'get',
            array('class' => 'attestoodle-button'));

    if (!learners_factory::get_instance()->has_learner($userid)) {
        $warningunknownlearnerid = get_string('learner_details_unknown_learner_id', 'block_attestoodle') . $userid;
        echo $warningunknownlearnerid;
    } else {
        // Link to the training learners list.
    //    echo $OUTPUT->single_button(
    //            new moodle_url('/blocks/attestoodle/pages/training_learners_list.php', array('id' => $trainingid)),
    //            get_string('training_details_learners_list_btn_text', 'block_attestoodle'),
    //            'get',
    //            array('class' => 'attestoodle-button'));
    //
    //    $training = trainings_factory::get_instance()->retrieve_training($trainingid);
        $learner = learners_factory::get_instance()->retrieve_learner($userid);
    //
    //    foreach ($training->get_courses() as $course) {
    //        echo $OUTPUT->heading($course->get_name());
    //
    //        $data = $course->get_activities_as_stdclass();
    //        $table = new html_table();
    //        $table->head = array('id', 'Type', 'Nom', 'Jalon');
    //        $table->data = $data;
    //
    //        echo html_writer::table($table);
        foreach ($learner->get_validated_activities() as $vact) {
            $act = $vact->get_activity();
            echo "<h1>" . $act->get_course()->get_training()->get_name() . "</h1>";
            echo "<h2>" . $act->get_name() . "</h2>";
            echo "<h3>" . $act->get_type(). " (" . parse_minutes_to_hours($act->get_marker()) . ") - Validée le " . parse_datetime_to_readable_format($vact->get_datetime()) . "</h3>";
            echo "<p>" . $act->get_description() . "</p>";
        }
    }
}

echo $OUTPUT->footer();
