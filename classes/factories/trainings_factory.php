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
 * This File describe factory of the trainings used by Attestoodle.
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_attestoodle\factories;

use tool_attestoodle\utils\singleton;
use tool_attestoodle\utils\db_accessor;
use tool_attestoodle\factories\categories_factory;
use tool_attestoodle\training;

/**
 * Implements the pattern Factory to create the trainings used by Attestoodle.
 *
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class trainings_factory extends singleton {
    /** @var trainings_factory Instance of the trainings_factory singleton */
    protected static $instance;

    /** @var training[] Array containing all the trainings */
    private $trainings;

    /** @var number training per page.*/
    private $perpage = 10;

    /**
     * Constructor method that instanciates the main trainings array.
     */
    protected function __construct() {
        parent::__construct();
        $this->trainings = array();
    }

    /**
     * Method that instanciates one page of trainings used by Attestoodle and
     * stores them in the main array.
     *
     * @param int $numpage the page number searched.
     */
    public function create_trainings($numpage = 0) {
        $this->trainings = array();
        // Must call categories_factory before find trainings.
        $dbtrainings = db_accessor::get_instance()->get_page_trainings($numpage * 10, 10);

        foreach ($dbtrainings as $dbtr) {
            $catid = $dbtr->categoryid;
            $cat = categories_factory::get_instance()->get_category($catid);
            if (!empty($cat)) {
                $trainingtoadd = new training($cat);
                $trainingtoadd->set_id($dbtr->id);
                $trainingtoadd->set_name($dbtr->name);
                $trainingtoadd->set_start($dbtr->startdate);
                $trainingtoadd->set_end($dbtr->enddate);
                $trainingtoadd->set_duration($dbtr->duration);

                $trainingtoadd->set_nextlaunch($dbtr->nextlaunch);
                $trainingtoadd->set_nbautolaunch($dbtr->nbautolaunch);
                $this->trainings[] = $trainingtoadd;
            }
        }
    }

    /**
     * Provides the number of training in a category.
     *
     * @param int $categoryid the identifier of the category where we compute the number of training.
     * return total training in the categorie.
     */
    public function get_count_training_by_categ($categoryid) {
        return db_accessor::get_instance()->get_count_training_by_categ($categoryid);
    }

    /**
     * List the first page of the category's training.
     * All the training are place in array. You must use get_trainings to get the list.
     *
     * @param int $categoryid the identifier of the category where we search training.
     */
    public function create_trainings_4_categ($categoryid) {
        $this->trainings = array();
        // Must call categories_factory before find trainings.
        $dbtrainings = db_accessor::get_instance()->get_page_trainings_categ(0, 10, $categoryid);

        foreach ($dbtrainings as $record) {
            $catid = $record->categoryid;
            $cat = categories_factory::get_instance()->get_category($catid);
            if (!empty($cat)) {
                $trainingtoadd = new training($cat);
                $trainingtoadd->set_id($record->id);
                $trainingtoadd->set_name($record->name);
                $trainingtoadd->set_start($record->startdate);
                $trainingtoadd->set_end($record->enddate);
                $trainingtoadd->set_duration($record->duration);
                $trainingtoadd->set_nextlaunch($record->nextlaunch);
                $trainingtoadd->set_nbautolaunch($record->nbautolaunch);
                $this->trainings[] = $trainingtoadd;
            }
        }
    }

    /**
     * Getter on number training per page.
     */
    public function get_perpage() {
        return $this->perpage;
    }

    /**
     * Provides the total number of training.
     */
    public function get_matchcount() {
        return db_accessor::get_instance()->get_training_matchcount();
    }

    /**
     * Method that instanciates the training associate with a category.
     *
     * @param int $categoryid the identifier of the category associated with the training.
     * @param int $trainingid the identifier of the training.
     */
    public function create_training_by_category($categoryid, $trainingid) {
        if ($trainingid > 0) {
            $dbtr = db_accessor::get_instance()->get_training_by_id($trainingid);
            $cat = categories_factory::get_instance()->get_category($dbtr->categoryid);
            if (!empty($cat)) {
                return $this->create4learner($cat, $dbtr->id, $dbtr->name, $dbtr);
            }
        } else {
            $dbtr = db_accessor::get_instance()->get_training_by_category($categoryid);
            if (!empty($dbtr->categoryid)) {
                $catid = $dbtr->categoryid;
                $cat = categories_factory::get_instance()->get_category($catid);
                if (!empty($cat)) {
                    return $this->create4learner($cat, $dbtr->id, $dbtr->name, $trainingid);
                }
            }
        }
    }

    /**
     * Create a training based on a specific category, stores it in the
     * main array then return it.
     * We limit the training to the course used.
     *
     * @param category $category The category that the training comes from
     * @param integer $id of the training.
     * @param string $name of the training.
     * @param \stdClass $dbtr Standard Moodle DB object training.
     * @return training The newly created training
     */
    private function create4learner($category, $id, $name = '', $dbtr = -1) {
        $trainingtoadd = new training($category);
        $trainingtoadd->set_id($id);
        $trainingtoadd->set_name($name);
        if ($dbtr instanceof \stdClass) {
            $trainingtoadd->set_start($dbtr->startdate);
            $trainingtoadd->set_end($dbtr->enddate);
            $trainingtoadd->set_duration($dbtr->duration);
            $trainingtoadd->set_nextlaunch($dbtr->nextlaunch);
            $trainingtoadd->set_nbautolaunch($dbtr->nbautolaunch);
        }
        $this->trainings[] = $trainingtoadd;

        $courses = courses_factory::get_instance()->retrieve_courses_of_training($id);

        // Add courses to the training.
        foreach ($courses as $course) {
            $trainingtoadd->add_course($course);
        }

        return $trainingtoadd;
    }

    /**
     * Finds the training associated with the category.
     * If only one formation exists, returns its identifier.
     * If more than one formation exists, returns -2.
     * If nothing is found, returns -1
     *
     * @param int $categoryid the identifier of the category where we looking for training.
     */
    public function find_training($categoryid) {
        $dbtr = db_accessor::get_instance()->get_training_by_category($categoryid);
        $nb = 0;
        $lastid = -1;
        foreach ($dbtr as $record) {
            if (isset($record->id)) {
                $lastid = $record->id;
            }
            $nb++;
        }
        if ($nb == 1) {
            return $lastid;
        }
        if ($nb > 1) {
            return -2;
        }
        return -1;
    }

    /**
     * Method that instanciates the training associate with a category.
     *
     * @param int $categoryid the identifier of the category associated with the training.
     * @param int $trainingid the identifier of the training.
     */
    public function create_training_for_managemilestone($categoryid, $trainingid) {
        $dbtr = db_accessor::get_instance()->get_training_by_id($trainingid);
        if (!empty($dbtr->categoryid)) {
            $catid = $dbtr->categoryid;
            $cat = categories_factory::get_instance()->get_category($catid);
            if (!empty($cat)) {
                $this->create($cat, $dbtr->id, $dbtr->name);
                $trainingtoadd = new training($cat);
                $trainingtoadd->set_id($dbtr->id);
                $trainingtoadd->set_name($dbtr->name);
                $trainingtoadd->set_start($dbtr->startdate);
                $trainingtoadd->set_end($dbtr->enddate);
                $trainingtoadd->set_duration($dbtr->duration);
                $this->trainings[] = $trainingtoadd;
                $courses = courses_factory::get_instance()->retrieve_courses_childof_category($categoryid, $dbtr->id);
                // Add courses to the training.
                foreach ($courses as $course) {
                    $trainingtoadd->add_course($course);
                }
            }
            return $trainingtoadd;
        }
    }

    /**
     * Create a training based on a specific category, stores it in the
     * main array then return it.
     *
     * @param category $category The category that the training comes from
     * @param integer $id of the training.
     * @param string $name of the training.
     * @return training The newly created training
     */
    private function create($category, $id, $name = '') {
        $trainingtoadd = new training($category);
        $trainingtoadd->set_id($id);
        $trainingtoadd->set_name($name);
        $trainingtoadd->set_start(\time());

        $this->trainings[] = $trainingtoadd;
        $categoryid = $trainingtoadd->get_categoryid();
        $courses = courses_factory::get_instance()->retrieve_courses_childof_category($categoryid, $id);

        // Add courses to the training.
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
     * Method that returns all the category IDs corresponding to a training.
     *
     * @return integer[] The category IDs in an array
     */
    public function get_training_category_ids() {
        $categoryids = array();
        foreach ($this->trainings as $tr) {
            $categoryids[] = $tr->get_categoryid();
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
     * @param integer $id Id of the category associate at the training to retrieve
     * @return training|null The training retrieved or NULL if no training has been
     * found with the specified ID
     */
    public function retrieve_training($id) {
        $training = null;
        foreach ($this->trainings as $t) {
            if ($t->get_categoryid() == $id) {
                $training = $t;
                break;
            }
        }
        return $training;
    }

    /**
     * Method that retrieves a training by ID.
     *
     * @param integer $id Id of the training to retrieve
     * @return training|null The training retrieved or NULL if no training has been
     * found with the specified ID
     */
    public function retrieve_training_by_id($id) {
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
            if ($t->get_categoryid() == $id) {
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
     * Remove a training in DB by ID.
     *
     * @param int $trainingid The category ID corresponding to the training to remove
     */
    public function remove_training_by_id($trainingid) {
        // Call delete in DB.
        db_accessor::get_instance()->delete_training_by_id($trainingid);
    }

    /**
     * Add a training both in current factory and DB.
     *
     * @param category $category The category corresponding to the training to add
     */
    public function add_training($category) {
        // Call insert in DB.
        $lastid = db_accessor::get_instance()->insert_training($category->get_id());

        // If OK, call $this->create with the category object.
        $lib = $category->get_name() . "_" . strval(\time());
        $training = $this->create($category, $lastid, $lib);
        db_accessor::get_instance()->updatetraining($training);
        return $lastid;
    }
}

