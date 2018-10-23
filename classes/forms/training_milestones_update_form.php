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
 * This is the class that handles the modification of milestones values through
 * a moodle moodleform object.
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_attestoodle\forms;
use tool_attestoodle\utils\db_accessor;

defined('MOODLE_INTERNAL') || die;

// Class \moodleform is defined in formslib.php.
require_once("$CFG->libdir/formslib.php");

class training_milestones_update_form extends \moodleform {
    /**
     * Method automagically called when the form is instanciated. It defines
     * all the elements (inputs, titles, buttons, ...) in the form.
     */
    public function definition() {
        $inputnameprefix = $this->_customdata['input_name_prefix'];
        $elements = $this->get_elements($this->_customdata['data'], $inputnameprefix);

        $mform = $this->_form;
        $suffix = get_string("training_milestones_form_input_suffix", "tool_attestoodle");
        foreach ($elements as $course) {
            $mform->addElement('header', $course->id, "{$course->name} : {$course->totalmilestones}");
            $mform->setExpanded($course->id, false);
            // For each activity in this course we add a form input element.
            foreach ($course->activities as $activity) {
                $groupname = "group_" . $activity->name;
                // The group contains the input, the label and a fixed span (required to have more complex form lines).
                $group = array();
                $group[] =& $mform->createElement("text", $activity->name, null, array("size" => 5)); // Max 5 char.
                $mform->setType($activity->name, PARAM_ALPHANUM); // Parsing the value in INT after submit.
                $mform->setDefault($activity->name, $activity->milestone); // Set default value to the current milestone value.
                $group[] =& $mform->createElement("static", null, null, "<span>{$suffix}</span>");
                $mform->addGroup($group, $groupname, "{$activity->label} ({$activity->type})", array(' '), false);
                $mform->addGroupRule($groupname, array(
                        $activity->name => array(
                                array(null, 'numeric', null, 'client')
                        )
                    ));
            }
        }
        $this->add_action_buttons();
    }

    private function get_elements($courses, $prefix) {
        $ret = array();
        foreach ($courses as $course) {
            $datacourse = new \stdClass();
            $datacourse->totalmilestones = parse_minutes_to_hours($course->get_total_milestones());
            $datacourse->id = $course->get_id();
            $datacourse->name = $course->get_name();
            $activities = db_accessor::get_instance()->get_activiesbysection($datacourse->id);

            foreach ($course->get_activities() as $activity) {
                $idfind = -1;
                foreach ($activities as $key => $value) {
                    if ($value->id == $activity->get_id()) {
                        $idfind = $key;
                    }
                }
                if ($idfind == -1) {
                    continue;
                }

                $dataactivity = $activities[$idfind];
                $dataactivity->name = $prefix  . $activity->get_id();
                $dataactivity->label = $activity->get_name();
                $dataactivity->type = get_string('modulename', $activity->get_type());
                $dataactivity->milestone = $activity->get_milestone();
                if (plugin_supports('mod', $activity->get_type(), FEATURE_MOD_ARCHETYPE) != MOD_ARCHETYPE_RESOURCE) {
                    $dataactivity->ressource = 0;
                } else {
                    $dataactivity->ressource = 1;
                }
            }
            $datacourse->activities = $activities;
            $ret[] = $datacourse;
        }
        return $ret;
    }
    /**
     * Custom validation function automagically called when the form
     * is submitted. The standard validations, such as required inputs or
     * value type check, are done by the parent validation() method.
     *
     * See validation() method in moodleform class for more details.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }
}
