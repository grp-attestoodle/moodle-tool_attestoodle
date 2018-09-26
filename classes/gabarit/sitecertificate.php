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

use tool_attestoodle\factories\trainings_factory;


$context = context_system::instance();
$idtemplate = optional_param('templateid', null, PARAM_INT);
$trainingname = 'standard';

if (!isset($idtemplate)) {
    $template = $DB->get_record('attestoodle_template', array('name' => 'Site'));
    $idtemplate = $template->id;
    $namelock = 1;
    $previewok = true;
} else {
    $template = $DB->get_record('attestoodle_template', array('id' => $idtemplate));
    if ($template == null) {
        // New model copy of template 'Site'.
        $template = $DB->get_record('attestoodle_template', array('name' => 'Site'));
        $idtemplate = $template->id;
        $template->name = '';
        $namelock = 0;
        $previewok = false;
        $create = true;
    } else {
        if ($template->name == "Site") {
            $namelock = 1;
        } else {
            if ($DB->record_exists('attestoodle_train_template', array('templateid' => $idtemplate))) {
                $namelock = 1;
            }
        }
        $previewok = true;
    }
}

$PAGE->set_context($context);
require_login();

$PAGE->set_url(new moodle_url(dirname(__FILE__) . '/sitecertificate.php', [] ));
$PAGE->set_title(get_string('template_certificate', 'tool_attestoodle'));
$title = get_string('pluginname', 'tool_attestoodle') . " - " .
    get_string('template_certificate', 'tool_attestoodle') . " - " . $trainingname;
$PAGE->set_heading($title);


$mform = new attestation_form();

if ($fromform = $mform->get_data()) {
    // In this case you process validated data. $mform->get_data() returns data posted in form. !
    $datas = $mform->get_data();

    if (isset($datas->cancel)) {
        $redirecturl = new \moodle_url('/admin/tool/attestoodle/classes/gabarit/listtemplate.php', array());
        redirect($redirecturl);
        return;
    }

    if ($datas->name != null) {
        // Create.
        if (!$DB->record_exists('attestoodle_template', array('name' => $datas->name))) {
            $model = new stdClass();
            $model->name = $datas->name;
            $model->timecreated = usergetdate(time())[0];
            $model->userid = $USER->id;
            $created = true;
            $idtemplate = $DB->insert_record('attestoodle_template', $model);
        }
    } else {
        // Update timemodified et userid.
        $template = $DB->get_record('attestoodle_template', array('id' => $datas->templateid));
        $template->timemodified = usergetdate(time())[0];
        $template->userid = $USER->id;
        $DB->update_record('attestoodle_template', $template);
        $idtemplate = $datas->templateid;
    }
    $previewok = true;
    $nvxtuples = array();

    $backgroundexist = false;
    if ($datas->fichier) {
        file_save_draft_area_files($datas->fichier, $context->id, 'tool_attestoodle', 'fichier', $idtemplate,
            array('subdirs' => 0, 'maxbytes' => 10485760, 'maxfiles' => 1));
        // Get and save file name.
        $fs = get_file_storage();
        $arrayfile = $fs->get_directory_files($context->id, 'tool_attestoodle', 'fichier',
                      $idtemplate, '/');
        $thefile = reset($arrayfile);
        if ($thefile !== false) {
            $templatedetail = new stdClass();
            $templatedetail->templateid = $idtemplate;
            $templatedetail->type = "background";
            $valeurs = new stdClass();
            $valeurs->filename = $thefile->get_filename();
            $templatedetail->data = json_encode($valeurs);
            $nvxtuples[] = $templatedetail;
        }
        $backgroundexist = true;
    }

    if (trim($datas->learnerPosx) != '') {
        $nvxtuples[] = data_to_structure($idtemplate, "learnername", $datas->learnerFontFamily, $datas->learnerEmphasis,
                $datas->learnerFontSize, $datas->learnerPosx, $datas->learnerPosy, $datas->learnerAlign,
                $datas->learnerlib);
    }

    if (trim($datas->trainingPosx) != '') {
        $nvxtuples[] = data_to_structure($idtemplate, "trainingname", $datas->trainingFontFamily, $datas->trainingEmphasis,
                $datas->trainingFontSize, $datas->trainingPosx, $datas->trainingPosy, $datas->trainingAlign,
                $datas->traininglib);
    }

    if (trim($datas->periodPosx) != '') {
        $nvxtuples[] = data_to_structure($idtemplate, "period", $datas->periodFontFamily, $datas->periodEmphasis,
                $datas->periodFontSize, $datas->periodPosx, $datas->periodPosy, $datas->periodAlign,
                $datas->periodlib);
    }

    if (trim($datas->totminutePosx) != '') {
        $nvxtuples[] = data_to_structure($idtemplate, "totalminutes", $datas->totminuteFontFamily, $datas->totminuteEmphasis,
                $datas->totminuteFontSize, $datas->totminutePosx, $datas->totminutePosy, $datas->totminuteAlign,
                $datas->totminutelib);
    }

    if (trim($datas->activitiesPosx) != '') {
        $nvxtuples[] = data_to_structure($idtemplate, "activities", $datas->activitiesFontFamily, $datas->activitiesEmphasis,
                $datas->activitiesFontSize, $datas->activitiesPosx, $datas->activitiesPosy, $datas->activitiesAlign,
                null, $datas->activitiessize);
    }

    if (trim($datas->text1lib) != '') {
        $nvxtuples[] = data_to_structure($idtemplate, "text", $datas->text1FontFamily, $datas->text1Emphasis,
                $datas->text1FontSize, $datas->text1Posx, $datas->text1Posy, $datas->text1Align, $datas->text1lib);
    }

    if (trim($datas->text2lib) != '') {
        $nvxtuples[] = data_to_structure($idtemplate, "text", $datas->text2FontFamily, $datas->text2Emphasis,
                $datas->text2FontSize, $datas->text2Posx, $datas->text2Posy, $datas->text2Align, $datas->text2lib);
    }

    if (trim($datas->text3lib) != '') {
        $nvxtuples[] = data_to_structure($idtemplate, "text", $datas->text3FontFamily, $datas->text3Emphasis,
                $datas->text3FontSize, $datas->text3Posx, $datas->text3Posy, $datas->text3Align, $datas->text3lib);
    }

    if (trim($datas->text4lib) != '') {
        $nvxtuples[] = data_to_structure($idtemplate, "text", $datas->text4FontFamily, $datas->text4Emphasis,
                $datas->text4FontSize, $datas->text4Posx, $datas->text4Posy, $datas->text4Align, $datas->text4lib);
    }

    if (trim($datas->text5lib) != '') {
        $nvxtuples[] = data_to_structure($idtemplate, "text", $datas->text5FontFamily, $datas->text5Emphasis,
                $datas->text5FontSize, $datas->text5Posx, $datas->text5Posy, $datas->text5Align, $datas->text5lib);
    }

    // Type pagebreak.
    $nvxtuples[] = pagebreak_to_structure($idtemplate, $datas->viewpagenumber, $datas->repeatbackground,
                $datas->repeatpreactivities, $datas->repeatpostactivities, $backgroundexist);
    // Type numpage.
    if ($datas->viewpagenumber > 0) {
        $nvxtuples[] = data_to_structure($idtemplate, "pagenumber", $datas->pagenumberFontFamily, $datas->pagenumberEmphasis,
                $datas->pagenumberFontSize, $datas->pagenumberPosx, $datas->pagenumberPosy, $datas->pagenumberAlign,
                $datas->pagenumberlib, null, $datas->pagenumber_total);
    }

    $DB->delete_records('attestoodle_template_detail', array ('templateid' => $idtemplate));
    if (count($nvxtuples) > 0) {
        foreach ($nvxtuples as $record) {
            $DB->insert_record('attestoodle_template_detail', $record);
        }
    }
    \core\notification::success(get_string('enregok', 'tool_attestoodle'));
    if (isset($created)) {
        // We can't modified values of the form so we reload page.
        $redirecturl = new \moodle_url('/admin/tool/attestoodle/classes/gabarit/sitecertificate.php',
                array("templateid" => $idtemplate));
        redirect($redirecturl);
        return;
    }
}
echo $OUTPUT->header();
$sql = "select type,data from {attestoodle_template_detail} where templateid = " . $idtemplate;
$rs = $DB->get_recordset_sql ( $sql, array () );
$valdefault = array();
$nbtxt = 0;
foreach ($rs as $result) {
    $obj = json_decode($result->data);

    switch($result->type) {
        case "learnername" :
            add_values_from_json($valdefault, "learner", $obj);
            break;
        case "trainingname" :
            add_values_from_json($valdefault, "training", $obj);
            break;
        case "period" :
            add_values_from_json($valdefault, "period", $obj);
            break;
        case "totalminutes" :
            add_values_from_json($valdefault, "totminute", $obj);
            break;
        case "activities" :
            add_values_from_json($valdefault, "activities", $obj);
            break;
        case "text" :
            $nbtxt ++;
            add_values_from_json($valdefault, $result->type . $nbtxt, $obj);
            break;
        case "pagenumber" :
            add_values_pagenumber($valdefault, $obj);
            break;
        case "pagebreak" :
            $numpage = 0;
            if ($obj->numpage == 'any') {
                $numpage = 1;
            }
            if ($obj->numpage == 'always') {
                $numpage = 2;
            }
            $valdefault['viewpagenumber'] = $numpage;
            $valdefault['repeatbackground'] = $obj->repeatbackground;
            $valdefault['repeatpreactivities'] = $obj->repeatstart;
            $valdefault['repeatpostactivities'] = $obj->repeatend;
            break;
    }
}
if (!isset($create)) {
    $valdefault['templateid'] = $idtemplate;
    // Get background image.
    if (empty($entry->id)) {
        $entry = new stdClass;
        $entry->id = null;
    }
    $draftitemid = file_get_submitted_draft_itemid('fichier');
    file_prepare_draft_area($draftitemid, $context->id, 'tool_attestoodle', 'fichier', $idtemplate,
        array('subdirs' => 0, 'maxbytes' => 10485760, 'maxfiles' => 1));
    $entry->fichier = $draftitemid;
    $mform->set_data($entry);
} else {
    $valdefault['templateid'] = -1;
}
// Set default data (if any)!
$formdata = $valdefault;
$mform->set_data($formdata);
$mform->set_data(array ('namelock' => $namelock));
$mform->set_data(array ('name' => $template->name));
// Displays the form !
$mform->display();
if ($previewok) {
    $previewlink = '<a target="preview" href="' . $CFG->wwwroot .
        '/admin/tool/attestoodle/classes/gabarit/view_export.php?templateid=' . $idtemplate .
        '" class= "btn-create pull-right">'.get_string('preview', 'tool_attestoodle').'</a>';
    echo $previewlink;
}

echo $OUTPUT->footer();


function add_values_from_json(&$arrayvalues, $prefixe, $objson) {
    // TODO placer ces tableaux dans un seul endroit ex lib.php.
    $emphases = array('', 'B', 'I');
    $alignments = array('L', 'R', 'C', 'J');
    $sizes = array('6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '18', '20', '22', '24', '26', '28', '32',
        '36', '40', '44', '48', '54', '60', '66', '72');
    $familles = array('courier', 'helvetica', 'times');

    $arrayvalues[$prefixe . 'Posx'] = $objson->location->x;
    $arrayvalues[$prefixe . 'Posy'] = $objson->location->y;
    $arrayvalues[$prefixe . 'FontFamily'] = array_search($objson->font->family, $familles);
    $arrayvalues[$prefixe . 'Emphasis'] = array_search($objson->font->emphasis, $emphases);
    $arrayvalues[$prefixe . 'FontSize'] = array_search($objson->font->size, $sizes);
    $arrayvalues[$prefixe . 'Align'] = array_search($objson->align, $alignments);
    if (isset($objson->lib)) {
        $arrayvalues[$prefixe . 'lib'] = $objson->lib;
    }
    if ($prefixe === "activities" && isset($objson->size)) {
        $arrayvalues[$prefixe . 'size'] = $objson->size;
    }
}
/**
 * Uses the pagenumber json record as a form value.
 * @param $arrayvalues form values array.
 * @param $objson object json to treat.
 */
function add_values_pagenumber(&$arrayvalues, $objson) {
    add_values_from_json($arrayvalues, 'pagenumber', $objson);
    $arrayvalues['pagenumber_total'] = $objson->ontotal;
}
function pagebreak_to_structure($dtotemplateid, $dtoviewpagenum, $dtorepeatbackgr, $dtorepeatstart,
 $dtorepeatend, $backgroundexist) {
    $templatedetail = new stdClass();
    $templatedetail->templateid = $dtotemplateid;
    $templatedetail->type = 'pagebreak';
    $valeurs = new stdClass();
    $numpage = 'never';
    if ($dtoviewpagenum == 1) {
        $numpage = 'any';
    }
    if ($dtoviewpagenum == 2) {
        $numpage = 'always';
    }
    $valeurs->numpage = $numpage;
    $repeatbackground = false;
    if (isset($dtorepeatbackgr) && $backgroundexist) {
        $repeatbackground = true;
    }
    $valeurs->repeatbackground = $repeatbackground;
    $repeatstart = false;
    if ($dtorepeatstart == 1) {
        $repeatstart = true;
    }
    $valeurs->repeatstart = $repeatstart;
    $repeatend = false;
    if ($dtorepeatend == 1) {
        $repeatend = true;
    }
    $valeurs->repeatend = $repeatend;
    $templatedetail->data = json_encode($valeurs);
    return $templatedetail;
}
/**
 * create a table TemplateDetail row structure for save into database.
 */
function data_to_structure($dtotemplateid, $dtotype, $dtofontfamily, $dtoemphasis, $dtofontsize, $dtoposx,
    $dtoposy, $dtoalign, $dtolib = null, $dtosize = null, $dtoontotal = null) {
    $emphases = array('', 'B', 'I');
    $alignments = array('L', 'R', 'C', 'J');
    $sizes = array('6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '18', '20', '22', '24', '26', '28', '32',
        '36', '40', '44', '48', '54', '60', '66', '72');
    $familles = array('courier', 'helvetica', 'times');

    $templatedetail = new stdClass();
    $templatedetail->templateid = $dtotemplateid;
    $templatedetail->type = $dtotype;

    $valeurs = new stdClass();
    $font = new stdClass();
    $font->family = $familles [$dtofontfamily];
    $font->emphasis = $emphases [$dtoemphasis];
    $font->size = $sizes [$dtofontsize];
    $valeurs->font = $font;
    $location = new stdClass();
    $location->x = $dtoposx;
    $location->y = $dtoposy;
    $valeurs->location = $location;
    $valeurs->align = $alignments[$dtoalign];
    if ($dtolib != null) {
        $valeurs->lib = $dtolib;
    }
    if ($dtosize != null) {
        $valeurs->size = $dtosize;
    }
    if ($dtotype == 'pagenumber') {
        if ($dtoontotal != null) {
            $valeurs->ontotal = true;
        } else {
            $valeurs->ontotal = false;
        }
    }
    $templatedetail->data = json_encode($valeurs);
    return $templatedetail;
}