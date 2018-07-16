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

namespace tool_attestoodle\gabarit;
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/lib/pdflib.php");

/**
 * Created a pdf representing a certificate according to a model for a learner.
 *
 * @package tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
class attestation_pdf {
    /** template of the pdf document, background, position of elements. etc. */
    protected $template;
    /** the name of picture background.*/
    protected $filename;
    /** Structure of data to print on PDF.*/
    protected $certificateinfos;
    /** the width of the Page, orientation landscape or portrait change the width.*/
    protected $pagewidth = 0;

    /**
     * Set the information to print.
     * @param \stdClass $info A standard class object containing the following to print provide
     * by methode certificate:get_pdf_informations().
     */
    public function set_infos($infos) {
        $this->certificateinfos = $infos;
    }

    /**
     * Extract informations of the template use to print.
     */
    public function set_idtemplate($idtemplate) {
        global $DB;
        $sql = "select type,data from {attestoodle_template_detail} where templateid = " . $idtemplate;
        $rs = $DB->get_recordset_sql ( $sql, array () );

        $this->template = array();

        foreach ($rs as $result) {
            $obj = json_decode($result->data);
            if ($result->type == "background") {
                $this->filename = $obj->filename;
            } else {
                $obj->type = $result->type;
                $this->template[] = $obj;
            }
        }
    }

    /**
     * Construct the pdf and send into browser.
     */
    public function print_activity() {
        $doc = $this->generate_pdf_object();
        $doc->Output('preview.pdf', 'I');
    }

    /**
     * Methods that create the virtual PDF file which can be "print" on an
     * actual PDF file within moodledata
     *
     * @todo translations
     *
     * @return \pdf The virtual pdf file using the moodle pdf class
     */
    public function generate_pdf_object() {
        $doc = $this->prepare_page();

        foreach ($this->template as $elt) {
            $doc->SetFont($elt->font->family, $elt->font->emphasis, $elt->font->size);
            $doc->SetXY($elt->location->x, $elt->location->y);

            switch ($elt->type) {
                case "learnername" :
                    if (isset($this->certificateinfos->learnername)) {
                        $doc->Cell($doc->GetStringWidth($this->certificateinfos->learnername), 0,
                                $this->certificateinfos->learnername, 0, 0, $elt->align, false);
                    }
                    break;
                case "trainingname" :
                    if (isset($this->certificateinfos->trainingname)) {
                        $doc->Cell($doc->GetStringWidth($this->certificateinfos->trainingname), 0,
                                $this->certificateinfos->trainingname, 0, 0, $elt->align, false);
                    }
                    break;
                case "period" :
                    if (isset($this->certificateinfos->period)) {
                        $doc->Cell($doc->GetStringWidth($this->certificateinfos->period), 0,
                                $this->certificateinfos->period, 0, 0, $elt->align, false);
                    }
                    break;
                case "totalminutes" :
                    if (isset($this->certificateinfos->totalminutes)) {
                        $texte = parse_minutes_to_hours($this->certificateinfos->totalminutes);
                        $doc->Cell($doc->GetStringWidth($texte), 0, $texte, 0, 0, $elt->align, false);
                    }
                    break;
                case "activities" :
                    if (isset($this->certificateinfos->activities)) {
                        $this->printactivities($doc, $elt, $this->certificateinfos->activities);
                    }
                    break;
            }
        }
        return $doc;
    }

    /**
     * Instanciate pdf document en prepare the first page
     * with background image en is orientation.
     */
    private function prepare_page() {
        global $CFG;
        $orientation = 'L';
        if (isset($this->filename)) {
            $taille = getimagesize("$CFG->dirroot/admin/tool/attestoodle/pix/" . $this->filename);
            $this->pagewidth = 297;
            $pageheight = 210;

            if ($taille[1] > $taille[0]) {
                $orientation = 'P';
                $this->pagewidth = 210;
                $pageheight = 297;
            }
        }

        $doc = new \pdf($orientation);
        $doc->setPrintHeader(false);
        $doc->setPrintFooter(false);
        $doc->SetAutoPagebreak(false);
        $doc->SetMargins(0, 0, 0);
        $doc->AddPage();
        if (isset($this->filename)) {
            $doc->Image("$CFG->dirroot/admin/tool/attestoodle/pix/" . $this->filename, '0', '0',
                $this->pagewidth, $pageheight, 'png', '', true);
        }
        return $doc;
    }

    /**
     * Display array of activities on pdf document.
     * @param $pdf the document pdf where we place activities
     * @param $model contains informations to place activities, only
     * the array is concerned (elements of array are relative to the array's corner top left)
     * @param $tabactivities the data to place (the activities)
     */
    private function printactivities($pdf, $model, $tabactivities) {
        $x = intval($model->location->x);
        $y = intval($model->location->y);
        $pdf->SetLineWidth(0.1);
        $heightline = intval($model->font->size);
        if ($heightline == 0) {
            $heightline = 10;
        }
        $height = count($tabactivities) * $heightline + ($heightline * 1.5);
        $width = ($this->pagewidth - $x * 2);
        // Main borders.
        $pdf->Rect($x, $y, $width, $height, "D");

        // Header border.
        $pdf->Line($x, $y + ($heightline * 1.5), $x + $width , $y + ($heightline * 1.5));

        // Columns.
        $widthfirstcolumn = $width * .75;
        $pdf->Line($widthfirstcolumn + $x, $y, $widthfirstcolumn + $x, $height + $y);

        // Column title course.
        $pdf->SetFont($model->font->family, 'B', $model->font->size);
        $pdf->SetXY($x + 5, $y + 5);
        $pdf->Cell($widthfirstcolumn - 10, 0, "Cours Suivis", 0, 0, 'C', false);
        // Column title "total hours".
        $pdf->SetXY($x + $widthfirstcolumn + 5, $y + 5);
        $widthsecondcolumn = $width - $widthfirstcolumn;
        $pdf->Cell($widthsecondcolumn - 10, 0, "Total heures", 0, 0, 'C', false);

        // Activities lines.
        $pdf->SetFont($model->font->family, '', $model->font->size);

        $lineheight = $model->font->size;
        $y = $y + ($heightline * 1.5);

        foreach ($tabactivities as $course) {
            $coursename = $course["coursename"];
            $totalminutes = $course["totalminutes"];
            $pdf->SetXY($x + 5, $y);
            // Activity type.
            $pdf->Cell($widthfirstcolumn - 10, $lineheight, $coursename, 0, 0, 'L');
            // Activity total hours.
            $pdf->SetXY($x + $widthfirstcolumn + 5, $y);
            $pdf->Cell($widthsecondcolumn - 10, $lineheight, parse_minutes_to_hours($totalminutes), 0, 0, 'C');
            $y += $lineheight;
            $pdf->Line($x, $y, $x + $width , $y);
        }

    }
}