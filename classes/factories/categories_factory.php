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
 * This File describe factory of the categorie used by Attestoodle.
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_attestoodle\factories;

use tool_attestoodle\utils\singleton;
use tool_attestoodle\utils\db_accessor;
use tool_attestoodle\category;

defined('MOODLE_INTERNAL') || die;
/**
 * Implements the pattern Factory to create the categories used by Attestoodle.
 *
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class categories_factory extends singleton {
    /** @var categories_factory Instance of the categories_factory singleton */
    protected static $instance;

    /** @var category[] Array containing all the categories */
    private $categories;

    /**
     * Constructor method that instanciates the main categories array.
     */
    protected function __construct() {
        parent::__construct();
        $this->categories = array();
    }

    /**
     * Create a category instance.
     * @param stdClass $enreg structure width id, name, description, parent.
     */
    private function create_category($enreg) {
        $desc = $enreg->description;
        $category = $this->create($enreg->id);

        $parent = null;
        if ($enreg->parent > 0) {
            $parent = $this->retrieve_category($enreg->parent);
            if (!isset($parent)) {
                $parent = $this->create($enreg->parent);
            }
        }
        $category->feed($enreg->name, $desc, $parent);
    }

    /**
     * Method that instanciates the categories corresponding to certain IDs.
     *
     * @param int[] $ids The category IDs we want to create
     */
    public function create_categories_by_ids($ids) {
        $dbcategories = db_accessor::get_instance()->get_categories_by_id($ids);

        foreach ($dbcategories as $dbcat) {
            $desc = $dbcat->description;

            $category = $this->retrieve_category($dbcat->id);
            // Create the -almost- void category object if it doesn't exist yet.
            if (!isset($category)) {
                $category = $this->create($dbcat->id);
            }

            $parent = null;
            // Computes the potential parent category.
            if ($dbcat->parent > 0) {
                // Try to retrieve the category object based on the id.
                $parent = $this->retrieve_category($dbcat->parent);
                if (!isset($parent)) {
                    // Create the -almost- void parent category object if needed.
                    $parent = $this->create($dbcat->parent);
                }
            }
            // Set the properties of the -almost- void category object.
            $category->feed($dbcat->name, $desc, $parent);
        }
    }


    /**
     * Method that retrieves a specific category in the main categories array
     * based on its id.
     *
     * @param integer $id The id of the category to search for
     * @return category|null The category retrieved or null if there is no
     * category with the specified in the main array
     */
    public function retrieve_category($id) {
        $category = null;
        foreach ($this->categories as $cat) {
            if ($cat->get_id() == $id) {
                $category = $cat;
                break;
            }
        }
        return $category;
    }

    /**
     * Method that creates a new category object with a given id.
     *
     * @param integer $id The id of the category to instanciate
     * @return category The newly created -almost- void category object
     */
    private function create($id) {
        $category = new category($id);
        $this->categories[] = $category;
        return $category;
    }

}

