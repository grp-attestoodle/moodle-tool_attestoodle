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

use block_attestoodle\factories\learners_factory;
use block_attestoodle\factories\trainings_factory;
use block_attestoodle\certificate;

class learner_details implements \renderable {
    public $learnerid;
    public $learner;
    public $begindate;
    public $actualbegindate;
    public $begindateerror;
    public $enddate;
    public $actualenddate;
    public $searchenddate;
    public $enddateerror;

    public function __construct($learnerid, $begindate, $enddate) {
        $this->learnerid = $learnerid;
        $this->learner = learners_factory::get_instance()->retrieve_learner($learnerid);

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

    public function learner_exists() {
        return isset($this->learner);
    }

    public function training_has_validated_activites($training) {
        $vas = $this->get_learner_validated_activities();
        $fas = array_filter($vas, function($va) use ($training){
            return $va->get_activity()->get_course()->get_training()->get_id() == $training->get_id();
        });
        return count($fas) > 0;
    }

    public function generate_certificate_file($trainingid) {
        $training = trainings_factory::get_instance()->retrieve_training($trainingid);
        $certificate = new certificate($this->learner, $training, $this->actualbegindate, $this->actualenddate);
        $certificate->create_file_on_server();
    }

    public function get_learner_registered_trainings() {
        return $this->learner->retrieve_training_registered();
    }

    public function get_learner_validated_activities() {
        return $this->learner->get_validated_activities_with_marker($this->actualbegindate, $this->searchenddate);
    }

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
     * TODO fat function
     * @return string
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

    public function get_table_head() {
        return array(
                get_string('learner_details_table_header_column_course_name', 'block_attestoodle'),
                get_string('learner_details_table_header_column_name', 'block_attestoodle'),
                get_string('learner_details_table_header_column_type', 'block_attestoodle'),
                get_string('learner_details_table_header_column_validated_time', 'block_attestoodle'),
                get_string('learner_details_table_header_column_milestones', 'block_attestoodle')
        );
    }

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
                $stdclassact->milestone = parse_minutes_to_hours($act->get_marker());

                $data[] = $stdclassact;
            }
        }

        return $data;
    }

    public function get_no_training_registered_message() {
        return get_string('learner_details_no_training_registered', 'block_attestoodle');
    }

    public function get_no_validated_activities_message() {
        return get_string('learner_details_no_validated_activities', 'block_attestoodle');
    }

    public function get_footer($training) {
        $output = "";

        $generatecertificatelinktext = get_string('learner_details_generate_certificate_link', 'block_attestoodle');
        $certificate = new certificate($this->learner, $training, $this->actualbegindate, $this->actualenddate);

        $output .= \html_writer::start_div('clearfix');

        // If the file already exists, add a link to it.
        if ($certificate->file_exists()) {
            $generatecertificatelinktext = get_string('learner_details_regenerate_certificate_link', 'block_attestoodle');

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
                $generatecertificatelinktext,
                array('class' => 'attestoodle-link')
        );
        $output .= \html_writer::end_div();

        return $output;
    }
}
