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
use block_attestoodle\utils\db_accessor;
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
     * Method that instanciates all the trainings used by Attestoodle and
     * stores them in the main array.
     */
    public function create_trainings() {
        $dbtrainings = db_accessor::get_instance()->get_all_trainings();
        $categoryids = array_column($dbtrainings, 'categoryid');

        $paths = db_accessor::get_instance()->get_categories_paths($categoryids);

        foreach ($paths as $path) {
            $matches = array();
            if (preg_match("/\/(\d+)/", $path->path, $matches)) {
                if (!in_array($matches[1], $categoryids)) {
                    $categoryids[] = $matches[1];
                }
            }
        }
        categories_factory::get_instance()->create_categories_by_ids($categoryids);

        foreach ($dbtrainings as $dbtr) {
            $catid = $dbtr->categoryid;
            $cat = categories_factory::get_instance()->retrieve_category($catid);
            $this->create($cat);
        }
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

        // Add courses to the training.
        foreach ($courses as $course) {
            $trainingtoadd->add_course($course);
        }

        // Waiting for all the courses being instanciate to retrieve the...
        // ...validated activities for each learner.
        learners_factory::get_instance()->retrieve_all_validated_activities();

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
     * Method that returns all the category IDs corresponding to a training.
     *
     * @return integer[] The category IDs in an array
     */
    public function get_training_category_ids() {
        $categoryids = array();
        foreach ($this->trainings as $tr) {
            $categoryids[] = $tr->get_id();
        }
        return $categoryids;
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
     * @param integer $id Id of the training (category) to retrieve
     * @return training|null The training retrieved or NULL if no training has been
     * found with the specified ID
     */
    public function retrieve_training($id) {
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
     * Method that retrieves the index of a training in trainings global array
     * based on its ID
     *
     * @param integer $id Id of the training (category) to retrieve
     * @return int The index retrieved or -1 if no training has been found with
     * the specified ID
     */
    public function retrieve_training_index($id) {
        $index = -1;
        foreach ($this->trainings as $i => $t) {
            if ($t->get_id() == $id) {
                $index = $i;
                break;
            }
        }
        return $index;
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

    /**
     * Remove a training both in current factory and DB.
     *
     * @param int $categoryid The category ID corresponding to the training to remove
     */
    public function remove_training($categoryid) {
        // Call delete in DB.
        db_accessor::get_instance()->delete_training($categoryid);

        // If OK, unset the training in $this->trainings.
        $index = $this->retrieve_training_index($categoryid);
        if ($index >= 0) {
            array_splice($this->trainings, $index, 1);
            return true;
        } else {
            return false;
        }

    }

    /**
     * Add a training both in current factory and DB.
     *
     * @param category $category The category corresponding to the training to add
     */
    public function add_training($category) {
        // Call insert in DB.
        db_accessor::get_instance()->insert_training($category->get_id());

        // If OK, call $this->create with the category object.
        $this->create($category);

        return true;
    }
}

