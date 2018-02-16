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

require_once($CFG->dirroot.'/blocks/attestoodle/classes/forms/categories_trainings_update_form.php');

use block_attestoodle\factories\categories_factory;
use block_attestoodle\factories\trainings_factory;
use block_attestoodle\forms\categories_trainings_update_form;

$currenturl = new moodle_url('/blocks/attestoodle/pages/trainings_management.php');
$PAGE->set_url($currenturl);
/* @todo May be replaced by "require_login(...)" + context_system
 * because coursecat throw  an error if id is not valid */
$PAGE->set_context(context_system::instance());

// @todo make a translation
$PAGE->set_title("Moodle - Attestoodle - Gestion des formations");

categories_factory::get_instance()->create_categories();
// Instanciate the custom form.
$mform = new categories_trainings_update_form(
        "",
        array(
                'data' => categories_factory::get_instance()->get_categories(),
                'input_name_prefix' => "attestoodle_category_id_"
        )
);

// Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    // Handle form cancel operation.
    $redirecturl = new moodle_url('/blocks/attestoodle/pages/trainings_list.php');
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
            $successlist = "Categories updated:<ul>";
            $errorlist = "Categories not updated:<ul>";

            foreach ($datafromform as $key => $value) {
                $regexp = "/attestoodle_category_id_(.+)/";
                if (preg_match($regexp, $key, $matches)) {
                    $idcategory = $matches[1];
                    if (!empty($idcategory)) {
                        if ($category = categories_factory::get_instance()->retrieve_category($idcategory)) {
                            $oldistrainingvalue = $category->is_training();
                            $boolvalue = boolval($value);
                            if ($category->set_istraining($boolvalue)) {
                                try {
                                    // Try to persist activity in DB.
                                    $category->persist();

                                    // If no Exception has been thrown by DB update.
                                    $updatecounter++;
                                } catch (Exception $ex) {
                                    // If record in DB failed, re-set the old value.
                                    $category->set_istraining($oldistrainingvalue);
                                    $errorcounter++;
                                }
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
        } else {
            // No submitted data.
            // @todo translations.
            \core\notification::warning("No submitted data");
        }
    }
}

// Setting the total hours after potential form submission.
$PAGE->set_heading("Gestion des formations");
echo $OUTPUT->header();

echo html_writer::start_div('clearfix');
// Link to the trainings list.
echo html_writer::link(
        new moodle_url('/blocks/attestoodle/pages/trainings_list.php'),
        get_string('trainings_list_btn_text', 'block_attestoodle'),
        array('class' => 'attestoodle-link'));
echo html_writer::end_div();

// Displaying the form in any case but invalid training ID.
$mform->display();

// Output footer in any case.
echo $OUTPUT->footer();
