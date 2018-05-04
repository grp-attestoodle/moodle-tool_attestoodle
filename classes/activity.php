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
 * This is the class describing an activity in Attestoodle
 *
 * @package    block_attestoodle
 * @copyright  2017 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_attestoodle;

defined('MOODLE_INTERNAL') || die;

class activity {
    /** @var string Id of the activity */
    private $id;

    /** @var string Id of the activity from its specific module type */
    private $idmodule;

    /** @var string Name of the activity */
    private $name;

    /** @var string Description of the activity */
    private $description;

    /** @var string Type of the activity */
    private $type;

    /**
     * @var integer Milestone time (in minutes) of the activity
     * @todo Replace by a class "Milestone" ?
     */
    private $milestone;

    /**
     * @var course The course corresponding to the activity
     * @todo Replace by the ID of the course to avoid bijective relation
     */
    private $course;

    /**
     * Constructor of the activity class
     *
     * @param string $id Id of the activity
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
     * Method that checks if the activity is a milestone
     *
     * @return boolean TRUE if the activity is a milestone
     */
    public function is_milestone() {
        return isset($this->milestone);
    }

    /**
     * Update the current activity data into the database.
     */
    public function persist() {
        global $DB;

        $obj = new \stdClass();
        $obj->id = $this->idmodule;
        $obj->intro = $this->description;

        $DB->update_record($this->type, $obj);
    }

    /**
     * Getter for $id property
     *
     * @return string Id of the activity
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Getter for $idmodule property
     *
     * @return string Id of the activity in its specific module
     */
    public function get_idmodule() {
        return $this->idmodule;
    }

    /**
     * Getter for $name property
     *
     * @return string Name of the activity
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Getter for $description property
     *
     * @return string Description of the activity
     */
    public function get_description() {
        return $this->description;
    }

    /**
     * Getter for $type property
     *
     * @return string Type of the activity
     */
    public function get_type() {
        return $this->type;
    }

    /**
     * Getter for $milestone property
     *
     * @return integer|null Milestone value of the activity
     */
    public function get_milestone() {
        return $this->milestone;
    }

    /**
     * Getter for $course property
     *
     * @return course The course of the activity
     */
    public function get_course() {
        return $this->course;
    }

    /**
     * Setter for $id property
     *
     * @param string $prop Id to set for the activity
     */
    public function set_id($prop) {
        $this->id = $prop;
    }

    /**
     * Setter for $idmodule property
     *
     * @param string $prop Id module to set for the activity
     */
    public function set_idmodule($prop) {
        $this->idmodule = $prop;
    }

    /**
     * Setter for $name property
     *
     * @param string $prop Name to set for the activity
     */
    public function set_name($prop) {
        $this->name = $prop;
    }

    /**
     * Setter for $description property
     *
     * @param string $prop Description to set for the activity
     */
    public function set_description($prop) {
        $this->description = $prop;
    }

    private function update_milestone_in_description() {
        $desc = $this->description;
        $milestone = $this->milestone;

        $regexp = "/<span class=(?:(?:\"tps_jalon\")|(?:\'tps_jalon\'))>(.+)<\/span>/iU";

        if ($milestone == null) {
            $desc = preg_replace($regexp, "", $desc);
        } else {
            if (preg_match($regexp, $desc)) {
                $desc = preg_replace($regexp, "<span class=\"tps_jalon\">{$milestone}</span>", $desc);
            } else {
                $desc = $desc . "<span class=\"tps_jalon\">{$milestone}</span>";
            }
        }

        $this->set_description($desc);
    }

    /**
     * Setter for $type property
     *
     * @param string $prop Type to set for the activity
     */
    public function set_type($prop) {
        $this->type = $prop;
    }

    /**
     * Set the $milestone property if the value is different from the current one
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
            $this->update_milestone_in_description();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Setter for $course property
     *
     * @param course $prop Course corresponding to the activity
     */
    public function set_course($prop) {
        $this->course = $prop;
    }
}
