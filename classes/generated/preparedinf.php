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
 * Prepare informations for all the certificate on a table.
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Main configuration importation (instanciate the $CFG global variable).
require_once(dirname(__FILE__) . '/../../../../../config.php');

use tool_attestoodle\factories\trainings_factory;
use tool_attestoodle\certificate;

$context = context_system::instance();
$PAGE->set_context($context);
require_login();
global $DB;

$categoryid = required_param('categoryid', PARAM_INT);
$trainingid = required_param('trainingid', PARAM_INT);
$begindate = required_param('begindate', PARAM_ALPHANUMEXT);
$enddate = required_param('enddate', PARAM_ALPHANUMEXT);

// NavBar.
$navlevel1 = get_string('navlevel1', 'tool_attestoodle');
$navlevel2 = get_string('navlevel2', 'tool_attestoodle');
$navlevel3a = get_string('navlevel3a', 'tool_attestoodle');

$PAGE->navbar->ignore_active();
$PAGE->navbar->add($navlevel1, new moodle_url('/admin/tool/attestoodle/index.php', array()));
$PAGE->navbar->add($navlevel2, new moodle_url('/admin/tool/attestoodle/index.php',
                            array('typepage' => 'trainingmanagement', 'categoryid' => $categoryid)));
$PAGE->navbar->add($navlevel3a, new moodle_url('/admin/tool/attestoodle/index.php',
                            array('typepage' => 'learners', 'categoryid' => $categoryid)));
$PAGE->set_url(new moodle_url('/admin/tool/attestoodle/classes/generated/preparedinf.php', [] ));

$PAGE->set_title(get_string('confirmation', 'tool_attestoodle'));
$title = get_string('pluginname', 'tool_attestoodle') . " - " . get_string('confirmation', 'tool_attestoodle');
$PAGE->set_heading($title);

// Compute time credited for all learners.
trainings_factory::get_instance()->create_training_by_category($categoryid, $trainingid);
$training = trainings_factory::get_instance()->retrieve_training_by_id($trainingid);

// Data preparation.
$nb = 0;
$DB->delete_records('tool_attestoodle_tmp', array ('trainingid' => $trainingid));
foreach ($training->get_learners() as $learner) {
    $template = $DB->get_record('tool_attestoodle_user_style',
                array('userid' => $learner->get_id(), 'trainingid' => $trainingid));
    $enablecertificate = 1;
    if (isset($template->enablecertificate)) {
        $enablecertificate = $template->enablecertificate;
    }

    if ($enablecertificate == 1) {
        $certificate = new certificate($learner, $training, new \DateTime($begindate), new \DateTime($enddate));
        $elvinfo = $certificate->get_pdf_informations();
        $fileinfo = $certificate->get_file_infos();
        // Store in DataBase.
        $object = new \stdClass();
        $object->trainingid = $trainingid;
        $object->fileinfo = json_encode($fileinfo);
        $object->pdfinfo = json_encode($elvinfo);
        $object->learnerid = $learner->get_id();
        $DB->insert_record('tool_attestoodle_tmp', $object);
        $nb++;
    }
}

// Display page.
echo $OUTPUT->header();

echo ("<center>");
echo (get_string('questgenerate', 'tool_attestoodle', $nb) . "<br/>");

$linkyes = \html_writer::link(
                new \moodle_url(
                            '/admin/tool/attestoodle/classes/generated/createdpdf.php',
                            array(
                                'trainingid' => $trainingid,
                                'categoryid' => $categoryid,
                                'begindate' => $begindate,
                                'enddate' => $enddate,
                                'nbmax' => $nb
                            )
                    ),
                    get_string('yes'),
                    array('class' => 'btn btn-default attestoodle-button'));

$linkno = \html_writer::link(
                new moodle_url('/admin/tool/attestoodle/index.php',
                            array('typepage' => 'learners',
                            'trainingid' => $trainingid,
                            'categoryid' => $categoryid,
                            'begindate' => $begindate,
                            'enddate' => $enddate)
                    ),
                    get_string('no'),
                    array('class' => 'btn btn-default attestoodle-button'));

echo $linkno . '&nbsp;&nbsp;' . $linkyes;
echo ("</center>");
echo $OUTPUT->footer();
