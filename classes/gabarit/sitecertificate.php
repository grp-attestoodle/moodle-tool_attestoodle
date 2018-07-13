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
 * Controler of attestion_form, to create template site certificate.
 * 
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Main configuration importation (instanciate the $CFG global variable).
require_once(dirname(__FILE__) . '/../../../../../config.php');

// Libraries imports.
require_once($CFG->libdir.'/pdflib.php');
require_once(dirname(__FILE__) .'/../../lib.php');
require_once('attestation_form.php');


$toolPath = dirname(__FILE__);
$context = context_system::instance();
$idTemplate = optional_param('templateid', null, PARAM_INT);

if (!isset($idTemplate)) {
	$idTemplate = 0;
} else {
	if (!$DB->record_exists('attestoodle_train_template', ['trainingid' => $idTemplate])) {
		$record = new stdClass();
		$record->trainingid = $idTemplate;
		$record->templateid = $idTemplate;
		$DB->insert_record('attestoodle_train_template', $record);
		
		$sql = "insert into {attestoodle_template_detail} (templateid,type,data) select ".$idTemplate." , type, data from {attestoodle_template_detail} where templateid = 0 ";
		$DB->execute($sql);		
	}
}

$PAGE->set_context($context);
require_login();

$PAGE->set_url(new moodle_url($toolPath . '/sitecertificate.php', [] ));
$PAGE->set_title(get_string('template_certificate', 'tool_attestoodle'));
$title = get_string('pluginname', 'tool_attestoodle') . " - " . get_string('template_certificate', 'tool_attestoodle');
$PAGE->set_heading($title);


$mform = new attestation_form();

if ($fromform = $mform->get_data()) {
	//In this case you process validated data. $mform->get_data() returns data posted in form.
	$datas = $mform->get_data();
	
	$idTemplate = $datas->templateid;
	
	if (isset($datas->cancel)) {
		$redirecturl = new \moodle_url('/admin/search.php',array());
		redirect($redirecturl);
		return;
	}
	
	$nvxTuples = array();
	if (trim($datas->filename) != '') {
		$templateDetail = new stdClass();
		$templateDetail->templateid = $datas->templateid;
		$templateDetail->type = "background";
		$valeurs = new stdClass();
		$valeurs->filename = $datas->filename;
		$templateDetail->data = json_encode($valeurs);
		$nvxTuples[] = $templateDetail;
	}
	
	if (trim($datas->learnerPosx) != '') {
		$nvxTuples[] = dataToStructure($datas->templateid, "learnername", $datas->learnerFontFamily, $datas->learnerEmphasis, $datas->learnerFontSize,
				$datas->learnerPosx, $datas->learnerPosy, $datas->learnerAlign);
	}
	
	if (trim($datas->trainingPosx) != '') {
		$nvxTuples[] = dataToStructure($datas->templateid, "trainingname", $datas->trainingFontFamily, $datas->trainingEmphasis, $datas->trainingFontSize,
				$datas->trainingPosx, $datas->trainingPosy, $datas->trainingAlign);
	}
	
	if (trim($datas->periodPosx) != '') {
		$nvxTuples[] = dataToStructure($datas->templateid, "period", $datas->periodFontFamily, $datas->periodEmphasis, $datas->periodFontSize,
				$datas->periodPosx, $datas->periodPosy, $datas->periodAlign);
	}
	
	if (trim($datas->totminutePosx) != '') {
		$nvxTuples[] = dataToStructure($datas->templateid, "totalminutes", $datas->totminuteFontFamily, $datas->totminuteEmphasis, $datas->totminuteFontSize,
				$datas->totminutePosx, $datas->totminutePosy, $datas->totminuteAlign);
	}
	if (trim($datas->activitiesPosx) != '') {
		$nvxTuples[] = dataToStructure($datas->templateid, "activities", $datas->activitiesFontFamily, $datas->activitiesEmphasis, $datas->activitiesFontSize,
				$datas->activitiesPosx, $datas->activitiesPosy, $datas->activitiesAlign);
	}
	
	$DB->delete_records('attestoodle_template_detail', array ('templateid' => $datas->templateid));
	if (count($nvxTuples) > 0) {
		foreach ($nvxTuples as $record) {
			$DB->insert_record('attestoodle_template_detail', $record);
		}
	}
	\core\notification::success(get_string('enregok', 'tool_attestoodle'));
} 
echo $OUTPUT->header();
	
$sql = "select type,data from {attestoodle_template_detail} where templateid = " . $idTemplate;
$rs = $DB->get_recordset_sql ( $sql, array () );
$valDefault = array();
	
foreach ( $rs as $result ) {
	$obj = json_decode($result->data);
		
	switch($result->type) {
		case "background" :
			$valDefault['filename'] = $obj->filename;
			break;
		case "learnername" :
			addValuesFromJson($valDefault, "learner", $obj);
			break;
		case "trainingname" :
			addValuesFromJson($valDefault, "training", $obj);
			break;
		case "period" :
			addValuesFromJson($valDefault, "period", $obj);
			break;
		case "totalminutes" :
			addValuesFromJson($valDefault, "totminute", $obj);
			break;
		case "activities" :
			addValuesFromJson($valDefault, "activities", $obj);
			break;
	}
}
$valDefault['templateid'] = $idTemplate;
	
//Set default data (if any)
$formdata =  $valDefault;
$mform->set_data($formdata);

//displays the form
$mform->display();
$previewLink = '<a target="preview" href="' . $CFG->wwwroot . '/admin/tool/attestoodle/classes/gabarit/view_export.php?templateid='.$idTemplate 
.'" class= "btn-create pull-right">'.get_string('preview', 'tool_attestoodle').'</a>';
echo $previewLink;

echo $OUTPUT->footer();


function addValuesFromJson(&$arrayValues, $prefixe, $objJson) {
	//XXX placer ces tableaux dans un seul endroit ex lib.php, actuellement ils sont définis dans 2 sources = pas bien
	$emphases = array('','B','I');
	$alignments = array('L','R','C','J');
	$sizes = array('6','7','8','9','10','11','12','13','14','15','16','18','20','22','24','26','28','32','36','40','44','48','54','60','66','72');
	$familles = array('courier', 'helvetica', 'times');
	
	$arrayValues[$prefixe . 'Posx'] = $objJson->location->x;
	$arrayValues[$prefixe . 'Posy'] = $objJson->location->y;
	$arrayValues[$prefixe . 'FontFamily'] = array_search($objJson->font->family, $familles);
	$arrayValues[$prefixe . 'Emphasis'] = array_search($objJson->font->emphasis, $emphases);
	$arrayValues[$prefixe . 'FontSize'] = array_search($objJson->font->size, $sizes);
	$arrayValues[$prefixe . 'Align'] = array_search($objJson->align, $alignments);
}
/**
 * create a table TemplateDetail row structure.
 */
function dataToStructure($dtoTemplateid, $dtoType, $dtoFontFamily, $dtoEmphasis, $dtoFontSize,
	$dtoPosx, $dtoPosy, $dtoAlign) {
//XXX placer ces tableaux dans un seul endroit ex lib.php, actuellement ils sont définis dans 2 sources = pas bien
	$emphases = array('','B','I');
	$alignments = array('L','R','C','J');
	$sizes = array('6','7','8','9','10','11','12','13','14','15','16','18','20','22','24','26','28','32','36','40','44','48','54','60','66','72');
	$familles = array('courier', 'helvetica', 'times');

	$templateDetail = new stdClass();
	$templateDetail->templateid = $dtoTemplateid;
	$templateDetail->type = $dtoType;
	
	$valeurs = new stdClass();
	$font = new stdClass();
	$font->family = $familles [$dtoFontFamily];
	$font->emphasis = $emphases [$dtoEmphasis];
	$font->size = $sizes [$dtoFontSize];
	$valeurs->font = $font;
	$location = new stdClass();
	$location->x = $dtoPosx;
	$location->y = $dtoPosy;
	$valeurs->location = $location;
	$valeurs->align = $alignments[$dtoAlign];
	$templateDetail->data = json_encode($valeurs);
	return $templateDetail;
}