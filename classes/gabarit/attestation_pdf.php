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
require_once("./simul_pdf.php");

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
    protected $pageheight = 0;
    /** Order from the beginning of the activity's table.*/
    protected $ytabstart;
    /** Order from the end of the activity's table.*/
    protected $ytabend;
    /** Order from the last detail.*/
    protected $yend;

    /** The url of background image (copy tmp).*/
    protected $file;

    protected $acceptoffset = false;
    protected $offset = 0;
    protected $currentpage = 1;
    protected $nbpage = 1;
    protected $numpage = 'never';
    protected $repeatbackground = false;
    protected $repeatstart = false;
    protected $repeatend = false;
    /**
     * Set the information to print.
     * @param \stdClass $info A standard class object containing the following to print provide
     * by methode certificate:get_pdf_informations().
     */
    public function set_infos($infos) {
        $this->certificateinfos = $infos;
    }

    public function set_categoryid($categoryid) {
        global $DB;
        $idtraining = $DB->get_field('attestoodle_training', 'id', ['categoryid' => $categoryid]);
        $idtemplate = $DB->get_field('attestoodle_train_template', 'templateid', ['trainingid' => $idtraining]);
        $this->set_idtemplate($idtemplate);
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
            } else if ($result->type == "pagebreak") {
                $this->numpage = $obj->numpage;
                $this->repeatbackground = $obj->repeatbackground;
                $this->repeatstart = $obj->repeatstart;
                $this->repeatend = $obj->repeatend;
            } else {
                $obj->type = $result->type;
                $this->template[] = $obj;
            }
        }
        $this->comparetemplate();

        // Get background file.
        if ($this->filename != null) {
            $fs = get_file_storage();
            $filestore = $fs->get_file(1, 'tool_attestoodle', 'fichier', $idtemplate, '/', $this->filename);
            if ($filestore) {
                $this->file = $filestore->copy_content_to_temp();
            } else {
                $this->filename = null;
            }
        }
    }

    /**
     * Sort $this->template on y ASC.
     */
    protected function comparetemplate() {
        $tab = array();
        $taille = count($this->template);
        $nb = 0;
        while ($nb < $taille) {
            $min = 10000; // Must be greater than 297.
            foreach ($this->template as $elt) {
                if ($elt != null && $elt->location->y < $min) {
                    $min = $elt->location->y;
                }
            }
            foreach ($this->template as $key => $elt) {
                if ($elt != null && $elt->location->y == $min) {
                    $tab[] = $elt;
                    unset($this->template[$key]);
                    $nb++;
                }
            }
            if ($min == 10000) {
                break;
            }
        }
        $this->template = $tab;
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
        $this->analysedata();
        $this->computenbpage($doc);
        foreach ($this->template as $elt) {
            $doc->SetFont($elt->font->family, $elt->font->emphasis, $elt->font->size);

            if ($elt->type == "activities" && isset($this->certificateinfos->activities)) {
                $this->printactivities($doc, $elt, $this->certificateinfos->activities);
            } else {
                switch ($elt->type) {
                    case "learnername" :
                        if (isset($this->certificateinfos->learnername)) {
                            $text = $this->certificateinfos->learnername;
                        }
                        break;
                    case "trainingname" :
                        if (isset($this->certificateinfos->trainingname)) {
                            $text = $this->certificateinfos->trainingname;
                        }
                        break;
                    case "period" :
                        if (isset($this->certificateinfos->period)) {
                            $text = $this->certificateinfos->period;
                        }
                        break;
                    case "totalminutes" :
                        if (isset($this->certificateinfos->totalminutes)) {
                            $text = parse_minutes_to_hours($this->certificateinfos->totalminutes);
                        }
                        break;
                    case "text" :
                        $text = "";
                        break;
                    case "pagenumber" :
                        $text = "";
                        if ($this->numpage == 'never') {
                            continue;
                        }
                        if ($this->nbpage < 2 && $this->numpage == 'any') {
                            continue;
                        }

                        $text = $this->nbpage;
                        if ($elt->ontotal) {
                            $text = $text . " / " . $this->nbpage;
                        }
                        if (isset($elt->lib)) {
                            $text = $elt->lib . $text;
                        }
                }
                if (isset($elt->lib) && $elt->type != "pagenumber") {
                    $text = $elt->lib . $text;
                }
                $text = trim($text);
                if (!empty($text)) {
                    $this->displaytext($text, $elt, $doc);
                }
            }
        }
        if (isset($this->file)) {
            @unlink($this->file);
        }
        return $doc;
    }

    /**
     * Il y a forcement plus d'une page dans attest a ce niveau
     * puisqu'on fait une rupture de page.
     */
    protected function displaypagenumber($pdf) {
        if ($this->numpage == 'never') {
            return;
        }
        $zr = $this->offset;
        $this->offset = 0;
        foreach ($this->template as $elt) {
            if ($elt->type != "pagenumber") {
                continue;
            }
            $pdf->SetFont($elt->font->family, $elt->font->emphasis, $elt->font->size);

            $text = $this->currentpage;
            if ($elt->ontotal) {
                $text = $text . " / " . $this->nbpage;
            }
            if (isset($elt->lib)) {
                $text = $elt->lib . $text;
            }
            $text = trim($text);
            $this->displaytext($text, $elt, $pdf);
        }
        $this->offset = $zr;
    }

    protected function displaybeforeactivities($pdf, $model) {
        if (!$this->repeatstart) {
            return;
        }
        foreach ($this->template as $elt) {
            if ($elt->location->y > $model->location->y) {
                break;
            }
            if ($elt->type == "pagenumber") {
                continue;
            }
            $pdf->SetFont($elt->font->family, $elt->font->emphasis, $elt->font->size);
            $text = "";
            switch ($elt->type) {
                case "learnername" :
                    if (isset($this->certificateinfos->learnername)) {
                        $text = $this->certificateinfos->learnername;
                    }
                    break;
                case "trainingname" :
                    if (isset($this->certificateinfos->trainingname)) {
                        $text = $this->certificateinfos->trainingname;
                    }
                    break;
                case "period" :
                    if (isset($this->certificateinfos->period)) {
                        $text = $this->certificateinfos->period;
                    }
                    break;
                case "totalminutes" :
                    if (isset($this->certificateinfos->totalminutes)) {
                        $text = parse_minutes_to_hours($this->certificateinfos->totalminutes);
                    }
                    break;
                case "text" :
                    break;
            }
            if (isset($elt->lib)) {
                $text = $elt->lib . $text;
            }
            $text = trim($text);
            $this->displaytext($text, $elt, $pdf);
        }
    }

    /**
     * affiche les données apres le tableau
     * avant ruture de page.
     */
    protected function displayaftertactivities($pdf, $model) {
        if (!$this->repeatend) {
            return;
        }
        foreach ($this->template as $elt) {
            if ($elt->location->y <= $model->location->y) {
                continue;
            }
            if ($elt->type == "pagenumber") {
                continue;
            }
            $pdf->SetFont($elt->font->family, $elt->font->emphasis, $elt->font->size);

            switch ($elt->type) {
                case "learnername" :
                    if (isset($this->certificateinfos->learnername)) {
                        $text = $this->certificateinfos->learnername;
                    }
                    break;
                case "trainingname" :
                    if (isset($this->certificateinfos->trainingname)) {
                        $text = $this->certificateinfos->trainingname;
                    }
                    break;
                case "period" :
                    if (isset($this->certificateinfos->period)) {
                        $text = $this->certificateinfos->period;
                    }
                    break;
                case "totalminutes" :
                    if (isset($this->certificateinfos->totalminutes)) {
                        $text = parse_minutes_to_hours($this->certificateinfos->totalminutes);
                    }
                    break;
                case "text" :
                    $text = "";
                    break;
            }
            if (isset($elt->lib)) {
                $text = $elt->lib . $text;
            }
            $text = trim($text);
            $this->displaytext($text, $elt, $pdf);
        }
    }

    /**
     * Compute align with lenth of text and code align.
     * @param $elt param for display mode
     * @param $widthtext the size of the data to display.
     */
    private function comput_align($elt, $widthtext) {
        $x = 0;
        switch ($elt->align) {
            case 'L' :
                $x = $elt->location->x;
                break;
            case 'R' :
                $x = $this->pagewidth - $elt->location->x - $widthtext;
                break;
            case 'C' :
                $x = ($this->pagewidth - $elt->location->x - $widthtext) / 2 +
                    $elt->location->x;
        }
        return $x;
    }
    /**
     * Instanciate pdf document en prepare the first page
     * with background image en is orientation.
     */
    private function prepare_page() {
        $orientation = 'P';
        $this->pagewidth = 210;
        $this->pageheight = 297;

        if (isset($this->filename)) {
            // Set orientation width the size of image.
            $taille = getimagesize($this->file);

            if ($taille[0] > $taille[1]) {
                $orientation = 'L';
                $this->pagewidth = 297;
                $this->pageheight = 210;
            }
        }

        $doc = new \pdf($orientation);
        $doc->setPrintHeader(false);
        $doc->setPrintFooter(false);
        $doc->SetAutoPagebreak(false);
        $doc->SetMargins(0, 0, 0);
        $doc->AddPage();

        if (isset($this->filename)) {
            $doc->Image($this->file, 0, 0, $this->pagewidth, $this->pageheight, 'png', '', true);
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
        $minwidth = 80;
        $width = 0;
        if (isset($model->size)) {
            $width = $model->size;
        }

        if ($width == 0) {
            $width = ($this->pagewidth - $model->location->x * 2);
        }

        // Force minimum width of activities table.
        if ($width < $minwidth) {
            $width = $minwidth;
        }

        $x = $this->comput_align($model, $width);
        if ($x + $minwidth > $this->pagewidth) {
            $x = $this->pagewidth - $minwidth;
        }

        $y = intval($model->location->y) + $this->offset;

        $pdf->SetLineWidth(0.1);
        $heightline = intval($model->font->size);
        if ($heightline == 0) {
            $heightline = 10;
        }

        // Main borders.
        $ystart = $y;

        // Header border.
        $pdf->Line($x, $y + ($heightline * 1.5), $x + $width , $y + ($heightline * 1.5));

        // Columns.
        $widthfirstcolumn = $width * .75;

        // Column title course.
        $pdf->SetFont($model->font->family, 'B', $model->font->size);
        $pdf->SetXY($x + 5, $y + 5);
        $pdf->Cell($widthfirstcolumn - 10, 0, get_string('activity_header_col_1', 'tool_attestoodle'), 0, 0, 'C', false);
        // Column title "total hours".
        $pdf->SetXY($x + $widthfirstcolumn + 5, $y + 5);
        $widthsecondcolumn = $width - $widthfirstcolumn;
        $pdf->Cell($widthsecondcolumn - 10, 0, get_string('activity_header_col_2', 'tool_attestoodle'), 0, 0, 'C', false);

        // Activities lines.
        $pdf->SetFont($model->font->family, '', $model->font->size);

        $lineheight = $model->font->size;
        $y = $y + ($heightline * 1.5);

        foreach ($tabactivities as $course) {
            // Test rupture page nécessaire ?
            if ($y - $this->ytabend + $this->yend + 10 > $this->pageheight) {
                $pdf->Rect($x, $ystart, $width, $y - $ystart, "D");
                $pdf->Line($widthfirstcolumn + $x, $ystart, $widthfirstcolumn + $x, $y);
                if ($y < $this->ytabend) {
                    $this->offset = 0;
                } else {
                    $this->offset = $y - $this->ytabend;
                }

                $this->displayaftertactivities($pdf, $model);
                $this->displaypagenumber($pdf);

                $pdf->AddPage();
                $this->currentpage ++;
                if (isset($this->filename) && $this->repeatbackground) {
                    $pdf->Image($this->file, 0, 0, $this->pagewidth, $this->pageheight, 'png', '', true);
                }
                $this->offset = 0;
                $this->displaybeforeactivities($pdf, $model);
                $y = $this->ytabstart;
                $ystart = $y;

                $pdf->Line($x, $y + ($heightline * 1.5), $x + $width , $y + ($heightline * 1.5));
                $pdf->SetFont($model->font->family, 'B', $model->font->size);
                $pdf->SetXY($x + 5, $y + 5);
                $pdf->Cell($widthfirstcolumn - 10, 0, get_string('activity_header_col_1', 'tool_attestoodle'), 0, 0, 'C', false);
                $pdf->SetXY($x + $widthfirstcolumn + 5, $y + 5);
                $pdf->Cell($widthsecondcolumn - 10, 0, get_string('activity_header_col_2', 'tool_attestoodle'), 0, 0, 'C', false);
                $pdf->SetFont($model->font->family, '', $model->font->size);
                $y = $y + ($heightline * 1.5);
            }

            $coursename = $course["coursename"];
            $totalminutes = $course["totalminutes"];
            // Activity type.
            $nbnewline = $this->displayactivity($pdf, $x + 3, $y, $widthfirstcolumn - 6, $lineheight, $coursename);
            // Activity total hours.
            $pdf->SetXY($x + $widthfirstcolumn + 5, $y);
            $pdf->Cell($widthsecondcolumn - 10, $lineheight, parse_minutes_to_hours($totalminutes), 0, 0, 'C');
            $y += $lineheight + ($nbnewline - 1) * $lineheight / 2;
            $pdf->Line($x, $y, $x + $width , $y);
        }
        $pdf->Rect($x, $ystart, $width, $y - $ystart, "D");
        $pdf->Line($widthfirstcolumn + $x, $ystart, $widthfirstcolumn + $x, $y);
        // Compute offset.
        if ($y < $this->ytabend) {
            $this->offset = 0;
        } else {
            $this->offset = $y - $this->ytabend;
        }
    }

    private function displayactivity($pdf, $x, $y, $widthcolumn, $lineheight, $text) {
        $nbsaut = 0;
        $offsettab = 0;
        while ($nbsaut < 5) {
            $nbsaut++;
            $relicat = "";
            while ($pdf->GetStringWidth($text) > $widthcolumn) {
                $position = strrpos($text, " ");
                if ($position) {
                    $relicat = substr($text, $position + 1) . " " . $relicat;
                    $text = substr($text, 0, $position);
                } else {
                    break;
                }
            }
            $pdf->SetXY($x, $y + $offsettab);
            $pdf->Cell($widthcolumn, $lineheight, $text, 0, 0, 'L');
            if ($relicat == "") {
                return $nbsaut;
            }
            $text = $relicat;
            $offsettab = $offsettab + $lineheight / 2;
        }
        return $nbsaut;
    }
    /**
     * Check if data accept offset, if data element have lib
     * they accept offset else they not.
     * Compute interval for table of activities.
     * Get the last y position.
     */
    private function analysedata() {
        $this->setacceptoffset();
        $this->computepagelimit();
    }

    private function computepagelimit() {
        $this->yend = 0;
        $this->ytabstart = 0;
        foreach ($this->template as $elt) {
            if ($elt->type == "activities") {
                $this->ytabstart = $elt->location->y;
            }
            $this->yend = $elt->location->y;
        }
        $this->ytabend = -1;
        foreach ($this->template as $elt) {
            if ($elt->location->y > $this->ytabstart && $this->ytabend == -1) {
                $this->ytabend = $elt->location->y;
            }
        }
        if ($this->ytabend == -1) {
            $this->ytabend = $this->pageheight - 30;
        }
        if ($this->yend == $this->ytabstart) {
            $this->yend = $this->ytabend;
        }
        // Hauteur du tableau = $this->ytabend - $this->ytabstart.
    }

    private function setacceptoffset() {
        if (!isset($this->filename)) {
            $this->acceptoffset = true;
        } else {
            $this->acceptoffset = true;
            foreach ($this->template as $elt) {
                if ($elt->type != "activities" && $elt->type != "text" && $elt->type != "background" &&
                    $elt->type != "period" ) {
                    if (!isset($elt->lib)) {
                        $this->acceptoffset = false;
                    }
                }
            }
        }
    }

    /**
     *
     */
    private function displaytext($text, $elt, $doc) {
        if ($elt->location->x > $this->pagewidth) {
            return;
        }
        $relicat = "";
        while ($doc->GetStringWidth($text) + $elt->location->x > $this->pagewidth) {
            $position = strrpos($text, " ");
            if ($position) {
                $relicat = substr($text, $position + 1) . " " . $relicat;
                $text = substr($text, 0, $position);
            } else {
                break;
            }
        }
        // Mark text cut.
        if ($relicat != "" && !$this->acceptoffset) {
            $text = $text . "...";
        }
        $x = $this->comput_align($elt, $doc->GetStringWidth($text));
        $doc->SetXY($x, $elt->location->y + $this->offset);
        $doc->Cell($doc->GetStringWidth($text), 0, $text, 0, 0, $elt->align, false);
        // Newline ?
        if ($this->acceptoffset && $relicat != "") {
            $this->offset = $this->offset + ($elt->font->size / 2);
            $this->displaytext($relicat, $elt, $doc);
        }
    }

    /**
     * Simul génération pdf for computation of number of page.
     * @param $doc pdf instance
     */
    private function computenbpage($doc) {
        $simulator = new simul_pdf($doc, $this->template);
        $simulator->initvalues($this->pagewidth, $this->ytabend, $this->yend, $this->pageheight,
            $this->ytabstart, $this->acceptoffset, $this->certificateinfos);
        $this->nbpage = $simulator->generate_pdf_object();
    }
}
