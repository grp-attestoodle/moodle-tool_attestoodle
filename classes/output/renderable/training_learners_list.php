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

use \renderable;

class training_learners_list implements renderable {
    public $training = null;

    public function __construct($training) {
        $this->training = $training;
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

            $output .= get_string('training_details_unknown_training_id', 'block_attestoodle');
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

            $stdclass->lastname = $o->get_lastname();
            $stdclass->firstname = $o->get_firstname();
            $stdclass->totalmarkers = parse_minutes_to_hours($o->get_total_markers());

            $parameters = array(
                'page' => 'learnerdetails',
                'training' => $this->training->get_id(),
                'learner' => $o->get_id());
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
}
