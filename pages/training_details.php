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

require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/categories_factory.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/trainings_factory.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/courses_factory.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/activities_factory.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/learners_factory.php');

require_once($CFG->dirroot.'/blocks/attestoodle/classes/training_from_category.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/category.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/course.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/activity.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/validated_activity.php');

require_once($CFG->dirroot.'/blocks/attestoodle/classes/forms/training_milestones_update_form.php');

use block_attestoodle\factories\categories_factory;
use block_attestoodle\factories\trainings_factory;
use block_attestoodle\forms\training_milestones_update_form;

$currenturl = new moodle_url('/blocks/attestoodle/pages/training_details.php', array('id' => $trainingid));
$PAGE->set_url($currenturl);
/* @todo May be replaced by "require_login(...)" + context_system
 * because coursecat throw  an error if id is not valid */
$PAGE->set_context(context_system::instance());

// @todo make a translation
$PAGE->set_title("Moodle - Attestoodle - Détail de la formation");

categories_factory::get_instance()->create_categories();
$trainingexist = trainings_factory::get_instance()->has_training($trainingid);

if (!$trainingexist) {
    $PAGE->set_heading("Error!");
    echo $OUTPUT->header();
    $warningunknownid = get_string('training_details_unknown_training_id', 'block_attestoodle') . $trainingid;
    echo $warningunknownid;
} else {
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
        $redirecturl = new moodle_url('/blocks/attestoodle/pages/training_learners_list.php', array('id' => $trainingid));
        // @todo translation.
        $message = "Form cancelled";
        redirect($redirecturl, $message, null, \core\output\notification::NOTIFY_INFO);
    } else if ($mform->is_submitted()) {
        // Handle form submit operation.
        // Check the data validity.
        if (!$mform->is_validated()) {
            // If not valid, warn the user.
            // @todo translations
            \core\notification::error("Form is not valid");
        } else {
            // If data are valid, process persistance.
            // Try to retrieve the submitted data.
            if ($datafromform = $mform->get_submitted_data()) {
                // Instanciate global variables to output to the user.
                $updatecounter = 0;
                $errorcounter = 0;
                $successlist = "Activities updated:<ul>";
                $errorlist = "Activities not updated:<ul>";

                foreach ($datafromform as $key => $value) {
                    $regexp = "/attestoodle_activity_id_(.+)/";
                    if (preg_match($regexp, $key, $matches)) {
                        $idactivity = $matches[1];
                        if (!empty($idactivity)) {
                            if ($activity = $training->retrieve_activity($idactivity)) {
                                $oldmarkervalue = $activity->get_marker();
                                if ($activity->set_marker($value)) {
                                    try {
                                        // Try to persist activity in DB.
                                        $activity->persist();

                                        // If no Exception has been thrown by DB update.
                                        $updatecounter++;

                                        // Instanciate the output for the user.
                                        if ($oldmarkervalue == null) {
                                            $fromstring = "<b>[no marker]</b>";
                                        } else {
                                            $fromstring = "<b>{$oldmarkervalue}</b> minutes";
                                        }
                                        if ($activity->get_marker() == null) {
                                            $tostring = "<b>[no marker]</b>";
                                        } else {
                                            $tostring = "<b>{$activity->get_marker()}</b> minutes";
                                        }

                                        $successlist .= "<li><b>{$activity->get_name()}</b> "
                                                . "from {$fromstring} to {$tostring}. </li>";
                                    } catch (Exception $ex) {
                                        // If record in DB failed, re-set the old value.
                                        $activity->set_marker($oldmarkervalue);
                                        $errorcounter++;

                                        // Output a warning to the user.
                                        if ($activity->get_marker() == null) {
                                            $oldstring = "<b>[no marker]</b>";
                                        } else {
                                            $oldstring = "<b>{$activity->get_marker()}</b> minutes";
                                        }

                                        $errorlist .= "<li><b>{$activity->get_name()}</b>. "
                                                . "Kept the old value of {$oldstring}. </li>";
                                    }
                                }
                            }
                        }
                    }
                }
                $successlist .= "</ul>";
                $errorlist .= "</ul>";

                $message = "";
                if ($errorcounter == 0) {
                    $message .= "Form submitted. <br />"
                            . "{$updatecounter} activities updated <br />";
                    $message .= $successlist;
                    \core\notification::success($message);
                } else {
                    $message .= "Form submitted with errors. <br />"
                            . "{$updatecounter} activities updated <br />"
                            . "{$errorcounter} errors (activities not updated in database).<br />";
                    $message .= $successlist . $errorlist;
                    \core\notification::warning($message);
                }
                // Reinstanciate the form to update training and courses total milestones.
                $mform = new training_milestones_update_form(
                        "?id={$trainingid}", array(
                    'data' => $training->get_courses(),
                    'input_name_prefix' => "attestoodle_activity_id_"
                ));
            } else {
                // No submitted data.
                // @todo translations.
                \core\notification::warning("No submitted data");
            }
        }
    }

    // Setting the total hours after potential form submission.
    $totaltrainingmilestones = parse_minutes_to_hours($training->get_total_milestones());
    $PAGE->set_heading("Gestion de la formation {$training->get_name()} : {$totaltrainingmilestones}");
    echo $OUTPUT->header();

    echo html_writer::start_div('clearfix');
    // Link to the training learners list.
    echo html_writer::link(
            new moodle_url('/blocks/attestoodle/pages/training_learners_list.php', array('id' => $trainingid)),
            get_string('training_details_learners_list_btn_text', 'block_attestoodle'),
            array('class' => 'attestoodle-link'));
    echo html_writer::end_div();

    // Displaying the form in any case but invalid training ID.
    $mform->display();
}

// Output footer in any case.
echo $OUTPUT->footer();
