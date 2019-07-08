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
 * This is the form for setting the attestation template.
 *
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package    tool_attestoodle
 */
defined('MOODLE_INTERNAL') || die();

// Load repository lib, will load filelib and formslib !
require_once($CFG->dirroot . '/repository/lib.php');

/**
 * Form to create or modify the template of certificate.
 *
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
class attestation_form extends moodleform {

    /**
     * Method automagically called when the form is instanciated. It defines
     * all the elements (inputs, titles, buttons, ...) in the form.
     */
    protected function definition() {
        $mform    = $this->_form;

        $mform->addElement('hidden', 'namelock');
        $mform->setType('namelock', PARAM_INT);

        $mform->addElement('text', 'name', get_string('templatename', 'tool_attestoodle'), array("size" => 35));
        $mform->setType('name', PARAM_NOTAGS);
        $mform->disabledIf('name', 'namelock', 'eq', 1);

        $mform->addElement('filemanager', 'fichier', get_string('background', 'tool_attestoodle'),
            null,
            array(
                'subdirs' => 0,
                'maxbytes' => 10485760,
                'areamaxbytes' => 10485760,
                'maxfiles' => 1,
                'accepted_types' => array('.png'),
                'return_types' => FILE_INTERNAL | FILE_EXTERNAL));

        $learnergroup = $this->create_line('learner');
        $mform->addGroup($learnergroup, 'learnergroup', get_string('learner', 'tool_attestoodle'), ' ', false);

        $traininggroup = $this->create_line('training');
        $mform->addGroup($traininggroup, 'training', get_string('training', 'tool_attestoodle'), ' ', false);

        $periodgroup = $this->create_line('period');
        $mform->addGroup($periodgroup, 'period', get_string('period', 'tool_attestoodle'), ' ', false);

        $totminutegroup = $this->create_line('totminute');
        $mform->addGroup($totminutegroup, 'totalminute', get_string('totalminute', 'tool_attestoodle'), ' ', false);

        $cumulminutesgroup = $this->create_line('cumulminutes');
        $mform->addGroup($cumulminutesgroup, 'cumulminutes', get_string('cumulminutes', 'tool_attestoodle'), ' ', false);

        $activitiesgroup = $this->create_line('activities');
        $mform->addGroup($activitiesgroup, 'activities', get_string('tabactivities', 'tool_attestoodle'), ' ', false);

        $mform->addElement('hidden', 'templateid');
        $mform->setType('templateid', PARAM_INT);
        $mform->addElement('hidden', 'trainingid');
        $mform->setType('trainingid', PARAM_INT);

        $mform->addElement('header', 'literaux', get_string('literaux', 'tool_attestoodle'));
        for ($i = 1; $i <= 5; $i++) {
            $group = $this->create_line('text' . $i);
            $mform->addGroup($group, 'text' . $i, get_string('literal', 'tool_attestoodle') . ' ' . $i, ' ', false);
        }
        $mform->setExpanded('literaux', false);

        $mform->addElement('header', 'pagebreak', get_string('pagebreak', 'tool_attestoodle'));
        $radioarray = array();
        $radioarray[] = $mform->createElement('radio', 'viewpagenumber', '', get_string('nl_never', 'tool_attestoodle'), 0);
        $radioarray[] = $mform->createElement('radio', 'viewpagenumber', '', get_string('nl_necessary', 'tool_attestoodle'), 1);
        $radioarray[] = $mform->createElement('radio', 'viewpagenumber', '', get_string('nl_always', 'tool_attestoodle'), 2);
        $mform->addGroup($radioarray, 'viewpagenumber', get_string('viewpagenumber', 'tool_attestoodle'), array(' '), false);
        $group = $this->create_line('pagenumber');
        $group[] =& $mform->createElement('checkbox', 'pagenumber_total', '', get_string('nl_ontotal', 'tool_attestoodle'));
        $mform->addGroup($group, 'pagenumber', get_string('nl_pagenumber', 'tool_attestoodle'), ' ', false);
        $mform->disabledIf('pagenumber', 'viewpagenumber', 'eq', 0);
        $group = array();
        $group[] =& $mform->createElement('checkbox', 'repeatbackground', '');
        $mform->addGroup($group, 'repback', get_string('nl_background', 'tool_attestoodle'), ' ', false);
        $group = array();
        $group[] =& $mform->createElement('radio', 'repeatpreactivities', '', get_string('nl_preactch1', 'tool_attestoodle'), 0);
        $group[] =& $mform->createElement('radio', 'repeatpreactivities', '', get_string('nl_preactch2', 'tool_attestoodle'), 1);
        $mform->addGroup($group, 'reppreact', get_string('nl_preact', 'tool_attestoodle'), ' ', false);

        $group = array();
        $group[] =& $mform->createElement('radio', 'repeatpostactivities', '', get_string('nl_postactch1', 'tool_attestoodle'), 0);
        $group[] =& $mform->createElement('radio', 'repeatpostactivities', '', get_string('nl_preactch2', 'tool_attestoodle'), 1);
        $mform->addGroup($group, 'reppostact', get_string('nl_postact', 'tool_attestoodle'), ' ', false);

        $mform->setExpanded('pagebreak', false);

        $mform->addElement('header', 'actionssection', get_string('actions', 'tool_attestoodle'));
        $actionbuttongroup = array();
        $actionbuttongroup[] =& $mform->createElement('submit', 'save', get_string('savechanges'), array('class' => 'send-button'));
        $actionbuttongroup[] =& $mform->createElement('submit', 'cancel', get_string('cancel'), array('class' => 'cancel-button'));
        $mform->addGroup($actionbuttongroup, 'actionbuttongroup', '', ' ', false);
        $mform->setExpanded('actionssection', true);
    }

    /**
     * Create a group with all elements of a line.
     * @param string $prefix name of the subject of the line.
     */
    protected function create_line($prefix) {
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
        $group[] =& $mform->createElement('select', $prefix . 'FontFamily', '', $familles, null);
        $group[] =& $mform->createElement("static", null, null, get_string('emphasis', 'tool_attestoodle'));
        $group[] =& $mform->createElement('select', $prefix . 'Emphasis', '', $emphases, null);
        $group[] =& $mform->createElement("static", null, null, get_string('size', 'tool_attestoodle'));
        $group[] =& $mform->createElement('select', $prefix . 'FontSize', '', $sizes, null);
        $group[] =& $mform->createElement("static", null, null, get_string('align', 'tool_attestoodle'));
        $group[] =& $mform->createElement('select', $prefix . 'Align', '', $alignments, null);
        if ($prefix != "activities") {
            $group[] =& $mform->createElement("static", null, null, '<br>' . get_string('rubric', 'tool_attestoodle'));
            $group[] =& $mform->createElement('text', $prefix . 'lib', '', array("size" => 45));
            $mform->setType($prefix . 'lib', PARAM_TEXT );
        } else {
            $group[] =& $mform->createElement("static", null, null, '<br>' . get_string('arraysize', 'tool_attestoodle'));
            $group[] =& $mform->createElement('text', $prefix . 'size', '', array("size" => 3));
            $mform->setType($prefix . 'size', PARAM_INT);
        }
        return $group;
    }

    /**
     * Intercept the display of form so can format errors as notifications.
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
     * @param string $name of form element to update
     * @param string $options of the element.
     * @param array $selected list of selected elements.
     * @return bool
     */
    public function update_selectgroup($name, $options, $selected=array()) {
        $mform   = $this->_form;
        $element = $mform->getElement($name);
        $element->_optGroups = array(); // Reset the optgroup array() !
        return $element->loadArrayOptGroups($options, $selected);
    }

    /**
     * validate the form.
     * If name no lock, then name must be not null.
     * @param stdClass $data of form
     * @param string $files list of the form files
     * @return array of error.
     */
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        if (isset($data['cancel'])) {
            return $errors;
        }

        if (empty($data['name']) && $data['namelock'] != 1) {
            $errors['body'] = get_string('errnotemplatename', 'tool_attestoodle');
        }

        if (!empty($data['name']) && $data['namelock'] != 1) {
            $sql = 'select * from {tool_attestoodle_template} where name = ? and id != ?';
            if ($DB->record_exists_sql($sql, array($data['name'], $data['templateid']))) {
                $errors['body'] = get_string('errduplicatename', 'tool_attestoodle');
            }
        }
        return $errors;
    }

    /**
     * Indicates if action equal submit.
     * @return null or action.
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
