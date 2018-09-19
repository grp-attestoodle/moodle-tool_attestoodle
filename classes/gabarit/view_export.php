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
use tool_attestoodle\factories\trainings_factory;


global $USER, $DB;

$idtemplate = optional_param('templateid', null, PARAM_INT);
$idtraining = optional_param('trainingid', null, PARAM_INT);
$test = optional_param('test', null, PARAM_ALPHA);

$context = context_system::instance();
$PAGE->set_context($context);

require_login();

if (!isset($idtemplate)) {
    $idtemplate = 1;
}

// Print the page header.
$PAGE->set_url(new moodle_url(dirname(__FILE__) . '/view_export.php', [] ));
$PAGE->set_title('preview');
$PAGE->set_heading(get_string('template_certificate', 'tool_attestoodle'));

$exp = new attestation_pdf();
// Create test data !
if (isset($test)) {
    $certificateinfos = create_debordement_test();
} else {
    $certificateinfos = create_standard_test($idtraining);
}
// End test data !

$exp->set_idtemplate($idtemplate);
$exp->set_infos($certificateinfos);
$exp->print_activity();
echo $OUTPUT->footer();

function create_standard_test($idtraining) {
    global $DB;
    $certificateinfos = new \stdClass();
    $certificateinfos->learnername = "Jean-Claude Confucius";
    if (isset($idtraining)) {
        trainings_factory::get_instance()->create_trainings();
        $idcategory = $DB->get_field('attestoodle_training', 'categoryid', ['id' => $idtraining]);
        $training = trainings_factory::get_instance()->retrieve_training($idcategory);
        if ($training != null) {
            $certificateinfos->trainingname = $training->get_name();
        }
    }
    if (!isset($certificateinfos->trainingname)) {
        $certificateinfos->trainingname = "Creation d Attestation PDF sous Moodle ";
    }

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
    return $certificateinfos;
}

function create_debordement_test() {
    $certificateinfos = new \stdClass();
    $certificateinfos->trainingname = "UN TITRE DE FORMATION EXTREMEMENT LONG POUR DECLENCHER UN CHANGEMENT " .
        "DE LIGNE VOIR PLUSIEURS RETOURS A LA LIGNE EN ESPERANT QUE CELA SUFFISE";
    $certificateinfos->learnername = "Jean-Claude Confucius";
    $certificateinfos->totalminutes = 530;
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
    $activitiesstructured[3] = array(
        "totalminutes" => 10,
        "coursename" => "Un titre de cours acces long pour nécessiter un saut de ligne."
        );
    $activitiesstructured[2] = array(
        "totalminutes" => 120,
        "coursename" => "Le langage PHP"
        );
    $activitiesstructured[7] = array(
        "totalminutes" => 5,
        "coursename" => "Un cours avec un titre dont on ne voit pas la fin (il est tres long n est ce pas)"
        );
    $activitiesstructured[30] = array(
        "totalminutes" => 100,
        "coursename" => "L'API File de Moodle"
        );
    $activitiesstructured[31] = array(
        "totalminutes" => 10,
        "coursename" => "Nouveau cours pour déclencher saut de page"
        );
    $activitiesstructured[32] = array(
        "totalminutes" => 10,
        "coursename" => "Un autre cours"
        );
    $activitiesstructured[33] = array(
        "totalminutes" => 10,
        "coursename" => "Encore un autre cours"
        );
    $activitiesstructured[34] = array(
        "totalminutes" => 5,
        "coursename" => "Mathématique"
        );
    $activitiesstructured[35] = array(
        "totalminutes" => 20,
        "coursename" => "L3 - Littérature et cinéma"
        );
    $activitiesstructured[36] = array(
        "totalminutes" => 20,
        "coursename" => "LANSAD L1 Anglais (Histoire et Geographie) - Luke Stewart"
        );
    $activitiesstructured[37] = array(
        "totalminutes" => 10,
        "coursename" => "13 cours nombre limite pour mode paysage"
        );
    $activitiesstructured[38] = array(
        "totalminutes" => 10,
        "coursename" => "14 ieme cours limite atteinte ?"
        );
    $activitiesstructured[39] = array(
        "totalminutes" => 10,
        "coursename" => "15 ieme cours limite dépassée"
        );
    $certificateinfos->activities = $activitiesstructured;
    return $certificateinfos;
}