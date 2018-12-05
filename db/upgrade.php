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
 * This file manipulate data when an upgrade of the plug-in has been detected.
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Upgrade code according to the evolution of the database.
 * This method is automagically called by Moodle.
 * @param int $oldversion number of the old version
 * @return bool
 */
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

    if ($oldversion < 2018120501) {
        // Define table to be renamed.
        $table = new xmldb_table('attestoodle_train_template');
        if ($dbman->table_exists($table)) {
            // Rename the table to use the correct Moodle naming convention.
            $dbman->rename_table($table, 'tool_attestoodle_train_style');
        }

        $table = new xmldb_table('attestoodle_template_detail');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'tool_attestoodle_tpl_detail');
        }
        $table = new xmldb_table('attestoodle_template');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'tool_attestoodle_template');
        }
        $table = new xmldb_table('attestoodle_value_log');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'tool_attestoodle_value_log');
        }
        $table = new xmldb_table('attestoodle_certif_log');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'tool_attestoodle_certif_log');
        }
        $table = new xmldb_table('attestoodle_launch_log');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'tool_attestoodle_launch_log');
        }
        $table = new xmldb_table('attestoodle_milestone');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'tool_attestoodle_milestone');
        }
        $table = new xmldb_table('attestoodle_training');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'tool_attestoodle_training');
        }

        upgrade_plugin_savepoint(true, 2018120501, 'tool', 'attestoodle');
    }
    return true;
}
