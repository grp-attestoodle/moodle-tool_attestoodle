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
use tool_attestoodle\utils\db_accessor;

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/formslib.php");
/**
 * This is the class that handles the manage of period to compute credited time.
 *
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class add_course_form extends \moodleform {
    /**
     * Method called when the form is instanciated. It defines
     * all the elements (inputs, titles, buttons, ...) in the form.
     */
    public function definition() {
        $mform = $this->_form;
        $group = array();
        $group[] =& $mform->createElement('text', 'coursename', '', array("size" => 35));
        $mform->setType('coursename', PARAM_NOTAGS);
        $group[] =& $mform->createElement('submit', 'action',
            'Ajouter', array('class' => 'send-button'));
        $mform->addGroup($group, 'period', 'Cours à ajouter' , '', false);
    }
}
