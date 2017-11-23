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
 * This is the class describing a training in Attestoodle
 *
 * @package    block_attestoodle
 * @copyright  2017 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_attestoodle;

use block_attestoodle\factories\courses_factory;

defined('MOODLE_INTERNAL') || die;

class training {
    /** @var string Id of the training */
    private $id;

    /** @var string Name of the training */
    private $name;

    /** @var string Description of the training */
    private $description;

    /** @var array Description of the training */
    private $courses;

    /**
     * Constructor of the training class
     *
     * @param string $id Id of the training
     * @param string $name Name of the training
     * @param string $description Description of the training
     * @param array $courses Courses of the training
     */
    public function __construct($id, $name, $description) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->courses = array();
    }

    /**
     * Returns the current training informations in an array
     * @TODO used to display in a moodle html_table object. It has to be
     * made in a specific UI class
     *
     * @return array The array containing the training informations
     */
    public function get_data_as_table() {
        return [
                $this->id,
                $this->name,
                $this->description
            ];
    }

    /**
     * Returns the current training informations as an stdClass object
     * @TODO used to display in a moodle html_table object. It has to be
     * made in a specific UI class
     *
     * @return stdClass The stdClass containing the training informations
     */
    public function get_object_as_stdclass() {
        $obj = new \stdClass();
        $obj->id = $this->id;
        $obj->name = $this->name;
        $obj->desc = $this->description;

        return $obj;
    }

    /**
     * Getter for $id property
     *
     * @return string Id of the training
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Getter for $name property
     *
     * @return string Name of the training
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Getter for $description property
     *
     * @return string Description of the training
     */
    public function get_description() {
        return $this->description;
    }

    /**
     * Getter for $courses property
     *
     * @return array Courses of the training
     */
    public function get_courses() {
        return $this->courses;
    }

    /**
     * Setter for $id property
     *
     * @param string $prop Id to set for the training
     */
    public function set_id($prop) {
        $this->id = $prop;
    }

    /**
     * Setter for $name property
     *
     * @param string $prop Name to set for the training
     */
    public function set_name($prop) {
        $this->name = $prop;
    }

    /**
     * Setter for $description property
     *
     * @param string $prop Description to set for the training
     */
    public function set_description($prop) {
        $this->description = $prop;
    }

    /**
     * Setter for $courses property
     *
     * @param array $prop Courses to set for the training
     */
    public function set_courses($prop) {
        $this->courses = $prop;
    }

    /**
     * Add a course to the training courses list
     *
     * @param course $course Course to add to the training
     */
    public function add_course($course) {
        $this->courses[] = $course;
    }
}
