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
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
	$ADMIN->add('courses',
			new admin_category('attestoodle', 'Attestoodle')
	);
	$ADMIN->add('attestoodle',
			new admin_externalpage('toolattestoodle1',
					get_string('add_training', 'tool_attestoodle'),
					"$CFG->wwwroot/course/"
			)
	);
	$ADMIN->add('attestoodle', 
			new admin_externalpage('toolattestoodle2',
					get_string('training_list_link', 'tool_attestoodle'),
					"$CFG->wwwroot/$CFG->admin/tool/attestoodle/index.php"
			)
	);
	$ADMIN->add('attestoodle',
			new admin_externalpage('toolattestoodle3',
					get_string('template_certificate', 'tool_attestoodle'),
					"$CFG->wwwroot/$CFG->admin/tool/attestoodle/classes/gabarit/sitecertificate.php"
			)
	);
}

global $DB;
$records = $DB->get_records('attestoodle_template_detail', null, null);
// if there aren't any entries in the table then we need to prepare them:
if(count($records) == 0) {
	$object = new stdClass();
	$object->templateid = 0;
	$object->type = 'background';
	$object->data = '{ "filename": "attest_background.png" } ';
	$DB->insert_record('attestoodle_template_detail', $object);
	
	$object = new stdClass();
	$object->templateid = 0;
	$object->type = 'learnername';
	$object->data = '{ "font": {"family":"helvetica","emphasis":"","size":"10"}, "location": {"x":"10","y":"90"}, "align":"L"} ';	
	$DB->insert_record('attestoodle_template_detail', $object);
	
	$object = new stdClass();
	$object->templateid = 0;
	$object->type = 'trainingname';
	$object->data = '{ "font": {"family":"helvetica","emphasis":"","size":"10"}, "location": {"x":"10","y":"95"}, "align":"L"} ';
	$DB->insert_record('attestoodle_template_detail', $object);
	
	$object = new stdClass();
	$object->templateid = 0;
	$object->type = 'period';
	$object->data = '{ "font": {"family":"helvetica","emphasis":"B","size":"14"}, "location": {"x":"0","y":"80"}, "align":"C"} ';
	$DB->insert_record('attestoodle_template_detail', $object);
	
	
	$object = new stdClass();
	$object->templateid = 0;
	$object->type = 'totalminutes';
	$object->data = '{ "font": {"family":"helvetica","emphasis":"B","size":"10"}, "location": {"x":"10","y":"100"}, "align":"L"} ';
	$DB->insert_record('attestoodle_template_detail', $object);
	
	$object = new stdClass();
	$object->templateid = 0;
	$object->type = 'activities';
	$object->data = '{ "font": {"family":"helvetica","emphasis":"","size":"10"}, "location": {"x":"10","y":"110"}, "align":"L"} ';
	$DB->insert_record('attestoodle_template_detail', $object);

}

/*
//XXX a supprimer
// Setting for the ID in mdl_role table corresponding to the student role.
$settings->add(new admin_setting_configtext('attestoodle/student_role_id',
                                                get_string('settings_student_role_label', 'tool_attestoodle'),
                                                get_string('settings_student_role_helper', 'tool_attestoodle'),
                                                5,
                                                PARAM_INT));
*/