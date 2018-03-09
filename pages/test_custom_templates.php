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

// Importation de la config $CFG qui importe égalment $DB et $OUTPUT.
require_once(dirname(__FILE__) . '/../../../config.php');

$isupdateactivated = optional_param('update', false, PARAM_BOOL);
$textparam = optional_param('letext', 'default_text', PARAM_ALPHANUMEXT);

$unestdclass = new \stdClass();
$unestdclass->attrib = "Une string dans le template";
$unestdclass->textparam = $textparam;

$PAGE->set_heading("heading de la page");
echo $OUTPUT->header();

if ($isupdateactivated) {
    echo "Edition is ON";
    echo $OUTPUT->render_from_template('block_attestoodle/tpl', $unestdclass);

    // créer un tableau de données numériques

    // creer le formulaire

    // ajouter le html table
    // dans un foreach
        // ajouter du html tr/td
        // ajouter le champ
    // fermer le html table
    // ajouter le bouton de submit

} else {
    echo "Edition is OFF";
    echo $OUTPUT->render_from_template('block_attestoodle/home', $unestdclass);
}

echo $OUTPUT->footer();
