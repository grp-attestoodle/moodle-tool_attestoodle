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
 * trainings used by Attestoodle
 *
 * @package    block_attestoodle
 * @copyright  2017 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_attestoodle\factories;

use block_attestoodle\utils\singleton;
use block_attestoodle\utils\db_accessor;
use block_attestoodle\training;

defined('MOODLE_INTERNAL') || die;

class trainings_factory extends singleton {
    /** @var trainings_factory Instance of the trainings_factory singleton */
    protected static $instance;

    /** @var training[] Array containing all the trainings */
    private $trainings;

    /**
     * Constructor method
     */
    protected function __construct() {
        parent::__construct();
        $this->trainings = array();
    }

    /**
     * Create a training from a Moodle request standard object, add it
     * to the array then return it
     *
     * @param stdClass $dbtraining Standard object from the Moodle request
     * @return training The training added in the array
     */
    private function create($dbtraining) {
        $id = $dbtraining->id;
        $name = $dbtraining->name;
        $desc = $dbtraining->description;

        $trainingtoadd = new training($id, $name, $desc);

        $this->trainings[] = $trainingtoadd;

        $courses = courses_factory::get_instance()->retrieve_courses_by_training($id);
        /* @todo: adding courses one by one with ->add_course method
        seems stupid */
        foreach ($courses as $course) {
            $trainingtoadd->add_course($course);
        }

        return $trainingtoadd;
    }

    /**
     * Create all the trainings within the database and store them in the
     * trainings list
     */
    public function create_trainings() {
        $dbtrainings = db_accessor::get_instance()->get_all_trainings();
        foreach ($dbtrainings as $training) {
            $this->create($training);
        }
        learners_factory::get_instance()->retrieve_all_validated_activities();
    }

    /**
     * Getter of the $trainings property
     *
     * @return training[] The trainings stored in the factory
     */
    public function get_trainings() {
        return $this->trainings;
    }

    /**
     * Method that checks if a training exists based on its ID
     *
     * @param string $id Id to search against
     * @return boolean TRUE if the training exists, FALSE if not
     */
    public function has_training($id) {
        $t = $this->retrieve_training($id);
        return isset($t);
    }

    /**
     * Method that retrieve a training within the list based on an ID
     *
     * @param string $id Id of the training to retrieve
     * @return training|null The training retrieved or NULL if no training has been
     * found
     */
    public function retrieve_training($id) {
        // TODO: problem with the training list cache (no cache).
        $this->trainings = array();
        $this->create_trainings();

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
     * Methods that retrieve an activity based on its id
     *
     * @param string $idactivity The id to search for
     * @return activity|null The activity retrieved if any
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

