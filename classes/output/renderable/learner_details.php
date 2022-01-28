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
 * Page learner details.
 *
 * This class implements the moodle renderable interface to help rendering
 * the learner_details page.
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_attestoodle\output\renderable;


use tool_attestoodle\factories\learners_factory;
use tool_attestoodle\factories\trainings_factory;
use tool_attestoodle\certificate;
use tool_attestoodle\utils\logger;
use tool_attestoodle\forms\period_form;
use tool_attestoodle\forms\learner_certificate_form;
use tool_attestoodle\utils\db_accessor;
/**
 * Display learner's information of a training.
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class learner_details implements \renderable {
    /** @var period_form The form used to select period */
    private $form;
    /** @var learner_certificate_form The form used to customize certificate */
    private $form2;

    /** @var integer Id of the learner being displayed */
    public $learnerid;
    /** @var learner Learner being displayed */
    public $learner;
    /** @var string Begin date formatted as YYYY-MM-DD */
    public $begindate;
    /** @var \DateTime Begin date object */
    public $actualbegindate;
    /** @var boolean True if the $begindate property is not parsable by the \DateTime constructor */
    public $begindateerror;
    /** @var string End date formatted as YYYY-MM-DD */
    public $enddate;
    /** @var \DateTime End date object */
    public $actualenddate;
    /** @var \DateTime End date object + 1 day (to simplify comparison) */
    public $searchenddate;
    /** @var boolean True if the $enddate property is not parsable by the \DateTime constructor */
    public $enddateerror;
    /** @var string the category id for navigation bar. */
    public $categorylnk;
    /** @var int the identifier of the training. */
    private $trainingid;
    /** @var Training in which the learner is enrolled.*/
    private $training;

    /**
     * Constructor of the renderable object.
     *
     * @param integer $learnerid Id of the learner being displayed (url param)
     * @param string $begindate Begin date formatted as YYYY-MM-DD (url param)
     * @param string $enddate End date formatted as YYYY-MM-DD (url param)
     * @param integer $categorylnk Id of the category associate with learners (nav bar)
     * @param integer $trainingid Identifier of the training.
     */
    public function __construct($learnerid, $begindate, $enddate, $categorylnk, $trainingid) {
        global $DB;
        $training = trainings_factory::get_instance()->retrieve_training_by_id($trainingid);
        $training->get_learners();
        $this->training = $training;
        $this->trainingid = $trainingid;
        $this->learnerid = $learnerid;

        $this->learner = learners_factory::get_instance()->retrieve_learner($learnerid);
        $this->categorylnk = $categorylnk;
        // Default dates are January 1st and December 31st of current year.
        $this->begindate = isset($begindate) ? $begindate : (new \DateTime('first day of January ' . date('Y')))->format('Y-m-d');
        $this->enddate = isset($enddate) ? $enddate : (new \DateTime('last day of December ' . date('Y')))->format('Y-m-d');
        // Parsing begin date.
        try {
            $this->actualbegindate = new \DateTime($this->begindate);
            $this->begindateerror = false;
        } catch (\Exception $ex) {
            $this->begindateerror = true;
        }
        // Parsing end date.
        try {
            $this->actualenddate = new \DateTime($this->enddate);
            $this->searchenddate = clone $this->actualenddate;
            $this->searchenddate->modify('+1 day');
            $this->enddateerror = false;
        } catch (\Exception $ex) {
            $this->enddateerror = true;
        }

        $this->form = new period_form(
                    new \moodle_url('/admin/tool/attestoodle/index.php',
                        array('typepage' => 'learnerdetails', 'categorylnk' => $this->categorylnk,
                            'learner' => $this->learnerid, 'trainingid' => $trainingid)),
                        array(), 'get' );

        $stime = \DateTime::createFromFormat("Y-m-d", $this->begindate);
        $etime = \DateTime::createFromFormat("Y-m-d", $this->enddate);
        $this->form->set_data(array ('input_begin_date' => $stime->getTimestamp(),
                    'input_end_date' => $etime->getTimestamp()));

        $idtemplate = 0;
        if ($DB->record_exists('tool_attestoodle_train_style', ['trainingid' => $trainingid ])) {
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
        $template = db_accessor::get_instance()->get_user_template($this->learnerid, $trainingid);

        $displaydate = false;
        $custom = false;
        $disablecertif = false;
        if (isset($template->id)) {
            $grp1 = $template->grpcriteria1;
            $grp2 = $template->grpcriteria2;
            if (isset($template->withdateformat)) {
                $displaydate = true;
            }
            $custom = true;
            if ($template->enablecertificate == 0) {
                $disablecertif = true;
            }
            $idtemplate = $template->templateid;
        }

        $this->form2 = new learner_certificate_form(
                    new \moodle_url('/admin/tool/attestoodle/index.php',
                        array('typepage' => 'learnerdetails', 'categorylnk' => $this->categorylnk,
                            'learner' => $this->learnerid, 'trainingid' => $trainingid)),
                        array('categoryid' => $categorylnk,
                        'idtraining' => $trainingid,
                        'idtemplate' => $idtemplate), 'get' );

        $this->form2->set_data(array ('group1' => $grp1, 'group2' => $grp2,
                'displaydate' => $displaydate, 'custom' => $custom,
                'disablecertif' => $disablecertif));
        if ($this->form2->is_submitted()) {
            $this->handle_form2_submitted();
        }
    }

    /**
     * Handles the form2 submission, customize treaner's template.
     */
    private function handle_form2_submitted() {
        global $DB;
        if ($this->form2->is_validated()) {
            $datafromform = $this->form2->get_submitted_data();
            if (!$datafromform->custom) {
                $DB->delete_records('tool_attestoodle_user_style',
                    array('userid' => $this->learnerid, 'trainingid' => $datafromform->idtraining));
            } else {
                $dataobject = new \stdClass();
                $dataobject->userid = $this->learnerid;
                $dataobject->trainingid = $datafromform->idtraining;
                $dataobject->templateid = $datafromform->template;
                $dataobject->grpcriteria1 = $datafromform->group1;
                $dataobject->grpcriteria2 = $datafromform->group2;
                if (empty($datafromform->group2)) {
                    $dataobject->grpcriteria2 = null;
                }
                if ($datafromform->disablecertif) {
                    $dataobject->enablecertificate = 0;
                } else {
                    $dataobject->enablecertificate = 1;
                }
                if (isset($datafromform->displaydate) && $datafromform->displaydate) {
                    $dataobject->withdateformat = get_string('dateformat', 'tool_attestoodle');
                    $dataobject->grpcriteria2 = null;
                } else {
                    $dataobject->withdateformat = null;
                }
                $rec = $DB->get_record('tool_attestoodle_user_style', array('userid' => $this->learnerid,
                                                                    'trainingid' => $datafromform->idtraining));
                if (isset($rec->id)) {
                    $dataobject->id = $rec->id;
                    $DB->update_record('tool_attestoodle_user_style', $dataobject);
                } else {
                    $DB->insert_record('tool_attestoodle_user_style', $dataobject);
                }
            }
        }
    }

    /**
     * Method that checks if the learner exists (meaning that the ID given is valid).
     *
     * @return boolean True if the learner exists
     */
    public function learner_exists() {
        return isset($this->learner);
    }

    /**
     * Checks if the training has validated activities
     *
     * @param training $training The training to check
     * @return boolean True if the training has validated activities
     */
    public function training_has_validated_activites($training) {
        $vas = $this->get_learner_validated_activities();
        $fas = array_filter($vas, function($va) use ($training){
            return $va->get_activity()->get_course()->get_training()->get_categoryid() == $training->get_categoryid();
        });
        return count($fas) > 0;
    }

    /**
     * Methods that instanciate a certificate object then ask it to create
     * the certificate file on the server. A notification is then send to the
     * user depending on the result of the file creation (error, overwritten, new file).
     */
    public function generate_certificate_file() {
        // Log the generation launch.
        $launchid = logger::log_launch($this->begindate, $this->enddate);

        $certificate = new certificate($this->learner, $this->training, $this->actualbegindate, $this->actualenddate);
        $status = $certificate->create_file_on_server();

        // Log the certificate informations.
        if (isset($launchid)) {
            logger::log_certificate($launchid, $status, $certificate);
        }

        $this->notify_result($status);
    }

    /**
     * Method that throws a notification to user to let him know the result of
     * the certificate file generation.
     *
     * @param integer $status The status of the file generation on the server (0: error,
     * 1: new file, 2: new file overwritten the old one)
     */
    private function notify_result($status) {
        $notificationmessage = "";

        switch ($status) {
            case 0: // Error.
                $notificationmessage .= \get_string('learner_details_notification_message_error', 'tool_attestoodle');
                \core\notification::error($notificationmessage);
                break;
            case 1: // New file.
                $notificationmessage .= \get_string('learner_details_notification_message_new', 'tool_attestoodle');
                \core\notification::success($notificationmessage);
                break;
            case 2: // File overwritten.
                $notificationmessage .= \get_string('learner_details_notification_message_overwritten', 'tool_attestoodle');
                \core\notification::success($notificationmessage);
                break;
        }
    }

    /**
     * Method that returns all the trainings registered by the learner being displayed.
     *
     * @return training[] The trainings registered by the learner
     */
    public function get_learner_registered_trainings() {
        return $this->learner->retrieve_training_registered();
    }

    /**
     * Method that returns all the validated activities of the learner being
     * displayed within the period requested.
     *
     * @return validated_activity[] The validated activites of the learner
     */
    public function get_learner_validated_activities() {
        return $this->learner->get_validated_activities_with_marker($this->actualbegindate, $this->searchenddate);
    }

    /**
     * Instanciate the title of the page, in the header, depending on the state
     * of the page (error or OK).
     *
     * @return string The title of the page
     */
    public function get_heading() {
        $heading = "";
        if (!$this->learner_exists()) {
            $heading = \get_string('learner_details_main_title_error', 'tool_attestoodle');
        } else {
            $heading = \get_string('learner_details_main_title', 'tool_attestoodle', $this->learner->get_fullname());
        }
        return $heading;
    }

    /**
     * Computes the content header depending on params (the filter form).
     *
     * @return string The computed HTML string of the page header
     */
    public function get_header() {
        $output = "";
        $output .= "<style>.col-md-3 {float:left;width:auto}</style>";

        // Verifying learner id.
        if (!$this->learner_exists()) {
            $output .= \get_string('unknown_learner_id', 'tool_attestoodle', $this->learnerid);
        } else {
            $output .= \html_writer::start_div('clearfix learner-detail-header');
            // Render the form.
            $output .= $this->form->render();
            $output .= \html_writer::end_div();
            $output .= $this->form2->render();
        }

        return $output;
    }

    /**
     * Computes the HTML content above tables within the page.
     *
     * @param training $training The training corresponding to the table being computes
     * @return string The computed HTML string of table above content
     */
    public function get_table_heading($training) {
        $output = "";

        $output .= "<h2>{$training->get_name()}</h2>";
        $output .= \html_writer::link(
                new \moodle_url(
                        '/admin/tool/attestoodle/index.php', array(
                                'typepage' => 'learners',
                                'categoryid' => $training->get_categoryid(),
                                'begindate' => $this->begindate,
                                'enddate' => $this->enddate,
                                'categorylnk' => $this->categorylnk,
                                'trainingid' => $this->trainingid
                        )
                ),
                \get_string('backto_training_learners_list_btn_text', 'tool_attestoodle'),
                array('class' => 'attestoodle-link')
        );
        $output .= "<br />";

        return $output;
    }

    /**
     * Returns the table head used by moodle html_table function to display a
     * html table head. It does not depend on any parameter.
     *
     * @return string[] The tables columns header
     */
    public function get_table_head() {
        return array(
                get_string('learner_details_table_header_column_course_name', 'tool_attestoodle'),
                get_string('learner_details_table_header_column_name', 'tool_attestoodle'),
                get_string('learner_details_table_header_column_type', 'tool_attestoodle'),
                get_string('learner_details_table_header_column_validated_time', 'tool_attestoodle'),
                get_string('learner_details_table_header_column_milestones', 'tool_attestoodle')
        );
    }

    /**
     * Returns the table content used by moodle html_table function to display a
     * html table content depending on the training being displayed.
     *
     * @param training $training The training being computes as a table
     * @return \stdClass[] The array of \stdClass used by html_table function
     */
    public function get_table_content($training) {
        $data = array();

        foreach ($this->get_learner_validated_activities() as $vact) {
            $act = $vact->get_activity();
            if ($act->get_course()->get_training()->get_categoryid() == $training->get_categoryid()) {
                $stdclassact = new \stdClass();

                $stdclassact->coursename = $act->get_course()->get_name();
                $stdclassact->name = $act->get_name();
                $stdclassact->type = get_string('modulename', $act->get_type());
                $stdclassact->validatedtime = parse_datetime_to_readable_format($vact->get_datetime());
                $stdclassact->milestone = parse_minutes_to_hours($act->get_milestone());

                $data[] = $stdclassact;
            }
        }

        return $data;
    }

    /**
     * Returns the string that says that the learner has no training registered.
     *
     * @return string The no training registered message, translated
     */
    public function get_no_training_registered_message() {
        return get_string('learner_details_no_training_registered', 'tool_attestoodle');
    }

    /**
     * Returns the string that says that the learner has no validated activities
     * within the specified period.
     *
     * @return string The no validated activities message, translated
     */
    public function get_no_validated_activities_message() {
        $output = \html_writer::start_tag("p", array("class" => "no-validated-activity"));
        $output .= get_string('learner_details_no_validated_activities', 'tool_attestoodle');
        $output .= \html_writer::end_tag("p");

        return $output;
    }

    /**
     * Computes the HTML content bellow tables within the page, with the
     * links to download and/or generate the certificate file.
     *
     * @param training $training The training corresponding to the table being computes
     * @return string The computed HTML string of table bellow content
     */
    public function get_footer($training) {
        $output = "";

        $totalmarkerperiod = $this->learner->get_total_milestones(
                    $training->get_categoryid(),
                    $this->actualbegindate, $this->searchenddate);
        $totalmarkers = parse_minutes_to_hours($totalmarkerperiod);
        $libtotal = get_string('totalminute', 'tool_attestoodle');
        $output .= $libtotal . ' : ' . $totalmarkers;

        $linktext = get_string('learner_details_generate_certificate_link', 'tool_attestoodle');
        $certificate = new certificate($this->learner, $training, $this->actualbegindate, $this->actualenddate);

        $context = \context_coursecat::instance($this->categorylnk);
        if (has_capability('tool/attestoodle:downloadcertificate', $context)) {
            $output .= \html_writer::start_div('clearfix');

            // If the file already exists, add a link to it.
            if ($certificate->file_exists()) {
                $linktext = get_string('learner_details_regenerate_certificate_link', 'tool_attestoodle');

                $output .= "<a href='" . $certificate->get_existing_file_url() . "' target='_blank'>" .
                    get_string('learner_details_download_certificate_link', 'tool_attestoodle') .
                    "</a>";
                $output .= "&nbsp;ou&nbsp;";
            }

            // Instanciate the "Generate certificate" link with specified filters.
            $dlcertifoptions = array(
                'typepage' => 'learnerdetails',
                'action' => 'generatecertificate',
                'learner' => $this->learnerid,
                'categorylnk' => $this->categorylnk,
                'trainingid' => $this->trainingid
            );
            if ($this->actualbegindate) {
                $dlcertifoptions['begindate'] = $this->actualbegindate->format('Y-m-d');
            }
            if ($this->actualenddate) {
                $dlcertifoptions['enddate'] = $this->actualenddate->format('Y-m-d');
            }
            // Print the "Generate certificate" link.
            $output .= \html_writer::link(
                new \moodle_url(
                        '/admin/tool/attestoodle/index.php',
                        $dlcertifoptions
                ),
                $linktext,
                array('class' => 'attestoodle-link')
            );
            $output .= \html_writer::end_div();
        }
        return $output;
    }
}
