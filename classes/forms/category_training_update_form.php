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
 * This is form for modification/suppression of training.
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_attestoodle\forms;
use tool_attestoodle\factories\trainings_factory;
defined('MOODLE_INTERNAL') || die;

// Class \moodleform is defined in formslib.php.
require_once("$CFG->libdir/formslib.php");
/**
 * Class that handles the modification/suppression of trainings through moodleform.
 *
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class category_training_update_form extends \moodleform {
    /** @var string name of trainning. */
    private $oldname = null;

    /**
     * Method automagically called when the form is instanciated. It defines
     * all the elements (inputs, titles, buttons, ...) in the form.
     */
    public function definition() {
        global $CFG, $DB;
        $editmode = 0;
        if ($this->_customdata['editmode']) {
            $editmode = 1;
        }
        $mform = $this->_form;

        $mform->addElement('hidden', 'edition');
        $mform->setType('edition', PARAM_INT);
        $mform->setDefault('edition', $editmode);

        $name = "checkbox_is_training";
        $label = get_string('training_management_checkbox_label', 'tool_attestoodle');

        $idtemplate = $this->_customdata['idtemplate'];
        $trainingid = $this->_customdata['trainingid'];
        $category = $this->_customdata['data'];
        $istraining = $category->is_training();

        if ($idtemplate == -1 && $editmode == 1) {
            $mform->addElement("static", null, null, get_string('confirmtraincreate', 'tool_attestoodle'));
            $group = array();
            $group[] =& $mform->createElement('submit', 'create_yes', get_string('yes'), array('class' => 'send-button'));
            $group[] =& $mform->createElement('submit', 'create_no', get_string('no'), array('class' => 'send-button'));
            $mform->addGroup($group, 'buttongroup', '', ' ', false);
        }

        if ($istraining) {
            $mform->addElement('text', 'name', get_string('trainingname', 'tool_attestoodle'), array("size" => 50));
            $mform->setType('name', PARAM_NOTAGS);
            $training = trainings_factory::get_instance()->retrieve_training($category->get_id());
            $mform->setDefault('name', $training->get_name());
            $this->oldname = $training->get_name();
            $mform->disabledIf('name', 'edition', 'eq', 0);
            $mform->addElement('date_selector', 'startdate', get_string('starttraining', 'tool_attestoodle'));
            $mform->disabledIf('startdate', 'edition', 'eq', 0);
            $mform->addElement('date_selector', 'enddate', get_string('endtraining', 'tool_attestoodle'));
            $mform->disabledIf('enddate', 'edition', 'eq', 0);
            $mform->addElement('text', 'duration', get_string('durationtraining', 'tool_attestoodle') , array("size" => 3));
            $mform->setType('duration', PARAM_INT);
            $mform->disabledIf('duration', 'edition', 'eq', 0);
        }

        if ($idtemplate > -1) {
            $mform->addElement('header', 'templatesection', get_string('template_certificate', 'tool_attestoodle'));
            $group = array();
            // Select template.
            $rs = $DB->get_records('tool_attestoodle_template', null, null, 'id, name');
            $lsttemplate = array();
            foreach ($rs as $result) {
                $lsttemplate[$result->id] = $result->name;
            }
            $group[] =& $mform->createElement('select', 'template', '', $lsttemplate, null);

            $context = \context_coursecat::instance($category->get_id());
            if (has_capability('tool/attestoodle:viewtemplate', $context)) {
                $previewlink = '<a target="preview" href="' . $CFG->wwwroot .
                    '/admin/tool/attestoodle/classes/gabarit/view_export.php?templateid=' . $idtemplate .
                    '&trainingid=' . $trainingid . '" class= "btn-create">'.
                    get_string('preview', 'tool_attestoodle').'</a>';
                $group[] =& $mform->createElement("static", null, null, $previewlink);
            }

            if (has_capability('tool/attestoodle:managetemplate', \context_system::instance())) {
                $previewlink = '&nbsp;<a href="' . $CFG->wwwroot .
                    '/admin/tool/attestoodle/classes/gabarit/sitecertificate.php?templateid=-1"
                    class= "btn-create">' . get_string('createtemplate', 'tool_attestoodle').'</a>';
                $group[] =& $mform->createElement("static", null, null, $previewlink);
            }

            $mform->addGroup($group, 'activities', get_string('template_certificate', 'tool_attestoodle'), ' ', false);
            $mform->disabledIf('activities', 'edition', 'eq', 0);
            // Level of grouping.
            $level1s = array(
                    'coursename' => get_string('grp_course', 'tool_attestoodle'),
                    'name' => get_string('grp_activity', 'tool_attestoodle'),
                    'type' => get_string('grp_type', 'tool_attestoodle')
                    );
            $level2s = array_merge(array('' => ''), $level1s);
            $mform->addElement('select', 'group1', get_string('grp_level1', 'tool_attestoodle'), $level1s, null);
            $mform->disabledIf('group1', 'edition', 'eq', 0);

            $mform->addElement('select', 'group2', get_string('grp_level2', 'tool_attestoodle'), $level2s, null);
            $mform->disabledIf('group2', 'edition', 'eq', 0);
            $mform->setExpanded('templatesection', false);
        }
        if ($editmode == 1 && $idtemplate != -1) {
            $mform->addElement('header', 'actionsection', get_string('actions'));
            $actionbuttongroup = array();
            $actionbuttongroup[] =& $mform->createElement('submit', 'save', get_string('savechanges'),
                array('class' => 'send-button'));
            $actionbuttongroup[] =& $mform->createElement('submit', 'delete', get_string('delete'),
                array('class' => 'cancel-button'));
            $mform->addGroup($actionbuttongroup, 'actionbuttongroup', '', ' ', false);
            $mform->setExpanded('actionsection', true);
        }
    }

    /**
     * Custom validation function automagically called when the form
     * is submitted. The standard validations, such as required inputs or
     * value type check, are done by the parent validation() method.
     * See validation() method in moodleform class for more details.
     * @param stdClass $data of form
     * @param string $files list of the form files
     * @return array of error.
     */
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        if (isset($data['name'])) {
            if ($this->oldname != $data['name']) {
                // Name Already exist ?
                $sql = 'select * from {tool_attestoodle_training} where name = ?';
                if ($DB->record_exists_sql($sql, array($data['name']))) {
                    $errors['name'] = get_string('errduplicatename', 'tool_attestoodle');
                }
            }
        }

        if (isset($data['enddate']) && $data['startdate'] > $data['enddate']) {
            $errors['enddate'] = get_string('errdateend', 'tool_attestoodle');
        }
        if (array_key_exists('group1', $data) && $data['group2'] == $data['group1']) {
            $errors['group2'] = get_string('error_same_criteria', 'tool_attestoodle');
        }
        return $errors;
    }
}
