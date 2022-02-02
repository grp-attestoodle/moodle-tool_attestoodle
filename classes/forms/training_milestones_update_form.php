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
 * This is form for the modification of milestones values.
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_attestoodle\forms;
use tool_attestoodle\utils\db_accessor;

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/formslib.php");
/**
 * This is the class that handles the modification of milestones values through moodleform.
 *
 * @copyright  2018 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class training_milestones_update_form extends \moodleform {
    /**
     * Method automagically called when the form is instanciated. It defines
     * all the elements (inputs, titles, buttons, ...) in the form.
     * It needs to receive an array during instanciation (usable here as $this->_customdata),
     * with the following elements :
     * 'data' => a collection of trainings's courses
     * 'input_name_prefix' => module temporary name prefix (for internal use) : "attestoodle_activity_id_"
     * 'type' => module type to filter with
     * 'namemod' => module name to filter with
     * 'visibmod' => filter only visible modules or not
     * 'restrictmod' => filter or not only modules with access restrictions
     * 'milestonemod' => filter only modules declared as milestone or not
     * 'orderbyselection' => type of module grouping (0 by course, 1 by expected completion month)
     * 'orderbyfrom' => array (year, month, day) to define starting month (only used when orderbyselection > 0)
     * 'modifallow' => true if milestones modification is allowed in this context
     */
    public function definition() {
        $inputnameprefix = $this->_customdata['input_name_prefix'];
        $courses = $this->get_elements($this->_customdata['data'], $inputnameprefix);
        if (isset($this->_customdata['orderbyfrom'])) {
            if (!is_array($this->_customdata['orderbyfrom'])) { // If orderbyfrom has been converted to timestamp,
                    // convert it back to array.
                $this->_customdata['orderbyfrom'] = array(
                    "day" => date("d", $this->_customdata['orderbyfrom']),
                    "month" => date("m", $this->_customdata['orderbyfrom']),
                    "year" => date("Y", $this->_customdata['orderbyfrom']));
            }
        } else { // Default value.
            $this->_customdata['orderbyfrom'] = array("day" => 01, "month" => 01, "year" => 2020);
        }

        $mform = $this->_form;
        $this->add_filter(); // Add filter part of the form.

        $this->add_orderby(); // Add orderby part of the form.

        $editmode = optional_param('edition', 0, PARAM_INT);
        if ($editmode != 0) {
            $this->_customdata['modifallow'] = true;
        }
        $mform->addElement('hidden', 'edition');
        $mform->setType('edition', PARAM_INT);
        if ($this->_customdata['modifallow']) {
            $mform->setDefault('edition', 1);
        } else {
            $mform->setDefault('edition', $editmode);
        }

        // Modules grouping.
        if (!isset($this->_customdata['orderbyselection'])) {
            $this->_customdata['orderbyselection'] = 0; // Default value.
        }
        switch ($this->_customdata['orderbyselection']) {
            case 0: // Choice orderbycourse.
                $modulelist = $this->get_moduleslist_by_course($courses);
                break;
            case 1: // Choice orderbymonth.
                $modulelist = $this->get_moduleslist_by_month($courses);
                break;
        }

        $this->generate_form_list($mform, $modulelist);

        if ($this->_customdata['modifallow']) {
            $this->add_action_buttons();
        }
    }

    /**
     * Explore the modules set to create a list, grouped by course.
     * In other words, the purpose is to have a set of course and in each a subset of modules
     * that belongs to that course.
     * @param array $courses integrated inside the training, including their own modules
     * @return array a grouped modules list
     */
    private function get_moduleslist_by_course($courses) {
        $grouping = array();
        foreach ($courses as $course) {
            $filteredactivities = array();
            foreach ($course->activities as $activity) {
                if ($this->retained_by_filter($activity)) {
                    $filteredactivities[] = $activity;
                }
            }

            $grp = new \stdClass();
            $grp->id = $course->id;
            $grp->name = $course->name;
            $grp->time = $course->totalmilestones;
            $grp->modulecount = count($filteredactivities);
            $grp->moduletot = count($course->activities);
            $grp->filteredactivities = $filteredactivities;

            $grouping[] = $grp;
        }
        return $grouping;
    }

    /**
     * Explore the modules set to create a list, grouped by expected completion date, classified by month.
     * In other words, the purpose is to have a set of month and in each a subset of modules
     * which has an expected completion date in this month.
     * @param array $courses integrated inside the training, including their own modules
     * @return array a grouped modules list
     */
    private function get_moduleslist_by_month($courses) {
        $classdatefrom = $this->_customdata['orderbyfrom'];
        $classdateinterval = 'M';
        $classdatecount = 12;

        $grouping = array();
        $nodatestring = get_string("module_expected_date_no", "tool_attestoodle");
        $outsidedatestring = get_string("module_expected_date_outside", "tool_attestoodle");

        // Create date classes.
        $d0 = new \DateTime($classdatefrom['year'].'-'.$classdatefrom['month'].'-01'); // Day is ignored.
        $grp = new \stdClass();
        $grouping[$nodatestring] = $grp;
        for ($i = 0; $i < $classdatecount; $i++) {
            $grp = new \stdClass();
            $grp->id = $this->get_month_identifier($d0);
            $grp->name = $this->get_month_identifier($d0);
            $grp->activities = array();
            $grp->filteredactivities = array();
            $grouping[$grp->name] = $grp;
            $d0->add(new \DateInterval('P1'.$classdateinterval));
        }

        // Populate classes with activities.
        foreach ($courses as $course) {
            foreach ($course->activities as $activity) {
                if ($activity->expecteddate == '0') {
                    $dateclass = $nodatestring;
                } else {
                    $d0->setTimestamp($activity->expecteddate);

                    if (array_key_exists($this->get_month_identifier($d0), $grouping)) {
                        $dateclass = $this->get_month_identifier($d0);
                    } else {
                        $dateclass = $outsidedatestring;
                    }
                }
                $grouping[$dateclass]->activities[] = $activity;
                if ($this->retained_by_filter($activity)) {
                    $grouping[$dateclass]->filteredactivities[] = $activity;
                }
            }
        }

        // Add informations to classes.
        foreach ($grouping as $key => $grp) {
            $grp->id = $key;
            $grp->name = $key;
            $time = 0;
            $grp->modulecount = 0;
            if (isset($grp->filteredactivities)) {
                foreach ($grp->filteredactivities as $activity) {
                    if (!is_null($activity->milestone)) {
                        $time += $activity->milestone;
                    }
                }
                $grp->modulecount = count($grp->filteredactivities);
            }
            $grp->moduletot = count($grp->activities);
            $grp->time = parse_minutes_to_hours($time);
        }

        return $grouping;
    }

    /**
     * A function to build a month class identifier
     *
     * @param datetime $date a date to be classified as month
     * @return a string that identify a class of date (a month)
     */
    private function get_month_identifier($date) {
        return $date->format("m Y");
    }

    /**
     * From a grouped modules list generate the display as an organized list of course modules on the page
     *
     * @param moodleform $mform the form to place the list in
     * @param array $grouping an imbricated array of groups containing, each, a set of modules to be displayed
     */
    private function generate_form_list($mform, $grouping) {
        global $CFG;
        $suffix = get_string("training_milestones_form_input_suffix", "tool_attestoodle");
        foreach ($grouping as $grp) {
            $mform->addElement('header', $grp->id,
                "<strong>{$grp->name}</strong> : {$grp->time} ({$grp->modulecount} / {$grp->moduletot})");
            $mform->setExpanded($grp->id, false);

            // For each activity in this course we add a form input element.
            if (isset($grp->filteredactivities)) {
                foreach ($grp->filteredactivities as $activity) {
                    $groupname = "group_" . $activity->name;
                    // The group contains the input, the label and a fixed span (required to have more complex form lines).
                    $group = array();

                    // For time input.
                    $group[] =& $mform->createElement("text", $activity->name, '', array("size" => 5)); // Max 5 char.
                    $mform->setType($activity->name, PARAM_ALPHANUM); // Parsing the value in INT after submit.
                    $mform->setDefault($activity->name, $activity->milestone); // Set default value to the current milestone value.
                    $mform->disabledIf($activity->name, 'edition', 'eq', 0);

                    // Unit.
                    $group[] =& $mform->createElement("static", null, null, "<span>$suffix</span>");

                    // Expected completion date.
                    if ($activity->expecteddate != "0") {
                        $date = date("d M Y", $activity->expecteddate);
                        $group[] =& $mform->createElement("static", $activity->id . '_date', '',
                            get_string("module_expected_date_label", "tool_attestoodle")
                            . '&nbsp;<strong>' . $date . '</strong>');
                    }

                    // Group label.
                    $libelactivity = "<a href='{$CFG->wwwroot}/course/modedit.php?update={$activity->id}'>"
                        . "{$activity->label} ({$activity->type})</a>&nbsp;";
                    if (!empty($activity->availability)) {
                        $libelactivity .= "<span class=\"fa fa-key\" aria-hidden=\"true\"></span> ";
                    }
                    if ($activity->visible == 0) {
                        $libelactivity .= "<span class=\"fa fa-eye-slash\" aria-hidden=\"true\"></span> ";
                    }
                    if ($activity->completion == 0) {
                        $libelactivity .= "<span class=\"fa fa-exclamation-triangle\" aria-hidden=\"true\"></span> ";
                    }
                    $mform->addGroup($group, $groupname, $libelactivity, '&nbsp;', false);
                    $mform->addGroupRule($groupname, array(
                        $activity->name => array(
                            array(null, 'numeric', null, 'client')
                        )
                    ));
                }
            }
        }
    }

    /**
     * add an order by selector to select the way activities are grouped on display
     */
    private function add_orderby() {
        $mform = $this->_form;

        $orderbygroup = array();

        // The 'order by' selector.
        $orderbychoices = array();
        $orderbychoices[] = get_string('orderbycourse', 'tool_attestoodle');
        $orderbychoices[] = get_string('orderbymonth', 'tool_attestoodle');
        $orderbygroup[] =& $mform->createElement('select', 'orderbyselection',
            get_string('orderbylabel', 'tool_attestoodle'), $orderbychoices);
        if (isset($this->_customdata['orderbyselection']) && $this->_customdata['orderbyselection'] > 0) {
            $mform->setDefault('orderbyselection', $this->_customdata['orderbyselection']);
        } else {
            $mform->setDefault('orderbyselection', 0); // Default is 1st choice.
        }

        // The 'order by' start date.
        $orderbygroup[] =& $mform->createElement('date_selector', 'orderbyfrom', get_string('monthfrom', 'tool_attestoodle'));
        $mform->setDefault('orderbyfrom', $this->_customdata['orderbyfrom']);
        $mform->hideIf('orderbyfrom', 'orderbyselection', 'neq', 1); // Only display when ordering on expected completion month.

        // The 'reorder' button.
        $orderbygroup[]=& $mform->createElement('submit', 'orderbybtn',
            get_string('orderbybtn', 'tool_attestoodle'), array('class' => 'send-button'));

        $mform->addGroup($orderbygroup, 'orderbygroup', get_string('orderbylabel', 'tool_attestoodle'), ' ', false);
        $mform->addHelpButton('orderbygroup', 'orderbygroup', 'tool_attestoodle');

    }

    /**
     * add filter bar to the form.
     */
    private function add_filter() {
        $mform = $this->_form;
        $filtergroup = array();
        $modules = db_accessor::get_instance()->get_allmodules();

        $milestonefilterchoice = array();
        $milestonefilterchoice[] = get_string('filtermodulemilestone', 'tool_attestoodle');
        $milestonefilterchoice[] = get_string('filtermodulemilestoneyes', 'tool_attestoodle');
        $milestonefilterchoice[] = get_string('filtermodulemilestoneno', 'tool_attestoodle');
        $filtergroup[] =& $mform->createElement('select', 'milestonemod', '', $milestonefilterchoice, null);
        if (isset($this->_customdata['milestonemod'])) {
            $mform->setDefault('milestonemod', $this->_customdata['milestonemod']);
        }
        // If milestone modification is disabled, disable milestonemod filter.
        $mform->disabledIf('milestonemod', 'edition', 'eq', 0); // Disabled.
        if (isset($this->_customdata['modifallow']) && ($this->_customdata['modifallow'] == false)) {
             $mform->setDefault('milestonemod', 1); // Set to Yes.
        }

        $lstmod = array();
        $lstmod[] = get_string('filtermodulealltype', 'tool_attestoodle');
        $lstmod[] = get_string('filtermoduleactivitytype', 'tool_attestoodle');
        foreach ($modules as $mod) {
            $lstmod[$mod->name] = get_string('modulename', $mod->name);
        }
        $filtergroup[] =& $mform->createElement('select', 'typemod', '', $lstmod, null);
        if (!empty($this->_customdata['type'])) {
            $mform->setDefault('typemod', $this->_customdata['type']);
        }

        $visiblefilterchoice = array();
        $visiblefilterchoice[] = get_string('filtermodulevisible', 'tool_attestoodle');
        $visiblefilterchoice[] = get_string('filtermodulevisibleyes', 'tool_attestoodle');
        $visiblefilterchoice[] = get_string('filtermodulevisibleno', 'tool_attestoodle');
        $filtergroup[] =& $mform->createElement('select', 'visibmod', '', $visiblefilterchoice, null);
        if (isset($this->_customdata['visibmod'])) {
            $mform->setDefault('visibmod', $this->_customdata['visibmod']);
        }

        $restrictfilterchoice = array();
        $restrictfilterchoice[] = get_string('filtermodulerestrict', 'tool_attestoodle');
        $restrictfilterchoice[] = get_string('filtermodulerestrictyes', 'tool_attestoodle');
        $restrictfilterchoice[] = get_string('filtermodulerestrictno', 'tool_attestoodle');
        $filtergroup[] =& $mform->createElement('select', 'restrictmod', '', $restrictfilterchoice, null);
        if (isset($this->_customdata['restrictmod'])) {
            $mform->setDefault('restrictmod', $this->_customdata['restrictmod']);
        }

        $filtergroup[] =& $mform->createElement('static', null, null,
            '&nbsp;&nbsp;'.get_string('filtermodulename', 'tool_attestoodle'));
        $filtergroup[] =& $mform->createElement('text', 'namemod', '', array("size" => 10));
        $mform->setType('namemod', PARAM_TEXT );
        if (!empty($this->_customdata['namemod'])) {
            $mform->setDefault('namemod', $this->_customdata['namemod']);
        }

        $filtergroup[] =& $mform->createElement('submit', 'filterbtn',
            get_string('filtermodulebtn', 'tool_attestoodle'), array('class' => 'send-button'));
        $mform->addGroup($filtergroup, 'filtergroup', get_string('filtergrouplabel', 'tool_attestoodle'), ' ', false);
        $mform->addHelpButton('filtergroup', 'modulefiltergroup', 'tool_attestoodle');
    }

    /**
     * Filter the specified module/activity.
     * @param array $module the module to test against filters.
     * @return bool true if the specified module passes the filter.
     */
    private function retained_by_filter($module) {
        // Test against filters
        // Module type filter.
        $lib = "";
        if (!empty($this->_customdata['type'])) {
            $filtertype = $this->_customdata['type'];
            if ($filtertype > "2") {
                $lib = get_string('modulename', $filtertype);
            }
        } else {
            $filtertype = 0;
        }
        $pass = $this->filtertype($module, $filtertype, $lib);

        // Is milestone filter.
        if (!$this->_customdata['modifallow'] && $module->milestone == 0) { // If the form is read only,
            // then only milestone are displayed.
            $pass = false;
        }
        if (isset($this->_customdata['milestonemod'])) {// Otherwise.
            if ($pass && $this->_customdata['milestonemod'] == 1) { // Yes.
                $pass = $module->milestone;
            }
            if ($pass && $this->_customdata['milestonemod'] == 2) { // No.
                $pass = !$module->milestone;
            }
        }

        // Is visible filter.
        if (isset($this->_customdata['visibmod'])) {
            if ($pass && $this->_customdata['visibmod'] == 1) {
                $pass = $module->visible;
            }
            if ($pass && $this->_customdata['visibmod'] == 2) {
                $pass = !$module->visible;
            }
        }

        // Is access restricted filter.
        $pass = $this->filterrestrict($module, $pass);

        // Module name filter (that has priority).
        $pass = $this->filtername($module, $pass);

        return $pass;
    }

    /**
     * Determines whether the activity crosses the filter type.
     * @param stdClass $activity to test.
     * @param integer $filtertype to cross.
     * @param string $lib the name of module.
     * @return true if activity cross the filter type.
     */
    private function filtertype($activity, $filtertype, $lib) {
        $ret = true;
        if ($filtertype == 1 && $activity->ressource == 1) {
            $ret = false;
        }
        if (!empty($lib) && strcmp($activity->type, $lib) !== 0) {
            $ret = false;
        }
        return $ret;
    }
    /**
     * Determines whether the activity crosses the filter mane.
     * @param stdClass $activity to test.
     * @param bool $pass the actual result of other test.
     * @return true if activity cross the filter name.
     */
    private function filtername($activity, $pass) {
        $ret = $pass;
        if (!empty($this->_customdata['namemod'])) {
            if ($ret && (!stristr($activity->label, $this->_customdata['namemod']))) {
                $ret = false;
            }
        }
        return $ret;
    }

    /**
     * Determines whether the activity crosses the filter availability.
     * @param stdClass $activity to test.
     * @param bool $pass the actual result of other test.
     * @return true if activity cross the filter availability.
     */
    private function filterrestrict($activity, $pass) {
        $ret = $pass;
        if (!isset($this->_customdata['restrictmod'])) {
            return $ret;
        }
        if ($ret && $this->_customdata['restrictmod'] == 0) {
            return $ret;
        }
        if ($ret && $this->_customdata['restrictmod'] == 2 && !empty($activity->availability)) {
            $ret = false;
        }
        if ($ret && $this->_customdata['restrictmod'] == 1 && empty($activity->availability)) {
            $ret = false;
        }
        return $ret;
    }
    /**
     * Build a table of courses and their activities to display.
     * The activities are arranged in their order of appearance.
     * @param stdClass[] $courses list of courses children of categories.
     * @param string $prefix text add to the elements of activities for make Id in the form.
     */
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
                $dataactivity->id = $activity->get_id();
                $dataactivity->name = $prefix  . $activity->get_id();
                $dataactivity->label = $activity->get_name();
                $dataactivity->type = get_string('modulename', $activity->get_type());
                $dataactivity->milestone = $activity->get_milestone();
                $dataactivity->visible = $dataactivity->visible * $activity->get_visible();
                $dataactivity->availability = $dataactivity->availability . $activity->get_availability();
                $dataactivity->completion = $activity->get_completion();
                if (plugin_supports('mod', $activity->get_type(), FEATURE_MOD_ARCHETYPE) != MOD_ARCHETYPE_RESOURCE) {
                    $dataactivity->ressource = 0;
                } else {
                    $dataactivity->ressource = 1;
                }
                $dataactivity->expecteddate = $activity->get_expected_completion_date();
            }

            $reste = array();
            foreach ($activities as $menage) {
                if (isset($menage->name)) {
                    $reste[] = $menage;
                }
            }
            $datacourse->activities = $reste;
            $ret[] = $datacourse;
        }
        return $ret;
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
        return $errors;
    }
}
