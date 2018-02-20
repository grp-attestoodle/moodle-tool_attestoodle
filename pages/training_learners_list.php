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

$trainingid = required_param('id', PARAM_INT);

require_once($CFG->dirroot.'/blocks/attestoodle/lib.php');

require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/categories_factory.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/trainings_factory.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/courses_factory.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/activities_factory.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/learners_factory.php');

require_once($CFG->dirroot.'/blocks/attestoodle/classes/training_from_category.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/category.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/course.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/activity.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/validated_activity.php');

use block_attestoodle\factories\trainings_factory;
use block_attestoodle\factories\categories_factory;

$PAGE->set_url(new moodle_url('/blocks/attestoodle/pages/training_learners_list.php', array('id' => $trainingid)));
// ...@todo May be replaced by "require_login(...)"
$PAGE->set_context(context_coursecat::instance($trainingid));
$PAGE->set_title(get_string('training_learners_list_page_title', 'block_attestoodle'));

categories_factory::get_instance()->create_categories();
$trainingexist = trainings_factory::get_instance()->has_training($trainingid);

if ($trainingexist) {
    $training = trainings_factory::get_instance()->retrieve_training($trainingid);
    $PAGE->set_heading(get_string('training_learners_list_main_title', 'block_attestoodle', $training->get_name()));
} else {
    $PAGE->set_heading(get_string('training_learners_list_main_title_error', 'block_attestoodle'));
}

echo $OUTPUT->header();

echo html_writer::start_div('clearfix');
// Link to the trainings list.
echo html_writer::link(
        new moodle_url('/blocks/attestoodle/pages/trainings_list.php', array()),
        get_string('trainings_list_btn_text', 'block_attestoodle'),
        array('class' => 'attestoodle-link'));

if (!$trainingexist) {
    echo html_writer::end_div();
    $warningunknownid = get_string('training_details_unknown_training_id', 'block_attestoodle') . $trainingid;
    echo $warningunknownid;
} else {
    // Link to the training details.
    echo html_writer::link(
            new moodle_url('/blocks/attestoodle/pages/training_details.php', array('id' => $trainingid)),
            get_string('training_learners_list_edit_training_link', 'block_attestoodle'),
            array('class' => 'btn btn-default attestoodle-button'));
    echo html_writer::end_div();

    $data = parse_learners_as_stdclass($training->get_learners(), $trainingid);
    $table = new html_table();
    $table->head = array(
        get_string('training_learners_list_table_header_column_id', 'block_attestoodle'),
        get_string('training_learners_list_table_header_column_firstname', 'block_attestoodle'),
        get_string('training_learners_list_table_header_column_lastname', 'block_attestoodle'),
        get_string('training_learners_list_table_header_column_validated_activities', 'block_attestoodle'),
        get_string('training_learners_list_table_header_column_total_milestones', 'block_attestoodle'),
        '');
    $table->data = $data;

    echo $OUTPUT->heading(get_string('training_learners_list_heading', 'block_attestoodle', count($data)));
    echo html_writer::table($table);
}

echo $OUTPUT->footer();
