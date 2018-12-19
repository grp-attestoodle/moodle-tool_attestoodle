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
 * pdf simulation utility file.
 * @package tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
namespace tool_attestoodle\gabarit;
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/lib/pdflib.php");

/**
 * Simul a pdf for compute nb page required.
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
class simul_pdf extends attestation_pdf {
    /**
     * Use the template of attestation_pdf for compute
     * the number of page necessary.
     * @param pdfObject $doc required for compute text size.
     * @param jsonobject[] $template design the background image, the position of the elements and so on.
     */
    public function __construct($doc, $template) {
        $this->pdf = $doc;
        $this->template = $template;
    }
    /**
     * Set values with the same values of the origin.
     * @param stdClass $pageparam contains the dimensions of the page and some benchmarks.
     * @param boolean $acceptoffset indicates if the offsets are accepted by the model.
     * @param stdClass $infos Structure of data to print on PDF.
     */
    public function initvalues($pageparam, $acceptoffset, $infos) {
        $this->pageparam = $pageparam;
        $this->acceptoffset = $acceptoffset;
        $this->certificateinfos = $infos;
    }

    /**
     * Methods that create the virtual PDF file which can be "print" on an
     * actual PDF file within moodledata
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
                $text = $this->handle_type($elt->type);
                if (isset($elt->lib) && $elt->type != "pagenumber") {
                    $text = $elt->lib . $text;
                }
                $text = trim($text);
                $this->displaytext($text, $elt);
            }
        }
        return $this->nbpage;
    }

    /**
     * Calculates the number of rows and pages needed to display the activity table.
     * @param stdClass $model contains informations to place activities, only
     * the array is concerned
     * @param stdClass[] $tabactivities the data to place the activities.
     */
    private function printactivities($model, $tabactivities) {
        $width = $this->computewidth($model);
        $x = $this->comput_align($model, $width);
        if ($x + 80 > $this->pageparam->pagewidth) {
            $x = $this->pageparam->pagewidth - 80;
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
            if ($y - $this->pageparam->ytabend + $this->pageparam->yend + 10 > $this->pageparam->pageheight) {
                if ($y < $this->pageparam->ytabend) {
                    $this->offset = 0;
                } else {
                    $this->offset = $y - $this->pageparam->ytabend;
                }
                $this->nbpage ++;
                $y = $this->pageparam->ytabstart;
                $ystart = $y;
                $y = $y + ($heightline * 1.5);
            }
            $coursename = $course["coursename"];
            // Activity type.
            $nbnewline = $this->displayactivity($widthfirstcolumn - 6, $lineheight, $coursename);
            // Activity total hours.
            $y += $lineheight + ($nbnewline - 1) * $lineheight / 2;
        }
        // Compute offset.
        if ($y < $this->pageparam->ytabend) {
            $this->offset = 0;
        } else {
            $this->offset = $y - $this->pageparam->ytabend;
        }
    }

    /**
     * Compute the space need to show an activity in the table.
     * @param integer $widthcolumn the width of the column.
     * @param integer $lineheight the height of one line.
     * @param string $text to print.
     */
    private function displayactivity($widthcolumn, $lineheight, $text) {
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

    /**
     * Just for compute the offset.
     * @param string $text to display.
     * @param stdClass $elt contains display settings.
     */
    private function displaytext($text, $elt) {
        if ($elt->location->x > $this->pageparam->pagewidth) {
            return;
        }
        $relicat = "";
        while ($this->pdf->GetStringWidth($text) + $elt->location->x > $this->pageparam->pagewidth) {
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
        if ($this->acceptoffset && $relicat != "") {
            $this->offset = $this->offset + ($elt->font->size / 2);
            $this->displaytext($relicat, $elt);
        }
    }
}
