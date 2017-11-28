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
 * This is the class describing a learner in Attestoodle
 *
 * @package    block_attestoodle
 * @copyright  2017 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_attestoodle;

defined('MOODLE_INTERNAL') || die;

class learner {
    /** @var string Id of the learner */
    private $id;

    /** @var string Firstname of the learner */
    private $firstname;

    /** @var string Lastname of the learner */
    private $lastname;

    /**
     * Constructor of the learner class
     *
     * @param string $id Id of the learner
     * @param string $firstname Firstname of the learner
     * @param string $lastname Lastname of the learner
     */
    public function __construct($id, $firstname, $lastname) {
        $this->id = $id;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
    }

    /**
     * Returns the current learner informations as an stdClass object
     * @TODO used to display in a moodle html_table object. It has to be
     * made in a specific UI class
     *
     * @return stdClass The stdClass containing the learner informations
     */
    public function get_object_as_stdclass() {
        $obj = new \stdClass();
        $obj->firstname = $this->firstname;
        $obj->lastname = $this->lastname;

        return $obj;
    }

    /**
     * Getter for $id property
     *
     * @return string Id of the learner
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Getter for $firstname property
     *
     * @return string Firstname of the learner
     */
    public function get_firstname() {
        return $this->firstname;
    }

    /**
     * Getter for $lastname property
     *
     * @return string Lastname of the learner
     */
    public function get_lastname() {
        return $this->lastname;
    }

    /**
     * Setter for $id property
     *
     * @param string $prop Id to set for the learner
     */
    public function set_id($prop) {
        $this->id = $prop;
    }

    /**
     * Setter for $firstname property
     *
     * @param string $prop Firstame to set for the learner
     */
    public function set_firstname($prop) {
        $this->firstname = $prop;
    }

    /**
     * Setter for $lastname property
     *
     * @param string $prop Lastname to set for the learner
     */
    public function set_lastname($prop) {
        $this->lastname = $prop;
    }
}
