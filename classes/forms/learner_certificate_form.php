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
 * This is form that allows the personalization of a learner's attestation template.
 *
 * @package    tool_attestoodle
 * @copyright  2019 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_attestoodle\forms;
use tool_attestoodle\factories\trainings_factory;
defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/formslib.php");
/**
 * Customization of a learner's attestation template.
 *
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class learner_certificate_form extends \moodleform {
    /**
     * Method automagically called when the form is instanciated. It defines
     * all the elements (inputs, titles, buttons, ...) in the form.
     */
    public function definition() {
        global $CFG, $DB;
        $mform = $this->_form;

        $categoryid = $this->_customdata['categoryid'];
        $idtraining = $this->_customdata['idtraining'];
        $idtemplate = $this->_customdata['idtemplate'];

        $mform->addElement('hidden', 'idtraining');
        $mform->setType('idtraining', PARAM_INT);
        $mform->setDefault('idtraining', $idtraining);
        $mform->addElement('header', 'templatesection', get_string('template_certificate', 'tool_attestoodle'));
        $mform->addElement("advcheckbox", 'custom',
            get_string('customizecertif', 'tool_attestoodle'),
            get_string('customizecertiflib', 'tool_attestoodle'));

        $mform->addElement("advcheckbox", 'disablecertif',
            get_string('disablecertif', 'tool_attestoodle'),
            get_string('disablecertiflib', 'tool_attestoodle'));
        $mform->disabledIf('disablecertif', 'custom', 'eq', 0);

        $group = array();
        // Select template.
        $rs = $DB->get_records('tool_attestoodle_template', null, null, 'id, name');
        $lsttemplate = array();
        foreach ($rs as $result) {
            $lsttemplate[$result->id] = $result->name;
        }
        $group[] =& $mform->createElement('select', 'template', '', $lsttemplate, null);
        $mform->setDefault('template', $idtemplate);
        $mform->disabledIf('template', 'custom', 'eq', 0);
        $mform->disabledIf('template', 'disablecertif', 'eq', 1);

        $context = \context_coursecat::instance($categoryid);
        if (has_capability('tool/attestoodle:viewtemplate', $context)) {
            $previewlink = '<a target="preview" href="' . $CFG->wwwroot .
                '/admin/tool/attestoodle/classes/gabarit/view_export.php?templateid=' . $idtemplate .
                '&trainingid=' . $idtraining . '" class= "btn-create">'.
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
        // Level of grouping.
        $level1s = array(
                    'coursename' => get_string('grp_course', 'tool_attestoodle'),
                    'name' => get_string('grp_activity', 'tool_attestoodle'),
                    'type' => get_string('grp_type', 'tool_attestoodle')
                    );
        $level2s = array_merge(array('' => ''), $level1s);
        $mform->addElement('select', 'group1', get_string('grp_level1', 'tool_attestoodle'), $level1s, null);
        $mform->disabledIf('group1', 'custom', 'eq', 0);
        $mform->disabledIf('group1', 'disablecertif', 'eq', 1);

        $mform->addElement('select', 'group2', get_string('grp_level2', 'tool_attestoodle'), $level2s, null);
        $mform->disabledIf('group2', 'custom', 'eq', 0);
        $mform->disabledIf('group2', 'disablecertif', 'eq', 1);

        $mform->setExpanded('templatesection', false);
        $this->add_action_buttons(false);
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
        $errors = parent::validation($data, $files);

        if (array_key_exists('group1', $data) && $data['group2'] == $data['group1']) {
            $errors['group2'] = get_string('error_same_criteria', 'tool_attestoodle');
        }
        return $errors;
    }
}
