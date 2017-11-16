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
 *
 *
 * @package    block_showcase
 * @copyright  2017 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Importation de la config $CFG qui importe égalment $DB et $OUTPUT.
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/blocks/attestoodle/lib.php');

require_once($CFG->dirroot.'/blocks/attestoodle/classes/utils/singleton.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/utils/db_accessor.php');
// require_once($CFG->dirroot.'/blocks/attestoodle/classes/training.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/training_factory.php');

// use block_attestoodle\utils\singleton;
use block_attestoodle\utils\db_accessor;
use block_attestoodle\factories\training_factory;
// use block_attestoodle\training;

// 1) récupération de tous les courses avec suivi d'achevement activé.
$courses = block_attestoodle_get_courses(true);

// 2) générer un tableau associatif des modules (id => name) depuis
// ...la table "modules".
$arraymodules = block_attestoodle_get_modules();


// 3) Récupérer tous les "course_modules" filtrés avec les résultats
// ...du 1).
$coursemodules = block_attestoodle_get_courses_modules($courses);


// 4) Pour chaque "course_module", récupérer le nom de la table dans
// ...le tableau 2) correspondant puis tous les enregistrements de
// ...ladite table ayant id = "instance" (dans 3)).
$arraymodulesname = array();
foreach ($coursemodules as $idcoursemodule => $idmodule){
    array_push($arraymodulesname, $arraymodules[$idmodule]);
}


// 5) Filtrer les résultats du 4) avec intro qui contient span.
$activitieswithintro = block_attestoodle_get_activities_with_intro($arraymodulesname);


// Output de la page.
echo $OUTPUT->header();

$parameters = array();
$url = new moodle_url('/blocks/attestoodle/pages/trainings_list.php', $parameters);
$label = get_string('trainings_list_btn_text', 'block_attestoodle');
$options = array('class' => 'attestoodle-button');
echo $OUTPUT->single_button($url, $label, 'get', $options);

echo $OUTPUT->heading('Liste des cours :');

// Print des resultats dans un tableau.
$table = new html_table();
$table->head = array('ID', 'Fullname', 'Completion enabled');
$table->data = $courses;
echo html_writer::table($table);

echo "<pre>\n";
echo "tableau course_modules\n";
var_dump($coursemodules);
echo "======================================\n";
echo "tableau array_modules\n";
var_dump($arraymodules);
echo "======================================\n";
echo "tableau array_printable\n";
var_dump($arrayprintable);
echo "======================================\n";
echo "tableau activities_with_intro\n";
var_dump($activitieswithintro);
echo "</pre>\n";
echo "<p>Petit test</p>";

echo "<pre>\n";
training_factory::get_instance()->create_trainings();

var_dump(training_factory::get_instance());
echo "</pre>\n";

// Print de la fin de la page.
echo $OUTPUT->footer();
