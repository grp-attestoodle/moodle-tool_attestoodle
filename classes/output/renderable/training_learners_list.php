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
 * Page training details.
 *
 * Renderable class that is used to render the page that lists all the
 * learners of a training
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_attestoodle\output\renderable;


use \renderable;
use tool_attestoodle\certificate;
use tool_attestoodle\utils\logger;
use tool_attestoodle\forms\period_form;

/**
 * Display list of learner of one training.
 *
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class training_learners_list implements renderable {
    /** @var period_form The form used to select period */
    private $form;

    /** @var training Training that is currently displayed */
    public $training = null;
    /** @var string Begin date formatted as YYYY-MM-DD */
    public $thebegindate;
    /** @var \DateTime Begin date object */
    public $theactualbegindate;
    /** @var boolean True if the $begindate property is not parsable by the \DateTime constructor */
    public $begindateisinerror;
    /** @var string End date formatted as YYYY-MM-DD */
    public $theenddate;
    /** @var \DateTime End date object */
    public $theactualenddate;
    /** @var \DateTime End date object + 1 day (to simplify comparison) */
    public $searchingenddate;
    /** @var boolean True if the $enddate property is not parsable by the \DateTime constructor */
    public $enddateisinerror;

    /**
     * Constructor of the renderable object.
     *
     * @param training $training Training being displayed
     * @param string $begindate Begin date formatted as YYYY-MM-DD (url param)
     * @param string $enddate End date formatted as YYYY-MM-DD (url param)
     */
    public function __construct($training, $begindate, $enddate) {
        $this->training = $training;

        // Default dates are January 1st and December 31st of current year.
        $this->thebegindate = isset($begindate) ? $begindate :
            (new \DateTime('first day of January ' . date('Y')))->format('Y-m-d');

        $this->theenddate = isset($enddate) ? $enddate : (new \DateTime('last day of December ' . date('Y')))->format('Y-m-d');
        // Parsing begin date.
        try {
            $this->theactualbegindate = new \DateTime($this->thebegindate);
            $this->begindateisinerror = false;
        } catch (\Exception $ex) {
            $this->begindateisinerror = true;
        }
        // Parsing end date.
        try {
            $this->theactualenddate = new \DateTime($this->theenddate);
            $this->searchingenddate = clone $this->theactualenddate;
            $this->searchingenddate->modify('+1 day');
            $this->enddateisinerror = false;
        } catch (\Exception $ex) {
            $this->enddateisinerror = true;
        }

        $this->form = new period_form(
                    new \moodle_url('/admin/tool/attestoodle/index.php',
                        array('typepage' => 'learners', 'categoryid' => $training->get_categoryid(),
                            'trainingid' => $training->get_id())),
                        array(), 'post' );

        $stime = \DateTime::createFromFormat("Y-m-d", $this->thebegindate);
        $etime = \DateTime::createFromFormat("Y-m-d", $this->theenddate);
        $this->form->set_data(array ('input_begin_date' => $stime->getTimestamp(),
            'input_end_date' => $etime->getTimestamp()));
    }

    /**
     * Method that checks if the training exists.
     *
     * @return boolean True if the training exists
     */
    public function training_exists() {
        return isset($this->training);
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
        $output .= "<style>.col-md-3 {float:left;width:auto}</style>";
        $output .= \html_writer::start_div('clearfix');
        if (!$this->training_exists()) {
            $output .= \html_writer::end_div();
        } else {
            // Link to the training details (management).
            $output .= \html_writer::link(
                    new \moodle_url(
                            '/admin/tool/attestoodle/index.php',
                            array('typepage' => 'trainingmanagement',
                                'categoryid' => $this->training->get_categoryid(),
                                'trainingid' => $this->training->get_id())
                    ),
                    get_string('backto_training_detail_btn_text', 'tool_attestoodle'),
                    array('class' => 'btn btn-default attestoodle-button'));
            $output .= \html_writer::end_div();

            $output .= \html_writer::start_div('clearfix training-report-header');

            // Render the form.
            $output .= $this->form->render();

            // Certicates related links.
            // Download ZIP link.
            $context = \context_coursecat::instance($this->training->get_categoryid());
            if (has_capability('tool/attestoodle:downloadcertificate', $context)) {
                $output .= \html_writer::start_div('clearfix');
                $output .= \html_writer::link(
                    new \moodle_url(
                            '/admin/tool/attestoodle/index.php',
                            array(
                                    'typepage' => 'learners',
                                    'action' => 'downloadzip',
                                    'categoryid' => $this->training->get_categoryid(),
                                    'begindate' => $this->thebegindate,
                                    'enddate' => $this->theenddate,
                                    'trainingid' => $this->training->get_id()
                            )
                    ),
                    get_string('training_learners_list_download_zip_link', 'tool_attestoodle'),
                    array('class' => 'btn btn-default attestoodle-button'));
                // Generate all certificates link.
                $output .= \html_writer::link(
                    new \moodle_url(
                            '/admin/tool/attestoodle/classes/generated/preparedinf.php',
                            array(
                                'trainingid' => $this->training->get_id(),
                                'categoryid' => $this->training->get_categoryid(),
                                'begindate' => $this->thebegindate,
                                'enddate' => $this->theenddate
                            )
                    ),
                    get_string('training_learners_list_generate_certificates_link', 'tool_attestoodle'),
                    array('class' => 'btn btn-default attestoodle-button'));
                $output .= \html_writer::end_div();
            }
        }

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
                get_string('training_learners_list_table_header_column_lastname', 'tool_attestoodle'),
                get_string('training_learners_list_table_header_column_firstname', 'tool_attestoodle'),
                get_string('training_learners_list_table_header_column_total_milestones', 'tool_attestoodle'),
                ''
        );
    }

    /**
     * Returns the table content used by moodle html_table function to display a
     * html table content depending on the training being displayed.
     *
     * @return \stdClass[] The array of \stdClass used by html_table function
     */
    public function get_table_content() {
        return array_map(function(\tool_attestoodle\learner $o) {
            $stdclass = new \stdClass();
            $totalmarkerperiod = $o->get_total_milestones(
                    $this->training->get_categoryid(),
                    $this->theactualbegindate,
                    $this->searchingenddate
            );

            $stdclass->lastname = $o->get_lastname();
            $stdclass->firstname = $o->get_firstname();
            $stdclass->totalmarkers = parse_minutes_to_hours($totalmarkerperiod);

            $parameters = array(
                'typepage' => 'learnerdetails',
                'learner' => $o->get_id(),
                'begindate' => $this->thebegindate,
                'enddate' => $this->theenddate,
                'categorylnk' => $this->training->get_categoryid(),
                'trainingid' => $this->training->get_id());
            $url = new \moodle_url('/admin/tool/attestoodle/index.php', $parameters);
            $label = get_string('training_learners_list_table_link_details', 'tool_attestoodle');
            $attributes = array('class' => 'attestoodle-button');
            $stdclass->link = "";
            $context = \context_coursecat::instance($this->training->get_categoryid());
            if (has_capability('tool/attestoodle:learnerdetails', $context)) {
                $stdclass->link = \html_writer::link($url, $label, $attributes);
            }

            return $stdclass;
        }, $this->training->get_learners());
    }

    /**
     * Returns the string that says that the ID training searched is not valid.
     *
     * @return string The unknow training ID message, translated
     */
    public function get_unknown_training_message() {
        return get_string('training_details_unknown_training_id', 'tool_attestoodle');
    }

    /**
     * The method retrieves all the certificate files on the server filtered by
     * the current training and period requested, then stores them in a new
     * ZIP file, then sends the archive to the client.
     * The method does not create any file! It is designed to be called after the
     * generate_certificates() method; it means that the ZIP file can be void.
     */
    public function send_certificates_zipped() {
        // Create ZIP file.
        $zipper = \get_file_packer('application/zip');
        $certificates = array();

        // Retrieve certificates based on period requested.
        foreach ($this->training->get_learners() as $learner) {
            $certificate = new certificate($learner, $this->training, $this->theactualbegindate, $this->theactualenddate);

            if ($certificate->file_exists()) {
                $file = $certificate->retrieve_file();
                $certificates[$file->get_filename()] = $file;
            }
        }

        // Archive file name.
        $filename = "certificates_{$this->training->get_name()}_";
        $filename .= $this->theactualbegindate->format("Ymd") . "_" . $this->theactualenddate->format("Ymd");
        $filename .= ".zip";
        $temppath = make_request_directory() . $filename;

        // Send the archive to the client.
        if ($zipper->archive_to_pathname($certificates, $temppath)) {
            send_temp_file($temppath, $filename);
        } else {
            // I m not ok with this => print_error("An error occured: impossible to send ZIP file.") !
            // print_error must receive an error code in parameter !
            \core\notification::error(get_string('errsendzip', 'tool_attestoodle'));
        }
    }
}
