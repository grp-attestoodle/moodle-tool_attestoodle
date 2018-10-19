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
 * File that allow to manipulate data when an upgrade of the plug-in has been
 * detected. The main method is automagically called by Moodle.
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

function xmldb_tool_attestoodle_upgrade($oldversion) {
    // Update this function in need of DB upgrade while installing new version.
    global $DB;
    $dbman = $DB->get_manager();
    // Add columns grpcriteria1 and grpcriteria2 to attestoodle_train_template.
    if ($oldversion < 2018101001) {
        $table = new xmldb_table('attestoodle_train_template');
        $field = new xmldb_field('grpcriteria1', XMLDB_TYPE_CHAR, '35', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('grpcriteria2', XMLDB_TYPE_CHAR, '35', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2018101001, 'tool', 'attestoodle');
    }
    if ($oldversion < 2018101611) {
        $table = new xmldb_table('attestoodle_value_log');
        $field = new xmldb_field('milestone', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'certificateid');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'moduleid');
            upgrade_plugin_savepoint(true, 2018101611, 'tool', 'attestoodle');
        }
    }
    if ($oldversion < 2018101705) {
        $table = new xmldb_table('attestoodle_milestone');
        $field = new xmldb_field('milestone', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'id');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'creditedtime');
            upgrade_plugin_savepoint(true, 2018101705, 'tool', 'attestoodle');
        }
    }
    return true;
}
