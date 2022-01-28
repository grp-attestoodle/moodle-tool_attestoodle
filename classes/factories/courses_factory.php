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
 * This File describe factory to create the courses used by Attestoodle.
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_attestoodle\factories;

use tool_attestoodle\utils\singleton;
use tool_attestoodle\utils\db_accessor;
use tool_attestoodle\course;

/**
 * Implements the pattern Factory to create the courses used by Attestoodle.
 *
 * @copyright  2018 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class courses_factory extends singleton {
    /** @var courses_factory Instance of the courses_factory singleton */
    protected static $instance;

    /**
     * Create a course from a Moodle request standard object, add it
     * to the array then return it
     *
     * @param stdClass $dbcourse Standard object from the Moodle request
     * @param int $trainingid The training ID whose course is being searched
     * @return course The course newly created
     */
    public function create($dbcourse, $trainingid) {
        $id = $dbcourse->id;
        $name = $dbcourse->fullname;

        $coursetoadd = new course($id, $name);

        // Retrieve the activities of the course being created.
        $activities = activities_factory::get_instance()->retrieve_activities_by_course($id, $trainingid);

        foreach ($activities as $activity) {
            $coursetoadd->add_activity($activity);
        }

        return $coursetoadd;
    }

    /**
     * Function that retrieves the courses corresponding to a specific category
     *
     * @param integer $id Id of the category to search courses for
     * @param int $trainingid The training ID whose course is being searched
     * @return course[] Array containing the courses objects
     */
    public function retrieve_courses_childof_category($id, $trainingid = 0) {
        $dbcourses = db_accessor::get_instance()->get_courses_childof_category($id);

        $courses = array();
        foreach ($dbcourses as $course) {
            $courses[$course->id] = $this->create($course, $trainingid);
        }
        if ($trainingid != 0) {
            $dbcourses = db_accessor::get_instance()->get_courses_of_training($trainingid);
            foreach ($dbcourses as $course) {
                if (!isset($courses[$course->id])) {
                    $courses[$course->id] = $this->create($course, $trainingid);
                }
            }
        }
        return $courses;
    }

    /**
     * Function that retrieves the courses corresponding to a specific training
     *
     * @param integer $trainingid Id of the training to search courses for
     * @return course[] Array containing the courses objects
     */
    public function retrieve_courses_of_training($trainingid) {
        $dbcourses = db_accessor::get_instance()->get_courses_of_training($trainingid);
        $courses = array();
        foreach ($dbcourses as $course) {
            $courses[] = $this->create($course, $trainingid);
        }
        return $courses;
    }
}
