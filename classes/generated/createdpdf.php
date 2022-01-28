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
 * Make all the certificate from tmp table.
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Main configuration importation (instanciate the $CFG global variable).
require_once(dirname(__FILE__) . '/../../../../../config.php');
require_once(dirname(__FILE__).'/../../lib.php');

// Get Jquery.
$PAGE->requires->jquery();

$context = context_system::instance();
$PAGE->set_context($context);
require_login();
global $DB, $USER;

$categoryid = required_param('categoryid', PARAM_INT);
$trainingid = required_param('trainingid', PARAM_INT);
$begindate = required_param('begindate', PARAM_ALPHANUMEXT);
$enddate = required_param('enddate', PARAM_ALPHANUMEXT);
$launchid = optional_param('launchid', -1, PARAM_INT);
$nbmax = required_param('nbmax', PARAM_ALPHANUMEXT);
// NavBar.
$PAGE->navbar->ignore_active();
$navlevel1 = get_string('navlevel1', 'tool_attestoodle');
$PAGE->navbar->add($navlevel1, new moodle_url('/admin/tool/attestoodle/index.php', array()));
$navlevel2 = get_string('navlevel2', 'tool_attestoodle');
$PAGE->navbar->add($navlevel2, new moodle_url('/admin/tool/attestoodle/index.php',
                            array('typepage' => 'trainingmanagement',
                                'categoryid' => $categoryid,
                                'trainingid' => $trainingid)));
$navlevel3a = get_string('navlevel3a', 'tool_attestoodle');
$PAGE->navbar->add($navlevel3a, new moodle_url('/admin/tool/attestoodle/index.php',
                            array('typepage' => 'learners',
                                'categoryid' => $categoryid,
                                'trainingid' => $trainingid)));

$PAGE->set_url(new moodle_url('/admin/tool/attestoodle/classes/generated/preparedinf.php', [] ));
$PAGE->set_title(get_string('certificategenerate', 'tool_attestoodle'));
$title = get_string('pluginname', 'tool_attestoodle') . " - " .
    get_string('certificategenerate', 'tool_attestoodle');
$PAGE->set_heading($title);

// Log the generation launch.
if ($launchid == -1) {
    $dataobject = new \stdClass();
    $dataobject->timegenerated = \time();
    $dataobject->begindate = $begindate;
    $dataobject->enddate = $enddate;
    $dataobject->operatorid = $USER->id;
    $launchid = $DB->insert_record('tool_attestoodle_launch_log', $dataobject, true);
}

// Load script Ajax.
$PAGE->requires->js('/admin/tool/attestoodle/classes/generated/scriptajax.js');

// Display page.
echo $OUTPUT->header();
echo ('<br>' . get_string('msgongoing', 'tool_attestoodle'));
echo (' (<span class="attestoodle-text-progressbar">0</span>) : ');
echo (' <progress id="progressBar" class="attestoodle-progressbar-generation"></progress>');
echo ('<script language="javascript">
var maxBar = ' . $nbmax . ';
var currentBar = 0;
var progressBar;
var intervalId;
var stop = false;

var initialisation = function() {
    progressBar = document.getElementById( "progressBar" );
    progressBar.value = currentBar;
    progressBar.max = maxBar;
}

var displayBar = function(valeur) {
    if (valeur == 0) {
        valeur = 1;
    }
    currentBar += valeur;
    if (currentBar > maxBar) {
        currentBar = maxBar;
    }
    progressBar.value = currentBar;
    if (currentBar < maxBar && stop == false) {
        ajax_certif_generate(0,[' . $launchid . ',' . $trainingid . ',' . $categoryid . ']);
    } else {
        var di = document.getElementById("btn_ret");
        di.style.display = "inline";
        var ds = document.getElementById("btn_stop");
        ds.style.display = "none";
    }
    $(".attestoodle-text-progressbar").text(currentBar + "/" + maxBar);
}

var halt = function(e, args) {
    if (e instanceof Event) {
        e.preventDefault();
    }
    stop = true;
    var di = document.getElementById("btn_ret");
    di.style.display = "inline";
}

initialisation();
</script>');
$PAGE->requires->js_init_call('ajax_certif_generate', array(array($launchid, $trainingid, $categoryid)));

$attrib = array('class' => 'btn-create');
$btnstop = $OUTPUT->action_link(new moodle_url('#'),
                get_string('stop', 'tool_attestoodle'),
                new component_action('click', 'halt', array()),
                $attrib);
echo ("<br/><div id='btn_stop' style='display:inline'>" . $btnstop . "</div>");

echo ("&nbsp;<div id='btn_ret' style='display:none'>");
$linkno = \html_writer::link(
                new moodle_url('/admin/tool/attestoodle/index.php',
                            array('typepage' => 'learners',
                            'categoryid' => $categoryid,
                            'begindate' => $begindate,
                            'enddate' => $enddate,
                            'trainingid' => $trainingid)
                    ),
                    get_string('back'),
                    array('class' => 'btn btn-default attestoodle-button'));

echo $linkno;

echo ("</div>");

echo $OUTPUT->footer();
