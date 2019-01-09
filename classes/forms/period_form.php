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
 * This is form for select period.
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_attestoodle\forms;

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/formslib.php");
/**
 * This is the class that handles the manage of period to compute credited time.
 *
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class period_form extends \moodleform {
    /**
     * Method automagically called when the form is instanciated. It defines
     * all the elements (inputs, titles, buttons, ...) in the form.
     */
    public function definition() {
        $mform = $this->_form;
        $group = array();
        $group[] =& $mform->createElement('date_selector', 'input_begin_date', '');
        $group[] =& $mform->createElement("static", null, null,
            get_string('learner_details_end_date_label', 'tool_attestoodle'));
        $group[] =& $mform->createElement('date_selector', 'input_end_date', '');

        $group[] =& $mform->createElement('submit', 'send',
            get_string('learner_details_submit_button_value', 'tool_attestoodle'), array('class' => 'send-button'));
        $mform->addGroup($group, 'period', get_string('learner_details_begin_date_label', 'tool_attestoodle') , '', false);
        $mform->addHelpButton('period', 'period_form', 'tool_attestoodle');
    }
}
