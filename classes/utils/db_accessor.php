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
 * Allows other classes to access the database and manipulate its data.
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_attestoodle\utils;


use tool_attestoodle\utils\plugins_accessor;
/**
 * This is the singleton class that allows other classes to access the database.
 *
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class db_accessor extends singleton {
    /** @var db_accessor Instance of the db_accessor singleton */
    protected static $instance;

    /** @var $DB Instance of the $DB Moodle variable */
    private static $db;

    /** @var the id of student role.*/
    protected static $studentroleid;

    /**
     * Protected constructor to avoid external instanciation.
     */
    protected function __construct() {
        global $DB;
        parent::__construct();
        self::$db = $DB;
    }

    /**
     * Retrieves one milestone based on moduleID.
     *
     * @param int $id The module ID to search the credited time.
     * @param int $trainingid The training ID of the milestone formation to look for
     * @return \stdClass Standard Moodle DB object
     */
    public function get_milestone_by_module($id, $trainingid) {
        if ($trainingid == 0) {
            $result = self::$db->get_record('tool_attestoodle_milestone', array('moduleid' => $id));
        } else {
            $result = self::$db->get_record('tool_attestoodle_milestone', array('moduleid' => $id, 'trainingid' => $trainingid));
        }
        return $result;
    }
    /**
     * Method that deletes an activity in the attestoodle_milestone table.
     *
     * @param activity $activity The activity to delete in table
     * @param int $trainingid The training ID of the milestone to be deleted
     */
    public function delete_milestone($activity, $trainingid) {
        self::$db->delete_records('tool_attestoodle_milestone',
                    array('moduleid' => $activity->get_id(),
                        'trainingid' => $trainingid));
    }

    /**
     * Method that insert an activity in the attestoodle_milestone table.
     *
     * @param activity $activity The activity to insert in table
     * @param integer $trainingid The training ID of the milestone to be add
     */
    public function insert_milestone($activity, $trainingid) {
        $dataobject = new \stdClass();
        $dataobject->creditedtime = $activity->get_milestone();
        $dataobject->moduleid = $activity->get_id();

        $dataobject->timemodified = \time();
        $dataobject->course = $activity->get_course()->get_id();
        $dataobject->name = $activity->get_name();
        $dataobject->trainingid = $trainingid;

        self::$db->insert_record('tool_attestoodle_milestone', $dataobject);
    }

    /**
     * Method that update an activity in the attestoodle_milestone table.
     *
     * @param activity $activity The activity to update in table
     * @param integer $trainingid The training ID of the milestone to be update
     */
    public function update_milestone($activity, $trainingid) {
        $request = " UPDATE {tool_attestoodle_milestone}
                        SET creditedtime = ?, timemodified = ?
                      WHERE moduleid = ? and trainingid = ?";

        self::$db->execute($request, array($activity->get_milestone(), \time(),
                $activity->get_id(), $trainingid));
    }

    /**
     * Update training in the attestoodle_training in moodle DB.
     * @param \stdClass $training training to update in DB.
     */
    public function updatetraining($training) {
        $dataobject = new \stdClass();
        $dataobject->id = $training->get_id();
        $dataobject->name = $training->get_name();
        $dataobject->startdate = $training->get_start();
        $dataobject->enddate = $training->get_end();
        $dataobject->duration = $training->get_duration();
        $dataobject->nextlaunch = $training->get_nextlaunch();
        $dataobject->nbautolaunch = $training->get_nbautolaunch();

        $dataobject->categoryid = $training->get_categoryid();
        self::$db->update_record('tool_attestoodle_training', $dataobject);
    }

    /**
     * Retrieves one category based on its ID.
     *
     * @param int $id The category ID to search the name for
     * @return \stdClass Standard Moodle DB object
     */
    public function get_category($id) {
        $result = self::$db->get_record('course_categories', array('id' => $id), 'id, name, description, parent');
        return $result;
    }

    /**
     * Retrieves the modules (activities) under a specific course.
     *
     * @param int $id Id of the course to retrieve activities for
     * @return \stdClass Standard Moodle DB object
     */
    public function get_course_modules_by_course($id) {
        $request = "select * from {course_modules} where course = ? and deletioninprogress = 0 and completion > 0";
        $result = self::$db->get_records_sql($request, array($id));

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
                SELECT DISTINCT u.id, u.firstname, u.lastname
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
     * Select the training's learner.
     * @param int $trainingid Id of the training.
     * @return \stdClass Standard Moodle DB object
     */
    public function get_learners_by_training($trainingid) {
        $request = "SELECT DISTINCT u.id, u.firstname, u.lastname
                      FROM {tool_attestoodle_learner} l
                      JOIN {user} u ON u.id = l.userid
                      where trainingid = ?
                      order by u.lastname, u.firstname";
        return self::$db->get_records_sql($request, array($trainingid));
    }

    /**
     * Test if a learner is enrol in the training.
     *
     * @param int $trainingid Id of the training.
     * @return false if some learner are enrol in the training, true in other case.
     */
    public function nolearner($trainingid) {
        return ! self::$db->record_exists('tool_attestoodle_learner', array('trainingid' => $trainingid));
    }

    /**
     * Determines the learners in the training from the learners enrolled in at least one course with milestones.
     *
     * @param int $trainingid Id of the training where enrol learner.
     * @param int $categoryid Id of the category of the training.
     */
    public function insert_learner($trainingid, $categoryid) {
        $req = "select distinct course
                  from {tool_attestoodle_milestone}
                 where trainingid = ?";
        $result = self::$db->get_records_sql($req, array($trainingid));
        $insertab = array();
        foreach ($result as $record) {
            $learnertab = self::get_learners_by_course($record->course);
            foreach ($learnertab as $learner) {
                $insertab[$learner->id] = $learner->id;
            }
        }

        $dataobject = new \stdClass();
        $dataobject->trainingid = $trainingid;
        $dataobject->categoryid = $categoryid;
        $dataobject->selected = 0;
        $dataobject->resultcriteria = 'new';
        foreach ($insertab as $learner) {
            if (! self::$db->record_exists('tool_attestoodle_learner', array('trainingid' => $trainingid, 'userid' => $learner))) {
                $dataobject->userid = $learner;
                self::$db->insert_record('tool_attestoodle_learner', $dataobject);
            }
        }
    }

    /**
     * Load a learner page from the training.
     *
     * @param int $numpage number of the line to be loaded.
     * = $number page x number line per page.
     * @param int $perpage number of record per page.
     * @param int $trainingid Id of the training containing the learners to load.
     * @param string $orderby sql order.
     */
    public function get_page_learner($numpage, $perpage, $trainingid, $orderby) {
        $req = 'select u.id, u.username as username, u.lastname as lastname, u.firstname as firstname,
                        u.email as email, l.selected as selected, l.resultcriteria as resultcriteria
                  from {tool_attestoodle_learner} l
                  join {user} u on u.id = l.userid
                 where trainingid = ? ' . $orderby;
        return self::$db->get_recordset_sql($req, array($trainingid), $numpage, $perpage);
    }

    /**
     * Compute the total number of learner in the training.
     *
     * @param int $trainingid Id of the training containing the learners.
     */
    public function get_count_learner($trainingid) {
        $req = 'select count(u.id)
                  from {tool_attestoodle_learner} l
                  join {user} u on u.id = l.userid
                 where trainingid = ';
        return self::$db->count_records_sql($req . $trainingid);
    }

    /**
     * Compute the total number of learner selected in the training.
     *
     * @param int $trainingid Id of the training containing the learners.
     */
    public function countselected($trainingid) {
        $req = 'select count(u.id)
                  from {tool_attestoodle_learner} l
                  join {user} u on u.id = l.userid
                 where selected = 1
                   and trainingid = ';
        return self::$db->count_records_sql($req . $trainingid);
    }

    /**
     * Select one learner.
     *
     * @param int $userid Id of the user to select.
     * @param int $trainingid Id of the training where we select learner.
     */
    public function check_learner($userid, $trainingid) {
        $request = " UPDATE {tool_attestoodle_learner}
                        SET selected = 1
                      WHERE userid = ? and trainingid = ?";

        self::$db->execute($request, array($userid, $trainingid));
    }

    /**
     * Marks all learners preceding the one received.
     *
     * @param int $trainingid Id of the training where we select learner.
     * @param int $upcheck Id of the learner selected.
     * @param string $order order of display of learners.
     */
    public function checkup_learner($trainingid, $upcheck, $order) {
        $orderby = ' order by ' . $order;
        $req = 'select u.id, u.username as username, u.lastname as lastname, u.firstname as firstname,
                        u.email as email, l.selected as selected, l.resultcriteria as resultcriteria
                  from {tool_attestoodle_learner} l
                  join {user} u on u.id = l.userid
                 where trainingid = ? ' . $orderby;
        $rs = self::$db->get_recordset_sql($req, array($trainingid));
        $ids = array();
        foreach ($rs as $result) {
            if ($result->id == $upcheck) {
                break;
            }
            if ($result->selected > 0) {
                $ids = array();
            } else {
                $ids[] = $result->id;
            }
        }

        $request = " UPDATE {tool_attestoodle_learner}
                        SET selected = 1
                      WHERE userid = ? and trainingid = ?";
        foreach ($ids as $id) {
            self::$db->execute($request, array($id, $trainingid));
        }
    }

    /**
     * Validates the list of learners of the training.
     *
     * @param int $trainingid Id of the training where we select learner.
     */
    public function validate_learner($trainingid) {
        $request = " UPDATE {tool_attestoodle_learner}
                        SET selected = 0, resultcriteria= '', predelete = 1
                      WHERE trainingid = ?";
        self::$db->execute($request, array($trainingid));
    }

    /**
     * Removes learners who are not validated in the training.
     *
     * @param int $trainingid Id of the training.
     */
    public function cancel_learner($trainingid) {
        $req = "delete from {tool_attestoodle_learner} where trainingid=:trainingid and predelete is null";
        self::$db->execute($req, ['trainingid' => $trainingid]);
        self::validate_learner($trainingid);
    }

    /**
     * Unselect one learner.
     *
     * @param int $userid Id of the user to unselect.
     * @param int $trainingid Id of the training where we unselect learner.
     */
    public function uncheck_learner($userid, $trainingid) {
        $request = " UPDATE {tool_attestoodle_learner}
                        SET selected = 0
                      WHERE userid = ? and trainingid = ?";

        self::$db->execute($request, array($userid, $trainingid));
    }

    /**
     * Fill in the criterion of the number of registrations for a training course.
     *
     * @param int $trainingid Id of the training.
     */
    public function fillnbcoursecriteria($trainingid) {
        $tabuser = array();
        $req = 'select l.userid
                  from {tool_attestoodle_learner} l
                 where l.trainingid = ? ';
        $rs = self::$db->get_recordset_sql($req, array($trainingid));
        foreach ($rs as $result) {
            $tabuser[$result->userid] = 0;
        }

        $listcourse = self::get_courses_of_training($trainingid);
        $enrolled = "SELECT ra.userid
                       FROM {role_assignments} ra
                       JOIN {context} cx ON ra.contextid = cx.id
                       JOIN {course} c ON cx.instanceid = c.id AND cx.contextlevel = 50
                      WHERE c.id = ?";
        $nbcourse = 0;
        foreach ($listcourse as $course) {
            $rs = self::$db->get_recordset_sql($enrolled, array($course->id));
            foreach ($rs as $result) {
                if (isset($tabuser[$result->userid])) {
                    $tabuser[$result->userid] = $tabuser[$result->userid] + 1;
                }
            }
            $nbcourse = $nbcourse + 1;
        }

        $req = " UPDATE {tool_attestoodle_learner}
                        SET resultcriteria= ?
                      WHERE userid = ? and trainingid = ?";

        foreach ($tabuser as $id => $value) {
            $crit = $value . "/" . $nbcourse;
            self::$db->execute($req, array($crit, $id, $trainingid));
        }
    }

    /**
     * Fill in the criterion of the number of training courses followed by the learners.
     *
     * @param int $trainingid Id of the training.
     */
    public function fillnbtrainingcriteria($trainingid) {
        $req = "select count(trainingid) as training, userid
                  from {tool_attestoodle_learner}
                 where userid in (SELECT userid FROM {tool_attestoodle_learner} where trainingid = ?)
                 group by userid";
        $rs = self::$db->get_recordset_sql($req, array($trainingid));
        $upd = " UPDATE {tool_attestoodle_learner}
                        SET resultcriteria= ?
                      WHERE userid = ? and trainingid = ?";
        foreach ($rs as $result) {
            $val = $result->training - 1;
            if ($val < 10) {
                $val = "0" . $val;
            }
            self::$db->execute($upd, array($val, $result->userid, $trainingid));
        }
    }

    /**
     * Delete selected learners.
     *
     * @param int $trainingid Id of the training where we want to delete selected learners.
     */
    public function select_off_learner($trainingid) {
        self::$db->delete_records('tool_attestoodle_learner', array('trainingid' => $trainingid, 'selected' => 1));
    }

    /**
     * Delete unselected learners.
     *
     * @param int $trainingid Id of the training where we want to delete unselected learners.
     */
    public function select_on_learner($trainingid) {
        $req = "delete from {tool_attestoodle_learner} where trainingid=:trainingid and selected != 1";
        self::$db->execute($req, ['trainingid' => $trainingid]);
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
        if ($result) {
            return $result->name;
        }
        return;
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
     * Retrieves the attestoodle trainings in moodle DB, associate with a category.
     *
     * @param int $categoryid the identifier of the category associated with the training.
     * @return \stdClass Standard Moodle DB object
     */
    public function get_training_by_category($categoryid) {
        return self::$db->get_records('tool_attestoodle_training', array('categoryid' => $categoryid));
    }

    /**
     * Retrieves the attestoodle trainings in moodle DB, by his id.
     *
     * @param int $id the identifier of the training.
     * @return \stdClass Standard Moodle DB object
     */
    public function get_training_by_id($id) {
        return self::$db->get_record('tool_attestoodle_training', array('id' => $id));
    }

    /**
     * Retrieves one page of attestoodle trainings in moodle DB.
     *
     * @param int $numpage the page number searched.
     * @param int $perpage the number of records per page.
     * @return \stdClass Standard Moodle DB object
     */
    public function get_page_trainings($numpage, $perpage) {
        $req = 'select * from {tool_attestoodle_training} order by name';
        return self::$db->get_recordset_sql($req, null, $numpage, $perpage);
    }

    /**
     * List the trainings of one category.
     *
     * @param int $numpage the page number searched.
     * @param int $perpage the number of records per page.
     * @param int $categoryid The category ID that we want to list the trainings.
     * @return \stdClass Standard Moodle DB object of tool_attestoodle_training table.
     */
    public function get_page_trainings_categ($numpage, $perpage, $categoryid) {
        $req = 'select * from {tool_attestoodle_training} where categoryid = ? order by id desc';
        return self::$db->get_recordset_sql($req, array($categoryid), $numpage, $perpage);
    }

    /**
     * Retrieve count training existing in a category.
     *
     * @param int $categoryid The category ID that we want to count the trainings.
     * @return the number of training for the category.
     */
    public function get_count_training_by_categ($categoryid) {
        return self::$db->count_records_sql("SELECT COUNT(id) from {tool_attestoodle_training} where categoryid = ". $categoryid);
    }

    /**
     * Retrieve count training.
     */
    public function get_training_matchcount() {
        return self::$db->count_records_sql("SELECT COUNT(id) from {tool_attestoodle_training}");
    }

    /**
     * Delete a training in training table based on the category ID.
     *
     * @param int $trainingid The training ID that we want to delete
     */
    public function delete_training_by_id($trainingid) {
        self::$db->delete_records('tool_attestoodle_training', array('id' => $trainingid));
        self::$db->delete_records('tool_attestoodle_train_style', array('trainingid' => $trainingid));
        self::$db->delete_records('tool_attestoodle_user_style', array('trainingid' => $trainingid));
        self::$db->delete_records('tool_attestoodle_milestone', array('trainingid' => $trainingid));
        self::$db->delete_records('tool_attestoodle_learner', array('trainingid' => $trainingid));

        // Delete generate files.
        $sql = "SELECT distinct filename, learnerid
                  FROM {tool_attestoodle_certif_log}
                 where trainingid = :trainingid";
        $result = self::$db->get_records_sql($sql, ['trainingid' => $trainingid]);
        $fs = get_file_storage();
        foreach ($result as $record) {
            $fileinfo = array(
                'contextid' => $record->learnerid,
                'component' => 'tool_attestoodle',
                'filearea' => 'certificates',
                'filepath' => '/',
                'itemid' => 0,
                'filename' => $record->filename
            );
            $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);
            if ($file) {
                $file->delete();
            }
        }

        // Delete log.
        $sql = "DELETE from {tool_attestoodle_launch_log}
                 WHERE id IN (SELECT launchid
                                FROM {tool_attestoodle_certif_log}
                               WHERE trainingid = :trainingid)";
        self::$db->execute($sql, ['trainingid' => $trainingid]);

        $sql = "DELETE from {tool_attestoodle_value_log}
                 WHERE certificateid IN (SELECT id
                                FROM {tool_attestoodle_certif_log}
                               WHERE trainingid = :trainingid)";
        self::$db->execute($sql, ['trainingid' => $trainingid]);

        self::$db->delete_records('tool_attestoodle_certif_log', array('trainingid' => $trainingid));

        // Delete in Sub plugin.
        plugins_accessor::get_instance()->delete_training($trainingid);
    }

    /**
     * Delete a training in training table based on the category ID.
     *
     * @param int $categoryid The category ID that we want to delete
     */
    public function delete_training($categoryid) {
        $training = self::$db->get_record('tool_attestoodle_training', array('categoryid' => $categoryid));
        self::delete_training_by_id($training->id);
        self::$db->delete_records('tool_attestoodle_training', array('categoryid' => $categoryid));
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
        $dataobject->startdate = \time();

        $idtraining = self::$db->insert_record('tool_attestoodle_training', $dataobject);
        $template = self::$db->get_record('tool_attestoodle_template', array('name' => 'Site'));
        $record = new \stdClass();
        $record->trainingid = $idtraining;
        $record->templateid = $template->id;
        $record->grpcriteria1 = 'coursename';
        self::$db->insert_record('tool_attestoodle_train_style', $record);
        return $idtraining;
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

        $launchid = self::$db->insert_record('tool_attestoodle_launch_log', $dataobject, true);
        return $launchid;
    }

    /**
     * Insert a log line in certificate_log table.
     *
     * @param string $filename Name of the file on the server
     * @param string $status Status of the file creation (ERROR, NEW, OVERWRITTEN)
     * @param integer $trainingid The training ID corresponding to the certicate
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

        $certificateid = self::$db->insert_record('tool_attestoodle_certif_log', $dataobject, true);
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
        self::$db->insert_records('tool_attestoodle_value_log', $milestones);
    }

    /**
     * Get module in order of course.
     * @param integer $courseid technical idenitifiant of the course carrying the modules.
     */
    public function get_activiesbysection($courseid) {
        $request = "SELECT sequence, section, visible, availability
                      FROM {course_sections}
                     WHERE course = ?
                       and sequence != ''
                  ORDER BY section";
        $result = self::$db->get_records_sql($request, array($courseid));
        $ret = array();
        foreach ($result as $enreg) {
            $morceaux = explode(",", $enreg->sequence);
            foreach ($morceaux as $morceau) {
                $dataobject = new \stdClass();
                $dataobject->id = $morceau;
                $dataobject->visible = $enreg->visible;
                $dataobject->availability = $enreg->availability;
                $ret[] = $dataobject;
            }
        }
        return $ret;
    }

    /**
     * Retrieves all the plugin mod visible.
     *
     * @return \stdClass Standard Moodle DB object (module).
     */
    public function get_allmodules() {
        $result = self::$db->get_records('modules', array('visible' => 1));
        return $result;
    }

    /**
     * Retrieves the courses under a specific course category (training).
     *
     * @param int $id Id of the course category to retrieve courses for
     * @return \stdClass Standard Moodle DB object
     */
    public function get_courses_childof_category($id) {
        $req = "select * from {course}
                 where enablecompletion = 1
                   and (category in (select id
                                      from {course_categories}
                                     where path like '%/".$id."/%')
                        or category = ".$id.");";

        $result = self::$db->get_records_sql($req, array());
        return $result;
    }

    /**
     * Retrieves the courses under a specific training.
     *
     * @param int $idtraining Id of the training to retrieve courses for
     * @return \stdClass Standard Moodle DB object
     */
    public function get_courses_of_training($idtraining) {
        $req = "select * from {course}
                 where id in (select course
                                      from {tool_attestoodle_milestone}
                                     where trainingid = ". $idtraining .");";

        $result = self::$db->get_records_sql($req, array());
        return $result;
    }

    /**
     * Check milestones exist and provides the total time of training.
     *
     * @param int $trainingid The training ID of the milestone to find.
     * @return int total time of training.
     */
    public function is_milestone_set($trainingid) {
        $req = "select sum(creditedtime) as tot from {tool_attestoodle_milestone} where trainingid = ?";
        return self::$db->get_field_sql($req, array ($trainingid));
    }

    /**
     * Provides orphan milestones, milestones that no longer have associated activities.
     *
     * @param int $trainingid The training ID of the milestone to find.
     * @return \stdClass Standard Moodle DB object (id,creditedtime, name, shortname).
     */
    public function get_milestone_off($trainingid) {
        $req = "select count(*) as nb from {tool_attestoodle_milestone} where trainingid = ?";
        $nb1 = self::$db->get_field_sql($req, array ($trainingid));

        $req = "select count(*) as nb
                  from {tool_attestoodle_milestone} a, {course_modules} c
                 where a.moduleid = c.id
                   and c.deletioninprogress = 0
                   and a.course = c.course
                   and a.trainingid = ?";

        $nb2 = self::$db->get_field_sql($req, array ($trainingid));
        if ($nb1 != $nb2) {
            $req = "select a.id, a.creditedtime,a.name, b.fullname
                      from {tool_attestoodle_milestone} a, {course} b
                     where b.id = a.course and a.trainingid = ?
                       and a.moduleid not in (select id
                                                from {course_modules}
                                                where deletioninprogress = 0)
                    union
                    select a.id, a.creditedtime,a.name, ''
                      from {tool_attestoodle_milestone} a
                     where a.trainingid = ?
                       and a.course not in (select id
                                              from {course})";
            return self::$db->get_records_sql($req, array ($trainingid, $trainingid));
        }
        return array();
    }

    /**
     * Provides new training activities.
     *
     * @param int $trainingid The training ID of the milestone to delete.
     * @return \stdClass Standard Moodle DB object (course's fullname and number
     * of new activity)
     */
    public function get_new_activities($trainingid) {
        $req = "select max(timemodified) as timemodified from {tool_attestoodle_milestone} where trainingid = ?";
        $lastupdate = self::$db->get_field_sql($req, array ($trainingid));
        if (!isset($lastupdate)) {
            return null;
        }
        $req = "select b.fullname as fullname, count(m.id) as nb
                  from {course_modules} m, {course} b
                 where m.added > ?
                   and m.course = b.id
                   and m.completion > 0
                   and m.course in (select course
                                      from {tool_attestoodle_milestone}
                                     where trainingid = ?)
                   group by fullname";
        return self::$db->get_records_sql($req, array ($lastupdate, $trainingid));
    }

    /**
     * Delete orphaned milestones.
     *
     * @param int $trainingid The training ID of the milestone to delete.
     */
    public function delete_milestones_off($trainingid) {
        $milestones = self::get_milestone_off($trainingid);
        foreach ($milestones as $milestone) {
            self::$db->delete_records('tool_attestoodle_milestone', array('id' => $milestone->id));
        }
    }

    /**
     * Updated milestones to be more recent than the activities
     *
     * @param int $trainingid The training ID of the milestone formation to update.
     */
    public function update_milestones($trainingid) {
        $request = " UPDATE {tool_attestoodle_milestone}
                        SET timemodified = ?
                      WHERE trainingid = ?";
        self::$db->execute($request, array(\time(), $trainingid));
    }

    /**
     * Provides a learner's personalized attestation template.
     *
     * @param int $userid The learner's technical identifier.
     * @param int $trainingid The training's technical identifier.
     * @return \stdClass Standard Moodle DB object (all columns of the table)
     */
    public function get_user_template($userid, $trainingid) {
        return self::$db->get_record('tool_attestoodle_user_style',
                array('userid' => $userid, 'trainingid' => $trainingid));
    }

    /**
     * Lists the courses whose names like "%$name%".
     *
     * @param string $name element search in the shortname of course.
     * @return The list of courses whose shortname like %$name%.
     */
    public function find_course($name) {
        $req = "select * from {course} where shortname like '%" . $name . "%'";
        return self::$db->get_records_sql($req, array());
    }
}
