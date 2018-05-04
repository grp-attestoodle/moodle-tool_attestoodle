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
 * Attestoodle plug-in settings.
 *
 * @package    block_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$settings->add(new admin_setting_heading('attestoodle_settings_header',
                                         get_string('settings_header', 'block_attestoodle'),
                                         get_string('settings_description', 'block_attestoodle')));

// Setting for the ID in mdl_role table corresponding to the student role.
$settings->add(new admin_setting_configtext('attestoodle/student_role_id',
                                                get_string('settings_student_role_label', 'block_attestoodle'),
                                                get_string('settings_student_role_helper', 'block_attestoodle'),
                                                5,
                                                PARAM_INT));
