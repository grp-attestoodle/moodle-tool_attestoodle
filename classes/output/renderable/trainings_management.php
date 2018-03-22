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
 * Renderable page that computes infos to give to the template
 */

namespace block_attestoodle\output\renderable;

defined('MOODLE_INTERNAL') || die;

use block_attestoodle\factories\categories_factory;
use block_attestoodle\forms\categories_trainings_update_form;

class trainings_management implements \renderable {
    private $form;

    public function __construct() {
        $categories = categories_factory::get_instance()->get_categories();
        $this->form = new categories_trainings_update_form(
                new \moodle_url('/blocks/attestoodle/index.php', ['page' => 'trainingsmanagement']),
                array(
                        'data' => $categories,
                        'input_name_prefix' => "attestoodle_category_id_"
                )
        );

        $this->handle_form();
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
        $redirecturl = new \moodle_url('/blocks/attestoodle/index.php', ['page' => 'trainingslist']);
        $message = get_string('trainings_management_info_form_canceled', 'block_attestoodle');
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
        \core\notification::error(get_string('trainings_management_warning_invalid_form', 'block_attestoodle'));
    }

    private function handle_form_has_submitted_data() {
        $datafromform = $this->form->get_submitted_data();
        // Instanciate global variables to output to the user.
        $updatecounter = 0;
        $errorcounter = 0;

        foreach ($datafromform as $key => $value) {
            $matches = [];
            $regexp = "/attestoodle_category_id_(.+)/";
            if (preg_match($regexp, $key, $matches)) {
                $idcategory = $matches[1];
                if (!empty($idcategory) && categories_factory::get_instance()->has_category($idcategory)) {
                    $category = categories_factory::get_instance()->retrieve_category($idcategory);
                    $oldistrainingvalue = $category->is_training();
                    $boolvalue = boolval($value);
                    if ($category->set_istraining($boolvalue)) {
                        try {
                            // Try to persist activity in DB.
                            $category->persist();

                            // If no Exception has been thrown by DB update.
                            $updatecounter++;
                        } catch (\Exception $ex) {
                            // If record in DB failed, re-set the old value.
                            $category->set_istraining($oldistrainingvalue);
                            $errorcounter++;
                        }
                    }
                }
            }
        }

        $message = "";
        if ($errorcounter == 0) {
            $message .= "Form submitted. <br />"
                    . "{$updatecounter} categories updated <br />";
            \core\notification::success($message);
        } else {
            $message .= "Form submitted with errors. <br />"
                    . "{$updatecounter} categories updated <br />"
                    . "{$errorcounter} errors (categories not updated in database).<br />";
            \core\notification::warning($message);
        }
    }

    public function get_header() {
        $output = "";

        $output .= \html_writer::start_div('clearfix');
        // Link to the trainings list.
        $output .= \html_writer::link(
                new \moodle_url('/blocks/attestoodle/index.php', ['page' => 'trainingslist']),
                get_string('trainings_management_trainings_list_link', 'block_attestoodle'),
                array('class' => 'attestoodle-link')
        );
        $output .= \html_writer::end_div();

        return $output;
    }

    public function get_content() {
        return $this->form->render();
    }
}
