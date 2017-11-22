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
    /** @var courses_factory Instance of the training_factory singleton */
    protected static $instance;

    private $modulenames;

    protected function __construct() {
        parent::__construct();
        $this->modulenames = array();
    }

    /**
     * Create a course from a Moodle request standard object, add it
     * to the array then return it
     *
     * @param stdClass $dbactivity Standard object from the Moodle request
     * @return activity The activity created
     */
    private function create($dbactivity) {
        $id = $dbactivity->id;
        $name = $dbactivity->name;
        $desc = $dbactivity->intro;

        return new activity($id, $name, $desc);
    }

    private function get_module_table_name($moduleid) {
        if (!isset($this->modulenames[$moduleid])) {
            $modulename = db_accessor::get_instance()->get_module_table_name($moduleid);
            $this->modulenames[$moduleid] = $modulename;
        }

        return $this->modulenames[$moduleid];
    }

    public function retrieve_activities_by_course($id) {
        $dbcoursemodules = db_accessor::get_instance()->get_course_modules_by_course($id);
        $activities = array();
        foreach ($dbcoursemodules as $coursemodule) {
            $moduleid = $coursemodule->module;
            $instanceid = $coursemodule->instance;

            $tablename = $this->get_module_table_name($moduleid);

            $coursemodulesinfos = db_accessor::get_instance()->get_course_modules_infos($instanceid, $tablename);

            $activities[] = $this->create($coursemodulesinfos);
        }
        return $activities;
    }
}
