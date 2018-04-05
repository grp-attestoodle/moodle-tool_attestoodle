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

class trainings_list implements renderable {
    private $trainings = [];

    public function __construct($trainings) {
        $this->trainings = $trainings;
    }

    public function get_header() {
        $output = "";

        $output .= \html_writer::start_div('clearfix');
        // Link to the trainings management page.
        $output .= \html_writer::link(
                new \moodle_url(
                        '/blocks/attestoodle/index.php',
                        ['page' => 'trainingsmanagement']
                ),
                get_string('trainings_list_manage_trainings_link', 'block_attestoodle'),
                array('class' => 'btn btn-default attestoodle-button')
        );
        $output .= \html_writer::end_div();

        return $output;
    }

    public function get_table_head() {
        return array(
                get_string('trainings_list_table_header_column_name', 'block_attestoodle'),
                get_string('trainings_list_table_header_column_hierarchy', 'block_attestoodle'),
                get_string('trainings_list_table_header_column_description', 'block_attestoodle'),
                ''
        );
    }

    public function get_table_content() {
        return array_map(function($training) {
            $stdclass = new \stdClass();

            $categorylink = new \moodle_url("/course/index.php", array("categoryid" => $training->get_id()));
            $stdclass->name = "<a href='{$categorylink}'>{$training->get_name()}</a>";

            $stdclass->hierarchy = $training->get_hierarchy();

            $stdclass->description = $training->get_description();

            $parameters = array(
                'page' => 'learners',
                'training' => $training->get_id());
            $url = new \moodle_url('/blocks/attestoodle/index.php', $parameters);
            $label = get_string('trainings_list_table_link_details', 'block_attestoodle');
            $attributes = array('class' => 'attestoodle-button');
            $stdclass->link = \html_writer::link($url, $label, $attributes);

            return $stdclass;
        }, $this->trainings);
    }

    public function get_no_training_message() {
        return get_string('trainings_list_warning_no_trainings', 'block_attestoodle');
    }

    public function get_trainings() {
        return $this->trainings;
    }
}
