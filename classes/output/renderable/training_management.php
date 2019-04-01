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
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_attestoodle\output\renderable;

defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir.'/tablelib.php');

use tool_attestoodle\factories\categories_factory;
use tool_attestoodle\factories\trainings_factory;
use tool_attestoodle\forms\category_training_update_form;
use tool_attestoodle\utils\db_accessor;
/**
 * Display information of a single training in Attestoodle.
 *
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class training_management implements \renderable {
    /** @var category_training_update_form The form used to manage trainings */
    private $form;

    /** @var integer The category ID that we want to manage */
    private $categoryid = null;

    /** @var category the actual category we want to manage */
    private $category = null;

    /**
     * Constructor method that instanciates the form.
     * @param integer $categoryid Id of the category associate with training (nav bar)
     */
    public function __construct($categoryid) {
        global $PAGE, $DB;

        $this->categoryid = $categoryid;
        $this->category = categories_factory::get_instance()->get_category($categoryid);

        // Handling form is useful only if the category exists.
        if (isset($this->category)) {
            $PAGE->set_heading(get_string('training_management_main_title', 'tool_attestoodle', $this->category->get_name()));

            $idtemplate = -1;
            $idtraining = -1;
            $grp1 = null;
            $grp2 = null;
            if ($this->category->is_training()) {
                $idtemplate = 0;
                $idtraining = $DB->get_field('tool_attestoodle_training', 'id', ['categoryid' => $this->categoryid]);
                if ($DB->record_exists('tool_attestoodle_train_style', ['trainingid' => $idtraining])) {
                    $associate = $DB->get_record('tool_attestoodle_train_style', array('trainingid' => $idtraining));
                    $idtemplate = $associate->templateid;
                    $grp1 = $associate->grpcriteria1;
                    if (empty($grp1)) {
                        $grp1 = 'coursename';
                    }
                    $grp2 = $associate->grpcriteria2;
                    if (empty($grp2)) {
                        $grp2 = '';
                    }
                }
            }
            $context = \context_coursecat::instance($this->categoryid);
            $editmode = has_capability('tool/attestoodle:managetraining', $context);
            $this->form = new category_training_update_form(
                    new \moodle_url('/admin/tool/attestoodle/index.php',
                        array('typepage' => 'trainingmanagement', 'categoryid' => $this->categoryid)),
                        array('data' => $this->category, 'idtemplate' => $idtemplate,
                        'idtraining' => $idtraining, 'editmode' => $editmode), 'get' );
            if ($idtemplate > -1) {
                $this->form->set_data(array ('template' => $idtemplate, 'group1' => $grp1, 'group2' => $grp2));
            }
            $this->handle_form();
        } else {
            $PAGE->set_heading(get_string('training_management_main_title_no_category', 'tool_attestoodle'));
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
     * Handles the form cancellation (redirect to trainings list with a message).
     */
    private function handle_form_cancelled() {
        // Handle form cancel operation.
        $redirecturl = new \moodle_url('/admin/tool/attestoodle/index.php', ['typepage' => 'trainingslist']);
        $message = get_string('training_management_info_form_canceled', 'tool_attestoodle');
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
        \core\notification::error(get_string('training_management_warning_invalid_form', 'tool_attestoodle'));
    }

    /**
     * Handles form submission if its valid. Return a notification message
     * to the user to let him know how much categories have been updated and if
     * there is any error while save in DB.
     *
     * @return void Return void if the user has not the rights to update in DB
     */
    private function handle_form_has_submitted_data() {
        global $DB;
        $context = \context_coursecat::instance($this->categoryid);
        if (has_capability('tool/attestoodle:managetraining', $context)) {
            $datafromform = $this->form->get_submitted_data();
            // Instanciate global variables to output to the user.
            $error = false;
            $updated = false;

            $value = $datafromform->checkbox_is_training;

            $oldistrainingvalue = $this->category->is_training();
            $boolvalue = boolval($value);

            if ($this->category->set_istraining($boolvalue)) {
                $updated = true;
                try {
                    // Try to persist training in DB.
                    $this->category->persist_training();
                } catch (\Exception $ex) {
                    // If record in DB failed, re-set the old value.
                    $this->category->set_istraining($oldistrainingvalue);
                    $error = true;
                }
                // Notify the user of the submission result.
                $this->notify_result($error, $updated, $boolvalue);
                if (!$error) {
                    $redirecturl = new \moodle_url('/admin/tool/attestoodle/index.php',
                        array ('typepage' => 'trainingmanagement', 'categoryid' => $this->categoryid));
                    redirect($redirecturl);
                    return;
                }
            } else {
                $training = trainings_factory::get_instance()->retrieve_training($this->category->get_id());
                if (!empty($training)) {
                    $training->changename($datafromform->name);
                    $nvxtemplate = $datafromform->template;
                    $idtraining = $DB->get_field('tool_attestoodle_training', 'id', ['categoryid' => $this->categoryid]);
                    $record = $DB->get_record('tool_attestoodle_train_style', ['trainingid' => $idtraining]);
                    $record->templateid = $nvxtemplate;
                    $record->grpcriteria1 = $datafromform->group1;
                    $record->grpcriteria2 = $datafromform->group2;
                    if (empty($datafromform->group2)) {
                        $record->grpcriteria2 = null;
                    }
                    \core\notification::info(get_string('updatetraintemplate', 'tool_attestoodle'));
                    $DB->update_record('tool_attestoodle_train_style', $record);
                }
            }
        }
    }

    /**
     * Method that throws a notification to user to let him know the result of
     * the form submission.
     *
     * @param boolean $error If there was an error
     * @param boolean $updated If the training has been updated
     * @param boolean $boolvalue True if the training has been added, false if
     * it has been removed
     */
    private function notify_result($error, $updated, $boolvalue) {
        $message = "";
        if (!$error) {
            if ($updated) {
                if ($boolvalue) {
                    $message .= get_string('training_management_submit_added', 'tool_attestoodle');
                } else {
                    $message .= get_string('training_management_submit_removed', 'tool_attestoodle');
                }
                \core\notification::success($message);
            } else {
                $message .= get_string('training_management_submit_unchanged', 'tool_attestoodle');
                \core\notification::info($message);
            }
        } else {
            $message .= get_string('training_management_submit_error', 'tool_attestoodle');
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

        $retcateg = optional_param('call', null, PARAM_ALPHA);

        if (isset($this->category)) {
            $output .= \html_writer::start_div('clearfix');
            // Link back to the category.
            if (isset($retcateg)) {
                $output .= \html_writer::link(
                    new \moodle_url("/course/index.php", array("categoryid" => $this->category->get_id())),
                    get_string('training_management_backto_category_link', 'tool_attestoodle'),
                    array('class' => 'btn-create pull-right'));
            } else {
                $output .= \html_writer::link(
                    new \moodle_url("/admin/tool/attestoodle/index.php", array()),
                    get_string('training_list_link', 'tool_attestoodle'),
                    array('class' => 'btn-create pull-right'));
            }

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
            $output .= get_string('training_management_no_category_id', 'tool_attestoodle');
        } else if (!isset($this->category)) {
            $output .= get_string('training_management_unknow_category_id', 'tool_attestoodle');
        } else {
            $output .= $this->form->render();

            // Link to the milestones management of the training.
            $parametersmilestones = array(
                'typepage' => 'managemilestones',
                'categoryid' => $this->category->get_id()
                );
            $urlmilestones = new \moodle_url('/admin/tool/attestoodle/index.php', $parametersmilestones);
            $labelmilestones = get_string('training_management_manage_training_link', 'tool_attestoodle');
            $attributesmilestones = array('class' => 'attestoodle-button');

            if ($this->category->is_training()) {
                $output .= "<br/>";

                $training = trainings_factory::get_instance()->retrieve_training($this->category->get_id());
                $tempstotal = db_accessor::get_instance()->is_milestone_set($training->get_id());
                if (isset($tempstotal)) {
                    $output .= "<br/> ". get_string('totaltimetraining', 'tool_attestoodle') .
                        " " . parse_minutes_to_hours($tempstotal) . "<br/>";

                    $context = \context_coursecat::instance($this->categoryid);
                    if (has_capability('tool/attestoodle:managetraining', $context)) {
                        $jalonssuppr = db_accessor::get_instance()->get_milestone_off($training->get_id());
                        $newsact = db_accessor::get_instance()->get_new_activities($training->get_id());
                        if (count($jalonssuppr) > 0) {
                            $output .= "<br/>" . $this->display_deleted_milestone($jalonssuppr);
                        }
                        if (count($newsact) > 0) {
                            $output .= "<br/>" . $this->display_new_activity($newsact);
                        }
                    }
                    $output .= "<br /> ";
                    // Link to the milestones management of the training.
                    $output .= \html_writer::link($urlmilestones, $labelmilestones, $attributesmilestones);
                    $output .= "<br /> ";
                    // Link to the learners list of the training.
                    $parameters = array(
                        'typepage' => 'learners',
                        'categoryid' => $this->category->get_id()
                    );
                    $url = new \moodle_url('/admin/tool/attestoodle/index.php', $parameters);
                    $label = get_string('training_management_training_details_link', 'tool_attestoodle');
                    $attributes = array('class' => 'attestoodle-button');
                    $output .= \html_writer::link($url, $label, $attributes);
                } else {
                    $output .= "<br /> " . get_string('nomilestone', 'tool_attestoodle') . "&nbsp;";
                    $output .= \html_writer::link($urlmilestones, $labelmilestones, $attributesmilestones);
                    $output .= "<br /> ";
                }
            }
        }
        return $output;
    }

    /**
     * Displays the list of orphaned milestones (the activity on which it is based no longer exists).
     *
     * @param stdClass $jalonssuppr List of milestone deleted.
     */
    private function display_deleted_milestone($jalonssuppr) {
        $ret = \html_writer::start_div('clearfix training-management-error');
        $ret .= "<h4>" . get_string('milestoneorphan', 'tool_attestoodle') ."</h4>";
        $table = new \html_table();

        $table->head = array(get_string('grp_course', 'tool_attestoodle'),
            get_string('grp_activity', 'tool_attestoodle'),
            get_string('timecredited', 'tool_attestoodle'));

        foreach ($jalonssuppr as $milestonedelete) {
            $table->data[] = array(
                                $milestonedelete->fullname,
                                $milestonedelete->name,
                                $milestonedelete->creditedtime);
        }
        $ret .= \html_writer::table($table);
        $deletelink = \html_writer::link(
                    new \moodle_url(
                            '/admin/tool/attestoodle/index.php',
                            array(
                                    'typepage' => 'trainingmanagement',
                                    'action' => 'deleteErrMilestone',
                                    'categoryid' => $this->categoryid
                            )
                    ),
                    get_string('btn_deletemilestonerr', 'tool_attestoodle'),
                    array('class' => 'btn btn-default attestoodle-button'));
        $ret .= "<br/>" . $deletelink;
        $ret .= \html_writer::end_div();
        return $ret;
    }

    /**
     * Displays the list of new activities.
     * Their creation date is more recent than the last modification of a milestone.
     *
     * @param stdClass $newsact List of activities added.
     */
    private function display_new_activity($newsact) {
        $ret = \html_writer::start_div('clearfix training-management-notif');
        $ret .= "<h4>" . get_string('milestonenews', 'tool_attestoodle') ."</h4>";
        $table = new \html_table();
        $table->head = array(get_string('grp_course', 'tool_attestoodle'),
            get_string('nbnewactivity', 'tool_attestoodle'));

        foreach ($newsact as $newact) {
            $table->data[] = array(
                                $newact->fullname,
                                $newact->nb);
        }
        $ret .= \html_writer::table($table);

        $deletelink = \html_writer::link(
                    new \moodle_url(
                            '/admin/tool/attestoodle/index.php',
                            array(
                                    'typepage' => 'trainingmanagement',
                                    'action' => 'deleteNotification',
                                    'categoryid' => $this->categoryid
                            )
                    ),
                    get_string('btn_deletenotification', 'tool_attestoodle'),
                    array('class' => 'btn btn-default attestoodle-button'));
        $ret .= "<br/>" . $deletelink;
        $ret .= \html_writer::end_div();
        return $ret;
    }
}
