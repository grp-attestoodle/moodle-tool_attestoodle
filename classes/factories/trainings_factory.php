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
 * trainings used by Attestoodle.
 *
 * @package    block_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_attestoodle\factories;

use block_attestoodle\utils\singleton;
use block_attestoodle\factories\categories_factory;
use block_attestoodle\training;

defined('MOODLE_INTERNAL') || die;

class trainings_factory extends singleton {
    /** @var trainings_factory Instance of the trainings_factory singleton */
    protected static $instance;

    /** @var training[] Array containing all the trainings */
    private $trainings;

    /**
     * Constructor method that instanciates the main trainings array.
     */
    protected function __construct() {
        parent::__construct();
        $this->trainings = array();
    }

    /**
     * Create a training based on a specific category, stores it in the
     * main array then return it.
     *
     * @param category $category The category that the training comes from
     * @return training The newly created training
     */
    public function create($category) {
        $trainingtoadd = new training($category);

        $this->trainings[] = $trainingtoadd;

        // Retrieve direct courses.
        $courses = courses_factory::get_instance()->retrieve_courses_by_training($trainingtoadd->get_id());
        // Retrieve courses in sub categories.
        $subcategories = categories_factory::get_instance()->retrieve_sub_categories($trainingtoadd->get_id());
        foreach ($subcategories as $subcat) {
            $subcatcourses = courses_factory::get_instance()->retrieve_courses_by_training($subcat->get_id());
            $courses = array_merge($courses, $subcatcourses);
        }

        /* @todo: adding courses one by one with ->add_course method
        seems stupid */
        foreach ($courses as $course) {
            $trainingtoadd->add_course($course);
        }

        return $trainingtoadd;
    }

    /**
     * Getter of the $trainings property.
     *
     * @return training[] The trainings stored in the factory main array
     */
    public function get_trainings() {
        return $this->trainings;
    }

    /**
     * Method that checks if a training exists in the main array based on an ID.
     *
     * @param integer $id Id to search against
     * @return boolean TRUE if the training exists, FALSE if not
     */
    public function has_training($id) {
        $t = $this->retrieve_training($id);
        return isset($t);
    }

    /**
     * Method that retrieves a training within the main array based on an ID.
     *
     * @param integer $id Id of the training to retrieve
     * @return training|null The training retrieved or NULL if no training has been
     * found with the specified ID
     */
    public function retrieve_training($id) {
        // TODO: problem with the training list cache (no cache).
        categories_factory::get_instance()->create_categories();

        $training = null;
        foreach ($this->trainings as $t) {
            if ($t->get_id() == $id) {
                $training = $t;
                break;
            }
        }
        return $training;
    }

    /**
     * Methods that retrieves an activity based on its id
     *
     * @param integer $idactivity The id to search for
     * @return activity|null The activity retrieved or NULL if no activity has
     * been found with the specified ID.
     */
    public function retrieve_activity($idactivity) {
        $activity = null;
        foreach ($this->trainings as $training) {
            $activity = $training->retrieve_activity($idactivity);
            if (isset($activity)) {
                break;
            }
        }
        return $activity;
    }
}

