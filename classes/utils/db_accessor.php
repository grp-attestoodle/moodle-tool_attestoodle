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
 * This is the class that allow other classes to access the database and
 * manipulate data
 *
 * @package    block_attestoodle
 * @copyright  2017 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_attestoodle\utils;

defined('MOODLE_INTERNAL') || die;

class db_accessor extends singleton {
    /** @var db_accessor Instance of the db_accessor singleton */
    protected static $instance;

    /** @var $DB Instance of the $DB Moodle variable */
    private static $DB;

    /**
     * Constructor of the db_accessor singleton
     * @global type $DB
     */
    protected function __construct() {
        global $DB;
        parent::__construct();
        self::$DB = $DB;
    }

    /**
     * 
     * @return stdClass
     */
    public function get_all_trainings() {
        $result = self::$DB->get_records('course_categories');
        return $result;
    }
}
