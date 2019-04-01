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

global $DB, $USER, $PAGE;

if ($hassiteconfig) {
    $ADMIN->add('courses', new admin_category('attestoodle', 'Attestoodle'));
    $ADMIN->add('attestoodle',
        new admin_externalpage('toolattestoodle1', get_string('add_training', 'tool_attestoodle'), "$CFG->wwwroot/course/"));
    $ADMIN->add('attestoodle',
        new admin_externalpage('toolattestoodle2', get_string('training_list_link', 'tool_attestoodle'),
            "$CFG->wwwroot/$CFG->admin/tool/attestoodle/index.php"));
    $ADMIN->add('attestoodle', new admin_externalpage('toolattestoodle3', get_string('template_certificate', 'tool_attestoodle'),
        "$CFG->wwwroot/$CFG->admin/tool/attestoodle/classes/gabarit/listtemplate.php"));
}

// If there aren't any entries in the table then we need to prepare them!
if (!$DB->record_exists('tool_attestoodle_template', array('name' => 'Site'))) {
    $model = new stdClass();
    $model->name = 'Site';
    $model->timecreated = usergetdate(time())[0];
    $model->userid = $USER->id;

    $idtemplate = $DB->insert_record('tool_attestoodle_template', $model);

    $object = new stdClass();
    $object->templateid = $idtemplate;
    $object->type = 'background';
    $object->data = '{ "filename": "attest_background.png" } ';
    $DB->insert_record('tool_attestoodle_tpl_detail', $object);

    $object = new stdClass();
    $object->templateid = $idtemplate;
    $object->type = 'learnername';
    $object->data = '{ "font": {"family":"helvetica","emphasis":"","size":"14"}, "location": {"x":"66","y":"33"}, "align":"L"} ';
    $DB->insert_record('tool_attestoodle_tpl_detail', $object);

    $object = new stdClass();
    $object->templateid = $idtemplate;
    $object->type = 'trainingname';
    $object->data = '{ "font": {"family":"helvetica","emphasis":"","size":"14"}, "location": {"x":"66","y":"39"}, "align":"L"} ';
    $DB->insert_record('tool_attestoodle_tpl_detail', $object);

    $object = new stdClass();
    $object->templateid = $idtemplate;
    $object->type = 'period';
    $object->data = '{ "font": {"family":"helvetica","emphasis":"B","size":"14"}, "location": {"x":"110","y":"18"}, "align":"L"} ';
    $DB->insert_record('tool_attestoodle_tpl_detail', $object);

    $object = new stdClass();
    $object->templateid = $idtemplate;
    $object->type = 'totalminutes';
    $object->data = '{ "font": {"family":"helvetica","emphasis":"B","size":"14"}, "location": {"x":"86","y":"45"}, "align":"L"} ';
    $DB->insert_record('tool_attestoodle_tpl_detail', $object);

    $object = new stdClass();
    $object->templateid = $idtemplate;
    $object->type = 'activities';
    $object->data = '{ "font": {"family":"helvetica","emphasis":"","size":"10"}, "location": {"x":"50","y":"60"}, "align":"C"} ';
    $DB->insert_record('tool_attestoodle_tpl_detail', $object);
    try {
        // Enreg image background in file storage.
        $fs = get_file_storage();
        $file = $fs->get_file(1, 'tool_attestoodle', 'fichier', $idtemplate, '/', 'attest_background.png');
        if (!$file) {
            $filerecord = array('contextid' => 1, 'component' => 'tool_attestoodle', 'filearea' => 'fichier',
                'itemid' => $idtemplate, 'filepath' => '/');
            $url = "$CFG->wwwroot/admin/tool/attestoodle/pix/attest_background.png";
            $fs->create_file_from_url($filerecord, $url, null, true);
        }
    } catch (Exception $e) {
        \core\notification::info('repertoire pix inacessible, modele par défaut sans image');
    }
}

