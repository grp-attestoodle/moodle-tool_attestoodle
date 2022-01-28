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
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_attestoodle\output\renderable;


use \renderable;
use tool_attestoodle\utils\plugins_accessor;
/**
 * Display the list of all the trainings declared in Attestoodle.
 *
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
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
        $ret = array(
                get_string('trainings_list_table_header_column_name', 'tool_attestoodle'),
                get_string('trainings_list_table_header_column_hierarchy', 'tool_attestoodle'),
                get_string('trainings_list_table_header_column_description', 'tool_attestoodle'),
                ''
        );
        if (plugins_accessor::get_instance()->get_task_plugin_info() != null) {
            $ret = array(
                get_string('trainings_list_table_header_column_name', 'tool_attestoodle'),
                get_string('trainings_list_table_header_column_hierarchy', 'tool_attestoodle'),
                get_string('trainings_list_table_header_column_description', 'tool_attestoodle'),
                get_string('deadline', 'tool_attestoodle'),
                '');
        }
        return $ret;
    }

    /**
     * Returns the table content used by moodle html_table function to display a
     * html table content.
     *
     * @return \stdClass[] The array of \stdClass used by html_table function
     */
    public function get_table_content() {
        return array_map(function($training) {
            global $OUTPUT;
            $stdclass = new \stdClass();

            $categorylink = new \moodle_url("/course/index.php", array("categoryid" => $training->get_categoryid()));
            $stdclass->name = "<a href='{$categorylink}'>{$training->get_name()}</a>";

            $stdclass->hierarchy = $training->get_hierarchy();

            $stdclass->description = $training->get_description();

            // Links.
            $parameters = array(
                'typepage' => 'learners', 'trainingid' => $training->get_id(),
                'categoryid' => $training->get_categoryid());
            $url = new \moodle_url('/admin/tool/attestoodle/index.php', $parameters);
            $label = "<img src=" . $OUTPUT->image_url ( 'i/group', 'moodle' ). " title='"
                . get_string('student_list_link', 'tool_attestoodle') ."' />";

            $studentlink = \html_writer::link($url, $label);

            $parameters = array('typepage' => 'trainingmanagement',
                'categoryid' => $training->get_categoryid(), 'trainingid' => $training->get_id());
            $url = new \moodle_url('/admin/tool/attestoodle/index.php', $parameters);
            $label = "<img src=" . $OUTPUT->image_url ( 'i/settings', 'moodle' ). " title='"
                . get_string('training_setting_link', 'tool_attestoodle') ."' />";

            $settinglink = \html_writer::link($url, $label);

            $parameters = array('typepage' => 'managemilestones',
                'categoryid' => $training->get_categoryid(), 'trainingid' => $training->get_id());
            $url = new \moodle_url('/admin/tool/attestoodle/index.php', $parameters);
            $label = "<img src=" . $OUTPUT->image_url ( 'navigation', 'tool_attestoodle' ). " title='"
                . get_string('milestone_manage_link', 'tool_attestoodle') ."' />";

            $milestonelink = \html_writer::link($url, $label);
            $stdclass->link = $settinglink . " &nbsp; " .  $milestonelink . " &nbsp; " . $studentlink;

            $urltask = plugins_accessor::get_instance()->get_task_link($training->get_id());
            if (!empty($urltask)) {
                $label = $training->get_nextlaunch();
                if (!isset($label) || $label == 0) {
                    $now = new \DateTime();
                    $trainingend = $training->get_end();
                    if ($trainingend != null && $now->getTimestamp() > $trainingend) {
                        $label = get_string('finished', 'tool_attestoodle');
                    } else {
                        $label = get_string('toplan', 'tool_attestoodle');
                    }
                } else {
                    $next = new \DateTime();
                    $next->setTimestamp($label);
                    $label = $next->format(get_string('dateformat', 'tool_attestoodle'));
                }
                $stdclass->task = \html_writer::link($urltask, $label);
            }
            return $stdclass;
        }, $this->trainings);
    }

    /**
     * Returns the string that says that there is no training yet.
     *
     * @return string The no training message, translated
     */
    public function get_no_training_message() {
        return get_string('trainings_list_warning_no_trainings', 'tool_attestoodle');
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
