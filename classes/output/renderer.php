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
use block_attestoodle\output\renderable\renderable_trainings_management;
use block_attestoodle\output\renderable\renderable_training_milestones;
use block_attestoodle\output\renderable\renderable_learner_details;

use block_attestoodle\factories\categories_factory;
use block_attestoodle\forms\categories_trainings_update_form;
use block_attestoodle\forms\training_milestones_update_form;

defined('MOODLE_INTERNAL') || die;

class renderer extends \plugin_renderer_base {
    /**
     *
     * @param renderable\trainings_list $obj Useful data to display on the page
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
     * @param renderable\training_learners_list $obj
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
     * @param \block_attestoodle\output\renderable_training_milestones $obj
     * @return string
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

    public function render_renderable_learner_details(renderable_learner_details $obj) {
        $output = "";

        // Verifying training id.
        if (!$obj->training_exists()) {
            $output .= \html_writer::start_div('clearfix');
            // Link to the trainings list if the training id is not valid.
            $output .= \html_writer::link(
                    new \moodle_url('/blocks/attestoodle/index.php', ['page' => 'trainingslist']),
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
                            '/blocks/attestoodle/index.php',
                            ['page' => 'learners', 'training' => $obj->get_trainingid()]),
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
