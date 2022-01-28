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
 * This file describe an activity validated by a learner in Attestoodle.
 *
 * A validated activity is an activity with a validated DateTime.
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_attestoodle;

/**
 * This is the class describing an activity validated by a learner in Attestoodle.
 *
 * @copyright  2018 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class validated_activity {
    /** @var activity Activity validated by a learner */
    private $activity;

    /** @var \DateTime Date and time when the activity has been validated by a learner */
    private $datetime;

    /**
     * Constructor of the validated_activity class.
     *
     * @param activity $activity The activity validated
     * @param integer $unixtime The unixtime when the activity has been validated
     */
    public function __construct($activity, $unixtime) {
        $this->activity = $activity;
        // The '@' is necessary for unixtime {@link https://bugs.php.net/bug.php?id=40171}.
        $this->datetime = new \DateTime("@$unixtime");
    }

    /**
     * Getter for $activity property.
     *
     * @return activity Activity validated
     */
    public function get_activity() {
        return $this->activity;
    }

    /**
     * Getter for $datetime property.
     *
     * @return \DateTime DateTime when the activity has been validated
     */
    public function get_datetime() {
        return $this->datetime;
    }

    /**
     * Setter for $activity property.
     *
     * @param activity $prop Validated activity to set
     */
    public function set_activity($prop) {
        $this->activity = $prop;
    }

    /**
     * Setter for $datetime property.
     *
     * @param \DateTime $prop DateTime to set for the validated activity
     */
    public function set_datetime($prop) {
        $this->datetime = $prop;
    }
}
