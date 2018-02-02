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
// Importation de la config $CFG qui importe égalment $DB et $OUTPUT.
require_once(dirname(__FILE__) . '/../../../config.php');

require_once($CFG->libdir.'/pdflib.php');

$trainingid = required_param('training', PARAM_INT);
$userid = required_param('user', PARAM_INT);

require_once($CFG->dirroot . '/blocks/attestoodle/lib.php');

require_once($CFG->dirroot . '/blocks/attestoodle/classes/factories/trainings_factory.php');
require_once($CFG->dirroot . '/blocks/attestoodle/classes/factories/courses_factory.php');
require_once($CFG->dirroot . '/blocks/attestoodle/classes/factories/activities_factory.php');
require_once($CFG->dirroot . '/blocks/attestoodle/classes/factories/learners_factory.php');

require_once($CFG->dirroot . '/blocks/attestoodle/classes/course.php');
require_once($CFG->dirroot . '/blocks/attestoodle/classes/activity.php');
require_once($CFG->dirroot . '/blocks/attestoodle/classes/validated_activity.php');

use block_attestoodle\factories\trainings_factory;
use block_attestoodle\factories\learners_factory;

// Verifying training id.
if (!trainings_factory::get_instance()->has_training($trainingid)) {
    // Link to the trainings list if the training id is not valid.
    echo $OUTPUT->header();

    echo $OUTPUT->single_button(
            new moodle_url(
                    '/blocks/attestoodle/pages/trainings_list.php',
                    array()),
            get_string('backto_trainings_list_btn_text', 'block_attestoodle'),
            'get',
            array('class' => 'attestoodle-button'));

    $warningunknowntrainingid = get_string('unknown_training_id', 'block_attestoodle', $trainingid);
    echo $warningunknowntrainingid;

    echo $OUTPUT->footer();
} else {
    // If the training id is valid...
    // Verifying learner id.
    if (!learners_factory::get_instance()->has_learner($userid)) {
        // Link to the training learners list.
        echo $OUTPUT->header();

        echo $OUTPUT->single_button(
                new moodle_url(
                        '/blocks/attestoodle/pages/training_learners_list.php',
                        array('id' => $trainingid)),
                get_string('backto_training_learners_list_btn_text', 'block_attestoodle'),
                'get',
                array('class' => 'attestoodle-button'));

        $warningunknownlearnerid = get_string('unknown_learner_id', 'block_attestoodle', $userid);
        echo $warningunknownlearnerid;

        echo $OUTPUT->footer();
    } else {
        // If the learner id is valid, process the PDF generation.
        $learner = learners_factory::get_instance()->retrieve_learner($userid);
        $certificateinfos = $learner->get_certificate_informations();
        $filename = "certificate_{$learner->get_firstname()}{$learner->get_lastname()}.pdf";

        // PDF Class instanciation.
        $pdf = new pdf();

        // Suppressing default header and footer.
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Suppressing margins.
        $pdf->SetAutoPagebreak(false);
        $pdf->SetMargins(0, 0, 0);

        // @Todo: Do the translations.
        foreach ($certificateinfos->certificates as $certificatekey => $certificate) {
            $pdf->AddPage();

            // Logo : 80 de largeur et 55 de hauteur.
            // $pdf->Image('logo_societe.png', 10, 10, 80, 55);
            // Title.
            $title = "Attestation mensuelle : temps d'apprentissage";
            $pdf->SetFont("helvetica", "", 14);
            $pdf->SetXY(0, 74);
            $pdf->Cell($pdf->GetPageWidth(), 0, $title, 0, 0, "C");

            // Period.
            $period = $certificateinfos->period;
            $pdf->SetFont("helvetica", "B", 14);
            $pdf->SetXY(0, 80);
            $pdf->Cell($pdf->GetPageWidth(), 0, $period, 0, 0, "C");

            // Learner name.
            $learnername = "Nom du stagiaire : " . $learner->get_firstname() . " " . $learner->get_lastname();
            $pdf->SetFont("helvetica", "", 10);
            $pdf->SetXY(10, 90);
            $pdf->Cell($pdf->GetStringWidth($learnername), 0, $learnername, 0, "L");

            // Training name.
            $trainingname = "Intitulé de la formation : " . $certificatekey;
            $pdf->SetXY(10, 95);
            $pdf->Cell($pdf->GetStringWidth($trainingname), 0, $trainingname, 0, "L");

            // Total amount of learning time.
            $totalvalidatedtime = "Temps total validé sur la période : " . parse_minutes_to_hours($certificate["totalminutes"]);
            $pdf->SetXY(10, 100);
            $pdf->Cell($pdf->GetStringWidth($totalvalidatedtime), 0, $totalvalidatedtime, 0, "L");

            // Validated activities details.
            $pdf->SetXY(10, 110);
            // Main borders.
            $pdf->SetLineWidth(0.1);
            $pdf->Rect(10, 110, 190, 90, "D");
            // Header border.
            $pdf->Line(10, 125, 200, 125);
            // Columns.
            $pdf->Line(150, 110, 150, 200);
            // Column title "type".
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(210, 210, 210);
            $pdf->SetXY(10, 110);
            $pdf->Cell(140, 15, "Type d'apprentissage", 1, 0, 'C', true);
            // Column title "total hours".
            $pdf->SetXY(150, 110);
            $pdf->Cell(50, 15, "Total heures", 1, 0, 'C', true);

            // Activities lines.
            $y = 125;
            $lineheight = 8;
            $pdf->SetFont('helvetica', '', 10);
            foreach ($certificate["activities"] as $type => $total) {
                $pdf->SetXY(10, $y);
                // Activity type.
                $pdf->Cell(140, $lineheight, $type, 0, 0, 'L');
                // Activity total hours
                $pdf->SetXY(150, $y);
                $pdf->Cell(50, $lineheight, parse_minutes_to_hours($total), 0, 0, 'C');
                $y += $lineheight;
                $pdf->Line(10, $y, 200, $y);
            }

            // Legal clause.
            $pdf->SetLineWidth(0.1);
            $pdf->Rect(5, 240, 200, 6, "D");
            $pdf->SetXY(0, 240);
            $pdf->SetFont('helvetica', '', 7);
            $pdf->Cell($pdf->GetPageWidth(), 7, "Cette attestation est faite pour servir et valoir ce que de droit", 0, 0, 'C');

            // Signatures.
            $pdf->SetXY(10, 250);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell($pdf->GetPageWidth() - 10, 0, "Signature stagiaire", 0, 1, 'L');
            $pdf->Cell($pdf->GetPageWidth() - 10, 0, "Signature responsable de formation", 0, 0, 'R');
        }

        $fs = get_file_storage();
        $usercontext = context_user::instance($userid);
        // var_dump($usercontext);
        // Prepare file record object
        $fileinfo = array(
            'contextid' => $usercontext->id, // ID of context
            'component' => 'block_attestoodle', // usually = table name
            'filearea' => 'certificates', // usually = table name
            'filepath' => '/',
            'itemid' => 0, // usually = ID of row in table
            // 'itemid' => $offlinequiz->id, // usually = ID of row in table
            'filename' => $filename); // any filename

        if ($oldfile = $fs->get_file(
                $fileinfo['contextid'],
                $fileinfo['component'],
                $fileinfo['filearea'],
                $fileinfo['itemid'],
                $fileinfo['filepath'],
                $fileinfo['filename'])) {
            /*echo "<pre>ANCIEN FILE";
            var_dump($oldfile);
            echo "</pre>";*/
            echo "<p>Une attestation avec les mêmes paramètres a été trouvée sur le serveur. Cette attestation va être supprimée et remplacée par la nouvelle</p>";
            $oldfile->delete();
        }
        $pdfstring = $pdf->Output('', 'S');
        /* echo "<pre>PDF FILE\n";
        echo $pdfstring;
        echo "</pre>";
        echo "</pre>"; */
        $file = $fs->create_file_from_string($fileinfo, $pdfstring);
        // Prepare file record object
        /*
        $fileinfofake = array(
            'contextid' => $usercontext->id, // ID of context
            'component' => 'block_attestoodle', // usually = table name
            'filearea' => 'certificates', // usually = table name
            'filepath' => '/',
            'itemid' => 0, // usually = ID of row in table
            // 'itemid' => $offlinequiz->id, // usually = ID of row in table
            'filename' => "filetoberemovediffound.txt"); // any filename
        $filefake = $fs->create_file_from_string($fileinfofake, "ceci est un text de test");
        var_dump($fileinfo);
        var_dump($pdfstring);

        $fs = get_file_storage();

        // Prepare file record object
        $fileinfo = array(
            'contextid' => $usercontext->id, // ID of context
            'component' => 'mod_mymodule',     // usually = table name
            'filearea' => 'myarea',     // usually = table name
            'itemid' => 0,               // usually = ID of row in table
            'filepath' => '/',           // any path beginning and ending in /
            'filename' => 'myfile2.txt'); // any filename

        // Create file containing text 'hello world'
        $fs->create_file_from_string($fileinfo, 'hello world');


        $pdf->Output($filename, "D");
        echo "<pre>FILE JUST CREATED";
        var_dump($filefake);
        echo $filefake->get_contextid() . "\n";
        echo $filefake->get_component() . "\n";
        echo $filefake->get_filearea() . "\n";
        echo $filefake->get_itemid() . "\n";
        echo $filefake->get_filepath() . "\n";
        echo $filefake->get_filename() . "\n";
        echo "</pre>";
        $url = moodle_url::make_pluginfile_url(
                $filefake->get_contextid(),
                $filefake->get_component(),
                $filefake->get_filearea(),
                null,
                $filefake->get_filepath(),
                $filefake->get_filename()); */
        /* echo "<pre>FILE JUST CREATED";
        var_dump($file);
        echo $file->get_contextid() . "\n";
        echo $file->get_component() . "\n";
        echo $file->get_filearea() . "\n";
        echo $file->get_itemid() . "\n";
        echo $file->get_filepath() . "\n";
        echo $file->get_filename() . "\n";
        echo "</pre>";*/
        $url = moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                null,
                $file->get_filepath(),
                $file->get_filename());
        // @todo translations
        echo "<p>L'attestation a été générée et stockée sur le serveur</p>";
        echo "<a href='" . $url . "'>" . get_string('download_certificate_file_link_text', 'block_attestoodle') . "</a>";
    }
}
