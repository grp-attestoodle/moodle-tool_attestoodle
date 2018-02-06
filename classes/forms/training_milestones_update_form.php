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
 * This is the class that handle the modification of milestones values.
 *
 * @package    block_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_attestoodle\forms;

defined('MOODLE_INTERNAL') || die;

// Class \moodleform is defined in formslib.php.
require_once("$CFG->libdir/formslib.php");

class training_milestones_update_form extends \moodleform {
    // Add elements to form.
    public function definition() {
        global $CFG;

        $inputnameprefix = $this->_customdata['input_name_prefix'];
        $courses = $this->_customdata['data'];

        $mform = $this->_form;

        // For each course we set a collapsible fieldset.
        foreach ($courses as $course) {
            $totalmilestones = parse_minutes_to_hours($course->get_total_milestones());
            $mform->addElement('header', $course->get_id(), "{$course->get_name()} : {$totalmilestones}");

            // For each activity in this course we add a form input element.
            foreach ($course->get_activities() as $activity) {
                $name = $inputnameprefix  . $activity->get_id();
                $label = $activity->get_name();
                $type = $activity->get_type();
                $marker = $activity->get_marker();

                $mform->addElement("text", $name, "{$label} ({$type})", array('size' => 5)); // Max 5 char.
                $mform->setType($name, PARAM_ALPHANUM); // Parsing the value in INT after submit.
                $mform->addRule($name, null, 'numeric', null, 'client'); // Handle error in JS (must be numeric).
                $mform->setDefault($name, $marker); // Set default value to the current milestone value.
            }
        }

        $this->add_action_buttons();
    }

    // Custom validation should be added here.
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }
}
