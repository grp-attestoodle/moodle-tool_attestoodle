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
 * This class implements the moodle renderable interface to help rendering
 * the learner_details page.
 *
 * @package    block_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_attestoodle\output\renderable;

defined('MOODLE_INTERNAL') || die;

use block_attestoodle\factories\learners_factory;
use block_attestoodle\factories\trainings_factory;
use block_attestoodle\certificate;

class learner_details implements \renderable {
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

    /**
     * Constructor of the renderable object.
     *
     * @param integer $learnerid Id of the learner being displayed (url param)
     * @param string $begindate Begin date formatted as YYYY-MM-DD (url param)
     * @param string $enddate End date formatted as YYYY-MM-DD (url param)
     */
    public function __construct($learnerid, $begindate, $enddate) {
        $this->learnerid = $learnerid;
        $this->learner = learners_factory::get_instance()->retrieve_learner($learnerid);

        // Default dates are January 1st and December 31st of current year
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
            return $va->get_activity()->get_course()->get_training()->get_id() == $training->get_id();
        });
        return count($fas) > 0;
    }

    /**
     * Methods that instanciate a certificate object then ask it to create
     * the certificate file on the server. A notification is then send to the
     * user depending on the result of the file creation (error, overwritten, new file).
     *
     * @param integer $trainingid The training ID of the certificate requested
     */
    public function generate_certificate_file($trainingid) {
        $training = trainings_factory::get_instance()->retrieve_training($trainingid);
        $certificate = new certificate($this->learner, $training, $this->actualbegindate, $this->actualenddate);
        $status = $certificate->create_file_on_server();
        $notificationmessage = "";

        switch ($status) {
            case 0:
                $notificationmessage .= "File not generated (an error occured).";
                \core\notification::error($notificationmessage);
                break;
            case 1:
                $notificationmessage .= "New file generated.";
                \core\notification::success($notificationmessage);
                break;
            case 2:
                $notificationmessage .= "File generated (overwritten).";
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
            $heading = \get_string('learner_details_main_title_error', 'block_attestoodle');
        } else {
            $heading = \get_string('learner_details_main_title', 'block_attestoodle', $this->learner->get_fullname());
        }
        return $heading;
    }

    /**
     * Computes the content header depending on params (the filter form).
     *
     * @todo Long method, could be reduce
     *
     * @return string The computed HTML string of the page header
     */
    public function get_header() {
        $output = "";

        // Verifying learner id.
        if (!$this->learner_exists()) {
            $output .= \get_string('unknown_learner_id', 'block_attestoodle', $this->learnerid);
        } else {
            // Basic form to allow user filtering the validated activities by begin and end dates.
            // TODO use a moodle_quickform ?
            $output .= '<form action="?" class="filterform"><div>'
                    . '<input type="hidden" name="page" value="learnerdetails" />'
                    . '<input type="hidden" name="learner" value="' . $this->learnerid . '" />';
            $output .= '<label for="input_begin_date">'
                    . get_string('learner_details_begin_date_label', 'block_attestoodle') . '</label>'
                    . '<input type="text" id="input_begin_date" name="begindate" value="' . $this->begindate . '" '
                    . 'placeholder="ex: ' . (new \DateTime('now'))->format('Y-m-d') . '" />';
            if ($this->begindateerror) {
                echo "<span class='error'>Erreur de format</span>";
            }
            $output .= '<label for="input_end_date">'
                    . get_string('learner_details_end_date_label', 'block_attestoodle') . '</label>'
                    . '<input type="text" id="input_end_date" name="enddate" value="' . $this->enddate . '" '
                    . 'placeholder="ex: ' . (new \DateTime('now'))->format('Y-m-d') . '" />';
            if ($this->enddateerror) {
                $output .= "<span class='error'>Erreur de format</span>";
            }
            $output .= '<input type="submit" value="'
                    . get_string('learner_details_submit_button_value', 'block_attestoodle') . '" />'
                    . '</div></form>' . "\n";

            $output .= "<hr />";
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
                        '/blocks/attestoodle/index.php', array(
                                'page' => 'learners',
                                'training' => $training->get_id(),
                                'begindate' => $this->begindate,
                                'enddate' => $this->enddate
                        )
                ),
                \get_string('backto_training_learners_list_btn_text', 'block_attestoodle'),
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
                get_string('learner_details_table_header_column_course_name', 'block_attestoodle'),
                get_string('learner_details_table_header_column_name', 'block_attestoodle'),
                get_string('learner_details_table_header_column_type', 'block_attestoodle'),
                get_string('learner_details_table_header_column_validated_time', 'block_attestoodle'),
                get_string('learner_details_table_header_column_milestones', 'block_attestoodle')
        );
    }

    /**
     * Returns the table content used by moodle html_table function to display a
     * html table content depending on the training being displayed.
     *
     * @param training $training The training being computes as a table
     * @return \stdClass The stdClass used by html_table function
     */
    public function get_table_content($training) {
        $data = array();

        foreach ($this->get_learner_validated_activities() as $vact) {
            $act = $vact->get_activity();
            if ($act->get_course()->get_training()->get_id() == $training->get_id()) {
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
        return get_string('learner_details_no_training_registered', 'block_attestoodle');
    }

    /**
     * Returns the string that says that the learner has no validated activities
     * within the specified period.
     *
     * @return string The no validated activities message, translated
     */
    public function get_no_validated_activities_message() {
        return get_string('learner_details_no_validated_activities', 'block_attestoodle');
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

        $linktext = get_string('learner_details_generate_certificate_link', 'block_attestoodle');
        $certificate = new certificate($this->learner, $training, $this->actualbegindate, $this->actualenddate);

        $output .= \html_writer::start_div('clearfix');

        // If the file already exists, add a link to it.
        if ($certificate->file_exists()) {
            $linktext = get_string('learner_details_regenerate_certificate_link', 'block_attestoodle');

            $output .= "<a href='" . $certificate->get_existing_file_url() . "' target='_blank'>" .
                    get_string('download_certificate_file_link_text', 'block_attestoodle') .
                    "</a>";
            $output .= "&nbsp;ou&nbsp;";
        }

        // Instanciate the "Generate certificate" link with specified filters.
        $dlcertifoptions = array(
                'page' => 'learnerdetails',
                'action' => 'generatecertificate',
                'training' => $training->get_id(),
                'learner' => $this->learnerid
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
                        '/blocks/attestoodle/index.php',
                        $dlcertifoptions
                ),
                $linktext,
                array('class' => 'attestoodle-link')
        );
        $output .= \html_writer::end_div();

        return $output;
    }
}
