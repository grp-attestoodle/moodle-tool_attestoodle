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
 * Construct test data for preview template pdf.
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Main configuration importation (instanciate the $CFG global variable).
require_once(dirname(__FILE__) . '/../../../../../config.php');
require_once(dirname(__FILE__).'/../../lib.php');

use tool_attestoodle\gabarit\attestation_pdf;

global $USER;

$idTemplate = optional_param('templateid', null, PARAM_INT);

$context = context_system::instance();
$PAGE->set_context($context);

require_login();

if (!isset($idTemplate)) {
	$idTemplate = 0;
}

// Print the page header.
$PAGE->set_url(new moodle_url(dirname(__FILE__) . '/view_export.php', [] ));
$PAGE->set_title('preview');
$PAGE->set_heading(get_string('template_certificate', 'tool_attestoodle'));


$exp = new attestation_pdf();
// Create test data 
$certificateinfos = new \stdClass();
$certificateinfos->learnername = "Jean-Claude Confucius";
$certificateinfos->trainingname = "Creation d Attestation PDF sous Moodle ";
$certificateinfos->totalminutes = 430;
$certificateinfos->period = "Du 01/07/2018 au 31/07/2018";
$activitiesstructured = array();
$activitiesstructured[15] = array(
		"totalminutes" => 30,
		"coursename" => "L API PDF de Moodle"
);
$activitiesstructured[23] = array(
		"totalminutes" => 180,
		"coursename" => "L API Forms de Moodle"
);
$activitiesstructured[2] = array(
		"totalminutes" => 120,
		"coursename" => "Le langage PHP"
);
$activitiesstructured[30] = array(
		"totalminutes" => 100,
		"coursename" => "L'API File de Moodle"
);
$certificateinfos->activities = $activitiesstructured;
//End test data

$exp->setIdTemplate($idTemplate);
$exp->setInfos($certificateinfos);

$exp->print_activity();
echo $OUTPUT->footer();