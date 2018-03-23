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

defined('MOODLE_INTERNAL') || die;

use block_attestoodle\output\renderable;

class renderer extends \plugin_renderer_base {
    /**
     * Page trainings list (default page)
     *
     * @param \block_attestoodle\output\renderable\trainings_list $obj Useful informations to display
     * @return string HTML content of the page
     */
    public function render_trainings_list(renderable\trainings_list $obj) {
        $output = "";

        $output .= $obj->get_header();

        if (count($obj->get_trainings()) > 0) {
            $table = new \html_table();
            $table->head = $obj->get_table_head();
            $table->data = $obj->get_table_content();

            $output .= \html_writer::table($table);
        } else {
            $output .= $obj->get_no_training_message();
        }

        return $output;
    }

    public function render_trainings_management(renderable\trainings_management $obj) {
        $output = "";

        $output .= $obj->get_header();
        $output .= $obj->get_content();

        return $output;
    }

    /**
     * Page training learners list
     *
     * @param \block_attestoodle\output\renderable\training_learners_list $obj Useful informations to display
     * @return string HTML content of the page
     */
    public function render_training_learners_list(renderable\training_learners_list $obj) {
        $output = "";

        $output .= $obj->get_header();

        if ($obj->training_exists()) {
            $table = new \html_table();
            $table->head = $obj->get_table_head();
            $table->data = $obj->get_table_content();

            $output .= $this->output->heading(get_string(
                    'training_learners_list_heading',
                    'block_attestoodle',
                    count($obj->training->get_learners())
            ));
            $output .= \html_writer::table($table);
        } else {
            $output .= $obj->get_unknown_training_message();
        }

        return $output;
    }

    public function render_training_milestones(renderable\training_milestones $obj) {
        $output = "";

        if (!$obj->training_exists()) {
            $output .= get_string('training_details_unknown_training_id', 'block_attestoodle') . $obj->get_trainingid();
        } else {
            $output .= $obj->get_header();
            $output .= $obj->get_content();
        }

        return $output;
    }

    /**
     * Page learner details
     *
     * @param \block_attestoodle\output\renderable\learner_details $obj Useful informations to display
     * @return string HTML content of the page
     */
    public function render_learner_details(renderable\learner_details $obj) {
        $output = "";

        $output .= $obj->get_header();

        if ($obj->training_exists() && $obj->learner_exists()) {
            // If the training and learner ids are valid...
            // Print validated activities informations (with marker only).
            if (count($obj->get_learner_validated_activities()) > 0) {
                $table = new \html_table();
                $table->head = $obj->get_table_head();
                $table->data = $obj->get_table_content();

                $output .= \html_writer::table($table);

                $output .= "<hr />";

                // TODO footer should be displayed even if there is no validated activities.
                $output .= $obj->get_footer();
            } else {
                $output .= $obj->get_no_validated_activities_message();
            }
        }

        return $output;
    }
}
