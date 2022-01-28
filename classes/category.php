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
 * This is the class describing a category in Attestoodle.
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_attestoodle;

use tool_attestoodle\factories\trainings_factory;
use tool_attestoodle\factories\categories_factory;

/**
 * Category Moodle with the ability of training.
 *
 * Warning : category and training seem similar but they don't.
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class category {
    /** @var integer Id of the category */
    private $id;

    /** @var string Name of the category */
    private $name;

    /** @var string Description of the category */
    private $description;

    /** @var string Define if the category is a training */
    private $istraining;

    /** @var category|null Parent category of the current category */
    private $parent;

    /** @var category id of parent.*/
    private $idparent;

    /**
     * Constructor of the category class. The properties are first set to null,
     * then set to the actual value after a data parsing in categories_factory.
     *
     * @param string $id Id of the category
     */
    public function __construct($id) {
        $this->id = $id;
        $this->name = null;
        $this->description = null;
        $this->istraining = null;
        $this->parent = null;
        $this->idparent = 0;
    }

    /**
     * Set the properties of the category after the data has been parsed
     * by the categories_factory.
     *
     * @param string $name Name of the category
     * @param string $description Description of the category
     * @param category|null $parent The parent category, if any
     * @param integer $idparent The id of the parent's category.
     */
    public function feed($name, $description, $parent, $idparent = 0) {
        $istraining = trainings_factory::get_instance()->has_training($this->id);

        $this->name = $name;
        $this->description = $description;
        $this->istraining = $istraining;
        $this->parent = $parent;
        $this->idparent = $idparent;
    }

    /**
     * Persists the data into the training table after remove/add training boolean
     */
    public function persist_training() {
        $istraining = $this->istraining;

        // No training: delete the record.
        if (!$istraining) {
            // Call training factory 'remove' (that will call delete in DB).
            trainings_factory::get_instance()->remove_training($this->id);
        } else {
            // Is training: insert the record.
            // call training factory 'add' (that will call insert in DB).
            trainings_factory::get_instance()->add_training($this);
        }
    }

    /**
     * Getter for $id property.
     *
     * @return string Id of the category
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Getter for $name property.
     *
     * @return string Name of the category
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Getter for $istraining property.
     *
     * @return boolean Value of the istraining property
     */
    public function is_training() {
        return $this->istraining;
    }

    /**
     * Getter for $description property.
     *
     * @return string Description of the category
     */
    public function get_description() {
        return $this->description;
    }

    /**
     * Getter for $parent property.
     *
     * @return category|null Parent category of the current category, if any
     */
    public function get_parent() {
        if ($this->parent == null && $this->idparent > 0) {
            $this->parent = categories_factory::get_instance()->get_category($this->idparent);
        }
        return $this->parent;
    }

    /**
     * Method that checks if the category has a parent.
     *
     * @return boolean True if the category has a parent
     */
    public function has_parent() {
        $this->get_parent();
        return isset($this->parent);
    }

    /**
     * Returns the parent hierarchy of the category.
     *
     * @return string The hierarchy formatted "[parent N-x] / [parent N-1] / [current category]"
     */
    public function get_hierarchy() {
        $hierarchy = "";
        if ($this->has_parent()) {
            $hierarchy = $this->get_parent()->get_hierarchy() . " / ";
        }
        return $hierarchy . $this->get_name();
    }

    /**
     * Setter for $name property.
     *
     * @param string $prop Name to set for the category
     */
    public function set_name($prop) {
        $this->name = $prop;
    }

    /**
     * Set the $istraining property if the value is different from the current one.
     *
     * @param boolean $prop Either if the category is a training or not
     * @return boolean True if the new value is different from the current one
     */
    public function set_istraining($prop) {
        if ($this->istraining != $prop) {
            $this->istraining = $prop;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Setter for $description property.
     *
     * @param string $prop Description to set for the category
     */
    public function set_description($prop) {
        $this->description = $prop;
    }

    /**
     * Setter for $parent property.
     *
     * @param category $prop Parent category to set for the current category
     */
    public function set_parent($prop) {
        $this->parent = $prop;
    }
}
