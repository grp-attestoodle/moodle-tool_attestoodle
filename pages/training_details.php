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

require_once($CFG->dirroot.'/blocks/attestoodle/classes/forms/training_milestones_update_form.php');

use block_attestoodle\factories\trainings_factory;
use block_attestoodle\forms\training_milestones_update_form;

// @todo Make a real header.
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

    // Retrieve the current training.
    $training = trainings_factory::get_instance()->retrieve_training($trainingid);

    // Instanciate the custom form.
    $mform = new training_milestones_update_form(
            "?id={$trainingid}",
            array(
                    'data' => $training->get_courses(),
                    'input_name_prefix' => "attestoodle_activity_id_"
            )
    );

    // Form processing and displaying is done here.
    if ($mform->is_cancelled()) {
        // Handle form cancel operation.
        echo "Form has been cancelled <br />";
        // @todo Redirect to training students detail.
    } else if ($mform->is_submitted()) {
        // Handle form submit operation.
        echo "Form has been submitted <br />";
        // Check the data validity.
        if (!$mform->is_validated()) {
            echo "Form is not valid <br />";
            // Redisplaying the form.
            $mform->display();
        } else {
            echo "Form is valid <br />";
            echo "processing update... <br />";
            // Data are valid, try to retrieve them.
            if ($datafromform = $mform->get_submitted_data()) {
                $i = 0;
                foreach ($datafromform as $key => $value) {
                    $i++;
                    $regexp = "/attestoodle_activity_id_(.+)/";
                    if (preg_match($regexp, $key, $matches)) {
                        $idactivity = $matches[1];
                        if (!empty($idactivity)) {
                            if ($activity = $training->retrieve_activity($idactivity)) {
                                $oldmarkervalue = $activity->get_marker();
                                if ($activity->set_marker($value)) {
                                    try {
                                        if ($i % 3 == 0) {
                                            throw new \Exception();
                                        }
                                        $activity->persist();
                                        $fromstring = $oldmarkervalue == null
                                                ? "<b>[no marker]</b>"
                                                : "<b>{$oldmarkervalue}</b> minutes";
                                        $tostring = $activity->get_marker() == null
                                                ? "<b>[no marker]</b>"
                                                : "<b>{$activity->get_marker()}</b> minutes";
                                        echo "Activity updated: <b>{$activity->get_name()}</b> from {$fromstring} to {$tostring}. <br />";
                                    } catch (Exception $ex) {
                                        $activity->set_marker($oldmarkervalue);
                                        $oldstring =
                                                $activity->get_marker() == null ?
                                                "<b>[no marker]</b>" :
                                                "<b>{$activity->get_marker()}</b> minutes";
                                        echo "An error occured while attempting to save <b>{$activity->get_name()}</b> activity in DB. Kept the old value of {$oldstring}. <br />";
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                // No submitted data.
                echo "no submitted data";
                // Redisplaying the form.
                $mform->display();
            }
        }
    } else {
        // First render of the form.
        $mform->display();
    }
}

echo $OUTPUT->footer();
