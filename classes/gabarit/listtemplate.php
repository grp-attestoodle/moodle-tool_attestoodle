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
 * List all the certificate template on a table.
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Main configuration importation (instanciate the $CFG global variable).
require_once(dirname(__FILE__) . '/../../../../../config.php');
require_once($CFG->libdir.'/tablelib.php');

define('DEFAULT_PAGE_SIZE', 10);

$delete = optional_param('delete', 0, PARAM_INT);
$confirm = optional_param('confirm', '', PARAM_ALPHANUM);
$page    = optional_param('page', 0, PARAM_INT); // Which page to show.
$perpage = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT); // How many per page.

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->navbar->ignore_active();
$navlevel1 = get_string('navlevel1b', 'tool_attestoodle');
$PAGE->navbar->add($navlevel1, new moodle_url('/admin/tool/attestoodle/classes/gabarit/listtemplate.php', array()));

require_login();

$PAGE->set_title(get_string('listtemplate_title', 'tool_attestoodle'));
$title = get_string('pluginname', 'tool_attestoodle') . " - " .
            get_string('listtemplate_title', 'tool_attestoodle');
$PAGE->set_heading($title);

$baseurl = new moodle_url('/admin/tool/attestoodle/classes/gabarit/listtemplate.php', array(
        'page' => $page,
        'perpage' => $perpage));
$PAGE->set_url($baseurl);

if ($delete) {
    if ($confirm != md5($delete)) { // Must be confirm.
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('deletemodel', 'tool_attestoodle'));
        $optionsyes = array('delete' => $delete, 'confirm' => md5($delete), 'sesskey' => sesskey());
        $returnurl = new moodle_url('/admin/tool/attestoodle/classes/gabarit/listtemplate.php', array());
        $deleteurl = new moodle_url($returnurl, $optionsyes);

        $deletebutton = new single_button($deleteurl, get_string('delete'), 'post');
        $template = $DB->get_record('tool_attestoodle_template', array('id' => $delete));

        echo $OUTPUT->confirm(get_string('confdeltemplate', 'tool_attestoodle', $template),
                              $deletebutton, $returnurl);
        echo $OUTPUT->footer();
        die;
    } else { // Delete after confirmation.
        $DB->delete_records('tool_attestoodle_tpl_detail', array('templateid' => $delete));
        $DB->delete_records('tool_attestoodle_template', array('id' => $delete));
    }
}

// Data preparation.
echo $OUTPUT->header();
if (get_string_manager()->string_exists('UrlHlpTo_listtemplate', 'tool_attestoodle')) {
    $urlhlp = get_string('UrlHlpTo_listtemplate', 'tool_attestoodle');
    echo "<a href='" . $urlhlp . "' target='aide' title='" . get_string('help') .
         "'><i class='fa fa-question-circle-o' aria-hidden='true'></i></a>";
}

$table = new flexible_table('admin_tool_lst');
$tablecolumns = array('idnom', 'idactions');
$tableheaders = array('Nom', 'Actions');

$table->define_columns($tablecolumns);
$table->define_headers($tableheaders);
$table->define_baseurl($baseurl->out());
$table->sortable(false);
$table->set_attribute('width', '80%');
$table->set_attribute('class', 'generaltable');

$table->column_style('idactions', 'width', '15%');

$table->setup();
$matchcount = $DB->count_records_sql("SELECT COUNT(id) from {tool_attestoodle_template}");

$table->pagesize($perpage, $matchcount);

$rs = $DB->get_recordset_sql('select id, name from {tool_attestoodle_template} order by name', null,
    $table->get_page_start(), $table->get_page_size());

$rows = array();
foreach ($rs as $result) {
    // Possible suppression test.
    $dellink = "";
    if (has_capability('tool/attestoodle:deletetemplate', \context_system::instance())) {
        if ($result->name != 'Site'
        && !$DB->record_exists('tool_attestoodle_train_style', array('templateid' => $result->id))
        && !$DB->record_exists('tool_attestoodle_user_style', array('templateid' => $result->id))) {
            $deleteurl = new moodle_url('/admin/tool/attestoodle/classes/gabarit/listtemplate.php',
                          ['delete' => $result->id]);
            $dellink = "<a href=" . $deleteurl . "><i class='fa fa-trash'></i></a>&nbsp;&nbsp;";
        }
    }
    // Manage template.
    $editlink = "";
    if (has_capability('tool/attestoodle:managetemplate', \context_system::instance())) {
        $url = new moodle_url('/admin/tool/attestoodle/classes/gabarit/sitecertificate.php',
                          ['templateid' => $result->id]);
        $editlink = "<a href=" . $url . "><i class='fa fa-edit'></i></a>  ";
    }
    // Preview template.
    $prevlink = "";
    if (has_capability('tool/attestoodle:viewtemplate', \context_system::instance())) {
        $previewurl = new moodle_url('/admin/tool/attestoodle/classes/gabarit/view_export.php',
                          ['templateid' => $result->id]);
        $prevlink = "<a target='preview' href=" . $previewurl . "><i class='fa fa-eye'></i></a>&nbsp;&nbsp;";
    }

    $rows[] = array('idnom' => $result->name, 'idactions' => $dellink . $prevlink . $editlink);
}

foreach ($rows as $row) {
    $table->add_data(array($row['idnom'], $row['idactions']));
}

$table->print_html();

if (has_capability('tool/attestoodle:managetemplate', \context_system::instance())) {
    $addurl = new moodle_url('/admin/tool/attestoodle/classes/gabarit/sitecertificate.php',
                          ['templateid' => -1]);
    echo $OUTPUT->single_button($addurl, get_string('add'), 'post');
}

echo $OUTPUT->footer();
