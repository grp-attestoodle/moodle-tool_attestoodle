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

class training_factory extends singleton {
    /** @var training_factory Instance of the training_factory singleton */
    protected static $instance;

    /** @var array Array containing all the trainings */
    private $trainings;

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

        $courses = courses_factory::get_instance()->retrieve_courses_by_training($id);
        foreach ($courses as $course) {
            $trainingtoadd->add_course($course);
        }

        $this->trainings[] = $trainingtoadd;

        return $trainingtoadd;
    }

    public function create_trainings() {
        $dbtrainings = db_accessor::get_instance()->get_all_trainings();
        foreach ($dbtrainings as $training) {
            $this->create($training);
        }
    }

    public function get_trainings() {
        return $this->trainings;
    }

    public function has_training($id) {
        $t = $this->retrieve_training($id);
        return isset($t);
    }

    public function retrieve_training($id) {
        // TODO: problem with the training list cache (no cache)
        $this->create_trainings();

        $training = null;
        foreach ($this->trainings as $t) {
            echo $t->get_id() . "\n";
            if ($t->get_id() == $id) {
                echo "retrieved";
                $training = $t;
                break;
            }
        }
        return $training;
    }
}

