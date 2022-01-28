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
 * This File describe factory of the activity used by Attestoodle.
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_attestoodle\factories;

use tool_attestoodle\utils\singleton;
use tool_attestoodle\utils\db_accessor;
use tool_attestoodle\activity;

/**
 * Implements the pattern Factory to create the activities used by Attestoodle.
 *
 * @copyright  2018 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activities_factory extends singleton {
    /** @var activities_factory Instance of the activities_factory singleton */
    protected static $instance;

    /**
     * @var array Associative array containing the modules tables names in
     * a key => value form where key = id of the module and value = table name
     */
    private $modulenames;

    /**
     * Constructor method (protected to avoid external instanciation)
     */
    protected function __construct() {
        parent::__construct();
        $this->modulenames = array();
    }

    /**
     * Create an activity from a Moodle request standard object, add it
     * to the array then return it.
     *
     * @param string $activityid Id of the activity in mdl_course_modules table
     * @param stdClass $dbactivity Standard object from the Moodle request
     * @param string $tablename Name of the db table where the activity is stored
     *  in, corresponding to the type of the activity (quiz, folder, file, ...)
     * @param stdClass $coursemodule Contains activity's attributes.
     * @param int $trainingid The training ID containing the activity
     * @return activity The activity created
     */
    private function create($activityid, $dbactivity, $tablename, $coursemodule, $trainingid) {
        $id = $activityid;
        $idmodule = $dbactivity->id;
        $name = $dbactivity->name;
        $desc = isset($dbactivity->intro) ? $dbactivity->intro : null;

        // Retrieve the potential milestone value of the activity.
        $milestone = $this->extract_milestone($id, $trainingid);
        $ret = new activity($id, $idmodule, $name, $desc, $tablename, $milestone);
        $ret->set_visible($coursemodule->visible);
        $ret->set_availability($coursemodule->availability);
        $ret->set_completion($coursemodule->completion);
        $ret->set_expected_completion_date($coursemodule->completionexpected);
        return $ret;
    }

    /**
     * Method that retrieves the milestone value of a specific module.
     *
     * @param integer $moduleid The module id that may have a milestone time value
     * @param int $trainingid The milestone formation identifier
     * @return integer|null The milestone time within the string or null if
     * no milestone time has been found
     */
    private function extract_milestone($moduleid, $trainingid) {
        $milestone = null;
        $rec = db_accessor::get_instance()->get_milestone_by_module($moduleid, $trainingid);

        if (!empty($rec)) {
            $milestone = (integer)$rec->creditedtime;
        }

        return $milestone;
    }

    /**
     * Method that retrieves a module table name based on the id of the module.
     *
     * @param string $moduleid Id of the module to search for
     * @return string Name of the table corresponding to the module id
     */
    private function get_module_table_name($moduleid) {
        if (!isset($this->modulenames[$moduleid])) {
            $modulename = db_accessor::get_instance()->get_module_table_name($moduleid);
            $this->modulenames[$moduleid] = $modulename;
        }

        return $this->modulenames[$moduleid];
    }

    /**
     * Method that retrieves all activities in a course.
     *
     * @param string $id Id of the course to search activities for
     * @param int $trainingid The training ID containing the courses
     * @return activity[] Array containing all the activity objects of the course
     */
    public function retrieve_activities_by_course($id, $trainingid) {
        $dbcoursemodules = db_accessor::get_instance()->get_course_modules_by_course($id);
        $activities = array();
        foreach ($dbcoursemodules as $coursemodule) {
            $activityid = $coursemodule->id;

            $moduleid = $coursemodule->module;
            $tablename = $this->get_module_table_name($moduleid);
            if ($tablename) {
                $instanceid = $coursemodule->instance;
                $coursemodulesinfos = db_accessor::get_instance()->get_course_modules_infos($instanceid, $tablename);

                $activities[] = $this->create($activityid, $coursemodulesinfos, $tablename, $coursemodule, $trainingid);
            }
        }
        return $activities;
    }

    /**
     * Method that checks if an activity is a milestone in the $milestone array.
     *
     * @param activity $activity The activity to check against
     * @param int $trainingid The training ID where we search the activity
     */
    public function is_milestone($activity, $trainingid) {
        $rec = db_accessor::get_instance()->get_milestone_by_module($activity->get_id(), $trainingid);
        return !empty($rec);
    }
}
