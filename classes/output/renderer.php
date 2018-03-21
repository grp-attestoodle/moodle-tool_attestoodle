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
 * This class is the main renderer of the Attestoodle plug-in.
 * It handles the rendering of each page, called in index.php. The method called
 * depends on the parameters passed to the index.php page (page and action)
 *
 * @package    block_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_attestoodle\output;

use block_attestoodle\output\renderable;
use block_attestoodle\output\renderable\renderable_training_milestones;

use block_attestoodle\forms\training_milestones_update_form;

defined('MOODLE_INTERNAL') || die;

class renderer extends \plugin_renderer_base {
    /**
     * Page trainings list (default page)
     *
     * @param \block_attestoodle\output\renderable\trainings_list $obj Useful informations to display
     * @return string HTML content of the page
     */
    public function render_trainings_list(renderable\trainings_list $obj) {
        $output = "";

        $output .= $obj->get_header();

        if (count($obj->trainings) > 0) {
            $table = new \html_table();
            $table->head = $obj->get_table_head();
            $table->data = $obj->get_table_content();

            $output .= \html_writer::table($table);
        } else {
            $output .= $obj->get_no_training_message();
        }

        return $output;
    }

    public function render_trainings_management(renderable\trainings_management $obj) {
        $output = "";

        $output .= $obj->get_header();

        $output .= $obj->get_content();

        return $output;
    }

    /**
     * Page training learners list
     *
     * @param \block_attestoodle\output\renderable\training_learners_list $obj Useful informations to display
     * @return string HTML content of the page
     */
    public function render_training_learners_list(renderable\training_learners_list $obj) {
        $output = "";

        $output .= $obj->get_header();

        if ($obj->training_exists()) {
            $table = new \html_table();
            $table->head = $obj->get_table_head();
            $table->data = $obj->get_table_content();

            $output .= $this->output->heading(get_string('training_learners_list_heading', 'block_attestoodle', count($obj->training->get_learners())));
            $output .= \html_writer::table($table);
        } else {
            $output .= $obj->get_unknown_training_message();
        }

        return $output;
    }

    /**
     * Page training management (declare milestones)
     *
     * @param \block_attestoodle\output\renderable_training_milestones $obj Useful informations to display
     * @return string HTML content of the page
     */
    public function render_renderable_training_milestones(renderable_training_milestones $obj) {
        $output = "";

        if (!$obj->training_exists()) {
            $output .= get_string('training_details_unknown_training_id', 'block_attestoodle') . $obj->get_trainingid();
        } else {
            $training = $obj->get_training();
            // Instanciate the custom form.
            $mform = new training_milestones_update_form(
                    new \moodle_url(
                            '/blocks/attestoodle/index.php',
                            ['page' => 'trainingmilestones', 'training' => $training->get_id()]),
                    array(
                        'data' => $training->get_courses(),
                        'input_name_prefix' => "attestoodle_activity_id_"
                    )
            );

            // Form processing and displaying is done here.
            if ($mform->is_cancelled()) {
                // Handle form cancel operation.
                $redirecturl = new \moodle_url(
                        '/blocks/attestoodle/index.php',
                        ['page' => 'learners', 'training' => $training->get_id()]);
                // TODO rename string variable
                $message = get_string('training_details_info_form_canceled', 'block_attestoodle');
                redirect($redirecturl, $message, null, \core\output\notification::NOTIFY_INFO);
            } else if ($mform->is_submitted() && has_capability('block/attestoodle:managetraining', \context_system::instance())) {
                // Handle form submit operation.
                // Check the data validity.
                if (!$mform->is_validated()) {
                    // If not valid, warn the user.
                    \core\notification::error(get_string('training_details_error_invalid_form', 'block_attestoodle'));
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
                                            } catch (\Exception $ex) {
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
                                new \moodle_url(
                                        '/blocks/attestoodle/index.php',
                                        ['page' => 'trainingmilestones', 'training' => $training->get_id()]
                                ),
                                array(
                                        'data' => $training->get_courses(),
                                        'input_name_prefix' => "attestoodle_activity_id_"
                                )
                        );
                    } else {
                        // No submitted data.
                        \core\notification::warning(get_string('training_details_warning_no_submitted_data', 'block_attestoodle'));
                    }
                }
            }

            $output .= \html_writer::start_div('clearfix');
            // Link to the training learners list.
            $output .= \html_writer::link(
                    new \moodle_url(
                            '/blocks/attestoodle/index.php',
                            ['page' => 'learners', 'training' => $training->get_id()]
                    ),
                    get_string('training_details_learners_list_btn_text', 'block_attestoodle'),
                    array('class' => 'attestoodle-link'));
            $output .= \html_writer::end_div();

            // Displaying the form in any case but invalid training ID.
            $output .= $mform->render();
        }

        return $output;
    }

    /**
     * Page learner details
     *
     * @param \block_attestoodle\output\renderable\learner_details $obj Useful informations to display
     * @return string HTML content of the page
     */
    public function render_learner_details(renderable\learner_details $obj) {
        $output = "";

        $output .= $obj->get_header();

        if ($obj->training_exists() && $obj->learner_exists()) {
            // If the training and learner ids are valid...
            // Print validated activities informations (with marker only).
            if (count($obj->get_learner_validated_activities()) > 0) {
                $table = new \html_table();
                $table->head = $obj->get_table_head();
                $table->data = $obj->get_table_content();

                $output .= \html_writer::table($table);

                $output .= "<hr />";

                // TODO footer should be displayed even if there is no validated activities
                $output .= $obj->get_footer();
            } else {
                $output .= $obj->get_no_validated_activities_message();
            }
        }

        return $output;
    }
}
