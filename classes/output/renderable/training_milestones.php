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

/**
 * Page training management (declare milestones)
 *
 * Renderable page that computes infos to give to the template
 */

namespace block_attestoodle\output\renderable;

defined('MOODLE_INTERNAL') || die;

use block_attestoodle\factories\trainings_factory;
use block_attestoodle\forms\training_milestones_update_form;

class training_milestones implements \renderable {
    private $trainingid;
    private $training;
    private $form;

    public function __construct($trainingid) {
        $this->trainingid = $trainingid;
        $this->training = trainings_factory::get_instance()->retrieve_training($trainingid);

        if ($this->training_exists()) {
            $this->form = new training_milestones_update_form(
                    new \moodle_url(
                            '/blocks/attestoodle/index.php',
                            ['page' => 'trainingmilestones', 'training' => $this->training->get_id()]),
                    array(
                        'data' => $this->training->get_courses(),
                        'input_name_prefix' => "attestoodle_activity_id_"
                    )
            );

            $this->handle_form();
        }
    }

    private function handle_form() {
        // Form processing and displaying is done here.
        if ($this->form->is_cancelled()) {
            $this->handle_form_cancelled();
        } else if ($this->form->is_submitted()) {
            $this->handle_form_submitted();
        } else {
            // First render, no process.
            return;
        }
    }

    private function handle_form_cancelled() {
        // Handle form cancel operation.
        $redirecturl = new \moodle_url(
                '/blocks/attestoodle/index.php',
                ['page' => 'learners', 'training' => $this->training->get_id()]
        );
        // TODO rename string variable.
        $message = get_string('training_details_info_form_canceled', 'block_attestoodle');
        redirect($redirecturl, $message, null, \core\output\notification::NOTIFY_INFO);
    }

    private function handle_form_submitted() {
         // Handle form submit operation.
        // Check the data validity.
        if (!$this->form->is_validated()) {
            $this->handle_form_not_validated();
        } else {
            // If data are valid, process persistance.
            // Try to retrieve the submitted data.
            $this->handle_form_has_submitted_data();
        }
    }

    private function handle_form_not_validated() {
        // If not valid, warn the user.
        \core\notification::error(get_string('training_details_error_invalid_form', 'block_attestoodle'));
    }

    private function handle_form_has_submitted_data() {
        if (has_capability('block/attestoodle:managetraining', \context_system::instance())) {
            // If data are valid, process persistance.
            // Retrieve the submitted data.
            $datafromform = $this->form->get_submitted_data();

            // Instanciate global variables to output to the user.
            $updatecounter = 0;
            $errorcounter = 0;
            $successlist = "Activities updated:<ul>";
            $errorlist = "Activities not updated:<ul>";

            foreach ($datafromform as $key => $value) {
                $resulthandling = $this->handle_form_activity($key, $value);

                switch($resulthandling->status) {
                    case -1:
                        // Error while updating.
                        $errorcounter++;
                        $errorlist .= "<li>"
                                . "<b>{$resulthandling->activityname}</b>. "
                                . "Kept the old value of <b>{$resulthandling->oldvalue}</b>."
                                . "</li>";
                        break;
                    case 1:
                        // Updated with success.
                        $updatecounter++;
                        $successlist .= "<li>"
                                . "<b>{$resulthandling->activityname}</b> "
                                . "from <b>{$resulthandling->oldvalue}</b> "
                                . "to <b>{$resulthandling->newvalue}</b>."
                                . "</li>";
                        break;
                    case 0:
                    default:
                        // Not updated, nothing to do.
                        break;
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
            $this->form = new training_milestones_update_form(
                    new \moodle_url(
                            '/blocks/attestoodle/index.php',
                            ['page' => 'trainingmilestones', 'training' => $this->training->get_id()]
                    ),
                    array(
                        'data' => $this->training->get_courses(),
                        'input_name_prefix' => "attestoodle_activity_id_"
                    )
            );
        } else {
            return;
        }
    }

    /**
     * TODO translations
     * @param type $key
     * @param type $value
     * @return \stdClass
     */
    private function handle_form_activity($key, $value) {
        // Instanciate default return object.
        $returnobject = new \stdClass();
        $returnobject->status = 0;
        $returnobject->activityname = null;
        $returnobject->oldvalue = null;
        $returnobject->newvalue = null;

        $matches = [];
        $regexp = "/attestoodle_activity_id_(.+)/";
        if (preg_match($regexp, $key, $matches)) {
            // There is an activity ID.
            $idactivity = $matches[1];
            if (!empty($idactivity) && $this->training->has_activity($idactivity)) {
                // The activity ID is valid.
                $activity = $this->training->retrieve_activity($idactivity);
                $oldmarkervalue = $activity->get_milestone();
                if ($activity->set_milestone($value)) {
                    // The activity milestone is different from the current one.
                    $returnobject->activityname = $activity->get_name();

                    try {
                        // Try to persist activity in DB.
                        $activity->persist();

                        // No Exception return, status to updated.
                        $returnobject->status = 1;

                        // Store values in return object.
                        if ($oldmarkervalue == null) {
                            $returnobject->oldvalue = "[no marker]";
                        } else {
                            $returnobject->oldvalue = "{$oldmarkervalue} minutes";
                        }
                        if ($activity->get_milestone() == null) {
                            $returnobject->newvalue = "[no marker]";
                        } else {
                            $returnobject->newvalue = "{$activity->get_milestone()} minutes";
                        }
                    } catch (\Exception $ex) {
                        // If record in DB failed, re-set the old value.
                        $activity->set_milestone($oldmarkervalue);
                        $returnobject->status = -1;

                        // Store old value in return object.
                        if ($activity->get_milestone() == null) {
                            $returnobject->oldvalue = "[no marker]";
                        } else {
                            $returnobject->oldvalue = "{$activity->get_milestone()} minutes";
                        }
                    }
                }
            }
        }

        return $returnobject;
    }

    public function get_heading() {
        $heading = "";
        if (!$this->training_exists()) {
            // TODO rename string variable.
            $heading = \get_string('training_details_main_title_error', 'block_attestoodle');
        } else {
            $totalhours = parse_minutes_to_hours($this->training->get_total_milestones());
            // TODO rename string variable.
            $heading = \get_string('training_details_main_title', 'block_attestoodle', $this->training->get_name());
            $heading .= $totalhours;
        }
        return $heading;
    }

    public function get_header() {
        $output = "";

        $output .= \html_writer::start_div('clearfix');
        // Link to the training learners list.
        $output .= \html_writer::link(
                new \moodle_url(
                        '/blocks/attestoodle/index.php',
                        ['page' => 'learners', 'training' => $this->training->get_id()]
                ),
                get_string('training_details_learners_list_btn_text', 'block_attestoodle'),
                array('class' => 'attestoodle-link'));
        $output .= \html_writer::end_div();

        return $output;
    }

    public function get_content() {
        return $this->form->render();
    }

    public function training_exists() {
        return isset($this->training);
    }

    public function get_trainingid() {
        return $this->trainingid;
    }
    public function get_training() {
        return $this->training;
    }
}
