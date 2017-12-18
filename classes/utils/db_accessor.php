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
 * This is the class that allow other classes to access the database and
 * manipulate data
 *
 * @package    block_attestoodle
 * @copyright  2017 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_attestoodle\utils;

defined('MOODLE_INTERNAL') || die;

class db_accessor extends singleton {
    /** @var db_accessor Instance of the db_accessor singleton */
    protected static $instance;

    /** @var $DB Instance of the $DB Moodle variable */
    private static $db;

    /**
     * Constructor of the db_accessor singleton
     * @global type $DB
     */
    protected function __construct() {
        global $DB;
        parent::__construct();
        self::$db = $DB;
    }

    /**
     *
     * @return stdClass
     */
    public function get_all_trainings() {
        $result = self::$db->get_records('course_categories');
        return $result;
    }

    /**
     * Retrieve the courses under a specific course category (training)
     *
     * @todo Improve to get a recursive exploration
     *
     * @param integer $id Id of the course category to retrieve courses for
     * @return stdClass Standard Moodle DB object
     */
    public function get_courses_by_training($id) {
        $result = self::$db->get_records('course', array('category' => $id));
        return $result;
    }

    /**
     *
     * @param int $id
     * @return stdClass
     */
    public function get_course_modules_by_course($id) {
        $result = self::$db->get_records('course_modules', array('course' => $id));
        return $result;
    }

    /**
     *
     * @param int $courseid
     * @return stdClass
     */
    public function get_learners_by_course($courseid) {
        $studentroleid = get_config('attestoodle', 'student_role_id');
        $request = "
                SELECT u.id, u.firstname, u.lastname
                FROM mdl_user u
                JOIN mdl_role_assignments ra
                    ON u.id = ra.userid
                JOIN mdl_context cx
                    ON ra.contextid = cx.id
                JOIN mdl_course c
                    ON cx.instanceid = c.id
                    AND cx.contextlevel = 50
                WHERE 1=1
                    AND c.id = ?
                    AND ra.roleid = ?
                ORDER BY u.lastname
            ";
        $result = self::$db->get_records_sql($request, array($courseid, $studentroleid));

        return $result;
    }

    /**
     *
     * @param type $learner
     * @return stdClass
     */
    public function get_activities_validated_by_learner($learner) {
        $result = self::$db->get_records(
                'course_modules_completion',
                array(
                    'userid' => $learner->get_id(),
                    'completionstate' => 1
                ));
        return $result;
    }

    /**
     *
     * @param int $id
     * @return stdClass
     */
    public function get_module_table_name($id) {
        $result = self::$db->get_record('modules', array('id' => $id), "name");
        return $result->name;
    }

    /**
     *
     * @param int $id
     * @return stdClass
     */
    public function get_course_modules_infos($instanceid, $tablename) {
        $result = self::$db->get_record($tablename, array('id' => $instanceid));
        return $result;
    }
}
