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
 * Confirm training deletion.
 *
 * @package    tool_attestoodle
 * @copyright  2019 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Main configuration importation (instanciate the $CFG global variable).
require_once(dirname(__FILE__) . '/../../../../../config.php');
require_once(dirname(__FILE__).'/../../lib.php');

use tool_attestoodle\factories\trainings_factory;

$context = context_system::instance();
$PAGE->set_context($context);
require_login();

$categoryid = required_param('categoryid', PARAM_INT);
$trainingid = required_param('trainingid', PARAM_INT);
$delete = optional_param('delete', 15, PARAM_INT);
$confirm = optional_param('confirm', '', PARAM_ALPHANUM);

$url = new moodle_url('/admin/tool/attestoodle/classes/training/delete_training.php',
            array('delete' => $delete, 'categoryid' => $categoryid, 'trainingid' => $trainingid));
$PAGE->set_url($url);

if ($confirm != md5($delete)) { // Must be confirm.
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('deletetraining', 'tool_attestoodle'));
    $optionsyes = array('delete' => $delete, 'confirm' => md5($delete), 'sesskey' => sesskey(),
                        'categoryid' => $categoryid, 'trainingid' => $trainingid);

    $returnurl = new moodle_url('/admin/tool/attestoodle/index.php',
                        array (
                            'typepage' => 'trainingmanagement',
                            'trainingid' => $trainingid,
                            'categoryid' => $categoryid));
    $deleteurl = new moodle_url('/admin/tool/attestoodle/classes/training/delete_training.php', $optionsyes);

    $deletebutton = new single_button($deleteurl, get_string('delete'), 'post');
    $training = $DB->get_record('tool_attestoodle_training', array('id' => $trainingid));
    echo $OUTPUT->confirm('Voulez-vous vraiment supprimer la formation ' . $training->name,
                              $deletebutton, $returnurl);
    echo $OUTPUT->footer();
    die;
} else { // Delete after confirmation.
    trainings_factory::get_instance()->remove_training_by_id($trainingid);
    \core\notification::info(get_string('training_management_submit_removed', 'tool_attestoodle'));
    $redirecturl = new \moodle_url('/admin/tool/attestoodle/index.php', ['typepage' => 'trainingslist']);
    redirect($redirecturl);
    return;
}
