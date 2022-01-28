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
 * This File describe activity in Attestoodle.
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_attestoodle;


use tool_attestoodle\utils\db_accessor;
use tool_attestoodle\factories\activities_factory;

/**
 * This is the class describing an activity in Attestoodle.
 *
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activity {
    /** @var integer Id of the activity */
    private $id;

    /** @var integer Id of the activity from its specific module type */
    private $idmodule;

    /** @var string Name of the activity */
    private $name;

    /** @var string Description of the activity */
    private $description;

    /** @var string Type of the activity */
    private $type;

    /** @var integer Milestone time (in minutes) of the activity */
    private $milestone;

    /**
     * @var course The course corresponding to the activity
     * @todo Replace by the ID of the course to avoid bijective relation
     */
    private $course;

    /** @var bool The visibility of the activity.*/
    private $visible;

    /** @var bool The availability of the activity.*/
    private $availability;

    /** @var bool The completion enable of the activity.*/
    private $completion;

    /** @var timestamp the expected completion date */
    private $expectedcompletiondate;

    /**
     * Getter for expectedcompletiondate
     *
     * @return timestamp
     */
    public function get_expected_completion_date() {
        return $this->expectedcompletiondate;
    }

    /**
     * Setter for expectedcompletiondate
     *
     * @param timestamp $date the expected completion date
     */
    public function set_expected_completion_date($date) {
        $this->expectedcompletiondate = $date;
    }

    /**
     * Constructor of the activity class.
     *
     * @param integer $id Id of the activity
     * @param integer $idmodule Id of the module corresponding to activity.
     * @param string $name Name of the activity
     * @param string $description Description of the activity
     * @param string $type Type of the activity
     * @param integer $milestone The milestone value of the activity if any
     */
    public function __construct($id, $idmodule, $name, $description, $type, $milestone = null) {
        $this->id = $id;
        $this->idmodule = $idmodule;
        $this->name = $name;
        $this->description = $description;
        $this->type = $type;
        $this->milestone = $milestone;
        $this->course = null;
    }

    /**
     * Method that checks if the activity is a milestone.
     *
     * @return boolean TRUE if the activity is a milestone
     */
    public function is_milestone() {
        return isset($this->milestone);
    }

    /**
     * Method that stores the milestone information into the database (insert,
     * update or delete in attestoodle_milestone table).
     * @param int $trainingid The training ID containing the activity
     */
    public function persist($trainingid) {
        $dba = db_accessor::get_instance();

        if ($this->is_milestone()) {
            // The activity is a milestone.
            if (activities_factory::get_instance()->is_milestone($this, $trainingid)) {
                // It already was one, so update.
                $dba->update_milestone($this, $trainingid);
            } else {
                // It wasn't already one, so insert.
                $dba->insert_milestone($this, $trainingid);
            }
        } else {
            // Not a milestone anymore, delete.
            $dba->delete_milestone($this, $trainingid);
        }
    }

    /**
     * Getter for $id property.
     *
     * @return string Id of the activity
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Getter for $idmodule property.
     *
     * @return string Id of the activity in its specific module
     */
    public function get_idmodule() {
        return $this->idmodule;
    }

    /**
     * Getter for $name property.
     *
     * @return string Name of the activity
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Getter for $description property.
     *
     * @return string Description of the activity
     */
    public function get_description() {
        return $this->description;
    }

    /**
     * Getter for $type property.
     *
     * @return string Type of the activity
     */
    public function get_type() {
        return $this->type;
    }

    /**
     * Getter for $milestone property.
     *
     * @return integer|null Milestone value of the activity
     */
    public function get_milestone() {
        return $this->milestone;
    }

    /**
     * Getter for $course property.
     *
     * @return course The course of the activity
     */
    public function get_course() {
        return $this->course;
    }

    /**
     * Getter for $visible property.
     *
     * @return visible The visibility of the activity
     */
    public function get_visible() {
        return $this->visible;
    }

    /**
     * Getter for $availability property.
     *
     * @return availability The Availability of the activity
     */
    public function get_availability() {
        return $this->availability;
    }

    /**
     * Getter for $completion property.
     *
     * @return availability The Availability of the activity
     */
    public function get_completion() {
        return $this->completion;
    }

    /**
     * Setter for $completion property.
     *
     * @param int $prop completion to set for the activity
     */
    public function set_completion($prop) {
        $this->completion = $prop;
    }

    /**
     * Setter for $idmodule property.
     *
     * @param string $prop Id module to set for the activity
     */
    public function set_idmodule($prop) {
        $this->idmodule = $prop;
    }

    /**
     * Setter for $name property.
     *
     * @param string $prop Name to set for the activity
     */
    public function set_name($prop) {
        $this->name = $prop;
    }

    /**
     * Setter for $description property.
     *
     * @param string $prop Description to set for the activity
     */
    public function set_description($prop) {
        $this->description = $prop;
    }

    /**
     * Setter for $type property.
     *
     * @param string $prop Type to set for the activity
     */
    public function set_type($prop) {
        $this->type = $prop;
    }

    /**
     * Set the $milestone property if the value is different from the current one.
     *
     * @param integer $prop Milestone value to set for the activity
     * @return boolean True if the new value is different from the current one
     */
    public function set_milestone($prop) {
        if ($this->milestone != $prop) {
            if ($prop == 0) {
                $this->milestone = null;
            } else {
                $this->milestone = $prop;
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Setter for $course property.
     *
     * @param course $prop Course corresponding to the activity
     */
    public function set_course($prop) {
        $this->course = $prop;
    }

    /**
     * Setter for $visible property.
     *
     * @param bool $prop Visibility of the activity
     */
    public function set_visible($prop) {
        $this->visible = $prop;
    }

    /**
     * Setter for $availability property.
     *
     * @param bool $prop Availability of the activity
     */
    public function set_availability($prop) {
        $this->availability = $prop;
    }
}
