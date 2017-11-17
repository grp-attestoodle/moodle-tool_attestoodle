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

defined('MOODLE_INTERNAL') || die;

class training {
    /** @var int Id of the training */
    private $id;

    /** @var string Name of the training */
    private $name;

    /** @var string Description of the training */
    private $description;

    /**
     * Constructor of the training class
     *
     * @param string $name Name of the training
     */
    public function __construct($id, $name, $description) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
    }

    public function get_data_as_table() {
        return [
                $this->id,
                $this->name,
                $this->description
            ];
    }

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
     * @return int Id of the training
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
     * @return string Name of the training
     */
    public function get_description() {
        return $this->description;
    }

    /**
     * Setter for $id property
     *
     * @param int $prop Id to set for the training
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
     * @param string $prop Name to set for the training
     */
    public function set_description($prop) {
        $this->description = $prop;
    }
}
