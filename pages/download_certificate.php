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

require_once($CFG->dirroot.'/blocks/attestoodle/vendor/fpdf/fpdf.php');

$trainingid = required_param('training', PARAM_INT);
$userid = required_param('user', PARAM_INT);

require_once($CFG->dirroot.'/blocks/attestoodle/lib.php');

require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/trainings_factory.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/courses_factory.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/activities_factory.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/learners_factory.php');

require_once($CFG->dirroot.'/blocks/attestoodle/classes/course.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/activity.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/validated_activity.php');

use block_attestoodle\factories\trainings_factory;
use block_attestoodle\factories\learners_factory;

// Verifying training id.
if (!trainings_factory::get_instance()->has_training($trainingid)) {
    // Link to the trainings list if the training id is not valid.
    echo $OUTPUT->header();

    echo $OUTPUT->single_button(
            new moodle_url('/blocks/attestoodle/pages/trainings_list.php', array()),
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
                new moodle_url('/blocks/attestoodle/pages/training_learners_list.php', array('id' => $trainingid)),
                get_string('backto_training_learners_list_btn_text', 'block_attestoodle'),
                'get',
                array('class' => 'attestoodle-button'));

        $warningunknownlearnerid = get_string('unknown_learner_id', 'block_attestoodle', $userid);
        echo $warningunknownlearnerid;

        echo $OUTPUT->footer();
    } else {
        // If the learner id is valid, process the PDF generation
        $learner = learners_factory::get_instance()->retrieve_learner($userid);
        $certificateinfos = $learner->get_certificate_informations();
        $filename = "certificate_{$learner->get_firstname()}{$learner->get_lastname()}.pdf";

        // le mettre au debut car plante si on declare $mysqli avant !
        $pdf = new FPDF( 'P', 'mm', 'A4' );

        // on sup les 2 cm en bas
        $pdf->SetAutoPagebreak(false);
        $pdf->SetMargins(0, 0, 0);


        foreach ($certificateinfos->certificates as $certificatekey => $certificate) {
            $pdf->AddPage();

            // logo : 80 de largeur et 55 de hauteur
            // $pdf->Image('logo_societe.png', 10, 10, 80, 55);

            // Titre
            $title = "Attestation mensuelle : temps d'apprentissage";
            $pdf->SetFont("Arial", "", 14);
            $pdf->SetXY(0, 74);
            $pdf->Cell($pdf->GetPageWidth(), 0, $title, 0, 0, "C");

            // Période.
            $period = $certificateinfos->period;
            $pdf->SetFont("Arial", "B", 14);
            $pdf->SetXY(0, 80);
            $pdf->Cell($pdf->GetPageWidth(), 0, $period, 0, 0, "C");

            // Nom du stagiaire
            $learnername = utf8_decode("Nom du stagiaire : " . $learner->get_firstname() . " " . $learner->get_lastname());
            $pdf->SetFont("Arial", "", 10);
            $pdf->SetXY(10, 90);
            $pdf->Cell($pdf->GetStringWidth($learnername), 0, $learnername, 0, "L");

            // Intitulé formation
            $trainingname = utf8_decode("Intitulé de la formation : " . $certificatekey);
            $pdf->SetXY(10, 95);
            $pdf->Cell($pdf->GetStringWidth($trainingname), 0, $trainingname, 0, "L");

            // Temps d'apprentissage sur le mois
            $totalvalidatedtime = utf8_decode("Temps total validé sur la période : " . parse_minutes_to_hours($certificate["totalminutes"]));
            $pdf->SetXY(10, 100);
            $pdf->Cell($pdf->GetStringWidth($totalvalidatedtime), 0, $totalvalidatedtime, 0, "L");

            // Détails des activités validées
            $pdf->SetXY(10, 110);
            // cadre avec 18 lignes max ! et 118 de hauteur --> 95 + 118 = 213 pour les traits verticaux
            $pdf->SetLineWidth(0.1);
            $pdf->Rect(10, 110, 190, 90, "D");
            // cadre titre des colonnes
            $pdf->Line(10, 125, 200, 125);
            // les traits verticaux colonnes
            $pdf->Line(150, 110, 150, 200);
            // Titre type apprentissage
            $pdf->SetFont('Arial','B',10);
            $pdf->SetFillColor(210, 210, 210);
            $pdf->SetXY(10, 110);
            $pdf->Cell(140, 15, "Type d'apprentissage", 1, 0, 'C', true);
            // Titre total heures
            $pdf->SetXY(150, 110);
            $pdf->Cell(50, 15, "Total heures", 1, 0, 'C', true);

            // Lignes d'activités
            $y = 125; $lineheight = 8;
            $pdf->SetFont('Arial','',10);
            foreach ($certificate["activities"] as $type => $total) {
                $pdf->SetXY(10, $y);
                // Type de l'activite
                $pdf->Cell(140, $lineheight, $type, 0, 0, 'L');
                // Total heures
                $pdf->SetXY(150, $y);
                $pdf->Cell(50, $lineheight, parse_minutes_to_hours($total), 0, 0, 'C');
                $y += $lineheight;
                $pdf->Line(10, $y, 200, $y);
            }

            // Clause légale.
            $pdf->SetLineWidth(0.1);
            $pdf->Rect(5, 240, 200, 6, "D");
            $pdf->SetXY(0, 240);
            $pdf->SetFont('Arial','',7);
            $pdf->Cell($pdf->GetPageWidth(), 7, "Cette attestation est faite pour servir et valoir ce que de droit", 0, 0, 'C');

            // Signatures.
            $pdf->SetXY(10, 250);
            $pdf->SetFont('Arial','',10);
            $pdf->Cell($pdf->GetPageWidth() - 10, 0, "Signature stagiaire", 0, 1, 'L');
            $pdf->Cell($pdf->GetPageWidth() - 10, 0, "Signature responsable de formation", 0, 0, 'R');
        }

        $pdf->Output("I", $filename, true);
    }
}
