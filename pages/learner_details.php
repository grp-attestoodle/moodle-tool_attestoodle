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

// Required params.
$trainingid = required_param('training', PARAM_INT);
$userid = required_param('user', PARAM_INT);
// Optionnal params.
$begindate = optional_param('begindate', null, PARAM_ALPHANUMEXT);
$enddate = optional_param('enddate', null, PARAM_ALPHANUMEXT);
$actualbegindate = $actualenddate = null;
$begindateerror = $enddateerror = false;
if (!$begindate) {
    $begindate = (new \DateTime('first day of January ' . date('Y')))->format('Y-m-d');
}
try {
    $actualbegindate = new \DateTime($begindate);
} catch (Exception $ex) {
    $begindateerror = true;
}
if (!$enddate) {
    $enddate = (new \DateTime('last day of December ' . date('Y')))->format('Y-m-d');
}
try {
    $actualenddate = new \DateTime($enddate);
    $searchenddate = clone $actualenddate;
    $searchenddate->modify('+1 day');
} catch (Exception $ex) {
    $enddateerror = true;
}


require_once($CFG->dirroot.'/blocks/attestoodle/lib.php');

require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/trainings_factory.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/categories_factory.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/courses_factory.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/activities_factory.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/learners_factory.php');

require_once($CFG->dirroot.'/blocks/attestoodle/classes/category.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/course.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/activity.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/validated_activity.php');

use block_attestoodle\factories\categories_factory;
use block_attestoodle\factories\trainings_factory;
use block_attestoodle\factories\learners_factory;

$PAGE->set_url(new moodle_url(
        '/blocks/attestoodle/pages/learner_details.php',
        array(
                'training' => $trainingid,
                'user' => $userid,
                'begindate' => $begindate,
                'enddate' => $enddate
        )
));

require_login();

$context = context_coursecat::instance($trainingid);
$userhascapability = has_capability('block/attestoodle:learnerdetails', $context);
require_capability('block/attestoodle:learnerdetails', $context);

// ...@todo May be replaced by "require_login(...)" + seems a bad context choice +
// ...throw error if param is not a valid course category id.
$PAGE->set_context($context);
$PAGE->set_title(get_string('learner_details_page_title', 'block_attestoodle'));

categories_factory::get_instance()->create_categories();
$chips = categories_factory::get_instance();
$prout = trainings_factory::get_instance();
$trainingexists = trainings_factory::get_instance()->has_training($trainingid);
$learnerexists = learners_factory::get_instance()->has_learner($userid);

if (!$trainingexists || !$learnerexists) {
    $PAGE->set_heading(get_string('learner_details_main_title_error', 'block_attestoodle'));
} else {
    $learner = learners_factory::get_instance()->retrieve_learner($userid);
    $PAGE->set_heading(get_string('learner_details_main_title', 'block_attestoodle', $learner->get_fullname()));
}
echo $OUTPUT->header();

// Verifying training id.
if (!$trainingexists) {
    echo html_writer::start_div('clearfix');
    // Link to the trainings list if the training id is not valid.
    echo html_writer::link(
            new moodle_url('/blocks/attestoodle/pages/trainings_list.php', array()),
            get_string('backto_trainings_list_btn_text', 'block_attestoodle'),
            array('class' => 'attestoodle-link'));
    echo html_writer::end_div();

    echo "<hr />";

    $warningunknowntrainingid = get_string('unknown_training_id', 'block_attestoodle', $trainingid);
    echo $warningunknowntrainingid;
} else {
    // If the training id is valid...
    echo html_writer::start_div('clearfix');
    // Link to the training learners list.
    echo html_writer::link(
            new moodle_url('/blocks/attestoodle/pages/training_learners_list.php', array('id' => $trainingid)),
            get_string('backto_training_learners_list_btn_text', 'block_attestoodle'),
            array('class' => 'attestoodle-link'));
    echo html_writer::end_div();

    echo "<hr />";

    // Verifying learner id.
    if (!$learnerexists) {
        $warningunknownlearnerid = get_string('unknown_learner_id', 'block_attestoodle', $userid);
        echo $warningunknownlearnerid;
    } else {
        // Basic form to allow user filtering the validated activities by begin and end dates.
        echo '<form action="?" class="filterform"><div>'
                . '<input type="hidden" name="training" value="'.$trainingid.'" />'
                . '<input type="hidden" name="user" value="'.$userid.'" />';
        echo '<label for="input_begin_date">'
                . get_string('learner_details_begin_date_label', 'block_attestoodle') .'</label>'
                .'<input type="text" id="input_begin_date" name="begindate" value="'.$begindate.'" '
                . 'placeholder="ex: '.(new \DateTime('now'))->format('Y-m-d').'" />';
        if ($begindateerror) {
            echo "<span class='error'>Erreur de format</span>";
        }
        echo '<label for="input_end_date">' . get_string('learner_details_end_date_label', 'block_attestoodle') . '</label>'
                .'<input type="text" id="input_end_date" name="enddate" value="'.$enddate.'" '
                . 'placeholder="ex: '.(new \DateTime('now'))->format('Y-m-d').'" />';
        if ($enddateerror) {
            echo "<span class='error'>Erreur de format</span>";
        }
        echo '<input type="submit" value="' . get_string('learner_details_submit_button_value', 'block_attestoodle').'" />'
                . '</div></form>' . "\n";

        echo "<hr />";

        // If the learner id is valid...
        // Print validated activities informations (with marker only).
        $validatedactivities = $learner->get_validated_activities_with_marker($actualbegindate, $searchenddate);
        if (count($validatedactivities) == 0) {
            echo get_string('learner_details_no_validated_activities', 'block_attestoodle');
        } else {
            // Generate table listing the activities.
            $table = new html_table();

            $table->head = array(
                    get_string('learner_details_table_header_column_training_name', 'block_attestoodle'),
                    get_string('learner_details_table_header_column_course_name', 'block_attestoodle'),
                    get_string('learner_details_table_header_column_name', 'block_attestoodle'),
                    get_string('learner_details_table_header_column_type', 'block_attestoodle'),
                    get_string('learner_details_table_header_column_validated_time', 'block_attestoodle'),
                    get_string('learner_details_table_header_column_milestones', 'block_attestoodle')
            );

            $data = array();
            foreach ($validatedactivities as $vact) {
                $act = $vact->get_activity();
                $stdclassact = new \stdClass();

                $stdclassact->trainingname = $act->get_course()->get_training()->get_name();
                $stdclassact->coursename = $act->get_course()->get_name();
                $stdclassact->name = $act->get_name();
                $stdclassact->type = get_string('modulename', $act->get_type());
                $stdclassact->validatedtime = parse_datetime_to_readable_format($vact->get_datetime());
                $stdclassact->milestone = parse_minutes_to_hours($act->get_marker());

                $data[] = $stdclassact;
            }
            $table->data = $data;

            echo html_writer::table($table);

            echo "<hr />";

            // Instanciate the "Generate certificate" link with specified filters.
            $dlcertifoptions = array('training' => $trainingid, 'user' => $userid);
            if ($actualbegindate) {
                $dlcertifoptions['begindate'] = $actualbegindate->format('Y-m-d');
            }
            if ($actualenddate) {
                $dlcertifoptions['enddate'] = $actualenddate->format('Y-m-d');
            }
            // Print the "Generate certificate" link.
            echo html_writer::start_div('clearfix');
            echo html_writer::link(
                    new moodle_url(
                            '/blocks/attestoodle/pages/download_certificate.php',
                            $dlcertifoptions
                    ),
                    get_string('learner_details_generate_certificate_link', 'block_attestoodle'),
                    array('class' => 'attestoodle-link')
            );
            echo html_writer::end_div();
        }
    }
}

echo $OUTPUT->footer();
