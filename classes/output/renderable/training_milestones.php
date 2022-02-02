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
 * Page training-milestone management.
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_attestoodle\output\renderable;


use tool_attestoodle\factories\trainings_factory;
use tool_attestoodle\forms\training_milestones_update_form;
/**
 * Display milestone of one training.
 *
 * Renderable class that is used to render the page that allow user to manage
 * the milestones of a training.
 *
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class training_milestones implements \renderable {
    /** @var integer Id of the category associate to training displayed */
    private $categoryid;
    /** @var training Actual training displayed */
    private $training;
    /** @var training_milestones_update_form The form used to manage milestones */
    private $form;
    /** @var trainingid Actual identifier of training displayed */
    private $trainingid;
    /**
     * Constructor method that computes training ID to an actual training.
     *
     * @param integer $categoryid The category ID requested.
     * @param integer $trainingid The training ID requested.
     */
    public function __construct($categoryid, $trainingid) {
        $this->categoryid = $categoryid;
        $this->training = trainings_factory::get_instance()->retrieve_training_by_id($trainingid);
        $this->trainingid = required_param('trainingid', PARAM_INT);

        $type = optional_param('type', null, PARAM_ALPHANUMEXT);
        $namemod = optional_param('namemod', null, PARAM_TEXT);
        $visibmod = optional_param('visibmod', 0, PARAM_INT);
        $restrictmod = optional_param('restrictmod', 0, PARAM_INT);
        $milestonemod = optional_param('milestonemod', 0, PARAM_INT);
        $orderbyselection = optional_param('orderbyselection', 0, PARAM_INT);
        // The orderbyfrom fields group may appears as an array of 3 values (day, month and year) when in POST form's data,
        // But sometime also as a unique timestamp value when in GET form's data, and both POST and GET are currently used.
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $orderbyfrom = optional_param('orderbyfrom', 0, PARAM_INT);
        } else {
            $orderbyfrom = optional_param_array('orderbyfrom', 0, PARAM_INT);
        }

        if ($this->training_exists()) {
            $courses = $this->training->get_courses();
            if (count($courses) == 0) {
                $message = get_string('infonocourses', 'tool_attestoodle');
                $this->goback($message);
            } else {
                $context = \context_coursecat::instance($categoryid);
                $modifallow = false;
                if (has_capability('tool/attestoodle:managemilestones', $context)) {
                    $modifallow = true;
                }
                $url = new \moodle_url(
                            '/admin/tool/attestoodle/index.php',
                            ['typepage' => 'managemilestones',
                            'categoryid' => $this->training->get_categoryid(),
                            'trainingid' => $this->trainingid]);
                $this->form = new training_milestones_update_form($url,
                                    array(
                                            'data' => $this->training->get_courses(),
                                            'input_name_prefix' => "attestoodle_activity_id_",
                                            'type' => $type,
                                            'namemod' => $namemod,
                                            'visibmod' => $visibmod,
                                            'restrictmod' => $restrictmod,
                                            'milestonemod' => $milestonemod,
                                            'orderbyselection' => $orderbyselection,
                                            'orderbyfrom' => $orderbyfrom,
                                            'modifallow' => $modifallow
                                          ) );
                $this->handle_form();
            }
        }
    }

    /**
     * Main form handling method (calls other actual handling method).
     *
     * @return void Return void if no handling is needed (first render).
     */
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

    /**
     * Handles the form cancellation (redirect to training details with a message).
     */
    private function handle_form_cancelled() {
        // Handle form cancel operation.
        $message = get_string('training_milestones_info_form_canceled', 'tool_attestoodle');
        $this->goback($message);
    }

    /**
     * Redirect and display message.
     * @param string $message to display.
     */
    private function goback($message) {
        $redirecturl = new \moodle_url(
                '/admin/tool/attestoodle/index.php',
                array('typepage' => 'trainingmanagement',
                    'categoryid' => $this->training->get_categoryid(),
                    'trainingid' => $this->trainingid)
        );
        redirect($redirecturl, $message, null, \core\output\notification::NOTIFY_INFO);
    }

    /**
     * Handles the form submission (calls other actual form submission handling
     * methods).
     */
    private function handle_form_submitted() {
         // Handle form submit operation.
        // Check the data validity.
        if (!$this->form->is_validated()) {
            $this->handle_form_not_validated();
        } else {
            // If data are valid, process persistance.
            // Try to retrieve the submitted data.
            $datafromform = $this->form->get_submitted_data();
            if (isset($datafromform->filterbtn) || isset($datafromform->orderbybtn)) {
                $url = new \moodle_url('/admin/tool/attestoodle/index.php',
                [
                'typepage' => 'managemilestones',
                'categoryid' => $this->training->get_categoryid(),
                'type' => $datafromform->typemod,
                'namemod' => $datafromform->namemod,
                'visibmod' => $datafromform->visibmod,
                'restrictmod' => $datafromform->restrictmod,
                'milestonemod' => $datafromform->milestonemod,
                'orderbyselection' => $datafromform->orderbyselection,
                'orderbyfrom' => $datafromform->orderbyfrom,
                'trainingid' => $this->trainingid
                ]);
                redirect($url);
                return;
            }
            $this->handle_form_has_submitted_data();
        }
    }

    /**
     * Handle form submission if its not valid (notify an error to the user).
     */
    private function handle_form_not_validated() {
        // If not valid, warn the user.
        \core\notification::error(get_string('training_milestones_error_invalid_form', 'tool_attestoodle'));
    }

    /**
     * Handles form submission if its valid. Return a notification message
     * to the user to let him know how much activites have been updated and if
     * there is any error while save in DB.
     *
     * @todo create a new private method to notify the user
     * @todo translations
     *
     * @return void Return void if the user has not the rights to update in DB
     */
    private function handle_form_has_submitted_data() {
        // If data are valid, process persistance.
        $contextcateg = \context_coursecat::instance($this->categoryid);
        if (has_capability('tool/attestoodle:managemilestones', $contextcateg)) {
            // Retrieve the submitted data.
            $datafromform = $this->form->get_submitted_data();

            // Instanciate global variables to output to the user.
            $updatecounter = 0;
            $errorcounter = 0;
            $successlist = \get_string('activitiesupdated', 'tool_attestoodle') . ":<ul>";
            $errorlist = \get_string('activitiesnoupdated', 'tool_attestoodle') . ":<ul>";

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
                            '/admin/tool/attestoodle/index.php',
                            ['typepage' => 'managemilestones',
                            'categoryid' => $this->training->get_categoryid(),
                            'trainingid' => $this->trainingid]
                    ),
                    array(
                        'data' => $this->training->get_courses(),
                        'input_name_prefix' => "attestoodle_activity_id_",
                        'type' => $datafromform->typemod,
                        'namemod' => $datafromform->namemod,
                        'visibmod' => $datafromform->visibmod,
                        'restrictmod' => $datafromform->restrictmod,
                        'milestonemod' => $datafromform->milestonemod,
                        'orderbyselection' => $datafromform->orderbyselection,
                        'orderbyfrom' => $datafromform->orderbyfrom,
                        'modifallow' => $datafromform->edition
                    )
            );
        } else {
            return;
        }
    }

    /**
     * Handle the process of update of one activity after the form has been
     * submitted (and its valid).
     * @param string $key Input name being computed
     * @param string $value Input value being computed
     * @return \stdClass A standard object defining the state of the current
     * update activity where:
     *   status = -1 || 0 || 1 (-1 = error, 0 = nothing to update, 1 = updated)
     *   activityname = name of the activity being updated || null if no udpate
     *   oldvalue = old value of the milestone activity (before update)
     *   new value = new value of the milestone activity (after update)
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
                        $activity->persist($this->training->get_id());

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

    /**
     * Instanciate the title of the page, in the header, depending on the state
     * of the page (error or OK).
     *
     * @return string The title of the page
     */
    public function get_heading() {
        $heading = "";
        if (!$this->training_exists()) {
            $heading = \get_string('training_milestones_main_title_error', 'tool_attestoodle');
        } else {
            $totalhours = parse_minutes_to_hours($this->training->get_total_milestones());
            $heading = \get_string('training_milestones_main_title', 'tool_attestoodle', $this->training->get_name());
            $heading .= $totalhours;
        }
        return $heading;
    }

    /**
     * Computes the content header.
     *
     * @return string The computed HTML string of the page header
     */
    public function get_header() {
        $output = "";

        $output .= \html_writer::start_div('clearfix');
        $output .= \html_writer::end_div();

        return $output;
    }

    /**
     * Render the form.
     *
     * @return string HTML string corresponding to the form
     */
    public function get_content() {
        return $this->form->render();
    }

    /**
     * Checks if the training is a valid one.
     *
     * @return boolean True if the training exists
     */
    public function training_exists() {
        return isset($this->training);
    }

    /**
     * Getter for $trainingid property.
     *
     * @return integer The cataegory ID
     */
    public function get_categoryid() {
        return $this->categoryid;
    }

    /**
     * Getter for $training property.
     *
     * @return training The actual training
     */
    public function get_training() {
        return $this->training;
    }
}
