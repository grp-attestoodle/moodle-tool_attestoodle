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

defined('MOODLE_INTERNAL') || die;

use \renderable;
use tool_attestoodle\certificate;
use tool_attestoodle\utils\logger;

class training_learners_list implements renderable {
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

        $output .= \html_writer::start_div('clearfix');
        if (!$this->training_exists()) {
            $output .= \html_writer::end_div();
        } else {
            // Link to the training details (management).
            $output .= \html_writer::link(
                    new \moodle_url(
                            '/admin/tool/attestoodle/index.php',
                            array('page' => 'trainingmanagement', 'categoryid' => $this->training->get_id())
                    ),
                    get_string('backto_training_detail_btn_text', 'tool_attestoodle'),
                    array('class' => 'btn btn-default attestoodle-button'));
            $output .= \html_writer::end_div();

            $output .= \html_writer::start_div('clearfix training-report-header');

            // Basic form to allow user filtering the validated activities by begin and end dates.
            // @TODO use a moodle_quickform.
            $output .= '<form action="?" class="filterform"><div>'
                    . '<input type="hidden" name="page" value="learners" />'
                    . '<input type="hidden" name="training" value="' . $this->training->get_id() . '" />';
            $output .= '<label for="input_begin_date">'
                    . get_string('learner_details_begin_date_label', 'tool_attestoodle') . '</label>'
                    . '<input type="text" id="input_begin_date" name="begindate" value="' . $this->thebegindate . '" '
                    . 'placeholder="ex: ' . (new \DateTime('now'))->format('Y-m-d') . '" />';
            if ($this->begindateisinerror) {
                echo "<span class='error'>Erreur de format</span>";
            }
            $output .= '<label for="input_end_date">'
                    . get_string('learner_details_end_date_label', 'tool_attestoodle') . '</label>'
                    . '<input type="text" id="input_end_date" name="enddate" value="' . $this->theenddate . '" '
                    . 'placeholder="ex: ' . (new \DateTime('now'))->format('Y-m-d') . '" />';
            if ($this->enddateisinerror) {
                $output .= "<span class='error'>Erreur de format</span>";
            }
            $output .= '<input type="submit" value="'
                    . get_string('learner_details_submit_button_value', 'tool_attestoodle') . '" />'
                    . '</div></form>' . "\n";

            // Certicates related links.
            $output .= \html_writer::start_div('clearfix');
            // Download ZIP link.
            $output .= \html_writer::link(
                    new \moodle_url(
                            '/admin/tool/attestoodle/index.php',
                            array(
                                    'page' => 'learners',
                                    'action' => 'downloadzip',
                                    'training' => $this->training->get_id(),
                                    'begindate' => $this->thebegindate,
                                    'enddate' => $this->theenddate
                            )
                    ),
                    get_string('training_learners_list_download_zip_link', 'tool_attestoodle'),
                    array('class' => 'btn btn-default attestoodle-button'));
            // Generate all certificates link.
            $output .= \html_writer::link(
                    new \moodle_url(
                            '/admin/tool/attestoodle/index.php',
                            array(
                                    'page' => 'learners',
                                    'action' => 'generatecertificates',
                                    'training' => $this->training->get_id(),
                                    'begindate' => $this->thebegindate,
                                    'enddate' => $this->theenddate
                            )
                    ),
                    get_string('training_learners_list_generate_certificates_link', 'tool_attestoodle'),
                    array('class' => 'btn btn-default attestoodle-button'));
            $output .= \html_writer::end_div();

            $output .= \html_writer::end_div();
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
                    $this->training->get_id(),
                    $this->theactualbegindate,
                    $this->theactualenddate
            );

            $stdclass->lastname = $o->get_lastname();
            $stdclass->firstname = $o->get_firstname();
            $stdclass->totalmarkers = parse_minutes_to_hours($totalmarkerperiod);

            $parameters = array(
                'page' => 'learnerdetails',
                'learner' => $o->get_id(),
                'begindate' => $this->thebegindate,
                'enddate' => $this->theenddate);
            $url = new \moodle_url('/admin/tool/attestoodle/index.php', $parameters);
            $label = get_string('training_learners_list_table_link_details', 'tool_attestoodle');
            $attributes = array('class' => 'attestoodle-button');
            $stdclass->link = \html_writer::link($url, $label, $attributes);

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
     * Method that generates certificates for all the learners in the training,
     * filtering activities by period given.
     * It creates the files on the server then notify the user if there is
     * any error or warning (file not created or other file creation error).
     */
    public function generate_certificates() {
        $errorcounter = 0;
        $newfilecounter = 0;
        $overwrittencounter = 0;

        // Log the generation launch.
        $launchid = logger::log_launch($this->thebegindate, $this->theenddate);

        foreach ($this->training->get_learners() as $learner) {
            $certificate = new certificate($learner, $this->training, $this->theactualbegindate, $this->theactualenddate);
            $status = $certificate->create_file_on_server();
            switch ($status) {
                case 0:
                    // Error.
                    $errorcounter++;
                    break;
                case 1:
                    // New file.
                    $newfilecounter++;
                    break;
                case 2:
                    // File overwritten.
                    $overwrittencounter++;
                    break;
            }
            // Log the certificate informations.
            if (isset($launchid)) {
                logger::log_certificate($launchid, $status, $certificate);
            }
        }

        $this->notify_results($newfilecounter, $overwrittencounter, $errorcounter);
    }

    /**
     * Method that throws a notification to user to let him know the results of
     * the certificate files generation (number of new files, overwritten ones and
     * the ones in error).
     *
     * @param integer $newfiles The number of new file generated
     * @param integer $filesoverwritten The number of new file that overwritten an identical old one
     * @param integer $errors The number of file creation in error
     */
    private function notify_results($newfiles, $filesoverwritten, $errors) {
        $notificationmessage = "";

        if ($newfiles > 0 || $filesoverwritten > 0) {
            if ($errors > 0) {
                // Generated with errors !
                $notificationmessage .= \get_string('training_learners_list_notification_message_with_error_one',
                    'tool_attestoodle') . "<br />";
                $notificationmessage .= \get_string('training_learners_list_notification_message_with_error_two',
                    'tool_attestoodle', $newfiles) . "<br />";
                $notificationmessage .= \get_string('training_learners_list_notification_message_with_error_three',
                    'tool_attestoodle', $filesoverwritten) . "<br />";
                $notificationmessage .= \get_string('training_learners_list_notification_message_with_error_viva_algerie',
                    'tool_attestoodle', $errors);
                \core\notification::warning($notificationmessage);
            } else { // Generated with success.
                $notificationmessage .= \get_string('training_learners_list_notification_message_success_one',
                    'tool_attestoodle') . "<br />";
                $notificationmessage .= \get_string('training_learners_list_notification_message_success_two',
                    'tool_attestoodle', $newfiles) . "<br />";
                $notificationmessage .= \get_string('training_learners_list_notification_message_success_three',
                    'tool_attestoodle', $filesoverwritten);
                \core\notification::success($notificationmessage);
            }
        } else if ($errors > 0) { // All files in error !
            $notificationmessage .= \get_string('training_learners_list_notification_message_error_one',
                'tool_attestoodle') . "<br />";
            $notificationmessage .= \get_string('training_learners_list_notification_message_error_two',
                'tool_attestoodle', $errors);
            \core\notification::error($notificationmessage);
        } else { // No file generated !
            $notificationmessage .= \get_string('training_learners_list_notification_message_no_file',
                'tool_attestoodle');
            \core\notification::warning($notificationmessage);
        }
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
            print_error("An error occured: impossible to send ZIP file.");
        }
    }
}
