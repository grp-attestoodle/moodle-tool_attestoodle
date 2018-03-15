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
 * Page training management (declare milestones)
 *
 * Renderable page that computes infos to give to the template
 */

namespace block_attestoodle\output\renderable;

use block_attestoodle\factories\trainings_factory;

class renderable_training_milestones implements \renderable {
    private $trainingid;
    private $training;

    public function __construct($trainingid) {
        $this->trainingid = $trainingid;
        $this->training = trainings_factory::get_instance()->retrieve_training($trainingid);
    }

    public function get_heading() {
        $heading = "";
        if (!$this->training_exists()) {
            // TODO rename string variable.
            $heading = \get_string('training_details_main_title_error', 'block_attestoodle');
        } else {
            $totaltrainingmilestones = parse_minutes_to_hours($this->training->get_total_milestones());
            // TODO rename string variable.
            $heading = \get_string('training_details_main_title', 'block_attestoodle', $this->training->get_name());
            $heading .= $totaltrainingmilestones;
        }
        return $heading;
    }

    public function training_exists() {
        return isset($this->training);
    }

    public function get_trainingid() {
        return $this->trainingid;
    }
    public function get_training() {
        return $this->training;
    }
}
