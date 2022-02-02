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
 * Attestoodle version details.
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->release   = 'v1.10.1';           // The current plugin release.
$plugin->version   = 2022020300;         // The current plugin version (Date: YYYYMMDDXX).10.
$plugin->requires  = 2019052000;         // Requires this Moodle version >= 3.7.
$plugin->supported = [37, 311]; // Moodle 3.7.x and later until 3.11.x are supported.
$plugin->maturity  = MATURITY_STABLE;
$plugin->component = 'tool_attestoodle'; // Full name of the plugin (used for diagnostics).
