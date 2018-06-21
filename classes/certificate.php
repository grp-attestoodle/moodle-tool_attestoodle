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
 * This is the class describing a certificate in Attestoodle
 *
 * @package    block_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_attestoodle;

defined('MOODLE_INTERNAL') || die;

use block_attestoodle\utils\db_accessor;

class certificate {
    /** @var learner Learner for whom the certificate is */
    private $learner;
    /** @var training Learner for which the certificate is */
    private $training;
    /** @var \DateTime Begining date for the certificate infos */
    private $begindate;
    /** @var \DateTime End date for the certificate infos */
    private $enddate;

    /**
     * Constructor of the class
     *
     * @param learner $learner The learner of the certificate
     * @param training $training The training of the certificate
     * @param \DateTime $begindate The begin date of the certificate
     * @param \DateTime $enddate The end date of the certificate
     */
    public function __construct($learner, $training, $begindate, $enddate) {
        $this->learner = $learner;
        $this->training = $training;
        $this->begindate = $begindate;
        $this->enddate = $enddate;
    }

    /**
     * Methods that return the file info within moodledata, parsed in
     * the array that moodle needs to retrieve/create the actual file.
     *
     * @return array The array containing the infos well formed
     */
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

    /**
     * Methods that generate the actual file name within moodledata,
     * depending on the learner, training and period
     *
     * @return string The file name formated as
     * "certificate_[learner last name][learner first name]_[begin date]_[end date]_[training name].pdf"
     * where the begin and end dates format is YYYYMMDD
     */
    private function get_file_name() {
        $filename = "certificate_{$this->learner->get_lastname()}{$this->learner->get_firstname()}_";
        $filename .= "{$this->begindate->format("Ymd")}_{$this->enddate->format("Ymd")}_";
        $filename .= $this->training->get_name();
        $filename .= ".pdf";

        return $filename;
    }

    /**
     * Methods that parses all the variable informations needed in the actual
     * certificate file such as learner name, period, etc.
     *
     * @return \stdClass A standard class object containing the following infos:
     * $obj->learnername = The learner full name
     * $obj->trainingname = The training name
     * $obj->totalminutes = The total amount of validated milestones in minutes
     * $obj->period = The period (begin and end date) in a readable format
     * $obj->activities = An array of key => value where the keys are the courses id
     * where at least one milestone has been validated, and the value is another
     * key => value array. This second array contain two fixed infos: the course
     * name and the course total validated milestones (in minutes). This last
     * property may be a void array.
     */
    private function get_pdf_informations() {
        $begindate = clone $this->begindate;
        $searchenddate = clone $this->enddate;
        $searchenddate->modify('+1 day');
        $trainingid = $this->training->get_id();
        $trainingname = $this->training->get_name();
        $totalminutes = 0;

        $filteredmilestones = $this->get_filtered_milestones();

        // Retrieve activities informations in an array structure.
        $activitiesstructured = array();
        foreach ($filteredmilestones as $fva) {
            // Retrieve activity.
            $activity = $fva->get_activity();

            // Increment total minutes for the training.
            $totalminutes += $activity->get_milestone();

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
            $activitiesstructured[$courseid]["totalminutes"] += $activity->get_milestone();
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

    /**
     * Method that returns the activities validated by the learner for the
     * training currently being computes, within the period and all
     *
     * @return activity[] The activities with milestones validated by the learner
     */
    private function get_filtered_milestones() {
        $begindate = clone $this->begindate;
        $searchenddate = clone $this->enddate;
        $searchenddate->modify('+1 day');
        $trainingid = $this->training->get_id();

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

        return $filteredmilestones;
    }

    /**
     * Methods that tries to retrieve the actual certificate file in moodledata
     * corresponding to the current certificate object.
     *
     * @return \stored_file|bool stored_file instance if exists, false if not
     */
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

    /**
     * Methods that checks if the actual certificate file exists within moodledata
     *
     * @return boolean False if the file exists, true if not. Or maybe vice versa, I'm not sure...
     */
    public function file_exists() {
        return $this->retrieve_file() ? true : false;
    }

    /**
     * Methods that generate the actual file URL using moodle make_pluginfile_url helper
     *
     * @return string The actual file URL on the server
     */
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

    /**
     * Method that creates the certificate file on the server
     *
     * @return int The status of the file creation:
     *  0 = An error occured while attempting to create the file
     *  1 = The file has been created normally
     *  2 = A certificate with the same informations has been found
     * on the server: the old file has been replaced by the new one
     */
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

            if (!$file) {
                $status = 0;
            }
        } catch (\Exception $e) {
            $status = 0;
        }

        return $status;
    }

    /**
     * Methods that create the virtual PDF file which can be "print" on an
     * actual PDF file within moodledata
     *
     * @todo translations
     *
     * @return \pdf The virtual pdf file using the moodle pdf class
     */
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

    /**
     * Method that logs the certificate generation in DB.
     *
     * @param integer $launchid The launch_log ID line, already in DB
     * @param integer $status The status of the file creation.
     */
    public function log($launchid, $status) {
        $statusstring = null;
        switch($status) {
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

        $certificatelogid = db_accessor::get_instance()->log_certificate(
                $this->get_file_name(),
                $statusstring,
                $this->training->get_id(),
                $this->learner->get_id(),
                $launchid);

        $milestones = $this->get_filtered_milestones();
        if (count($milestones) > 0) {
            try {
                db_accessor::get_instance()->log_values($certificatelogid, $milestones);
            } catch (\Exception $ex) {
                // Do something?
            }
        }
    }
}
