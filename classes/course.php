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
 * This file describe a course in Attestoodle.
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_attestoodle;

/**
 * This is the class describing a course in Attestoodle.
 *
 * @copyright  2018 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course {
    /** @var integer Id of the course */
    private $id;

    /** @var string Name of the course */
    private $name;

    /** @var activity[] Activities of the course */
    private $activities;

    /**
     * @var training Training corresponding to the course.
     * @todo Replace by the ID of the training to avoid bijective relation
     */
    private $training;

    /**
     * Constructor of the course class
     *
     * @param integer $id Id of the course
     * @param string $name Name of the course
     */
    public function __construct($id, $name) {
        $this->id = $id;
        $this->name = $name;
        $this->activities = array();
        $this->training = null;
    }

    /**
     * Getter for $id property.
     *
     * @return integer Id of the course
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Getter for $name property.
     *
     * @return string Name of the course
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Getter for $activities property.
     *
     * @return activity[] Activities of the course
     */
    public function get_activities() {
        return $this->activities;
    }

    /**
     * Getter for $training property.
     *
     * @return training Training of the course
     */
    public function get_training() {
        return $this->training;
    }

    /**
     * Method that computes the total amount of milestones declared in
     * the activities of the current course.
     *
     * @return integer The total amount of milestones in minutes
     */
    public function get_total_milestones() {
        $total = 0;
        foreach ($this->activities as $act) {
            if ($act->is_milestone()) {
                $total += $act->get_milestone();
            }
        }
        return $total;
    }

    /**
     * Setter for $name property.
     *
     * @param string $prop Name to set for the course
     */
    public function set_name($prop) {
        $this->name = $prop;
    }

    /**
     * Setter for $activities property.
     *
     * @param activity[] $prop Activities to set for the course
     */
    public function set_activities($prop) {
        $this->activities = $prop;
    }

    /**
     * Add an activity to the course activities list.
     *
     * @param activity $activity Activity to add to the course
     */
    public function add_activity($activity) {
        $activity->set_course($this);
        $this->activities[] = $activity;
    }

    /**
     * Setter for $training property.
     *
     * @param training $prop Training to set for the course
     */
    public function set_training($prop) {
        $this->training = $prop;
    }

    /**
     * Methods that retrieve an activity within the course activities
     * list based on its id.
     *
     * @param integer $idactivity The id to search for
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
