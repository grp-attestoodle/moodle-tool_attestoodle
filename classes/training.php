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
 * This File describe a training in Attestoodle.
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_attestoodle;

use tool_attestoodle\factories\learners_factory;
use tool_attestoodle\utils\db_accessor;
/**
 * This is the class describing a training in Attestoodle.
 *
 * @copyright  2018 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class training {
    /** @var category Category corresponding to the training */
    private $category;

    /** @var course[] Courses of the training */
    private $courses;

    /** @var learner[] Learners registered in the training */
    private $learners;

    /** @var integer Id of the training. */
    private $id;

    /** @var string The name of the training. */
    private $name;

    /** @var int Start date of the training. */
    private $dtstart;

    /** @var int End date of the training. */
    private $dtend;

    /** @var int Theoretical duration of the training.*/
    private $duration;

    /** @var int Deadline for the next generation of certification. */
    private $nextlaunch;

    /** @var int Number of attestations planned for this training. */
    private $nbautolaunch;

    /**
     * Constructor of the training class.
     *
     * @param category $category Category object that the training depends on
     */
    public function __construct($category) {
        $this->category = $category;
        $this->courses = array();
        $this->learners = array();
    }

    /**
     * Returns the learners registered to at least one course of the training.
     *
     * @return learner[] The array containing the learners of the training
     */
    public function get_learners() {
        if (empty($this->learners)) {
            learners_factory::get_instance()->retrieve_learners_by_training($this);
        }
        return $this->learners;
    }

    /**
     * Test if the training has learner.
     *
     * @return true if the treaning has some learner, false in other case.
     */
    public function has_learners() {
        return ! db_accessor::get_instance()->nolearner($this->id);
    }

    /**
     * Return the count of learners.
     */
    public function count_learners() {
        return db_accessor::get_instance()->get_count_learner($this->id);
    }

    /**
     * Getter for the $start property.
     *
     * @return integer date start of the training.
     */
    public function get_start() {
        return $this->dtstart;
    }

    /**
     * Getter for the $end property.
     *
     * @return integer date end of the training.
     */
    public function get_end() {
        return $this->dtend;
    }

    /**
     * Getter for the $duration property.
     *
     * @return integer duration of the training.
     */
    public function get_duration() {
        return $this->duration;
    }

    /**
     * Shortcut getter for the category $id property.
     *
     * @return integer Id of the training
     */
    public function get_categoryid() {
        return $this->category->get_id();
    }

    /**
     * Shortcut getter for the category $name property.
     *
     * @return string Name of the training
     */
    public function get_name() {
        if (empty($this->name)) {
            return $this->category->get_name();
        }
        return $this->name;
    }

    /**
     * Setter for property name, and save in bdd the value.
     * @param string $prop the new name of the training.
     * @param int $dtstart the new start date of the training.
     * @param int $dtend the new end date of the training.
     * @param int $dtduration the new duration of the training.
     */
    public function change($prop, $dtstart, $dtend, $dtduration) {
        if (empty($prop)) {
            return;
        }
        $update = false;
        if ($this->name != $prop) {
            $this->name = $prop;
            $update = true;
        }
        if ($this->dtstart != $dtstart) {
            $this->dtstart = $dtstart;
            $update = true;
        }
        if ($this->dtend != $dtend) {
            $this->dtend = $dtend;
            $update = true;
        }
        if ($this->duration != $dtduration) {
            $this->duration = $dtduration;
            $update = true;
        }
        if ($update) {
            db_accessor::get_instance()->updatetraining($this);
        }
    }

    /**
     * Shortcut getter for the category hierarchy property.
     *
     * @return string The hierarchy of the training
     */
    public function get_hierarchy() {
        return $this->category->get_hierarchy();
    }

    /**
     * Shortcut getter for the category $description property.
     *
     * @return string Description of the training
     */
    public function get_description() {
        return $this->category->get_description();
    }

    /**
     * Getter for $courses property.
     *
     * @return course[] Courses of the training
     */
    public function get_courses() {
        return $this->courses;
    }

    /**
     * Method that computes the total amount of milestones included in
     * the courses of the current training.
     *
     * @return integer The total amount of milestones in minutes
     */
    public function get_total_milestones() {
        $total = 0;
        foreach ($this->courses as $course) {
            $total += $course->get_total_milestones();
        }
        return $total;
    }

    /**
     * Shortcut getter for the training $id property.
     *
     * @return integer Id of the training
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Setter for $start property.
     *
     * @param int $prop start to set for the training
     */
    public function set_start($prop) {
        $this->dtstart = $prop;
    }

    /**
     * Setter for $end property.
     *
     * @param int $prop end to set for the training
     */
    public function set_end($prop) {
        $this->dtend = $prop;
    }

    /**
     * Setter for $duration property.
     *
     * @param int $prop duration to set for the training
     */
    public function set_duration($prop) {
        $this->duration = $prop;
    }

    /**
     * Shortcut setter for the training $id property.
     *
     * @param string $prop Id to set for the training
     */
    public function set_id($prop) {
        $this->id = $prop;
    }

    /**
     * Shortcut setter for the training $name property.
     *
     * @param string $prop Name to set for the training
     */
    public function set_name($prop) {
        $this->name = $prop;
    }

    /**
     * Setter for $nextlaunch property.
     *
     * @param int $prop nextlaunch to set for the training
     */
    public function set_nextlaunch($prop) {
        $this->nextlaunch = $prop;
    }

    /**
     * Setter for $nbautolaunch property.
     *
     * @param int $prop nbautolaunch to set for the training
     */
    public function set_nbautolaunch($prop) {
        $this->nbautolaunch = $prop;
    }

    /**
     * Getter for $nbautolaunch property.
     *
     * @return int nbautolaunch of the training
     */
    public function get_nbautolaunch() {
        return $this->nbautolaunch;
    }

    /**
     * Getter for $nextlaunch property.
     *
     * @return int nextlaunch of the training
     */
    public function get_nextlaunch() {
        return $this->nextlaunch;
    }

    /**
     * Shortcut setter for the category $description property.
     *
     * @param string $prop Description to set for the training
     */
    public function set_description($prop) {
        $this->category->set_description($prop);
    }

    /**
     * Setter for $courses property.
     *
     * @param course[] $prop Courses to set for the training
     */
    public function set_courses($prop) {
        $this->courses = $prop;
    }

    /**
     * Add a course to the training courses list.
     *
     * @param course $course Course to add to the training
     */
    public function add_course($course) {
        $course->set_training($this);
        $this->courses[] = $course;
    }

    /**
     * Setter for $learners property.
     *
     * @param learner[] $prop Learners to set for the training
     */
    public function set_learners($prop) {
        $this->learners = $prop;
    }

    /**
     * Methods that retrieves an activity based on an id.
     *
     * @param string $idactivity The id to search for
     * @return activity|null The activity retrieved or null if no activity has
     * the specified ID in the training
     */
    public function retrieve_activity($idactivity) {
        $activity = null;
        foreach ($this->courses as $course) {
            $activity = $course->retrieve_activity($idactivity);
            if (isset($activity)) {
                break;
            }
        }
        return $activity;
    }

    /**
     * Method that checks if an activity exists in the training based on
     * a specific ID.
     *
     * @param integer $idactivity The activity id to search for
     * @return boolean True if the activity exists
     */
    public function has_activity($idactivity) {
        $a = $this->retrieve_activity($idactivity);
        return isset($a);
    }
}
