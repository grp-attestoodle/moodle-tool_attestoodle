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
use tool_attestoodle\forms\add_course_form;
use tool_attestoodle\utils\db_accessor;
use tool_attestoodle\utils\plugins_accessor;
/**
 * Display information of a single training in Attestoodle.
 *
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class training_management implements \renderable {
    /** @var category_training_update_form The form used to manage trainings */
    private $form;
    /** @var add_course_form The form use to add course.*/
    private $form2;

    /** @var integer The category ID that we want to manage */
    private $categoryid = null;

    /** @var category the actual category we want to manage */
    private $category = null;

    /** @var integer Id of the training. */
    private $trainingid = null;
    /**
     * Constructor method that instanciates the form.
     *
     * @param integer $categoryid Id of the category associate with training (nav bar)
     * @param integer $trainingid Id of the training managed.
     */
    public function __construct($categoryid, $trainingid) {
        global $PAGE, $DB;

        $this->categoryid = $categoryid;
        $this->category = categories_factory::get_instance()->get_category($categoryid);
        $this->trainingid = $trainingid;

        // Handling form is useful only if the category exists.
        if (isset($this->category)) {
            $PAGE->set_heading(get_string('training_management_main_title', 'tool_attestoodle', $this->category->get_name()));

            $idtemplate = -1;
            $grp1 = null;
            $grp2 = null;
            $training = null;
            if ($trainingid > 0) {
                $idtemplate = 0;
                if ($DB->record_exists('tool_attestoodle_train_style', ['trainingid' => $trainingid])) {
                    $associate = $DB->get_record('tool_attestoodle_train_style', array('trainingid' => $trainingid));
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
                $training = trainings_factory::get_instance()->create_training_by_category($categoryid, $trainingid);
            }
            $context = \context_coursecat::instance($this->categoryid);
            $editmode = has_capability('tool/attestoodle:managetraining', $context);
            $this->form = new category_training_update_form(
                    new \moodle_url('/admin/tool/attestoodle/index.php',
                        array('typepage' => 'trainingmanagement',
                        'categoryid' => $this->categoryid,
                        'trainingid' => $trainingid)),
                        array('data' => $this->category, 'idtemplate' => $idtemplate,
                        'trainingid' => $trainingid, 'editmode' => $editmode), 'get' );
            if ($training) {
                $this->form->set_data(array ('startdate' => $training->get_start(),
                    'enddate' => $training->get_end(), 'duration' => $training->get_duration()));
            }
            if ($idtemplate > -1) {
                $this->form->set_data(array ('template' => $idtemplate, 'group1' => $grp1, 'group2' => $grp2));
            }
            $this->handle_form();
            if ($editmode) {
                $this->form2 = new add_course_form(
                    new \moodle_url('/admin/tool/attestoodle/classes/training/course_outof_categ.php',
                        array('categoryid' => $this->categoryid, 'trainingid' => $trainingid)),
                        array('data' => $this->category), 'get');
            }
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

            if (isset($datafromform->delete)) {
                $redirecturl = new \moodle_url('/admin/tool/attestoodle/classes/training/delete_training.php',
                        array (
                            'trainingid' => $this->trainingid,
                            'categoryid' => $this->categoryid));
                redirect($redirecturl);
                return;
            }

            if ($this->trainingid > 0) {
                trainings_factory::get_instance()->create_training_by_category($this->categoryid, $this->trainingid);
                $training = trainings_factory::get_instance()->retrieve_training_by_id($this->trainingid);
                if (!empty($training)) {
                    $training->change($datafromform->name, $datafromform->startdate,
                        $datafromform->enddate, $datafromform->duration);
                    $nvxtemplate = $datafromform->template;

                    $record = $DB->get_record('tool_attestoodle_train_style', ['trainingid' => $this->trainingid]);
                    $record->templateid = $nvxtemplate;
                    $record->grpcriteria1 = $datafromform->group1;
                    $record->grpcriteria2 = $datafromform->group2;
                    if (empty($datafromform->group2)) {
                        $record->grpcriteria2 = null;
                    }
                    \core\notification::info(get_string('updatetraintemplate', 'tool_attestoodle'));
                    $DB->update_record('tool_attestoodle_train_style', $record);
                }
            } else {
                if (!isset($this->category)) {
                    $this->category = categories_factory::get_instance()->get_category($this->categoryid);
                }
                if (isset($datafromform->create_no)) {
                    $redirecturl = new \moodle_url("/course/index.php", array("categoryid" => $this->categoryid));
                    redirect($redirecturl);
                    return;
                }
                // Create new training.
                $newid = trainings_factory::get_instance()->add_training($this->category);
                \core\notification::info(get_string('training_management_submit_added', 'tool_attestoodle'));
                $redirecturl = new \moodle_url('/admin/tool/attestoodle/index.php',
                        array (
                            'typepage' => 'trainingmanagement',
                            'categoryid' => $this->categoryid,
                            'trainingid' => $newid));
                redirect($redirecturl);
            }
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
        } else { // Add a new training ?
            if ($this->trainingid == -1) {
                trainings_factory::get_instance()->find_training($this->categoryid);
                trainings_factory::get_instance()->create_trainings_4_categ($this->categoryid);
                $nbtotal = trainings_factory::get_instance()->get_count_training_by_categ($this->categoryid);
                if ($nbtotal > 0) {
                    $tabtraining = trainings_factory::get_instance()->get_trainings();
                    $output .= get_string('notifytotaltraining', 'tool_attestoodle', $nbtotal) . " <ul>";

                    foreach ($tabtraining as $train) {
                        $parameters = array(
                            'typepage' => 'trainingmanagement',
                            'categoryid' => $this->categoryid,
                            'trainingid' => $train->get_id()
                        );
                        $url = new \moodle_url('/admin/tool/attestoodle/index.php', $parameters);
                        $output .= " <li>" . \html_writer::link($url, $train->get_name() . "</li>", $parameters);
                    }
                    $output .= "</ul>";
                }
                if ($nbtotal > 10) {
                    $parameters = array('typepage' => 'trainingslist');
                    $url = new \moodle_url('/admin/tool/attestoodle/index.php', $parameters);
                    $output .= \html_writer::link($url, get_string('linktotraininglst', 'tool_attestoodle'), $parameters);
                }
            }
            $output .= $this->form->render();

            // Link to the milestones management of the training.
            $parametersmilestones = array(
                'typepage' => 'managemilestones',
                'categoryid' => $this->categoryid,
                'trainingid' => $this->trainingid
                );
            $urlmilestones = new \moodle_url('/admin/tool/attestoodle/index.php', $parametersmilestones);
            $labelmilestones = get_string('training_management_manage_training_link', 'tool_attestoodle');
            $attributesmilestones = array('class' => 'attestoodle-button');

            if ($this->trainingid > -1) {
                $output .= "<br/><legend class='ftogger'><a class='fheader' href='#'>" .
                    get_string('milestones', 'tool_attestoodle') . "</a></legend>";

                trainings_factory::get_instance()->create_training_by_category($this->categoryid, $this->trainingid);
                $training = trainings_factory::get_instance()->retrieve_training_by_id($this->trainingid);

                $tempstotal = db_accessor::get_instance()->is_milestone_set($training->get_id());
                if (isset($tempstotal)) {
                    $output .= "<br/> ". get_string('totaltimetraining', 'tool_attestoodle') .
                        " " . parse_minutes_to_hours($tempstotal) . "<br/>";

                    $context = \context_coursecat::instance($this->categoryid);
                    if (has_capability('tool/attestoodle:managetraining', $context)) {
                        $jalonssuppr = db_accessor::get_instance()->get_milestone_off($this->trainingid);
                        $newsact = db_accessor::get_instance()->get_new_activities($this->trainingid);
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
                    $output .= $this->form2->render();

                    $countlearner = $training->count_learners();
                    $textlearner = get_string('learners', 'tool_attestoodle') . " (". $countlearner. ")";
                    $output .= "<br/><legend class='ftogger'><a class='fheader' href='#'>" .
                        $textlearner . "</a></legend>";

                    $parameters = array(
                        'categoryid' => $this->categoryid,
                        'trainingid' => $this->trainingid
                    );
                    $url = new \moodle_url('/admin/tool/attestoodle/classes/training/select_learners.php', $parameters);
                    $label = get_string('selectlearner', 'tool_attestoodle');
                    $attributes = array('class' => 'btn btn-default attestoodle-button');
                    $output .= \html_writer::link($url, $label, $attributes);

                    // Link to the learners list of the training.
                    $parameters = array(
                        'typepage' => 'learners',
                        'categoryid' => $this->categoryid,
                        'trainingid' => $this->trainingid
                    );
                    if ($countlearner > 0) {
                        $url = new \moodle_url('/admin/tool/attestoodle/index.php', $parameters);
                        $label = get_string('training_management_training_details_link', 'tool_attestoodle');
                        $attributes = array('class' => 'btn btn-default attestoodle-button');
                        $output .= "&nbsp;&nbsp;" . \html_writer::link($url, $label, $attributes);
                    }
                } else {
                    $output .= "<br /> " . get_string('nomilestone', 'tool_attestoodle') . "&nbsp;";
                    $output .= \html_writer::link($urlmilestones, $labelmilestones, $attributesmilestones);
                    $output .= "<br /> ";
                    $output .= $this->form2->render();
                }
            }
        }

        if ($this->trainingid != -1) {
            $output .= " &nbsp;" . plugins_accessor::get_instance()->get_save_btn($this->trainingid);
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
                                    'categoryid' => $this->categoryid,
                                    'trainingid' => $this->trainingid
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
                                    'categoryid' => $this->categoryid,
                                    'trainingid' => $this->trainingid
                            )
                    ),
                    get_string('btn_deletenotification', 'tool_attestoodle'),
                    array('class' => 'btn btn-default attestoodle-button'));
        $ret .= "<br/>" . $deletelink;
        $ret .= \html_writer::end_div();
        return $ret;
    }
}
