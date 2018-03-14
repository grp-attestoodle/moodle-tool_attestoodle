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
 * Renderable page that computes infos to give to the template
 */

namespace block_attestoodle\output\renderable;

use block_attestoodle\factories\learners_factory;
use block_attestoodle\factories\trainings_factory;

class renderable_learner_details implements \renderable {
    private $learnerid;
    private $learner;
    private $trainingid;
    private $training;
    private $begindate;
    private $actualbegindate;
    private $begindateerror;
    private $enddate;
    private $actualenddate;
    private $searchenddate;
    private $enddateerror;

    public function __construct($learnerid, $trainingid, $begindate, $enddate) {
        $this->learnerid = $learnerid;
        $this->learner = learners_factory::get_instance()->retrieve_learner($learnerid);

        $this->trainingid = $trainingid;
        $this->training = trainings_factory::get_instance()->retrieve_training($trainingid);

        $this->begindate = isset($begindate) ? $begindate : (new \DateTime('first day of January ' . date('Y')))->format('Y-m-d');
        $this->enddate = isset($enddate) ? $enddate : (new \DateTime('last day of December ' . date('Y')))->format('Y-m-d');
        // Parsing begin date.
        try {
            $this->actualbegindate = new \DateTime($this->begindate);
            $this->begindateerror = false;
        } catch (\Exception $ex) {
            $this->begindateerror = true;
        }
        // Parsing end date.
        try {
            $this->actualenddate = new \DateTime($this->enddate);
            $this->searchenddate = clone $this->actualenddate;
            $this->searchenddate->modify('+1 day');
            $this->enddateerror = false;
        } catch (Exception $ex) {
            $this->enddateerror = true;
        }
    }

    public function get_heading() {
        $heading = "";
        if (!$this->learner_exists() || !$this->training_exists()) {
            $heading = \get_string('learner_details_main_title_error', 'block_attestoodle');
        } else {
            $heading = \get_string('learner_details_main_title', 'block_attestoodle', $this->learner->get_fullname());
        }
        return $heading;
    }

    public function learner_exists() {
        return isset($this->learner);
    }

    public function training_exists() {
        return isset($this->training);
    }

    public function get_learner() {
        return $this->learner;
    }
    public function get_learnerid() {
        return $this->learnerid;
    }
    public function get_training() {
        return $this->training;
    }
    public function get_trainingid() {
        return $this->trainingid;
    }
    public function get_begindate() {
        return $this->begindate;
    }
    public function get_actualbegindate() {
        return $this->actualbegindate;
    }
    public function get_enddate() {
        return $this->enddate;
    }
    public function get_actualenddate() {
        return $this->actualenddate;
    }
    public function get_searchenddate() {
        return $this->searchenddate;
    }
    public function has_begindateerror() {
        return $this->begindateerror;
    }
    public function has_enddateerror() {
        return $this->enddateerror;
    }
}
