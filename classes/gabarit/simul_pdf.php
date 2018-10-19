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
 * Simul a pdf for compute nb page required.
 *
 * @package tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
class simul_pdf {
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

    protected $nbpage;
    protected $pdf;

    public function __construct($doc, $template) {
        $this->pdf = $doc;
        $this->template = $template;
    }
    public function initvalues($pagewidth, $ytabend, $yend, $pageheight, $ytabstart, $acceptoffset, $infos) {
        $this->pagewidth = $pagewidth;
        $this->ytabend = $ytabend;
        $this->yend = $yend;
        $this->pageheight = $pageheight;
        $this->ytabstart = $ytabstart;
        $this->acceptoffset = $acceptoffset;
        $this->certificateinfos = $infos;
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
        $this->nbpage = 1;
        foreach ($this->template as $elt) {
            $this->pdf->SetFont($elt->font->family, $elt->font->emphasis, $elt->font->size);

            if ($elt->type == "activities" && isset($this->certificateinfos->activities)) {
                $this->printactivities($elt, $this->certificateinfos->activities);
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
                }
                if (isset($elt->lib)) {
                    $text = $elt->lib . $text;
                }
                $text = trim($text);
                $this->displaytext($text, $elt);
            }
        }
        return $this->nbpage;
    }

    private function printactivities($model, $tabactivities) {
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
        $heightline = intval($model->font->size);
        if ($heightline == 0) {
            $heightline = 10;
        }
        // Main borders.
        $ystart = $y;
        // Columns.
        $widthfirstcolumn = $width * .75;
        // Column title course.
        $this->pdf->SetFont($model->font->family, 'B', $model->font->size);
        // Activities lines.
        $this->pdf->SetFont($model->font->family, '', $model->font->size);
        $lineheight = $model->font->size;
        $y = $y + ($heightline * 1.5);
        foreach ($tabactivities as $course) {
            // Test rupture page nécessaire ?
            if ($y - $this->ytabend + $this->yend + 10 > $this->pageheight) {
                if ($y < $this->ytabend) {
                    $this->offset = 0;
                } else {
                    $this->offset = $y - $this->ytabend;
                }
                $this->nbpage ++;
                $y = $this->ytabstart;
                $ystart = $y;
                $y = $y + ($heightline * 1.5);
            }
            $coursename = $course["coursename"];
            // Activity type.
            $nbnewline = $this->displayactivity($x + 3, $y, $widthfirstcolumn - 6, $lineheight, $coursename);
            // Activity total hours.
            $y += $lineheight + ($nbnewline - 1) * $lineheight / 2;
        }
        // Compute offset.
        if ($y < $this->ytabend) {
            $this->offset = 0;
        } else {
            $this->offset = $y - $this->ytabend;
        }
    }

    private function displayactivity($x, $y, $widthcolumn, $lineheight, $text) {
        $nbsaut = 0;
        $offsettab = 0;
        while ($nbsaut < 5) {
            $nbsaut++;
            $relicat = "";
            while ($this->pdf->GetStringWidth($text) > $widthcolumn) {
                $position = strrpos($text, " ");
                if ($position) {
                    $relicat = substr($text, $position + 1) . " " . $relicat;
                    $text = substr($text, 0, $position);
                } else {
                    break;
                }
            }
            if ($relicat == "") {
                return $nbsaut;
            }
            $text = $relicat;
            $offsettab = $offsettab + $lineheight / 2;
        }
        return $nbsaut;
    }

    private function displaytext($text, $elt) {
        if ($elt->location->x > $this->pagewidth) {
            return;
        }
        $relicat = "";
        while ($this->pdf->GetStringWidth($text) + $elt->location->x > $this->pagewidth) {
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
        $x = $this->comput_align($elt, $this->pdf->GetStringWidth($text));
        // Newline ?
        if ($this->acceptoffset && $relicat != "") {
            $this->offset = $this->offset + ($elt->font->size / 2);
            $this->displaytext($relicat, $elt);
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
}
