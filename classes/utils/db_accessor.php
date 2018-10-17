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
 * This is the singleton class that allows other classes to access the
 * database and manipulate its data.
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_attestoodle\utils;

defined('MOODLE_INTERNAL') || die;

class db_accessor extends singleton {
    /** @var db_accessor Instance of the db_accessor singleton */
    protected static $instance;

    /** @var $DB Instance of the $DB Moodle variable */
    private static $db;

    /** @var the id of student role.*/
    protected static $studentroleid;

    /**
     * Protected constructor to avoid external instanciation.
     *
     * @global type $DB The global moodle $DB object
     */
    protected function __construct() {
        global $DB;
        parent::__construct();
        self::$db = $DB;
    }

    /**
     * Retrieves all the course_categories in moodle DB.
     *
     * @return \stdClass Standard Moodle DB object
     */
    public function get_all_categories() {
        $result = self::$db->get_records('course_categories', null, null, 'id, name, description, parent');
        return $result;
    }

    /**
     * Retrieves all the attestoodle milestones in moodle DB.
     *
     * @return \stdClass Standard Moodle DB object
     */
    public function get_all_milestones() {
        $result = self::$db->get_records('attestoodle_milestone');
        return $result;
    }

    /**
     * Method that deletes an activity in the attestoodle_milestone table.
     *
     * @param activity $activity The activity to delete in table
     */
    public function delete_milestone($activity) {
        self::$db->delete_records(
                'attestoodle_milestone',
                array('moduleid' => $activity->get_id())
        );
    }

    /**
     * Method that insert an activity in the attestoodle_milestone table.
     *
     * @param activity $activity The activity to insert in table
     */
    public function insert_milestone($activity) {
        $dataobject = new \stdClass();
        $dataobject->milestone = $activity->get_milestone();
        $dataobject->moduleid = $activity->get_id();

        self::$db->insert_record('attestoodle_milestone', $dataobject);
    }

    /**
     * Method that update an activity in the attestoodle_milestone table.
     *
     * @param activity $activity The activity to update in table
     */
    public function update_milestone($activity) {
        $request = "
                UPDATE {attestoodle_milestone}
                SET milestone = ?
                WHERE moduleid = ?
            ";
        self::$db->execute(
                $request,
                array(
                        $activity->get_milestone(),
                        $activity->get_id()
                ));
    }

    /**
     * Retrieves all the attestoodle trainings in moodle DB.
     *
     * @return \stdClass Standard Moodle DB object
     */
    public function get_all_trainings() {
        $result = self::$db->get_records('attestoodle_training');
        return $result;
    }

    /**
     * Retrieves the path of the course categories that linked
     * to a training in Attestoodle.
     *
     * @param int[] $categoryids The ids of the categories to retrieve
     * @return \stdClass Standard Moodle DB object
     */
    public function get_categories_paths($categoryids) {
        $result = self::$db->get_records_list(
                'course_categories',
                'id',
                $categoryids,
                null,
                'path');
        return $result;
    }

    /**
     * Retrieves informations of the course categories that linked
     * to a training in Attestoodle.
     *
     * @param int[] $categoryids The ids of the categories to retrieve
     * @return \stdClass Standard Moodle DB object
     */
    public function get_categories_by_id($categoryids) {
        $result = self::$db->get_records_list(
                'course_categories',
                'id',
                $categoryids, // It must be an array !!
                null,
                'id, name, description, parent');
        return $result;
    }

    /**
     * Retrieves the courses under a specific course category (training).
     *
     * @param int $id Id of the course category to retrieve courses for
     * @return \stdClass Standard Moodle DB object
     */
    public function get_courses_by_category($id) {
        $result = self::$db->get_records('course', array('category' => $id, 'enablecompletion' => '1'));
        return $result;
    }

    /**
     * Retrieves the modules (activities) under a specific course.
     *
     * @param int $id Id of the course to retrieve activities for
     * @return \stdClass Standard Moodle DB object
     */
    public function get_course_modules_by_course($id) {
        $result = self::$db->get_records('course_modules', array('course' => $id, 'deletioninprogress' => '0'));
        return $result;
    }

    /**
     * Retrieve the role id for student.
     * if is not set we retrieve the value from database.
     */
    protected function get_studentrole() {
        if (isset(self::$studentroleid)) {
            return self::$studentroleid;
        }

        $result = self::$db->get_record('role', array('shortname' => 'student'), "id");
        self::$studentroleid = $result->id;
        return self::$studentroleid;
    }
    /**
     * Retrieves the learners (student users) registered to a specific course
     *
     * @param int $courseid Id of the course to retrieve learners for
     * @return \stdClass Standard Moodle DB object
     */
    public function get_learners_by_course($courseid) {
        $studentroleid = self::get_studentrole();

        $request = "
                SELECT u.id, u.firstname, u.lastname
                FROM {user} u
                JOIN {role_assignments} ra
                    ON u.id = ra.userid
                JOIN {context} cx
                    ON ra.contextid = cx.id
                JOIN {course} c
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
     * Retrieves the activities IDs validated by a specific learner.
     *
     * @param learner $learner The learner to search activities for
     * @return \stdClass Standard Moodle DB object
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
     * Retrieves the name of a module (activity type) based on its ID.
     *
     * @param int $id The module ID to search the name for
     * @return \stdClass Standard Moodle DB object
     */
    public function get_module_table_name($id) {
        $result = self::$db->get_record('modules', array('id' => $id), "name");
        return $result->name;
    }

    /**
     * Retrieves the details of an activity (module) in its specific DB table.
     *
     * @param int $instanceid Activity of the module in its specific DB table
     * @param string $tablename DB table of the module searched
     * @return \stdClass Standard Moodle DB object
     */
    public function get_course_modules_infos($instanceid, $tablename) {
        $result = self::$db->get_record($tablename, array('id' => $instanceid));
        return $result;
    }

    /**
     * Delete a training in training table based on the category ID.
     *
     * @param int $categoryid The category ID that we want to delete
     */
    public function delete_training($categoryid) {
        $training = self::$db->get_record('attestoodle_training', array('categoryid' => $categoryid));
        self::$db->delete_records('attestoodle_training', array('categoryid' => $categoryid));
        self::$db->delete_records('attestoodle_train_template', array('trainingid' => $training->id));
    }

    /**
     * Insert a training in training table for a specific category ID.
     *
     * @param int $categoryid The category ID that we want to insert
     */
    public function insert_training($categoryid) {
        $dataobject = new \stdClass();
        $dataobject->name = "";
        $dataobject->categoryid = $categoryid;
        $idtraining = self::$db->insert_record('attestoodle_training', $dataobject);
        $template = self::$db->get_record('attestoodle_template', array('name' => 'Site'));
        $record = new \stdClass();
        $record->trainingid = $idtraining;
        $record->templateid = $template->id;
        $record->grpcriteria1 = 'coursename';
        self::$db->insert_record('attestoodle_train_template', $record);
    }

    /**
     * Insert a log line in launch_log table.
     *
     * @param integer $timecreated The current unix time
     * @param string $begindate The begin date of the period requested
     * @param string $enddate The end date of the period requested
     * @param integer $operatorid ID of the user that requested the generation launch
     * @return integer The newly created ID in DB
     */
    public function log_launch($timecreated, $begindate, $enddate, $operatorid) {
        $dataobject = new \stdClass();
        $dataobject->timegenerated = $timecreated;
        $dataobject->begindate = $begindate;
        $dataobject->enddate = $enddate;
        $dataobject->operatorid = $operatorid;

        $launchid = self::$db->insert_record('attestoodle_launch_log', $dataobject, true);
        return $launchid;
    }

    /**
     * Insert a log line in certificate_log table.
     *
     * @param string $filename Name of the file on the server
     * @param string $status Status of the file creation (ERROR, NEW, OVERWRITTEN)
     * @param integer $trainingid The training ID corresponding to the certificate
     * @param integer $learnerid The learner ID corresponding to the certificate
     * @param integer $launchid The launch_log ID corresponding to this certificate log
     * @return integer The newly created certificate_log id
     */
    public function log_certificate($filename, $status, $trainingid, $learnerid, $launchid) {
        $dataobject = new \stdClass();
        $dataobject->filename = $filename;
        $dataobject->status = $status;
        $dataobject->trainingid = $trainingid;
        $dataobject->learnerid = $learnerid;
        $dataobject->launchid = $launchid;

        $certificateid = self::$db->insert_record('attestoodle_certif_log', $dataobject, true);
        return $certificateid;
    }

    /**
     * Insert log lines in value_log table.
     *
     * @param integer $certificatelogid The ID of the certificate_log corresponding
     * @param validated_activity[] $validatedactivities An array of validated activities
     * that has been use for the certificate
     */
    public function log_values($certificatelogid, $validatedactivities) {
        $milestones = array();
        foreach ($validatedactivities as $fva) {
            $act = $fva->get_activity();
            $dataobject = new \stdClass();
            $dataobject->creditedtime = $act->get_milestone();
            $dataobject->certificateid = $certificatelogid;
            $dataobject->moduleid = $act->get_id();

            $milestones[] = $dataobject;
        }
        self::$db->insert_records('attestoodle_value_log', $milestones);
    }
}
