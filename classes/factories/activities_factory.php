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
 * This is the class that implements the pattern Factory to create the
 * activities used by Attestoodle
 *
 * @package    block_attestoodle
 * @copyright  2017 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_attestoodle\factories;

use block_attestoodle\utils\singleton;
use block_attestoodle\utils\db_accessor;
use block_attestoodle\activity;

defined('MOODLE_INTERNAL') || die;

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
     * Create a course from a Moodle request standard object, add it
     * to the array then return it
     *
     * @param string $activityid Id of the activity in mdl_course_modules table
     * @param stdClass $dbactivity Standard object from the Moodle request
     * @param string $tablename Name of the db table where the activity is stored
     *  in, corresponding to the type of the activity
     * @return activity The activity created
     */
    private function create($activityid, $dbactivity, $tablename) {
        $id = $activityid;
        $idmodule = $dbactivity->id;
        $name = $dbactivity->name;
        $desc = $dbactivity->intro;

        $marker = null;
        if (isset($desc)) {
            $marker = $this->extractmarker($desc);
        }

        return new activity($id, $idmodule, $name, $desc, $tablename, $marker);
    }

    /**
     * Method that extract the marker time value in a string
     *
     * @todo Use a XMLParser function instead of a RegExp
     *
     * @param string $string The string that may contain a marker time value
     * @return integer|null The marker time within the string, null if no marker time has
     * been found
     */
    private function extractmarker($string) {
        $marker = null;
        $matches = array();
        $regexp = "/<span class=(?:(?:\"tps_jalon\")|(?:\'tps_jalon\'))>(.+)<\/span>/iU";
        if (preg_match($regexp, $string, $matches)) {
            $marker = (integer)$matches[1];
        }
        return $marker;
    }

    /**
     * Method that retrieve a module table name based on the id of the module
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
     * Method that retrieve all activities linked to a course
     *
     * @param string $id Id of the course to search activities for
     * @return activity[] Array containing all the activity objects of the course
     */
    public function retrieve_activities_by_course($id) {
        $dbcoursemodules = db_accessor::get_instance()->get_course_modules_by_course($id);
        $activities = array();
        foreach ($dbcoursemodules as $coursemodule) {
            $activityid = $coursemodule->id;

            $moduleid = $coursemodule->module;
            $tablename = $this->get_module_table_name($moduleid);

            $instanceid = $coursemodule->instance;
            $coursemodulesinfos = db_accessor::get_instance()->get_course_modules_infos($instanceid, $tablename);

            $activities[] = $this->create($activityid, $coursemodulesinfos, $tablename);
        }
        return $activities;
    }
}
