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
 * Select learner for training.
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../../../config.php');
require_once($CFG->libdir.'/tablelib.php');

use tool_attestoodle\utils\db_accessor;

define('DEFAULT_PAGE_SIZE', 10);

$context = context_system::instance();
$PAGE->set_context($context);

require_login();

$page    = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT);
$trainingid = required_param('trainingid', PARAM_INT);
$categoryid = required_param('categoryid', PARAM_INT);
$order    = optional_param('tsort', 0, PARAM_INT);
$action    = optional_param('action', '', PARAM_ALPHA);

if ($action == "validate") {
    db_accessor::get_instance()->validate_learner($trainingid);
    $redirecturl = new \moodle_url(
                '/admin/tool/attestoodle/index.php',
                array('typepage' => 'trainingmanagement', 'categoryid' => $categoryid, 'trainingid' => $trainingid));
    redirect($redirecturl);
}

if ($action == "cancel") {
    db_accessor::get_instance()->cancel_learner($trainingid);
    $redirecturl = new \moodle_url(
                '/admin/tool/attestoodle/index.php',
                array('typepage' => 'trainingmanagement', 'categoryid' => $categoryid, 'trainingid' => $trainingid));
    redirect($redirecturl);
}


handle_actions($trainingid, $categoryid);

// Navbar.
$PAGE->navbar->ignore_active();
$titlepage = get_string('selectlearner', 'tool_attestoodle');

$navlevel1 = get_string('navlevel1', 'tool_attestoodle');
$PAGE->navbar->add($navlevel1, new moodle_url('/admin/tool/attestoodle/index.php', array()));
$navlevel2 = get_string('navlevel2', 'tool_attestoodle');
$PAGE->navbar->add($navlevel2, new moodle_url('/admin/tool/attestoodle/index.php',
                                                array('typepage' => 'trainingmanagement',
                                                    'categoryid' => $categoryid,
                                                    'trainingid' => $trainingid)));

$param = array('categoryid' => $categoryid, 'trainingid' => $trainingid);
$PAGE->navbar->add($titlepage, new moodle_url('/admin/tool/attestoodle/classes/training/select_learners.php', $param));
$PAGE->set_url(new moodle_url('/admin/tool/attestoodle/classes/training/select_learners.php', $param));
$PAGE->set_title($titlepage);
$trainingname = db_accessor::get_instance()->get_training_by_id($trainingid)->name;

$title = $titlepage . get_string('fortraining', 'tool_attestoodle', $trainingname);
$PAGE->set_heading($title);

// Data preparation.
if (db_accessor::get_instance()->nolearner($trainingid, $categoryid)) {
    db_accessor::get_instance()->insert_learner($trainingid, $categoryid);
}

echo $OUTPUT->header();
// Add help here.
if (get_string_manager()->string_exists('UrlHlpTo_selectlearners', 'tool_attestoodle')) {
    $urlhlp = get_string('UrlHlpTo_selectlearners', 'tool_attestoodle');
    echo "<a href='" . $urlhlp . "' target='aide' title='" . get_string('help') .
         "'><i class='fa fa-question-circle-o' aria-hidden='true'></i></a>";
}

// Table.
$baseurl = new moodle_url('/admin/tool/attestoodle/classes/training/select_learners.php', array(
        'page' => $page,
        'perpage' => $perpage,
        'categoryid' => $categoryid,
        'trainingid' => $trainingid));

$table = new flexible_table('admin_tool_learners');
$tablecolumns = array('username', 'lastname', 'firstname', 'email', 'selected', 'resultcriteria');
$tableheaders = array(get_string('username'), get_string('lastname'), get_string('firstname'),
                      get_string('email', 'tool_attestoodle'),
                      get_string('selection', 'tool_attestoodle'),
                      get_string('result', 'tool_attestoodle'));

$table->define_columns($tablecolumns);
$table->define_headers($tableheaders);
$table->define_baseurl($baseurl->out());
$table->sortable(true, 'lastname', SORT_DESC);
$table->set_attribute('width', '90%');
$table->set_attribute('class', 'generaltable');
$table->column_style('selected', 'width', '5%');

$table->setup();
$matchcount = db_accessor::get_instance()->get_count_learner($trainingid);

$table->pagesize($perpage, $matchcount);
$order = " order by " . $table->get_sql_sort();
$rs = db_accessor::get_instance()->get_page_learner($table->get_page_start(), $table->get_page_size(), $trainingid, $order);

$rows = array();

$hasselect = false;

foreach ($rs as $result) {
    if ($result->selected != 1) {
        $select = new moodle_url('/admin/tool/attestoodle/classes/training/select_learners.php',
                          ['check' => $result->id,
                          'categoryid' => $categoryid,
                          'trainingid' => $trainingid,
                          'page' => $page,
                          'perpage' => $perpage]);
        $sellink = "<a href=" . $select . "><i class='fa fa-square-o'></i></a>&nbsp;&nbsp;";
        $hasselect = false;
    } else {
        $select = new moodle_url('/admin/tool/attestoodle/classes/training/select_learners.php',
                          ['uncheck' => $result->id,
                          'categoryid' => $categoryid,
                          'trainingid' => $trainingid,
                          'page' => $page,
                          'perpage' => $perpage]);
        $sellink = "<a href=" . $select . "><i class='fa fa-check-square-o'></i></a>&nbsp;&nbsp;";
        // Up.
        if (!$hasselect && ($page > 0 || count($rows) > 0)) {
            $selectup = new moodle_url('/admin/tool/attestoodle/classes/training/select_learners.php',
                          ['upcheck' => $result->id,
                          'categoryid' => $categoryid,
                          'trainingid' => $trainingid,
                          'page' => $page,
                          'perpage' => $perpage,
                          'order' => $table->get_sql_sort()]);
            $sellink = $sellink . "&nbsp; <a href=" . $selectup . "><i class='fa fa-level-up'></i>";
        }
        $hasselect = true;
    }
    $rows[] = array('username' => $result->username,
            'lastname' => $result->lastname,
            'firstname' => $result->firstname,
            'email' => $result->email,
            'selected' => $sellink,
            'resultcriteria' => $result->resultcriteria
            );
}

foreach ($rows as $row) {
    $table->add_data(
            array(
                $row['username'], $row['lastname'], $row['firstname'], $row['email'],
                $row['selected'], $row['resultcriteria']));
}
echo get_string('number_learners', 'tool_attestoodle', $matchcount);

$table->print_html();

echo "<br>";

$parameters = array(
                    'categoryid' => $categoryid,
                    'trainingid' => $trainingid,
                    'perpage' => $perpage,
                    'action' => 'selecton'
                    );

$attributes = array('class' => 'btn btn-default attestoodle-button');
$nbselect = db_accessor::get_instance()->countselected($trainingid);

if ($nbselect > 0) {
    $parameters['page'] = 0;
    $url = new \moodle_url('/admin/tool/attestoodle/classes/training/select_learners.php', $parameters);
    $label = get_string('keepselect', 'tool_attestoodle') . " (" . $nbselect . ")";
    $btn = \html_writer::link($url, $label, $attributes);

    $noselect = $matchcount - $nbselect;
    $parameters['action'] = 'selectoff';
    $url = new \moodle_url('/admin/tool/attestoodle/classes/training/select_learners.php', $parameters);
    $label = get_string('excludeselect', 'tool_attestoodle') . "(" . $noselect . ")";
    $btn .= "&nbsp;&nbsp;" . \html_writer::link($url, $label, $attributes);

    echo $btn;
}

$parameters['page'] = $page;
$parameters['action'] = 'reinit';
$url = new \moodle_url('/admin/tool/attestoodle/classes/training/select_learners.php', $parameters);
$label = get_string('findlearner', 'tool_attestoodle');
$btn = "&nbsp;&nbsp;" . \html_writer::link($url, $label, $attributes);
echo $btn;

$parameters['action'] = 'enrol';
$url = new \moodle_url('/admin/tool/attestoodle/classes/training/select_learners.php', $parameters);
$label = get_string('enrolcriteria', 'tool_attestoodle');
$btntest = \html_writer::link($url, $label, $attributes);
echo "  " . $btntest;


$parameters['action'] = 'training';
$url = new \moodle_url('/admin/tool/attestoodle/classes/training/select_learners.php', $parameters);
$label = get_string('trainingcriteria', 'tool_attestoodle');
$btntest = \html_writer::link($url, $label, $attributes);
echo "  " . $btntest;

// Button valid.
$parameters['action'] = 'validate';
$url = new \moodle_url('/admin/tool/attestoodle/classes/training/select_learners.php', $parameters);
$label = get_string('validate', 'tool_attestoodle');
$btnok = \html_writer::link($url, $label, $attributes);
// Button cancel.
$parameters['action'] = 'cancel';
$url = new \moodle_url('/admin/tool/attestoodle/classes/training/select_learners.php', $parameters);
$label = get_string('cancel');
$btncancel = \html_writer::link($url, $label, $attributes);

echo "<br/><br/>" . $btnok . "&nbsp;&nbsp;" . $btncancel;


echo $OUTPUT->footer();

/**
 * Carries out the actions requested on the apprentices.
 *
 * @param int $trainingid ID of the current training.
 * @param int $categoryid ID of the category parent of training.
 */
function handle_actions($trainingid, $categoryid) {
    $check = optional_param('check', -1, PARAM_INT);
    $uncheck = optional_param('uncheck', -1, PARAM_INT);
    $upcheck = optional_param('upcheck', -1, PARAM_INT);
    $action = optional_param('action', '', PARAM_ALPHA);
    $order = optional_param('order', '', PARAM_RAW);

    if ($check != -1) {
        db_accessor::get_instance()->check_learner($check, $trainingid);
    }

    if ($uncheck != -1) {
        db_accessor::get_instance()->uncheck_learner($uncheck, $trainingid);
    }

    if ($action == "selectoff") {
        db_accessor::get_instance()->select_off_learner($trainingid);
    }

    if ($action == "selecton") {
        db_accessor::get_instance()->select_on_learner($trainingid);
    }

    if ($action == "reinit") {
        db_accessor::get_instance()->insert_learner($trainingid, $categoryid);
    }
    if ($upcheck != -1) {
        db_accessor::get_instance()->checkup_learner($trainingid, $upcheck, $order);
    }

    if ($action == "enrol") {
        db_accessor::get_instance()->fillnbcoursecriteria($trainingid);
    }
    if ($action == "training") {
        db_accessor::get_instance()->fillnbtrainingcriteria($trainingid);
    }
}
