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
if ($begindate) {
    try {
        $actualbegindate = new \DateTime($begindate);
    } catch (Exception $ex) {
        $begindateerror = true;
    }
}
if ($enddate) {
    try {
        $actualenddate = (new \DateTime($enddate))->modify('+1 day');;
    } catch (Exception $ex) {
        $enddateerror = true;
    }
}

require_once($CFG->dirroot.'/blocks/attestoodle/lib.php');

require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/trainings_factory.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/courses_factory.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/activities_factory.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/learners_factory.php');

require_once($CFG->dirroot.'/blocks/attestoodle/classes/course.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/activity.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/validated_activity.php');

require_once($CFG->dirroot.'/blocks/attestoodle/classes/forms/learner_details_period_form.php');

use block_attestoodle\factories\trainings_factory;
use block_attestoodle\factories\learners_factory;
//use block_attestoodle\forms\learner_details_period_form;

$PAGE->set_url(new moodle_url(
        '/blocks/attestoodle/pages/learner_details.php',
        array('training' => $trainingid, 'user' => $userid)));
// @todo May be replaced by "require_login(...)" + seems a bad context choice +
// throw error if param is not a valid course category id.
$PAGE->set_context(context_coursecat::instance($trainingid));
// @todo Make a translation string.
$PAGE->set_title("Moodle - Attestoodle - Détail de l'étudiant");

$trainingexists = trainings_factory::get_instance()->has_training($trainingid);
$learnerexists = learners_factory::get_instance()->has_learner($userid);

if (!$trainingexists || !$learnerexists) {
    // @todo translations
    $PAGE->set_heading("Erreur !");
} else {
    $learner = learners_factory::get_instance()->retrieve_learner($userid);
    // @todo translations
    $PAGE->set_heading("Jalons validés par {$learner->get_fullname()}");
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
        // Basic form to allow user filtering the validated activities by begin and end dates
        echo '<form action="?" class="filterform"><div>'
                . '<input type="hidden" name="training" value="'.$trainingid.'" />'
                . '<input type="hidden" name="user" value="'.$userid.'" />';
        echo '<label for="input_begin_date">Begin date: </label>'
                .'<input type="text" id="input_begin_date" name="begindate" value="'.$begindate.'" placeholder="ex: '.(new \DateTime('now'))->format('Y-m-d').'" />';
        if ($begindateerror) {
            echo "<span class='error'>Erreur de format</span>";
        }
        echo '<label for="input_end_date">End date: </label>'
                .'<input type="text" id="input_end_date" name="enddate" value="'.$enddate.'" placeholder="ex: '.(new \DateTime('now'))->format('Y-m-d').'" />';
        if ($enddateerror) {
            echo "<span class='error'>Erreur de format</span>";
        }
        echo '<input type="submit" value="Filtrer" /></div></form>'."\n";

        echo "<hr />";

        // If the learner id is valid...
        $counternomarker = 0;
        // Print validated activities informations (with marker only).
        foreach ($learner->get_validated_activities_with_marker($actualbegindate, $actualenddate) as $vact) {
            $act = $vact->get_activity();
            echo "<h1>" . $act->get_course()->get_training()->get_name() . "</h1>";
            echo "<h2>" . $act->get_name() . "</h2>";
            echo "<h3>" . $act->get_type(). " (" . parse_minutes_to_hours($act->get_marker())
                    . ") - Validée le " . parse_datetime_to_readable_format($vact->get_datetime())
                    . "</h3>";
        }

        echo "<hr />";

        $certificateinfos = $learner->get_certificate_informations();

        $dlcertifoptions = array('training' => $trainingid, 'user' => $userid);
        if ($actualbegindate) {
            $dlcertifoptions['begindate'] = $actualbegindate->format('Y-m-d');
        }
        if ($actualenddate) {
            $dlcertifoptions['enddate'] = $actualenddate->format('Y-m-d');
        }

        echo html_writer::start_div('clearfix');
        echo html_writer::link(
                new moodle_url(
                        '/blocks/attestoodle/pages/download_certificate.php',
                        $dlcertifoptions
                ),
                get_string('generate_certificate_link_text', 'block_attestoodle'),
                array('class' => 'attestoodle-link')
        );
        echo html_writer::end_div();
    }
}

echo $OUTPUT->footer();
