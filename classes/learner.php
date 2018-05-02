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
 * This is the class describing a learner in Attestoodle
 *
 * @package    block_attestoodle
 * @copyright  2017 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_attestoodle;

defined('MOODLE_INTERNAL') || die;

use block_attestoodle\factories\trainings_factory;

class learner {
    /** @var string Id of the learner */
    private $id;

    /** @var string Firstname of the learner */
    private $firstname;

    /** @var string Lastname of the learner */
    private $lastname;

    /** @var validated_activity[] Array of activity validated by the learner */
    private $validatedactivities;

    /**
     * Constructor of the learner class
     *
     * @param string $id Id of the learner
     * @param string $firstname Firstname of the learner
     * @param string $lastname Lastname of the learner
     */
    public function __construct($id, $firstname, $lastname) {
        $this->id = $id;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->validatedactivities = array();
    }

    /**
     * Methods that return the number of activities validated by the learner
     *
     * @return integer The number of activities validated by the learner
     */
    public function get_total_validated_activities() {
        return count($this->validatedactivities);
    }

    /**
     * Methods that return the total amount of markers validated by the learner in
     * an optional specified training
     *
     * @param string $trainingid Id of the training to filter the activities
     * @return integer The total amount of minutes validated by the learner in
     * the specified training or all trainings if not specified
     */
    public function get_total_markers($trainingid = null) {
        $totalminutes = 0;
        foreach ($this->validatedactivities as $validatedactivity) {
            $act = $validatedactivity->get_activity();
            if (!isset($trainingid) ||
                    $act->get_course()->get_training()->get_id() == $trainingid) {
                if ($act->has_marker()) {
                    $totalminutes += $act->get_marker();
                }
            }
        }
        return $totalminutes;
    }

    /**
     * Methods that return the total amount of markers validated by the learner
     * within a training and an optional period of time
     *
     * @param string $trainingid Id of the training to filter the activities
     * @param \DateTime $begindate The begining date to filter the activities
     * @param \DateTime $enddate The ending date to filter the activities
     * @return integer The total amount of minutes validated by the learner in
     * the specified training and the specified period of time
     */
    public function get_total_markers_period($trainingid, $begindate = null, $enddate = null) {
        $totalminutes = 0;
        $validatedactivities = $this->get_validated_activities_with_marker($begindate, $enddate);
        foreach ($validatedactivities as $va) {
            $act = $va->get_activity();
            if ($act->get_course()->get_training()->get_id() == $trainingid) {
                $totalminutes += $act->get_marker();
            }
        }
        return $totalminutes;
    }

    /**
     * Methods that return all the trainings where the learner is registered in.
     *
     * @return training[] The trainings registered by the learner.
     */
    public function retrieve_training_registered() {
        $trainingsregistered = array();

        $alltraining = trainings_factory::get_instance()->get_trainings();
        foreach ($alltraining as $t) {
            $alllearners = $t->get_learners();
            foreach ($alllearners as $l) {
                if ($l->get_id() == $this->id) {
                    $trainingsregistered[$t->get_id()] = $t;
                }
            }
        }

        return $trainingsregistered;
    }

    /**
     * Getter for $id property
     *
     * @return string Id of the learner
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Getter for $firstname property
     *
     * @return string Firstname of the learner
     */
    public function get_firstname() {
        return $this->firstname;
    }

    /**
     * Getter for $lastname property
     *
     * @return string Lastname of the learner
     */
    public function get_lastname() {
        return $this->lastname;
    }

    /**
     * Get the full name of the learner
     *
     * @return string The full name formatted as "FirstName LastName"
     */
    public function get_fullname() {
        return $this->firstname . " " . $this->lastname;
    }

    /**
     * Getter for $validatedactivities property
     *
     * @return validated_activity[] Validated activities of the learner
     */
    public function get_validated_activities() {
        return $this->validatedactivities;
    }

    /**
     * Method that returns the validated activities with marker in an optional
     * period of time
     *
     * @param \DateTime $begindate The begining date to filter the activities
     * @param \DateTime $enddate The ending date to filter the activities
     * @return validated_activity[] Validated activities with marker of the learner
     */
    public function get_validated_activities_with_marker($begindate = null, $enddate = null) {
        return array_filter($this->validatedactivities, function($va) use ($begindate, $enddate) {
            if ($va->get_activity()->has_marker()) {
                if (!$begindate || $va->get_datetime() > $begindate) {
                    if (!$enddate || $va->get_datetime() < $enddate) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        });
    }

    /**
     * Setter for $id property
     *
     * @param string $prop Id to set for the learner
     */
    public function set_id($prop) {
        $this->id = $prop;
    }

    /**
     * Setter for $firstname property
     *
     * @param string $prop Firstame to set for the learner
     */
    public function set_firstname($prop) {
        $this->firstname = $prop;
    }

    /**
     * Setter for $lastname property
     *
     * @param string $prop Lastname to set for the learner
     */
    public function set_lastname($prop) {
        $this->lastname = $prop;
    }

    /**
     * Setter for $validatedactivities property
     *
     * @param validated_activity[] $prop Validated activities to set for the learner
     */
    public function set_validated_activities($prop) {
        $this->validatedactivities = $prop;
    }

    /**
     * Add a validated activity to the validated activities list
     *
     * @param validated_activity $validatedactivity Validated activity to add
     */
    public function add_validated_activity($validatedactivity) {
        $this->validatedactivities[] = $validatedactivity;
    }
}
