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

defined('MOODLE_INTERNAL') || die();

// Load repository lib, will load filelib and formslib !
require_once($CFG->dirroot . '/repository/lib.php');

/**
 * Form to create or modify the template of certificate.
 *
 * @package tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
class attestation_form extends moodleform {

    protected function definition() {
        global $PAGE;

        $mform    = $this->_form;

        $mform->addElement('text', 'filename', get_string('background', 'tool_attestoodle'), array('size' => '30'));
        $mform->setType('filename', PARAM_TEXT);

        $learnergroup = $this->creer_ligne('learner');
        $mform->addGroup($learnergroup, 'learnergroup', get_string('learner', 'tool_attestoodle'), ' ', false);

        $traininggroup = $this->creer_ligne('training');
        $mform->addGroup($traininggroup, 'training', get_string('training', 'tool_attestoodle'), ' ', false);

        $periodgroup = $this->creer_ligne('period');
        $mform->addGroup($periodgroup, 'period', get_string('period', 'tool_attestoodle'), ' ', false);

        $totminutegroup = $this->creer_ligne('totminute');
        $mform->addGroup($totminutegroup, 'totalminute', get_string('totalminute', 'tool_attestoodle'), ' ', false);

        $activitiesgroup = $this->creer_ligne('activities');
        $mform->addGroup($activitiesgroup, 'activities', get_string('tabactivities', 'tool_attestoodle'), ' ', false);

        $mform->addElement('hidden', 'templateid');
        $mform->setType('templateid', PARAM_INT);
        $mform->addElement('hidden', 'trainingid');
        $mform->setType('trainingid', PARAM_INT);

        $mform->addElement('header', 'actionssection', get_string('actions', 'tool_attestoodle'));
        $actionbuttongroup = array();
        $actionbuttongroup[] =& $mform->createElement('submit', 'save', get_string('savechanges'), array('class' => 'send-button'));
        $actionbuttongroup[] =& $mform->createElement('submit', 'cancel', get_string('cancel'), array('class' => 'cancel-button'));
        $mform->addGroup($actionbuttongroup, 'actionbuttongroup', '', ' ', false);
        $mform->setExpanded('actionssection', true);
    }

    /**
     * Create a group with all elements of a line.
     * @param $prefix name of the subject of the line.
     */
    protected function creer_ligne($prefix) {
        $familles = array('courier', 'helvetica', 'times');
        $emphases = array('', 'B', 'I');
        $alignments = array('L', 'R', 'C', 'J');
        $sizes = array('6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16',
            '18', '20', '22', '24', '26', '28', '32', '36', '40', '44', '48', '54', '60', '66', '72');

        $mform    = $this->_form;
        $group = array();
        $group[] =& $mform->createElement("static", null, null, " X :");
        $group[] =& $mform->createElement('text', $prefix . 'Posx', '', array("size" => 3));
        $mform->setType($prefix . 'Posx', PARAM_INT);
        $group[] =& $mform->createElement("static", null, null, " Y :");
        $group[] =& $mform->createElement('text', $prefix . 'Posy', '', array("size" => 3));
        $mform->setType($prefix . 'Posy', PARAM_INT);
        $group[] =& $mform->createElement("static", null, null, get_string('font', 'tool_attestoodle'));
        $group[] =& $mform->createElement('select', $prefix . 'FontFamily', '', $familles, array("size" => 12));
        $group[] =& $mform->createElement("static", null, null, get_string('emphasis', 'tool_attestoodle'));
        $group[] =& $mform->createElement('select', $prefix . 'Emphasis', '', $emphases, array("size" => 1));
        $group[] =& $mform->createElement("static", null, null, get_string('size', 'tool_attestoodle'));
        $group[] =& $mform->createElement('select', $prefix . 'FontSize', '', $sizes, array("size" => 2));
        $group[] =& $mform->createElement("static", null, null, get_string('align', 'tool_attestoodle'));
        $group[] =& $mform->createElement('select', $prefix . 'Align', '', $alignments, array("size" => 1));
        return $group;
    }

    /**
     * Intercept the display of form so can format errors as notifications
     *
     * @global type $OUTPUT
     */
    public function display() {
        global $OUTPUT;

        if ($this->_form->_errors) {
            foreach ($this->_form->_errors as $error) {
                echo $OUTPUT->notification($error, 'notifyproblem');
            }
            unset($this->_form->_errors);
        }

        parent::display();
    }

    /**
     * Helper method, because removeElement can't handle groups and there no
     * method to do this, how suckful!
     *
     * @param string $elementname
     * @param string $groupname
     */
    public function remove_from_group($elementname, $groupname) {
        $group = $this->_form->getElement($groupname);
        foreach ($group->_elements as $key => $element) {
            if ($element->_attributes['name'] == $elementname) {
                unset($group->_elements[$key]);
            }
        }
    }

    /**
     * Helper method
     * @param type $name
     * @param type $options
     * @param type $selected
     * @return type
     */
    public function update_selectgroup($name, $options, $selected=array()) {
        $mform   = $this->_form;
        $element = $mform->getElement($name);
        $element->_optGroups = array(); // Reset the optgroup array() !
        return $element->loadArrayOptGroups($options, $selected);
    }

    /**
     * Returns the options array to use in dialogue text editor
     *
     * @return array
     */
    public static function editor_options() {
        global $CFG, $COURSE, $PAGE;

        $maxbytes = get_user_max_upload_file_size($PAGE->context, $CFG->maxbytes, $COURSE->maxbytes);
        return array(
            'collapsed' => true,
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'maxbytes' => $maxbytes,
            'trusttext' => true,
            'accepted_types' => '*',
            'return_types' => FILE_INTERNAL | FILE_EXTERNAL
        );
    }

    /**
     * Returns the options array to use in filemanager for dialogue attachments
     *
     * @return array
     */
    public static function attachment_options() {
        global $CFG, $COURSE, $PAGE;
        $maxbytes = get_user_max_upload_file_size($PAGE->context, $CFG->maxbytes,
                $COURSE->maxbytes, $PAGE->activityrecord->maxbytes);
        return array(
            'subdirs' => 0,
            'maxbytes' => $maxbytes,
            'maxfiles' => $PAGE->activityrecord->maxattachments,
            'accepted_types' => '*',
            'return_types' => FILE_INTERNAL
        );
    }

    /**
     *
     * @param type $data
     * @param type $files
     * @return type
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Add validation rule on $data['trainingPosx'] > 0 !
        return $errors;
    }

    /**
     *
     * @return null
     */
    public function get_submit_action() {
        $submitactions = array('send', 'save', 'cancel', 'trash');
        foreach ($submitactions as $submitaction) {
            if (optional_param($submitaction, false, PARAM_BOOL)) {
                return $submitaction;
            }
        }
        return null;
    }
}
