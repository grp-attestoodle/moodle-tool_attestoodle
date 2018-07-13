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
	protected $certificateInfos;
	/** the width of the Page, orientation landscape or portrait change the width.*/ 
	protected $pageWidth = 0;
	
	/**
	 * Set the information to print.
	 * @param \stdClass $info A standard class object containing the following to print provide
	 * by methode certificate:get_pdf_informations(). 
	 */
	public function setInfos($infos) {
		$this->certificateInfos = $infos;
	}
	
	/**
	 * Extract informations of the template use to print.
	 */
	public function setIdTemplate($idTemplate) {
		global $DB;
		$sql = "select type,data from {attestoodle_template_detail} where templateid = ".$idTemplate;
		$rs = $DB->get_recordset_sql ( $sql, array () );
		
		$this->template = array();
		
		foreach ( $rs as $result ) {
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
		$doc = $this->generatePdfObject();
		$doc->Output('preview.pdf', 'I');
		exit;
	}
	
	/**
	 * Methods that create the virtual PDF file which can be "print" on an
	 * actual PDF file within moodledata
	 *
	 * @todo translations
	 *
	 * @return \pdf The virtual pdf file using the moodle pdf class
	 */
	public function generatePdfObject() {
		global $CFG;
		
		$orientation = 'L';
		if (isset($this->filename)) {
			$taille = getimagesize("$CFG->dirroot/admin/tool/attestoodle/pix/" . $this->filename);
			$this->pageWidth = 297;
			$this->pageHeight = 210;
		
			if ($taille[1] > $taille[0]) {
				$orientation = 'P';
				$this->pageWidth = 210;
				$this->pageHeight = 297;
			}
		}
		
		$doc = new \pdf($orientation);
		$doc->setPrintHeader(false);
		$doc->setPrintFooter(false);
		$doc->SetAutoPagebreak(false);
		$doc->SetMargins(0, 0, 0);
		$doc->AddPage();
		
		if (isset($this->filename)) {
			$doc->Image("$CFG->dirroot/admin/tool/attestoodle/pix/" . $this->filename, '0', '0', $this->pageWidth, $this->pageHeight, 'png', '', true);
		}

		foreach ($this->template as $elt) {
			$doc->SetFont($elt->font->family, $elt->font->emphasis, $elt->font->size);
			$doc->SetXY($elt->location->x, $elt->location->y);
		
			switch ($elt->type) {
				case "learnername" :
					if (isset($this->certificateInfos->learnername)) {
						$doc->Cell($doc->GetStringWidth($this->certificateInfos->learnername), 0, $this->certificateInfos->learnername, 0, 0, $elt->align ,false);
					}
					break;
				case "trainingname" :
					if (isset($this->certificateInfos->trainingname)) {
						$doc->Cell($doc->GetStringWidth($this->certificateInfos->trainingname), 0, $this->certificateInfos->trainingname, 0, 0, $elt->align ,false);
					}
					break;
				case "period" :
					if (isset($this->certificateInfos->period)) {
						$doc->Cell($doc->GetStringWidth($this->certificateInfos->period), 0, $this->certificateInfos->period, 0, 0, $elt->align ,false);
					}
					break;
				case "totalminutes" :
					if (isset($this->certificateInfos->totalminutes)) {
						$texte = parse_minutes_to_hours($this->certificateInfos->totalminutes);
						$doc->Cell($doc->GetStringWidth($texte), 0, $texte, 0, 0, $elt->align ,false);
					}
					break;
				case "activities" :
					if (isset($this->certificateInfos->activities)) {
						$this->printActivities($doc, $elt, $this->certificateInfos->activities);
					}
					break;
			}
		}
		return $doc;
	}
	
	/**
	 * Display array of activities on pdf document.
	 * @param $pdf the document pdf where we place activities
	 * @param $model contains informations to place activities, only
	 * the array is concerned (elements of array are relative to the array's corner top left)
	 * @param $tabActivities the data to place (the activities)
	 */
	private function printActivities($pdf, $model, $tabActivities) {
		$x = intval($model->location->x);
		$y = intval($model->location->y);
		$pdf->SetLineWidth(0.1);
		$heightLine = intval($model->font->size);
		if ($heightLine == 0) {
			$heightLine = 10;
		}
		$height = count($tabActivities) * $heightLine + ($heightLine * 1.5);
		$width = ($this->pageWidth - $x * 2);
		// Main borders.
		$pdf->Rect($x, $y, $width, $height, "D");
		
		// Header border.
		$pdf->Line($x, $y + ($heightLine * 1.5), $x + $width , $y + ($heightLine * 1.5));
		
		// Columns.
		$widthFirstColumn = $width * .75;
		$pdf->Line($widthFirstColumn + $x, $y, $widthFirstColumn + $x, $height + $y);
		
		// Column title course.
		$pdf->SetFont($model->font->family, 'B', $model->font->size);
		$pdf->SetXY($x + 5, $y + 5);
		$pdf->Cell($widthFirstColumn - 10, 0, "Cours Suivis", 0, 0, 'C' ,false);
		// Column title "total hours".
		$pdf->SetXY($x + $widthFirstColumn + 5, $y + 5);
		$widthSecondColumn = $width - $widthFirstColumn; 
		$pdf->Cell($widthSecondColumn - 10, 0, "Total heures", 0, 0, 'C', false);
		
		
		// Activities lines.
		$pdf->SetFont($model->font->family, '', $model->font->size);
		
		$lineheight = $model->font->size;
		$y = $y + ($heightLine * 1.5);
		
		foreach ($tabActivities as $course) {
			$coursename = $course["coursename"];
			$totalminutes = $course["totalminutes"];
			$pdf->SetXY($x + 5, $y);
			// Activity type.
			$pdf->Cell($widthFirstColumn - 10, $lineheight, $coursename, 0, 0, 'L');
			// Activity total hours.
			$pdf->SetXY($x + $widthFirstColumn + 5, $y);
			$pdf->Cell($widthSecondColumn - 10, $lineheight, parse_minutes_to_hours($totalminutes), 0, 0, 'C');
			$y += $lineheight;
			$pdf->Line($x, $y, $x + $width , $y);
		}
		
	}
}