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
 * This is the class describing a course in Attestoodle
 *
 * @package    block_attestoodle
 * @copyright  2017 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_attestoodle;

defined('MOODLE_INTERNAL') || die;

class course {
    /** @var string Id of the course */
    private $id;

    /** @var string Name of the course */
    private $name;

    /** @var activity[] Activities of the course */
    private $activities;

    /** @var learner[] Learners registered for the course */
    private $learners;

    /** @var training Training corresponding to the course */
    private $training;

    /**
     * Constructor of the course class
     *
     * @param string $id Id of the course
     * @param string $name Name of the course
     */
    public function __construct($id, $name) {
        $this->id = $id;
        $this->name = $name;
        $this->activities = array();
        $this->learners = array();
    }

    /**
     * Returns the current course informations in an array
     *
     * @todo Used to display in a moodle html_table object. It has to be
     * made in a specific UI class
     *
     * @return array The array containing the course informations
     */
    public function get_data_as_table() {
        return [
                $this->id,
                $this->name,
            ];
    }

    /**
     * Returns the current course informations as an stdClass object
     *
     * @todo Used to display in a moodle html_table object. It has to be
     * made in a specific UI class
     *
     * @return stdClass The stdClass containing the course informations
     */
    public function get_object_as_stdclass() {
        $obj = new \stdClass();
        $obj->id = $this->id;
        $obj->name = $this->name;

        return $obj;
    }

    /**
     * Returns the current course activities informations as an array of
     * stdClass object
     *
     * @todo Used to display in a moodle html_table object. It has to be
     * made in a specific UI class
     *
     * @return stdClass The array containing the course activities informations
     */
    public function get_activities_as_stdclass() {
        return array_map(function ($act) {
            return $act->get_object_as_stdclass();
        }, $this->activities);
    }

    /**
     * Returns the current course learners informations as an array of
     * stdClass object
     *
     * @todo Used to display in a moodle html_table object. It has to be
     * made in a specific UI class
     *
     * @return stdClass The array containing the course learners informations
     */
    public function get_learners_as_stdclass() {
        return array_map(function ($l) {
            return $l->get_object_as_stdclass();
        }, $this->learners);
    }

    /**
     * Getter for $id property
     *
     * @return string Id of the course
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Getter for $name property
     *
     * @return string Name of the course
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Getter for $activities property
     *
     * @return activity[] Activities of the course
     */
    public function get_activities() {
        return $this->activities;
    }

    /**
     * Getter for $learners property
     *
     * @return learner[] Learners of the course
     */
    public function get_learners() {
        return $this->learners;
    }

    /**
     * Getter for $training property
     *
     * @return training Training of the course
     */
    public function get_training() {
        return $this->training;
    }

    /**
     * Setter for $id property
     *
     * @param string $prop Id to set for the course
     */
    public function set_id($prop) {
        $this->id = $prop;
    }

    /**
     * Setter for $name property
     *
     * @param string $prop Name to set for the course
     */
    public function set_name($prop) {
        $this->name = $prop;
    }

    /**
     * Setter for $activities property
     *
     * @param activity[] $prop Activities to set for the course
     */
    public function set_activities($prop) {
        $this->activities = $prop;
    }

    /**
     * Add an activity to the course activities list
     *
     * @param activity $activity Activity to add to the course
     */
    public function add_activity($activity) {
        $activity->set_course($this);
        $this->activities[] = $activity;
    }

    /**
     * Setter for $learners property
     *
     * @param learner[] $prop Learners to set for the course
     */
    public function set_learners($prop) {
        $this->learners = $prop;
    }

    /**
     * Add a learner to the course learners list
     *
     * @param learner $learner Learner to add to the course
     */
    public function add_learner($learner) {
        $this->learners[] = $learner;
    }

    /**
     * Setter for $training property
     *
     * @param training $prop Training to set for the course
     */
    public function set_training($prop) {
        $this->training = $prop;
    }

    /**
     * Methods that retrieve an activity within the course activities
     * list based on its id
     *
     * @param string $idactivity The id to search for
     * @return activity|null The activity retrieved if any
     */
    public function retrieve_activity($idactivity) {
        $activity = null;
        foreach ($this->activities as $activitytotest) {
            if ($activitytotest->get_id() == $idactivity) {
                $activity = $activitytotest;
                break;
            }
        }
        return $activity;
    }
}
