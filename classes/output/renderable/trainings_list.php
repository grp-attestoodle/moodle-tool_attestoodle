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
 * Page trainings list.
 *
 * Renderable class that is used to render the page that allow user to display
 * the list of all the trainings declared in Attestoodle.
 *
 * @package    block_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_attestoodle\output\renderable;

defined('MOODLE_INTERNAL') || die;

use \renderable;

class trainings_list implements renderable {
    /** @var training[] Trainings in Attestoodle */
    private $trainings = [];

    /**
     * Constructor class.
     *
     * @param training[] $trainings Trainings in Attestoodle
     */
    public function __construct($trainings) {
        $this->trainings = $trainings;
    }

    /**
     * Computes the content header.
     *
     * @return string The computed HTML string of the page header
     */
    public function get_header() {
        $output = "";

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
                get_string('trainings_list_table_header_column_name', 'block_attestoodle'),
                get_string('trainings_list_table_header_column_hierarchy', 'block_attestoodle'),
                get_string('trainings_list_table_header_column_description', 'block_attestoodle'),
                ''
        );
    }

    /**
     * Returns the table content used by moodle html_table function to display a
     * html table content.
     *
     * @return \stdClass[] The array of \stdClass used by html_table function
     */
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

    /**
     * Returns the string that says that there is no training yet.
     *
     * @return string The no training message, translated
     */
    public function get_no_training_message() {
        return get_string('trainings_list_warning_no_trainings', 'block_attestoodle');
    }

    /**
     * Getter for $trainings property.
     *
     * @return training[] The trainings in Attestoodle
     */
    public function get_trainings() {
        return $this->trainings;
    }
}
