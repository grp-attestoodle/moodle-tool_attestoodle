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
 * @copyright  2017 Pole de Ressource Numerique de l'Université du Mans
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
     * Constructor method
     */
    protected function __construct() {
        parent::__construct();
        $this->categories = array();
    }

    public function create_categories() {
        $dbcategories = db_accessor::get_instance()->get_all_categories();

        foreach ($dbcategories as $dbcat) {
            $desc = $dbcat->description;
            $istraining = $this->extract_training($desc);

            $category = $this->retrieve_category($dbcat->id);
            if (!isset($category)) {
                $category = $this->create($dbcat->id);

                if ($istraining) {
                    trainings_factory::get_instance()->create($category);
                }
            }

            $parent = null;
            if ($dbcat->parent > 0) {
                $parent = $this->retrieve_category($dbcat->parent);
                if (!isset($parent)) {
                    $parent = $this->create($dbcat->parent);
                }
            }
            $category->feed($dbcat->name, $desc, $istraining, $parent);
        }
        // Waiting for all the courses being instanciate to retrieve the...
        // ...validated activities for each learner.
        learners_factory::get_instance()->retrieve_all_validated_activities();
    }

    private function extract_training($string) {
        $regexp = "/<span class=(?:(?:\"attestoodle_training\")|(?:\'attestoodle_training\'))><\/span>/iU";
        $istraining = preg_match($regexp, $string);
        return $istraining;
    }

    public function has_category($id) {
        $c = $this->retrieve_category($id);
        return isset($c);
    }

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

    private function create($id) {
        $category = new category($id);
        $this->categories[] = $category;
        return $category;
    }

    public function get_categories() {
        return $this->categories;
    }
}

