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
 * This is the abstract class implementing the Singleton pattern to
 * help factories creating their objects
 *
 * @package    block_attestoodle
 * @copyright  2017 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_attestoodle\utils;

defined('MOODLE_INTERNAL') || die;

abstract class singleton {
    /** @var singleton Instance of the singleton */
    protected static $instance;

    /**
     * This makes __construct method protected to avoid external instanciation
     */
    protected function __construct() {}

    /**
     * Method that returns or generates the singleton instance
     *
     * @return singleton The instance of the singleton
     */
    public static function get_instance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * This overrides __clone method to avoid cloning of the singleton
     */
    public function __clone()
    {
        trigger_error('Cloning is not allowed', E_USER_ERROR);
    }
}
