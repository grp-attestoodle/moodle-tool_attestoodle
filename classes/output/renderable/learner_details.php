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

class learner_details implements \renderable {
    public $learnerid;
    public $learner;
    public $trainingid;
    public $training;
    public $begindate;
    public $actualbegindate;
    public $begindateerror;
    public $enddate;
    public $actualenddate;
    public $searchenddate;
    public $enddateerror;

    public function __construct($learnerid, $trainingid, $begindate, $enddate) {
        $this->learnerid = $learnerid;
        $this->learner = learners_factory::get_instance()->retrieve_learner($learnerid);

        $this->trainingid = $trainingid;
        $this->training = trainings_factory::get_instance()->retrieve_training($trainingid);

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

    public function training_exists() {
        return isset($this->training);
    }

    public function get_learner_validated_activities() {
        return $this->learner->get_validated_activities_with_marker($this->actualbegindate, $this->searchenddate);
    }

    public function get_heading() {
        $heading = "";
        if (!$this->learner_exists() || !$this->training_exists()) {
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

        // Verifying training id.
        if (!$this->training_exists()) {
            $output .= \html_writer::start_div('clearfix');
            // Link to the trainings list if the training id is not valid.
            $output .= \html_writer::link(
                    new \moodle_url('/blocks/attestoodle/index.php', ['page' => 'trainingslist']),
                    get_string('backto_trainings_list_btn_text', 'block_attestoodle'),
                    array('class' => 'attestoodle-link'));
            $output .= \html_writer::end_div();
            $output .= "<hr />";
            $output .= get_string('unknown_training_id', 'block_attestoodle', $this->trainingid);
        } else {
            // If the training id is valid...
            $output .= \html_writer::start_div('clearfix');
            // Link to the training learners list.
            $output .= \html_writer::link(
                    new \moodle_url(
                            '/blocks/attestoodle/index.php',
                            ['page' => 'learners', 'training' => $this->trainingid]),
                    \get_string('backto_training_learners_list_btn_text', 'block_attestoodle'),
                    array('class' => 'attestoodle-link'));
            $output .= \html_writer::end_div();

            $output .= "<hr />";

            // Verifying learner id.
            if (!$this->learner_exists()) {
                $output .= \get_string('unknown_learner_id', 'block_attestoodle', $this->learnerid);
            } else {
                // Basic form to allow user filtering the validated activities by begin and end dates.
                // TODO use a moodle_quickform ?
                $output .= '<form action="?" class="filterform"><div>'
                        . '<input type="hidden" name="page" value="learnerdetails" />'
                        . '<input type="hidden" name="training" value="' . $this->trainingid . '" />'
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
        }

        return $output;
    }

    public function get_table_head() {
        return array(
                get_string('learner_details_table_header_column_training_name', 'block_attestoodle'),
                get_string('learner_details_table_header_column_course_name', 'block_attestoodle'),
                get_string('learner_details_table_header_column_name', 'block_attestoodle'),
                get_string('learner_details_table_header_column_type', 'block_attestoodle'),
                get_string('learner_details_table_header_column_validated_time', 'block_attestoodle'),
                get_string('learner_details_table_header_column_milestones', 'block_attestoodle')
        );
    }

    public function get_table_content() {
        $data = array();

        foreach ($this->get_learner_validated_activities() as $vact) {
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

        return $data;
    }

    public function get_no_validated_activities_message() {
        return get_string('learner_details_no_validated_activities', 'block_attestoodle');
    }

    public function get_footer() {
        $output = "";

        // Instanciate the "Generate certificate" link with specified filters.
        $dlcertifoptions = array('training' => $this->trainingid, 'user' => $this->learnerid);
        if ($this->actualbegindate) {
            $dlcertifoptions['begindate'] = $this->actualbegindate->format('Y-m-d');
        }
        if ($this->actualenddate) {
            $dlcertifoptions['enddate'] = $this->actualenddate->format('Y-m-d');
        }
        // Print the "Generate certificate" link.
        $output .= \html_writer::start_div('clearfix');
        $output .= \html_writer::link(
                new \moodle_url(
                        '/blocks/attestoodle/pages/download_certificate.php',
                        $dlcertifoptions
                ),
                get_string('learner_details_generate_certificate_link', 'block_attestoodle'),
                array('class' => 'attestoodle-link')
        );
        $output .= \html_writer::end_div();

        return $output;
    }
}
