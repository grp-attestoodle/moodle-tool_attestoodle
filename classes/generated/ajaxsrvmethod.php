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
 * Method call by ajax, that method created Certificate PDF from tmp table information.
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('AJAX_SCRIPT', true);
require_once(dirname(__FILE__) . '/../../../../../config.php');
require_once(dirname(__FILE__).'/../../lib.php');

use tool_attestoodle\gabarit\attestation_pdf;
global $DB;

// Require for context.
require_login();
require_sesskey();

$context = context_system::instance();
$PAGE->set_context($context);
$launchid = optional_param('launchid', -5, PARAM_INT);
$trainingid = optional_param('trainingid', -1, PARAM_INT);
$categoryid = optional_param('categoryid', 1, PARAM_INT);

// Load Data and generated certificate.
$nb = 0;
$rs = $DB->get_records('tool_attestoodle_tmp', array ('trainingid' => $trainingid), '', '*', 0, 2);
$tabid = array();
foreach ($rs as $result) {
    $tabid[] = $result->id;
    $pdfinfo = json_decode($result->pdfinfo);
    $fileinfos = json_decode($result->fileinfo);
    $nb++;

    $tab = $pdfinfo->activities;
    $index = 0;
    $activities = array();
    while (isset($pdfinfo->activities->{'act'.$index})) {
        $obj = $pdfinfo->activities->{'act'.$index};
        $activities[$index]["coursename"] = $obj->coursename;
        $activities[$index]["totalminutes"] = $obj->totalminutes;
        $activities[$index]["moduleid"] = $obj->moduleid;
        $activities[$index]["name"] = $obj->name;
        $activities[$index]["description"] = $obj->description;
        $activities[$index]["type"] = $obj->type;
        $activities[$index]["cmid"] = $obj->cmid;
        $activities[$index]["courseid"] = $obj->courseid;
        $index++;
    }
    $pdfinfo->activities = $activities;

    $status = 1;
    $fs = get_file_storage();
    $file = $fs->get_file($fileinfos->contextid, $fileinfos->component,
                $fileinfos->filearea, $fileinfos->itemid,
                $fileinfos->filepath, $fileinfos->filename
        );
    if ($file) {
        $oldfile = $file;
        $oldfile->delete();
        $status = 2;
    }

    $template = $DB->get_record('tool_attestoodle_user_style',
                array('userid' => $pdfinfo->learnerid, 'trainingid' => $trainingid));
    $pdf = new attestation_pdf();
    if (!isset($template->id)) {
        $pdf->set_trainingid($trainingid);
    } else {
        $pdf->set_idtemplate($template->templateid);
        $pdf->set_grpcriteria1($template->grpcriteria1);
        $pdf->set_grpcriteria2($template->grpcriteria2);
    }
    $pdf->set_infos($pdfinfo);
    $pdfgen = $pdf->generate_pdf_object();
    $pdfstring = $pdfgen->Output('', 'S');
    $file = $fs->create_file_from_string($fileinfos, $pdfstring);
    if (!$file) {
        $status = 0;
    }

    // Log the certificate informations.
    if ($launchid > 0) {
        $statusstring = null;
        switch ($status) {
            case 0:
                $statusstring = 'ERROR';
                break;
            case 1:
                $statusstring = 'NEW';
                break;
            case 2:
                $statusstring = 'OVERWRITTEN';
                break;
        }

        // Try to record the certificate log.
        $dataobject = new \stdClass();
        $dataobject->filename = $fileinfos->filename;
        $dataobject->status = $statusstring;
        $dataobject->trainingid = $trainingid;
        $dataobject->learnerid = $result->learnerid;
        $dataobject->launchid = $launchid;

        $certificatelogid = $DB->insert_record('tool_attestoodle_certif_log', $dataobject, true);

        // Try to record the values used to generate the certificate.
        $milestones = array();
        foreach ($activities as $obj) {
            $dataobject = new \stdClass();
            $dataobject->creditedtime = $obj["totalminutes"];
            $dataobject->certificateid = $certificatelogid;
            $dataobject->moduleid = $obj["moduleid"];
            $milestones[] = $dataobject;
        }
        $DB->insert_records('tool_attestoodle_value_log', $milestones);
    }
}

for ($i = 0; $i < count($tabid); $i++) {
    $DB->delete_records('tool_attestoodle_tmp', array ('id' => $tabid[$i]));
}



$res = array();
$res['result'] = true;
$res['nb'] = $nb;
echo $OUTPUT->header();
echo json_encode($res);
