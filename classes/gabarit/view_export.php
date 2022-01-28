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
$criteria1 = optional_param('criteria1', null, PARAM_ALPHA);
$criteria2 = optional_param('criteria2', null, PARAM_ALPHA);

$context = context_system::instance();
$PAGE->set_context($context);

require_login();

if (!isset($idtemplate)) {
    $idtemplate = 1;
}

// Print the page header.
$PAGE->set_url(new moodle_url('/admin/tool/attestoodle/classes/gabarit/view_export.php', [] ));
$PAGE->set_title('preview');
$PAGE->set_heading(get_string('template_certificate', 'tool_attestoodle'));

$exp = new attestation_pdf();
// Create test data !
$certificateinfos = create_debordement_test();
// End test data !

$exp->set_idtemplate($idtemplate);
if (!empty($criteria1)) {
    $exp->set_grpcriteria1($criteria1);
    if (!empty($criteria2)) {
        $exp->set_grpcriteria2($criteria2);
    }
}

if (isset($idtraining)) {
    trainings_factory::get_instance()->create_trainings();
    $idcategory = $DB->get_field('tool_attestoodle_training', 'categoryid', ['id' => $idtraining]);
    $training = trainings_factory::get_instance()->retrieve_training($idcategory);
    if ($training != null) {
        $certificateinfos->trainingname = $training->get_name();
    }
    $exp->set_trainingid($idtraining);
}

$exp->set_infos($certificateinfos);
$exp->print_activity();

/**
 * creating a test set to trigger overflows.
 */
function create_debordement_test() {
    $certificateinfos = new \stdClass();
    $certificateinfos->trainingname = "UN TITRE DE FORMATION EXTREMEMENT LONG POUR DECLENCHER UN CHANGEMENT " .
        "DE LIGNE VOIR PLUSIEURS RETOURS A LA LIGNE EN ESPERANT QUE CELA SUFFISE";
    $certificateinfos->learnername = "MyFirstname MyLastname";
    $certificateinfos->totalminutes = 550;
    $certificateinfos->cumulminutes = 950;
    $certificateinfos->period = "Du 01/07/2018 au 31/07/2018";
    $activitiesstructured = array();
    $activitiesstructured[15] = array(
        "totalminutes" => 30,
        "coursename" => "L API PDF de Moodle",
        "name" => "devoir 1",
        "type" => "assign");
    $activitiesstructured[23] = array(
        "totalminutes" => 180,
        "coursename" => "L API Forms de Moodle",
        "name" => "Evaluation",
        "type" => "quiz");
    $activitiesstructured[3] = array(
        "totalminutes" => 10,
        "coursename" => "Un titre de cours acces long pour nécessiter un saut de ligne.",
        "name" => "redaction",
        "type" => "journal");
    $activitiesstructured[2] = array(
        "totalminutes" => 120,
        "coursename" => "Le langage PHP",
        "name" => "Ecrire un programme hello world",
        "type" => "assign");
    $activitiesstructured[7] = array(
        "totalminutes" => 5,
        "coursename" => "Un cours avec un titre dont on ne voit pas la fin (il est tres long n est ce pas)",
        "name" => "Chapitre 2",
        "type" => "label");
    $activitiesstructured[30] = array(
        "totalminutes" => 100,
        "coursename" => "L'API File de Moodle",
        "name" => "Evaluation 1",
        "type" => "quiz");
    $activitiesstructured[31] = array(
        "totalminutes" => 10,
        "coursename" => "L'API File de Moodle",
        "name" => "Evaluation 2",
        "type" => "quiz");
    $activitiesstructured[32] = array(
        "totalminutes" => 10,
        "coursename" => "L'API File de Moodle",
        "name" => "Fichiers et formulaire",
        "type" => "label");
    $activitiesstructured[33] = array(
        "totalminutes" => 10,
        "coursename" => "L API Forms de Moodle",
        "name" => "Ajouter les définitions des termes vu dans le cours",
        "type" => "wiki");
    $activitiesstructured[34] = array(
        "totalminutes" => 5,
        "coursename" => "Mathématique",
        "name" => "la logique floue",
        "type" => "lesson");
    $activitiesstructured[35] = array(
        "totalminutes" => 20,
        "coursename" => "L API PDF de Moodle",
        "name" => "devoir 2",
        "type" => "quiz");
    $activitiesstructured[36] = array(
        "totalminutes" => 20,
        "coursename" => "L API PDF de Moodle",
        "name" => "PDF et géométrie",
        "type" => "assign");
    $activitiesstructured[37] = array(
        "totalminutes" => 10,
        "coursename" => "L API PDF de Moodle",
        "name" => "Changement de page",
        "type" => "assign");
    $activitiesstructured[38] = array(
        "totalminutes" => 10,
        "coursename" => "L'API File de Moodle",
        "name" => "Evaluation finale",
        "type" => "quiz");
    $activitiesstructured[39] = array(
        "totalminutes" => 10,
        "coursename" => "Le langage PHP",
        "name" => "Traitement des dates",
        "type" => "assign");
    $certificateinfos->activities = $activitiesstructured;
    return $certificateinfos;
}
