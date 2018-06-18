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
 * Page training management.
 *
 * Renderable class that is used to render the page that allow user to manage
 * a single training in Attestoodle.
 *
 * @package    block_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_attestoodle\output\renderable;

defined('MOODLE_INTERNAL') || die;

use block_attestoodle\factories\categories_factory;
use block_attestoodle\forms\category_training_update_form;

class training_management implements \renderable {
    /** @var category_training_update_form The form used to manage trainings */
    private $form;

    /** @var integer The category ID that we want to manage */
    private $categoryid = null;

    /** @var category the actual category we want to manage */
    private $category = null;

    /**
     * Constructor method that instanciates the form.
     */
    public function __construct($categoryid) {
        global $PAGE;

        $this->categoryid = $categoryid;

        categories_factory::get_instance()->create_categories_by_ids(array($categoryid));
        $this->category = categories_factory::get_instance()->retrieve_category($categoryid);

        // Handling form is useful only if the category exists.
        if (isset($this->category)) {
            $PAGE->set_heading(get_string('training_management_main_title', 'block_attestoodle', $this->category->get_name()));

            $this->form = new category_training_update_form(
                    new \moodle_url(
                            '/blocks/attestoodle/index.php',
                            array(
                                    'page' => 'trainingmanagement',
                                    'categoryid' => $this->categoryid
                            )),
                    array(
                            'data' => $this->category,
                    ),
                    'get'
            );

            $this->handle_form();
        } else {
            $PAGE->set_heading(get_string('training_management_main_title_no_category', 'block_attestoodle'));
        }
        $chips = true;
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
     * Handles the form cancellation (redirect to trainings list with a message).
     */
    private function handle_form_cancelled() {
        // Handle form cancel operation.
        $redirecturl = new \moodle_url('/blocks/attestoodle/index.php', ['page' => 'trainingslist']);
        $message = get_string('training_management_info_form_canceled', 'block_attestoodle');
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
            $this->handle_form_has_submitted_data();
        }
    }

    /**
     * Handle form submission if its not valid (notify an error to the user).
     */
    private function handle_form_not_validated() {
        // If not valid, warn the user.
        \core\notification::error(get_string('training_management_warning_invalid_form', 'block_attestoodle'));
    }

    /**
     * Handles form submission if its valid. Return a notification message
     * to the user to let him know how much categories have been updated and if
     * there is any error while save in DB.
     *
     * @todo should "@return void Return void if the user has not the rights to update in DB"
     */
    private function handle_form_has_submitted_data() {
        $datafromform = $this->form->get_submitted_data();
        // Instanciate global variables to output to the user.
        $error = false;
        $updated = false;

        $value = $datafromform->checkbox_is_training;

        $category = categories_factory::get_instance()->retrieve_category($this->categoryid);
        $oldistrainingvalue = $category->is_training();
        $boolvalue = boolval($value);

        if ($category->set_istraining($boolvalue)) {
            $updated = true;
            try {
                // Try to persist training in DB.
                $category->persist_training();
            } catch (\Exception $ex) {
                // If record in DB failed, re-set the old value.
                $category->set_istraining($oldistrainingvalue);
                $error = true;
            }
        }

        $message = "";
        if (!$error) {
            if ($updated) {
                if ($boolvalue) {
                    $message .= get_string('training_management_submit_added', 'block_attestoodle');
                } else {
                    $message .= get_string('training_management_submit_removed', 'block_attestoodle');
                }
                \core\notification::success($message);
            } else {
                $message .= get_string('training_management_submit_unchanged', 'block_attestoodle');
                \core\notification::info($message);
            }
        } else {
            $message .= get_string('training_management_submit_error', 'block_attestoodle');
            \core\notification::warning($message);
        }
    }

    /**
     * Computes the content header.
     *
     * @return string The computed HTML string of the page header
     */
    public function get_header() {
        $output = "";

        if (isset($this->category)) {
            $output .= \html_writer::start_div('clearfix');
            // Link back to the category.
            $output .= \html_writer::link(
                    new \moodle_url("/course/index.php", array("categoryid" => $this->category->get_id())),
                    get_string('training_management_backto_category_link', 'block_attestoodle'),
                    array('class' => 'attestoodle-link')
            );
            $output .= \html_writer::end_div();
        }

        return $output;
    }

    /**
     * Render the form.
     *
     * @return string HTML string corresponding to the form
     */
    public function get_content() {
        $output = "";
        if (!isset($this->categoryid)) {
            $output .= get_string('training_management_no_category_id', 'block_attestoodle');
        } else if (!isset($this->category)) {
            $output .= get_string('training_management_unknow_category_id', 'block_attestoodle');
        } else {
            $output .= $this->form->render();

            if ($this->category->is_training()) {
                $output .= \html_writer::start_div('clearfix training-management-content');

                // Link to the learners list of the training.
                $parameters = array(
                        'page' => 'learners',
                        'training' => $this->category->get_id()
                );
                $url = new \moodle_url('/blocks/attestoodle/index.php', $parameters);
                $label = get_string('training_management_training_details_link', 'block_attestoodle');
                $attributes = array('class' => 'attestoodle-button');
                $output .= \html_writer::link($url, $label, $attributes);

                $output .= "<br />";

                // Link to the milestones management of the training.
                $parametersmilestones = array(
                        'page' => 'trainingmilestones',
                        'training' => $this->category->get_id()
                );
                $urlmilestones = new \moodle_url('/blocks/attestoodle/index.php', $parametersmilestones);
                $labelmilestones = get_string('training_management_manage_training_link', 'block_attestoodle');
                $attributesmilestones = array('class' => 'attestoodle-button');
                $output .= \html_writer::link($urlmilestones, $labelmilestones, $attributesmilestones);

                $output .= \html_writer::end_div();
            }
        }
        return $output;
    }
}
