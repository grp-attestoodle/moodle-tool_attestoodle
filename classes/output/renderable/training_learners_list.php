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

use \renderable;
use block_attestoodle\certificate;

class training_learners_list implements renderable {
    public $training = null;
    public $begindate;
    public $actualbegindate;
    public $begindateerror;
    public $enddate;
    public $actualenddate;
    public $searchenddate;
    public $enddateerror;

    public function __construct($training, $begindate, $enddate) {
        $this->training = $training;

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

    public function training_exists() {
        return isset($this->training);
    }

    public function get_header() {
        $output = "";

        $output .= \html_writer::start_div('clearfix');
        // Link to the trainings list.
        $output .= \html_writer::link(
                new \moodle_url('/blocks/attestoodle/index.php'),
                get_string('trainings_list_btn_text', 'block_attestoodle'),
                array('class' => 'attestoodle-link')
        );

        if (!$this->training_exists()) {
            $output .= \html_writer::end_div();
        } else {
            // Link to the training details (management).
            $output .= \html_writer::link(
                    new \moodle_url(
                            '/blocks/attestoodle/index.php',
                            ['page' => 'trainingmilestones', 'training' => $this->training->get_id()]
                    ),
                    get_string('training_learners_list_edit_training_link', 'block_attestoodle'),
                    array('class' => 'btn btn-default attestoodle-button'));
            $output .= \html_writer::end_div();
            $output .= "<hr />";

            // Basic form to allow user filtering the validated activities by begin and end dates.
            // TODO use a moodle_quickform ?
            $output .= '<form action="?" class="filterform"><div>'
                    . '<input type="hidden" name="page" value="learners" />'
                    . '<input type="hidden" name="training" value="' . $this->training->get_id() . '" />';
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

            // Certicates related links.
            $output .= \html_writer::start_div('clearfix');
            // Download ZIP link.
            $output .= \html_writer::link(
                    new \moodle_url(
                            '/blocks/attestoodle/index.php',
                            array(
                                    'page' => 'learners',
                                    'action' => 'downloadzip',
                                    'training' => $this->training->get_id(),
                                    'begindate' => $this->begindate,
                                    'enddate' => $this->enddate
                            )
                    ),
                    get_string('training_learners_list_download_zip_link', 'block_attestoodle'),
                    array('class' => 'btn btn-default attestoodle-button'));
            // Generate all certificates link.
            $output .= \html_writer::link(
                    new \moodle_url(
                            '/blocks/attestoodle/index.php',
                            array(
                                    'page' => 'learners',
                                    'action' => 'generatecertificates',
                                    'training' => $this->training->get_id(),
                                    'begindate' => $this->begindate,
                                    'enddate' => $this->enddate
                            )
                    ),
                    get_string('training_learners_list_generate_certificates_link', 'block_attestoodle'),
                    array('class' => 'btn btn-default attestoodle-button'));
            $output .= \html_writer::end_div();

            $output .= "<hr />";
        }

        return $output;
    }

    public function get_table_head() {
        return array(
                get_string('training_learners_list_table_header_column_lastname', 'block_attestoodle'),
                get_string('training_learners_list_table_header_column_firstname', 'block_attestoodle'),
                get_string('training_learners_list_table_header_column_total_milestones', 'block_attestoodle'),
                ''
        );
    }

    public function get_table_content() {
        return array_map(function(\block_attestoodle\learner $o) {
            $stdclass = new \stdClass();
            $totalmarkerperiod = $o->get_total_milestones(
                    $this->training->get_id(),
                    $this->actualbegindate,
                    $this->actualenddate
            );

            $stdclass->lastname = $o->get_lastname();
            $stdclass->firstname = $o->get_firstname();
            $stdclass->totalmarkers = parse_minutes_to_hours($totalmarkerperiod);

            $parameters = array(
                'page' => 'learnerdetails',
                'learner' => $o->get_id(),
                'begindate' => $this->begindate,
                'enddate' => $this->enddate);
            $url = new \moodle_url('/blocks/attestoodle/index.php', $parameters);
            $label = get_string('training_learners_list_table_link_details', 'block_attestoodle');
            $attributes = array('class' => 'attestoodle-button');
            $stdclass->link = \html_writer::link($url, $label, $attributes);

            return $stdclass;
        }, $this->training->get_learners());
    }

    public function get_unknown_training_message() {
        return get_string('training_details_unknown_training_id', 'block_attestoodle');
    }

    public function generate_certificates() {
        $errorcounter = 0;
        $newfilecounter = 0;
        $overwrittencounter = 0;

        $notificationmessage = "";

        foreach ($this->training->get_learners() as $learner) {
            $certificate = new certificate($learner, $this->training, $this->actualbegindate, $this->actualenddate);
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
        }
        if ($newfilecounter > 0 || $overwrittencounter > 0) {
            if ($errorcounter > 0) {
                $notificationmessage .= "Certificates generated with errors: <br />";
                $notificationmessage .= "{$newfilecounter} new files <br />";
                $notificationmessage .= "{$overwrittencounter} files overwritten <br />";
                $notificationmessage .= "{$errorcounter} errors.";
                \core\notification::warning($notificationmessage);
            } else {
                $notificationmessage .= "Certificates generated! <br />";
                $notificationmessage .= "{$newfilecounter} new files <br />";
                $notificationmessage .= "{$overwrittencounter} files overwritten <br />";
                \core\notification::success($notificationmessage);
            }
        } else if ($errorcounter > 0) {
            $notificationmessage .= "Problem with certificates generation! <br />";
            $notificationmessage .= "{$errorcounter} errors.";
            \core\notification::error($notificationmessage);
        } else {
            // No file generated.
            $notificationmessage .= "No file created.";
            \core\notification::warning($notificationmessage);
        }
    }

    public function send_certificates_zipped() {
        $zipper = \get_file_packer('application/zip');
        $certificates = array();

        foreach ($this->training->get_learners() as $learner) {
            $certificate = new certificate($learner, $this->training, $this->actualbegindate, $this->actualenddate);

            if ($certificate->file_exists()) {
                $file = $certificate->retrieve_file();
                $certificates[$file->get_filename()] = $file;
            }
        }

        $filename = "certificates_{$this->training->get_name()}_";
        $filename .= $this->actualbegindate->format("Ymd") . "_" . $this->actualenddate->format("Ymd");
        $filename .= ".zip";
        $temppath = make_request_directory() . $filename;

        if ($zipper->archive_to_pathname($certificates, $temppath)) {
            send_temp_file($temppath, $filename);
        } else {
            print_error("An error occured: impossible to send ZIP file.");
        }
    }
}
