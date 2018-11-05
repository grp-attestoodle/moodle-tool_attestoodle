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
use tool_attestoodle\gabarit\simul_pdf;

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
    /**
     * struct contains :
     * pagewidth the width of the Page, orientation landscape or portrait change the width.
     * pageheight the height of the page
     * ytabstart start of table activities
     * ytabend end of table activities
     * yend the last element in the page.
     */
    protected $pageparam;

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

    protected $groupe1 = "coursename";
    protected $groupe2 = "";

    /**
     * Set the information to print.
     * @param \stdClass $info A standard class object containing the following to print provide
     * by methode certificate:get_pdf_informations().
     */
    public function set_infos($infos) {
        $this->certificateinfos = $infos;
        $activities = $this->regroup($infos->activities);
        $this->certificateinfos->activities = $activities;
    }

    public function set_categoryid($categoryid) {
        global $DB;
        $idtraining = $DB->get_field('attestoodle_training', 'id', ['categoryid' => $categoryid]);
        $associate = $DB->get_record('attestoodle_train_template', array('trainingid' => $idtraining));

        $this->set_idtemplate($associate->templateid);
        $this->set_grpcriteria1($associate->grpcriteria1);
        $this->set_grpcriteria2($associate->grpcriteria2);
    }
    public function set_grpcriteria1($criteria) {
        if (empty($criteria)) {
            $this->groupe1 = "coursename";
        } else {
            $this->groupe1 = $criteria;
        }
    }

    public function set_grpcriteria2($criteria) {
        if (empty($criteria)) {
            $this->groupe2 = "";
        } else {
            $this->groupe2 = $criteria;
        }
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
     * Sort $this->template on 'y' ASC.
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
                $text = $this->handle_type($elt->type);
                if ($elt->type === "pagenumber") {
                    $text = $this->handle_pagenumber($elt);
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
            $text = $this->handle_type($elt->type);
            if (isset($elt->lib)) {
                $text = $elt->lib . $text;
            }
            $text = trim($text);
            $this->displaytext($text, $elt, $pdf);
        }
    }

    /**
     * Display data after table activities.
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
            $text = $this->handle_type($elt->type);
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
    protected function comput_align($elt, $widthtext) {
        $x = 0;
        $pagewidth = $this->pageparam->pagewidth;
        switch ($elt->align) {
            case 'L' :
                $x = $elt->location->x;
                break;
            case 'R' :
                $x = $pagewidth - $elt->location->x - $widthtext;
                break;
            case 'C' :
                $x = ($pagewidth - $elt->location->x - $widthtext) / 2 +
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
        $this->pageparam = new \stdClass();
        $this->pageparam->pagewidth = 210;
        $this->pageparam->pageheight = 297;

        if (isset($this->filename)) {
            // Set orientation width the size of image.
            $taille = getimagesize($this->file);

            if ($taille[0] > $taille[1]) {
                $orientation = 'L';
                $this->pageparam->pagewidth = 297;
                $this->pageparam->pageheight = 210;
            }
        }

        $doc = new \pdf($orientation);
        $doc->setPrintHeader(false);
        $doc->setPrintFooter(false);
        $doc->SetAutoPagebreak(false);
        $doc->SetMargins(0, 0, 0);
        $doc->AddPage();

        if (isset($this->filename)) {
            $doc->Image($this->file, 0, 0, $this->pageparam->pagewidth, $this->pageparam->pageheight,
            'png', '', true);
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
        $width = $this->computewidth($model);
        $widthfirstcolumn = $width * .75;
        $widthsecondcolumn = $width - $widthfirstcolumn;
        $x = $this->comput_align($model, $width);
        if ($x + 80 > $this->pageparam->pagewidth) {
            $x = $this->pageparam->pagewidth - 80;
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
        // Column title course.
        $this->displaytitlecolumn($pdf, $model, $x, $y, $widthfirstcolumn, $widthsecondcolumn);
        // Activities lines.
        $lineheight = $model->font->size;
        $y = $y + ($heightline * 1.5);
        $pdf->Line($widthfirstcolumn + $x, $ystart, $widthfirstcolumn + $x, $y);

        foreach ($tabactivities as $course) {
            // Page break needed ?
            if ($y - $this->pageparam->ytabend + $this->pageparam->yend + 10 > $this->pageparam->pageheight) {
                $pdf->Rect($x, $ystart, $width, $y - $ystart, "D");
                if ($y < $this->pageparam->ytabend) {
                    $this->offset = 0;
                } else {
                    $this->offset = $y - $this->pageparam->ytabend;
                }
                $this->displayaftertactivities($pdf, $model);
                $this->displaypagenumber($pdf);

                $pdf->AddPage();
                $this->currentpage ++;
                if (isset($this->filename) && $this->repeatbackground) {
                    $pdf->Image($this->file, 0, 0, $this->pageparam->pagewidth, $this->pageparam->pageheight,
                    'png', '', true);
                }
                $this->offset = 0;
                $this->displaybeforeactivities($pdf, $model);
                $y = $this->pageparam->ytabstart;
                $ystart = $y;

                $pdf->Line($x, $y + ($heightline * 1.5), $x + $width , $y + ($heightline * 1.5));
                $this->displaytitlecolumn($pdf, $model, $x, $y, $widthfirstcolumn, $widthsecondcolumn);

                $y = $y + ($heightline * 1.5);
                $pdf->Line($widthfirstcolumn + $x, $ystart, $widthfirstcolumn + $x, $y);
            }
            $nbnewline = $this->displaydetail($pdf, $model, $course, $x, $y , $widthfirstcolumn, $lineheight, $widthsecondcolumn);
            $y += $lineheight + ($nbnewline - 1) * $lineheight / 2;
            $pdf->Line($x, $y, $x + $width , $y);
        }
        $pdf->Rect($x, $ystart, $width, $y - $ystart, "D");
        // Compute offset.
        if ($y < $this->pageparam->ytabend) {
            $this->offset = 0;
        } else {
            $this->offset = $y - $this->pageparam->ytabend;
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
        $this->pageparam->yend = 0;
        $this->pageparam->ytabstart = 0;
        foreach ($this->template as $elt) {
            if ($elt->type == "activities") {
                $this->pageparam->ytabstart = $elt->location->y;
            }
            $this->pageparam->yend = $elt->location->y;
        }
        $this->pageparam->ytabend = -1;
        foreach ($this->template as $elt) {
            if ($elt->location->y > $this->pageparam->ytabstart && $this->pageparam->ytabend == -1) {
                $this->pageparam->ytabend = $elt->location->y;
            }
        }
        if ($this->pageparam->ytabend == -1) {
            $this->pageparam->ytabend = $this->pageparam->pageheight - 30;
        }
        if ($this->pageparam->yend == $this->pageparam->ytabstart) {
            $this->pageparam->yend = $this->pageparam->ytabend;
        }
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
        if ($elt->location->x > $this->pageparam->pagewidth) {
            return;
        }
        $relicat = "";
        while ($doc->GetStringWidth($text) + $elt->location->x > $this->pageparam->pagewidth) {
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
        $simulator->initvalues($this->pageparam, $this->acceptoffset, $this->certificateinfos);
        $this->nbpage = $simulator->generate_pdf_object();
    }

    protected function regroup($tabactivities) {
        $tabreturn = array();
        foreach ($tabactivities as $act) {
            $subtab = array();
            $discrim1 = $act[$this->groupe1];
            $tot1 = 0;
            foreach ($tabactivities as $key => $subact) {
                if ($subact[$this->groupe1] == $discrim1) {
                    $tot1 += $subact["totalminutes"];
                    $subtab[] = $subact;
                    unset($tabactivities[$key]);
                }
            }

            if (count($subtab) > 0 && empty($this->groupe2) == false) {
                // Subgroup processing.
                $tabcomput = array();
                foreach ($subtab as $subact) {
                    $val = $subact[$this->groupe2];
                    if (!array_key_exists($val, $tabcomput)) {
                        $tabcomput[$val] = array(
                            "totalminutes" => 0,
                            "coursename" => $val
                            );
                    }
                    // Increment total minutes for the course id in the training.
                    $tabcomput[$val]["totalminutes"] += $subact["totalminutes"];
                }
                // Add criteria1 with totalminutes = -1.
                $tabreturn[] = array ("totalminutes" => -1, "coursename" => $discrim1);
                $tabreturn = array_merge($tabreturn, $tabcomput);
            }

            if (empty($this->groupe2) && $tot1 > 0) {
                $tabreturn[] = array ("totalminutes" => $tot1, "coursename" => $discrim1);
            }
        }
        return $tabreturn;
    }
    /**
     * Defines the text according to the type value.
     */
    protected function handle_type($type) {
        $text = "";
        switch ($type) {
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
        }
        return $text;
    }

    protected function handle_pagenumber($elt) {
        $text = "";
        if ($this->numpage != 'never') {
            if ($this->nbpage > 1 || $this->numpage == 'always') {
                $text = $this->nbpage;
                if ($elt->ontotal) {
                    $text = $text . " / " . $this->nbpage;
                }
                if (isset($elt->lib)) {
                    $text = $elt->lib . $text;
                }
            }
        }
        return $text;
    }
    protected function computewidth($model) {
        $width = 0;
        $minwidth = 80;
        if (isset($model->size)) {
            $width = $model->size;
        }

        if ($width == 0) {
            $width = ($this->pageparam->pagewidth - $model->location->x * 2);
        }

        // Force minimum width of activities table.
        if ($width < $minwidth) {
            $width = $minwidth;
        }
        return $width;
    }
    protected function displaytitlecolumn($pdf, $model, $x, $y, $widthfirstcolumn, $widthsecondcolumn) {
        $pdf->SetFont($model->font->family, 'B', $model->font->size);
        $pdf->SetXY($x + 5, $y + 5);
        $pdf->Cell($widthfirstcolumn - 10, 0, get_string('activity_header_' . $this->groupe1 .
                                    "_" . $this->groupe2, 'tool_attestoodle'), 0, 0, 'C', false);
        // Column title "total hours".
        $pdf->SetXY($x + $widthfirstcolumn + 5, $y + 5);
        $pdf->Cell($widthsecondcolumn - 10, 0, get_string('activity_header_col_2', 'tool_attestoodle'), 0, 0, 'C', false);
        $pdf->SetFont($model->font->family, '', $model->font->size);
    }
    private function displaydetail($pdf, $model, $course, $x, $y , $widthfirstcolumn, $lineheight, $widthsecondcolumn) {
        $nbnewline = 0;
        $coursename = $course["coursename"];
        $totalminutes = $course["totalminutes"];
        // Activity type.
        if ($totalminutes != -1) {
            $nbnewline = $this->displayactivity($pdf, $x + 3, $y, $widthfirstcolumn - 6, $lineheight, $coursename);
        } else {
            $pdf->SetFont($model->font->family, 'B', $model->font->size);
            $nbnewline = $this->displayactivity($pdf, $x + 1, $y, $widthfirstcolumn - 6, $lineheight, $coursename);
            $pdf->SetFont($model->font->family, '', $model->font->size);
        }
        // Activity total hours.
        if ($totalminutes != -1) {
            $pdf->SetXY($x + $widthfirstcolumn + 5, $y);
            $pdf->Cell($widthsecondcolumn - 10, $lineheight, parse_minutes_to_hours($totalminutes), 0, 0, 'C');
            $pdf->Line($widthfirstcolumn + $x, $y, $widthfirstcolumn + $x,
                            $y + $lineheight + ($nbnewline - 1) * $lineheight / 2);
        }
        return $nbnewline;
    }
}
