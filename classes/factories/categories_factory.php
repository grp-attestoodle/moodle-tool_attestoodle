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
 * This is the class that implements the pattern Factory to create the
 * categories used by Attestoodle
 *
 * @package    block_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_attestoodle\factories;

use block_attestoodle\utils\singleton;
use block_attestoodle\utils\db_accessor;
use block_attestoodle\category;

defined('MOODLE_INTERNAL') || die;

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
     * Method that instanciates all the categories used by Attestoodle and
     * stores them in the main array.
     */
    public function create_categories() {
        $dbcategories = db_accessor::get_instance()->get_all_categories();

        foreach ($dbcategories as $dbcat) {
            $desc = $dbcat->description;
            $istraining = $this->extract_training($desc);

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
            $category->feed($dbcat->name, $desc, $istraining, $parent);
        }
        // Waiting for all the categories to be instanciated to instanciate...
        // ... the trainings and all the courses of a training.
        foreach ($this->categories as $cat) {
            if ($cat->is_training()) {
                trainings_factory::get_instance()->create($cat);
            }
        }

        // Waiting for all the courses being instanciate to retrieve the...
        // ...validated activities for each learner.
        learners_factory::get_instance()->retrieve_all_validated_activities();
    }

    /**
     * Method that checks if a string specifies a training. If the string contains
     * the specific following HTML tag:
     * <span class="attestoodle_training"></span>
     * then it is a training.
     *
     * @todo Use a XMLParser function instead of a RegExp
     *
     * @param string $string The string that may contain a training marker
     * @return boolean True if the string contains the training marker.
     */
    private function extract_training($string) {
        $regexp = "/<span class=(?:(?:\"attestoodle_training\")|(?:\'attestoodle_training\'))><\/span>/iU";
        $istraining = preg_match($regexp, $string);
        return $istraining;
    }

    /**
     * Method that checks if the main categories array contains a specific
     * category based on an id.
     *
     * @param integer $id The id of the category to search for
     * @return boolean True if the main array contains the specified category
     */
    public function has_category($id) {
        $c = $this->retrieve_category($id);
        return isset($c);
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
     * Method that retrieves all the sub categories of a specific category.
     * The method works recursively (meaning that the sub-sub-categories, and
     * bellow, are also returned).
     *
     * @param integer $id The id of the category to search sub-categories for
     * @return category[] An array containing all the sub-categories
     */
    public function retrieve_sub_categories($id) {
        $categories = array();
        foreach ($this->categories as $cat) {
            if ($cat->has_parent() && ($cat->get_parent()->get_id() == $id)) {
                $categories[] = $cat;
                // Recursivity.
                $categories = array_merge($categories, $this->retrieve_sub_categories($cat->get_id()));
                break;
            }
        }
        return $categories;
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

    /**
     * Getter for the $categories property.
     *
     * @return category[] The main categories array of the factory
     */
    public function get_categories() {
        return $this->categories;
    }
}

