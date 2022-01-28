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

/**
 * Implements the pattern Factory to create the categories used by Attestoodle.
 *
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class categories_factory extends singleton {
    /** @var categories_factory Instance of the categories_factory singleton */
    protected static $instance;

    /**
     * Method that instanciate the category corresponding to certain ID.
     *
     * @param int $id The category ID we want.
     */
    public function get_category($id) {
        $dbcategory = db_accessor::get_instance()->get_category($id);
        $category = new category($dbcategory->id);
        $category->feed($dbcategory->name, $dbcategory->description, null, $dbcategory->parent);
        return $category;
    }
}

