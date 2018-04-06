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
 * This is the class describing an activity validated by a learner in Attestoodle
 *
 * @package    block_attestoodle
 * @copyright  2017 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_attestoodle;

defined('MOODLE_INTERNAL') || die;

class certificate {
    private $learner;
    private $training;
    private $begindate;
    private $enddate;

    public function __construct($learner, $training, $begindate, $enddate) {
        $this->learner = $learner;
        $this->training = $training;
        $this->begindate = $begindate;
        $this->enddate = $enddate;
    }

    private function get_file_infos() {
        $usercontext = \context_user::instance($this->learner->get_id());

        // Prepare file record object.
        $fileinfos = array(
                'contextid' => $usercontext->id,
                'component' => 'block_attestoodle',
                'filearea' => 'certificates',
                'filepath' => '/',
                'itemid' => 0,
                'filename' => $this->get_file_name()
        );

        return $fileinfos;
    }

    private function get_file_name() {
        $filename = "certificate_{$this->learner->get_firstname()}{$this->learner->get_lastname()}_";
        $filename .= "{$this->begindate->format("Ymd")}_{$this->enddate->format("Ymd")}_";
        $filename .= $this->training->get_name();
        $filename .= ".pdf";

        return $filename;
    }

    private function get_pdf_informations() {
        $begindate = clone $this->begindate;
        $searchenddate = clone $this->enddate;
        $searchenddate->modify('+1 day');
        $trainingid = $this->training->get_id();
        $trainingname = $this->training->get_name();
        $totalminutes = 0;

        $validatedmilestones = $this->learner->get_validated_activities_with_marker($begindate, $searchenddate);
        // Filtering activities based on the training.
        $filteredmilestones = array_filter($validatedmilestones, function($va) use($trainingid) {
            $act = $va->get_activity();
            if ($act->get_course()->get_training()->get_id() == $trainingid) {
                return true;
            } else {
                return false;
            }
        });

        // Retrieve activities informations in an array structure.
        $activitiesstructured = array();
        foreach ($filteredmilestones as $fva) {
            // Retrieve activity.
            $activity = $fva->get_activity();

            // Increment total minutes for the training.
            $totalminutes += $activity->get_marker();

            // Retrieve current activity informations.
            $course = $activity->get_course();
            $courseid = $course->get_id();
            $coursename = $course->get_name();

            // Instanciate course in the global array if needed.
            if (!array_key_exists($courseid, $activitiesstructured)) {
                $activitiesstructured[$courseid] = array(
                    "totalminutes" => 0,
                    "coursename" => $coursename
                );
            }
            // Increment total minutes for the course id in the training.
            $activitiesstructured[$courseid]["totalminutes"] += $activity->get_marker();
        }
        // Retrieve global informations.
        // ...@TODO translations.
        $period = "Du {$this->begindate->format("d/m/Y")} au {$this->enddate->format("d/m/Y")}";

        $certificateinfos = new \stdClass();
        $certificateinfos->learnername = $this->learner->get_fullname();
        $certificateinfos->trainingname = $trainingname;
        $certificateinfos->totalminutes = $totalminutes;
        $certificateinfos->period = $period;
        $certificateinfos->activities = $activitiesstructured;

        return $certificateinfos;
    }

    public function retrieve_file() {
        $fs = get_file_storage();
        $fileinfos = $this->get_file_infos();

        $file = $fs->get_file(
                $fileinfos['contextid'],
                $fileinfos['component'],
                $fileinfos['filearea'],
                $fileinfos['itemid'],
                $fileinfos['filepath'],
                $fileinfos['filename']
        );

        return $file;
    }

    public function file_exists() {
        return $this->retrieve_file() ? true : false;
    }

    public function get_existing_file_url() {
        $file = $this->retrieve_file();

        $url = \moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                null,
                $file->get_filepath(),
                $file->get_filename());

        return $url;
    }

    public function create_file_on_server() {
        $status = 1;
        $fs = get_file_storage();

        try {
            $fileinfos = $this->get_file_infos();

            if ($this->file_exists()) {
                $oldfile = $this->retrieve_file();
                $oldfile->delete();
                $status = 2;
            }

            $pdf = $this->generate_pdf_object();
            $pdfstring = $pdf->Output('', 'S');

            $file = $fs->create_file_from_string($fileinfos, $pdfstring);
        } catch (\Exception $e) {
            $status = 0;
        }

        return $status;
    }

    // TODO translations.
    private function generate_pdf_object() {
        // PDF Class instanciation.
        $pdf = new \pdf();

        $certificateinfos = $this->get_pdf_informations();

        // Suppressing default header and footer.
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Suppressing margins.
        $pdf->SetAutoPagebreak(false);
        $pdf->SetMargins(0, 0, 0);

        $pdf->AddPage();

        // Logo : 80 de largeur et 55 de hauteur.
        // To add logo : $pdf->Image('logo_societe.png', 10, 10, 80, 55);
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
        $learnername = "Nom du stagiaire : " . $certificateinfos->learnername;
        $pdf->SetFont("helvetica", "", 10);
        $pdf->SetXY(10, 90);
        $pdf->Cell($pdf->GetStringWidth($learnername), 0, $learnername, 0, "L");

        // Training name.
        $trainingname = "Intitulé de la formation : " . $certificateinfos->trainingname;
        $pdf->SetXY(10, 95);
        $pdf->Cell($pdf->GetStringWidth($trainingname), 0, $trainingname, 0, "L");

        // Total amount of learning time.
        $totalvalidatedtime = "Temps total validé sur la période : " . parse_minutes_to_hours($certificateinfos->totalminutes);
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
        $pdf->Cell(140, 15, "Cours suivis", 1, 0, 'C', true);
        // Column title "total hours".
        $pdf->SetXY(150, 110);
        $pdf->Cell(50, 15, "Total heures", 1, 0, 'C', true);

        // Activities lines.
        $y = 125;
        $lineheight = 8;
        $pdf->SetFont('helvetica', '', 10);
        foreach ($certificateinfos->activities as $course) {
            $coursename = $course["coursename"];
            $totalminutes = $course["totalminutes"];
            $pdf->SetXY(10, $y);
            // Activity type.
            $pdf->Cell(140, $lineheight, $coursename, 0, 0, 'L');
            // Activity total hours.
            $pdf->SetXY(150, $y);
            $pdf->Cell(50, $lineheight, parse_minutes_to_hours($totalminutes), 0, 0, 'C');
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
        $pdf->Cell($pdf->GetPageWidth() / 2 - 10, 0, "Signature stagiaire", 0, 0, 'L');
        $pdf->Cell($pdf->GetPageWidth() / 2 - 10, 0, "Signature responsable de formation", 0, 0, 'R');

        return $pdf;
    }
}
