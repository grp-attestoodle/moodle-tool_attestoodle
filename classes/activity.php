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

    /** @var string Name of the activity */
    private $description;

    /** @var int Jalon of the activity */
    private $marker;

    /**
     * Constructor of the activity class
     *
     * @param string $id Id of the course
     * @param string $name Name of the course
     * @param string $description Description of the course
     */
    public function __construct($id, $name, $description, $marker = null) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->marker = $marker;
    }

    public function has_marker() {
        return isset($this->marker);
    }

    public function get_object_as_stdclass() {
        $obj = new \stdClass();
        // $obj->id = $this->id;
        $obj->name = $this->name;
        $obj->description = $this->description;
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
     * Getter for $marker property
     *
     * @return int Marker value of the activity
     */
    public function get_marker() {
        return $this->marker;
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
     * Setter for $marker property
     *
     * @param int $prop Marker value to set for the activity
     */
    public function set_marker($prop) {
        $this->marker = $prop;
    }
}
