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

use block_attestoodle\output\renderable\renderable_trainings_management;
use block_attestoodle\output\renderable\renderable_trainings_list;
use block_attestoodle\output\renderable\renderable_training_learners_list;
use block_attestoodle\output\renderable\renderable_learner_details;

use block_attestoodle\factories\categories_factory;
use block_attestoodle\forms\categories_trainings_update_form;

defined('MOODLE_INTERNAL') || die;

class renderer extends \plugin_renderer_base {

    // Automagically called
    public function render_renderer_with_template_page(renderer_with_template_page $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('block_attestoodle/renderer_template', $data);
    }

    // Automagically called
    public function render_renderer_simple_page(renderer_simple_page $page) {
        $out = $this->output->heading($page->training->get_name());
        $out .= $this->output->container($page->sometext);
        return $out;
    }

    /**
     * Page trainings management
     *
     * @param renderable_trainings_management $obj
     * @return type
     */
    public function render_renderable_trainings_management(renderable_trainings_management $obj) {
        $output = "";

        $mform = new categories_trainings_update_form(
                new \moodle_url('/blocks/attestoodle/index.php', ['page' => 'trainingsmanagement']),
                array(
                        'data' => $obj->get_categories(),
                        'input_name_prefix' => "attestoodle_category_id_"
                )
        );

        // Form processing and displaying is done here.
        if ($mform->is_cancelled()) {
            // Handle form cancel operation.
            $redirecturl = new \moodle_url('/blocks/attestoodle/index.php', ['page' => 'trainingslist']);
            $message = get_string('trainings_management_info_form_canceled', 'block_attestoodle');
            redirect($redirecturl, $message, null, \core\output\notification::NOTIFY_INFO);
        } else if ($mform->is_submitted()) {
            // Handle form submit operation.
            // Check the data validity.
            if (!$mform->is_validated()) {
                // If not valid, warn the user.
                \core\notification::error(get_string('trainings_management_warning_invalid_form', 'block_attestoodle'));
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
                    \core\notification::warning(get_string('trainings_management_warning_no_submitted_data', 'block_attestoodle'));
                }
            }
        }

        $output .= \html_writer::start_div('clearfix');
        // Link to the trainings list.
        $output .= \html_writer::link(
                new \moodle_url(
                        '/blocks/attestoodle/index.php',
                        ['page' => 'trainingslist']),
                get_string('trainings_management_trainings_list_link', 'block_attestoodle'),
                array('class' => 'attestoodle-link'));
        $output .= \html_writer::end_div();

        // Displaying the form in any case.
        $output .= $mform->render();

        return $output;
    }

    /**
     *
     * @param renderable_trainings_list $data Useful data to display on the page
     */
    public function render_renderable_trainings_list(renderable_trainings_list $obj) {
        // create and return the output
        $trainings = $obj->trainings;
        $output = "";

        $output .= \html_writer::start_div('clearfix');
        // Link to the trainings management page.
        $output .= \html_writer::link(
                new \moodle_url('/blocks/attestoodle/index.php', ['page' => 'trainingsmanagement']),
                get_string('trainings_list_manage_trainings_link', 'block_attestoodle'),
                array('class' => 'btn btn-default attestoodle-button'));
        $output .= \html_writer::end_div();

        if (count($trainings) > 0) {
            $data = parse_trainings_as_stdclass($trainings);

            $table = new \html_table();
            $table->head = array(
                get_string('trainings_list_table_header_column_name', 'block_attestoodle'),
                get_string('trainings_list_table_header_column_hierarchy', 'block_attestoodle'),
                get_string('trainings_list_table_header_column_description', 'block_attestoodle'),
                '');
            $table->data = $data;

            $output .= \html_writer::table($table);
        } else {
            $message = get_string('trainings_list_warning_no_trainings', 'block_attestoodle');
            $output .= $message;
        }

        return $output;
    }

    /**
     *
     * @param renderable_training_learners_list $obj
     */
    public function render_renderable_training_learners_list(renderable_training_learners_list $obj) {
         $training = $obj->training;
         $trainingexist = isset($training);
         $output = "";

        $output .= \html_writer::start_div('clearfix');
        // Link to the trainings list.
        $output .= \html_writer::link(
                new \moodle_url('/blocks/attestoodle/index.php'),
                get_string('trainings_list_btn_text', 'block_attestoodle'),
                array('class' => 'attestoodle-link'));

        if (!$trainingexist) {
            $output .= \html_writer::end_div();
            $warningunknownid = get_string('training_details_unknown_training_id', 'block_attestoodle');
            $output .= $warningunknownid;
        } else {
            $trainingid = $training->get_id();
            // Link to the training details.
            $output .= \html_writer::link(
                    new \moodle_url(
                            '/blocks/attestoodle/pages/training_details.php',
                            array('id' => $trainingid)),
                    get_string('training_learners_list_edit_training_link', 'block_attestoodle'),
                    array('class' => 'btn btn-default attestoodle-button'));
            $output .= \html_writer::end_div();

            $data = parse_learners_as_stdclass($training->get_learners(), $trainingid);
            $table = new \html_table();
            $table->head = array(
                get_string('training_learners_list_table_header_column_lastname', 'block_attestoodle'),
                get_string('training_learners_list_table_header_column_firstname', 'block_attestoodle'),
                get_string('training_learners_list_table_header_column_validated_activities', 'block_attestoodle'),
                get_string('training_learners_list_table_header_column_total_milestones', 'block_attestoodle'),
                '');
            $table->data = $data;

            $output .= $this->output->heading(get_string('training_learners_list_heading', 'block_attestoodle', count($data)));
            $output .= \html_writer::table($table);
        }

        return $output;
    }

    public function render_renderable_learner_details(renderable_learner_details $obj) {
        $output = "";

        // Verifying training id.
        if (!$obj->training_exists()) {
            $output .= \html_writer::start_div('clearfix');
            // Link to the trainings list if the training id is not valid.
            $output .= \html_writer::link(
                    new \moodle_url(
                            '/blocks/attestoodle/pages/trainings_list.php',
                            array()),
                    get_string('backto_trainings_list_btn_text', 'block_attestoodle'),
                    array('class' => 'attestoodle-link'));
            $output .= \html_writer::end_div();
            $output .= "<hr />";
            $output .= get_string('unknown_training_id', 'block_attestoodle', $obj->get_trainingid());
        } else {
            // If the training id is valid...
            $output .= \html_writer::start_div('clearfix');
            // Link to the training learners list.
            $output .= \html_writer::link(
                    new \moodle_url(
                            '/blocks/attestoodle/pages/training_learners_list.php',
                            array('id' => $obj->get_trainingid())),
                    \get_string('backto_training_learners_list_btn_text', 'block_attestoodle'),
                    array('class' => 'attestoodle-link'));
            $output .= \html_writer::end_div();

            $output .= "<hr />";

            // Verifying learner id.
            if (!$obj->learner_exists()) {
                $output .= \get_string('unknown_learner_id', 'block_attestoodle', $obj->get_learnerid());
            } else {
                // Basic form to allow user filtering the validated activities by begin and end dates.
                $output .= '<form action="?" class="filterform"><div>'
                        . '<input type="hidden" name="page" value="learnerdetails" />'
                        . '<input type="hidden" name="training" value="' . $obj->get_trainingid() . '" />'
                        . '<input type="hidden" name="learner" value="' . $obj->get_learnerid() . '" />';
                $output .= '<label for="input_begin_date">'
                        . get_string('learner_details_begin_date_label', 'block_attestoodle') . '</label>'
                        . '<input type="text" id="input_begin_date" name="begindate" value="' . $obj->get_begindate() . '" '
                        . 'placeholder="ex: ' . (new \DateTime('now'))->format('Y-m-d') . '" />';
                if ($obj->has_begindateerror()) {
                    echo "<span class='error'>Erreur de format</span>";
                }
                $output .= '<label for="input_end_date">' . get_string('learner_details_end_date_label', 'block_attestoodle') . '</label>'
                        . '<input type="text" id="input_end_date" name="enddate" value="' . $obj->get_enddate() . '" '
                        . 'placeholder="ex: ' . (new \DateTime('now'))->format('Y-m-d') . '" />';
                if ($obj->has_enddateerror()) {
                    $output .= "<span class='error'>Erreur de format</span>";
                }
                $output .= '<input type="submit" value="'
                        . get_string('learner_details_submit_button_value', 'block_attestoodle') . '" />'
                        . '</div></form>' . "\n";

                $output .= "<hr />";

                // If the learner id is valid...
                // Print validated activities informations (with marker only).
                $validatedactivities = $obj->get_learner()->get_validated_activities_with_marker($obj->get_actualbegindate(), $obj->get_searchenddate());
                if (count($validatedactivities) == 0) {
                    $output .= get_string('learner_details_no_validated_activities', 'block_attestoodle');
                } else {
                    // Generate table listing the activities.
                    $table = new \html_table();

                    $table->head = array(
                        get_string('learner_details_table_header_column_training_name', 'block_attestoodle'),
                        get_string('learner_details_table_header_column_course_name', 'block_attestoodle'),
                        get_string('learner_details_table_header_column_name', 'block_attestoodle'),
                        get_string('learner_details_table_header_column_type', 'block_attestoodle'),
                        get_string('learner_details_table_header_column_validated_time', 'block_attestoodle'),
                        get_string('learner_details_table_header_column_milestones', 'block_attestoodle')
                    );

                    $data = array();
                    foreach ($validatedactivities as $vact) {
                        $act = $vact->get_activity();
                        $stdclassact = new \stdClass();

                        $stdclassact->trainingname = $act->get_course()->get_training()->get_name();
                        $stdclassact->coursename = $act->get_course()->get_name();
                        $stdclassact->name = $act->get_name();
                        $stdclassact->type = get_string('modulename', $act->get_type());
                        $stdclassact->validatedtime = parse_datetime_to_readable_format($vact->get_datetime());
                        $stdclassact->milestone = parse_minutes_to_hours($act->get_marker());

                        $data[] = $stdclassact;
                    }
                    $table->data = $data;

                    $output .= \html_writer::table($table);

                    $output .= "<hr />";

                    // Instanciate the "Generate certificate" link with specified filters.
                    $dlcertifoptions = array('training' => $obj->get_trainingid(), 'user' => $obj->get_learnerid());
                    if ($obj->get_actualbegindate()) {
                        $dlcertifoptions['begindate'] = $obj->get_actualbegindate()->format('Y-m-d');
                    }
                    if ($obj->get_actualenddate()) {
                        $dlcertifoptions['enddate'] = $obj->get_actualenddate()->format('Y-m-d');
                    }
                    // Print the "Generate certificate" link.
                    $output .= \html_writer::start_div('clearfix');
                    $output .= \html_writer::link(
                            new \moodle_url(
                                    '/blocks/attestoodle/pages/download_certificate.php',
                                     $dlcertifoptions),
                            get_string('learner_details_generate_certificate_link', 'block_attestoodle'),
                            array('class' => 'attestoodle-link'));
                    $output .= \html_writer::end_div();
                }
            }
        }

        return $output;
    }
}
