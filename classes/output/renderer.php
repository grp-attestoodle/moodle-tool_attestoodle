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
 * This class is the main renderer of the Attestoodle plug-in.
 * It handles the rendering of each page, called in index.php. The method called
 * depends on the parameters passed to the index.php page (page and action)
 *
 * @package    block_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_attestoodle\output;

use block_attestoodle\output\renderable\renderable_trainings_list;
use block_attestoodle\output\renderable\renderable_training_learners_list;

defined('MOODLE_INTERNAL') || die;

class renderer extends \plugin_renderer_base {

    // Automagically called
    public function render_renderer_with_template_page(renderer_with_template_page $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('block_attestoodle/renderer_template', $data);
    }

    // Automagically called
    public function render_renderer_simple_page(renderer_simple_page $page) {
        $out = $this->output->heading($page->training->get_name());
        $out .= $this->output->container($page->sometext);
        return $out;
    }

    /**
     *
     * @param renderable_trainings_list $data Useful data to display on the page
     */
    public function render_renderable_trainings_list(renderable_trainings_list $obj) {
        // create and return the output
        $trainings = $obj->trainings;
        $output = "";

        $output .= \html_writer::start_div('clearfix');
        // Link to the trainings management page.
        $output .= \html_writer::link(
                new \moodle_url(
                        '/blocks/attestoodle/pages/trainings_management.php'),
                        get_string('trainings_list_manage_trainings_link', 'block_attestoodle'),
                        array('class' => 'btn btn-default attestoodle-button'));
        $output .= \html_writer::end_div();

        if (count($trainings) > 0) {
            $data = parse_trainings_as_stdclass($trainings);

            $table = new \html_table();
            $table->head = array(
                get_string('trainings_list_table_header_column_name', 'block_attestoodle'),
                get_string('trainings_list_table_header_column_hierarchy', 'block_attestoodle'),
                get_string('trainings_list_table_header_column_description', 'block_attestoodle'),
                '');
            $table->data = $data;

            $output .= \html_writer::table($table);
        } else {
            $message = get_string('trainings_list_warning_no_trainings', 'block_attestoodle');
            $output .= $message;
        }

        return $output;
    }

    /**
     *
     * @param renderable_training_learners_list $obj
     */
    public function render_renderable_training_learners_list(renderable_training_learners_list $obj) {
         $training = $obj->training;
         $trainingexist = isset($training);
         $output = "";

        $output .= \html_writer::start_div('clearfix');
        // Link to the trainings list.
        $output .= \html_writer::link(
                new \moodle_url('/blocks/attestoodle/index.php'),
                get_string('trainings_list_btn_text', 'block_attestoodle'),
                array('class' => 'attestoodle-link'));

        if (!$trainingexist) {
            $output .= \html_writer::end_div();
            $warningunknownid = get_string('training_details_unknown_training_id', 'block_attestoodle');
            $output .= $warningunknownid;
        } else {
            $trainingid = $training->get_id();
            // Link to the training details.
            $output .= \html_writer::link(
                    new \moodle_url(
                            '/blocks/attestoodle/pages/training_details.php',
                            array('id' => $trainingid)),
                    get_string('training_learners_list_edit_training_link', 'block_attestoodle'),
                    array('class' => 'btn btn-default attestoodle-button'));
            $output .= \html_writer::end_div();

            $data = parse_learners_as_stdclass($training->get_learners(), $trainingid);
            $table = new \html_table();
            $table->head = array(
                get_string('training_learners_list_table_header_column_lastname', 'block_attestoodle'),
                get_string('training_learners_list_table_header_column_firstname', 'block_attestoodle'),
                get_string('training_learners_list_table_header_column_validated_activities', 'block_attestoodle'),
                get_string('training_learners_list_table_header_column_total_milestones', 'block_attestoodle'),
                '');
            $table->data = $data;

            $output .= $this->output->heading(get_string('training_learners_list_heading', 'block_attestoodle', count($data)));
            $output .= \html_writer::table($table);
        }

        return $output;
    }
}
