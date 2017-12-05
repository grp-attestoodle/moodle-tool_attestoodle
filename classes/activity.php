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

    /** @var string Name of the activity */
    private $name;

    /** @var string Description of the activity */
    private $description;

    /** @var string Type of the activity */
    private $type;

    /** @var integer Marker time (in minutes) of the activity */
    private $marker;

    /** @var course The course corresponding to the activity */
    private $course;

    /**
     * Constructor of the activity class
     *
     * @param string $id Id of the activity
     * @param string $name Name of the activity
     * @param string $description Description of the activity
     * @param string $type Type of the activity
     * @param integer $marker The marker time of the activity if any
     */
    public function __construct($id, $name, $description, $type, $marker = null) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->type = $type;
        $this->marker = $marker;
        $this->course = null;
    }

    /**
     * Method that checks if the activity contains a marker
     *
     * @return boolean TRUE if the activity contains a marker
     */
    public function has_marker() {
        return isset($this->marker);
    }

    /**
     * Returns the current activity informations as an stdClass object
     *
     * @todo used to display in a moodle html_table object. It has to be
     * made in a specific UI class
     *
     * @return stdClass The stdClass containing the activity informations
     */
    public function get_object_as_stdclass() {
        $obj = new \stdClass();
        $obj->id = $this->id;
        $obj->type = $this->type;
        $obj->name = $this->name;
        $obj->hasmarker = $this->has_marker() ? $this->marker . " minutes" : 'Non';

        return $obj;
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
     * Getter for $marker property
     *
     * @return integer|null Marker value of the activity
     */
    public function get_marker() {
        return $this->marker;
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

    /**
     * Setter for $type property
     *
     * @param string $prop Type to set for the activity
     */
    public function set_type($prop) {
        $this->type = $prop;
    }

    /**
     * Setter for $marker property
     *
     * @param integer $prop Marker value to set for the activity
     */
    public function set_marker($prop) {
        $this->marker = $prop;
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
