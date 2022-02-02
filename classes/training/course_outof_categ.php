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

use tool_attestoodle\utils\db_accessor;
use tool_attestoodle\factories\courses_factory;
use tool_attestoodle\factories\trainings_factory;
use tool_attestoodle\forms\training_milestones_update_form;

$context = context_system::instance();
$PAGE->set_context($context);
require_login();
global $CFG;

$categoryid = required_param('categoryid', PARAM_INT);
$trainingid = required_param('trainingid', PARAM_INT);
$coursename = optional_param('coursename', '', PARAM_NOTAGS);

$context = \context_coursecat::instance($categoryid);
$modifallow = false;
if (has_capability('tool/attestoodle:managemilestones', $context)) {
    $modifallow = true;
}

$coursetoadd = search_course($coursename, $categoryid, $trainingid);
$url = new \moodle_url('/admin/tool/attestoodle/classes/training/course_outof_categ.php',
    array(
        'categoryid' => $categoryid,
        'trainingid' => $trainingid,
        'coursename' => $coursename));

$form = new training_milestones_update_form($url,
                                array(
                                    'data' => array($coursetoadd),
                                    'input_name_prefix' => "attestoodle_activity_id_",
                                    'modifallow' => $modifallow)); // No need to prefill the milestone form.

$urltrainingmgm = new moodle_url('/admin/tool/attestoodle/index.php',
                            array(
                                'typepage' => 'trainingmanagement',
                                'categoryid' => $categoryid,
                                'trainingid' => $trainingid));

if ($form->is_cancelled()) {
    redirect($urltrainingmgm);
}

if ($form->get_data()) {
    handle_form($categoryid, $trainingid, $form, $coursetoadd);
    redirect($url);
}

// NavBar.
$PAGE->navbar->ignore_active();
$navlevel1 = get_string('navlevel1', 'tool_attestoodle');
$PAGE->navbar->add($navlevel1, new moodle_url('/admin/tool/attestoodle/index.php', array()));
$navlevel2 = get_string('navlevel2', 'tool_attestoodle');
$PAGE->navbar->add($navlevel2, $urltrainingmgm);
$navlevel3a = get_string('onecoursemilestonetitle', 'tool_attestoodle', $coursetoadd->get_name());
$PAGE->navbar->add($navlevel3a, $url);

$PAGE->set_url(new moodle_url('/admin/tool/attestoodle/classes/training/course_outof_categ.php', [] ));
$PAGE->set_title($navlevel3a);
$PAGE->set_heading($navlevel3a);

// Display page.
echo $OUTPUT->header();

$form->display();

echo $OUTPUT->footer();

/**
 * Processing of form entry.
 *
 * @param integer $categoryid The category ID wich contains the training.
 * @param integer $trainingid The training ID where we want add the course.
 * @param moodleform $form to handle.
 * @param attestoodle\course $course contains activities.
 */
function handle_form($categoryid, $trainingid, $form, $course) {
    $contextcateg = \context_coursecat::instance($categoryid);
    if (!has_capability('tool/attestoodle:managemilestones', $contextcateg)) {
        return;
    }
    trainings_factory::get_instance()->create_training_by_category($categoryid, $trainingid);
    $training = trainings_factory::get_instance()->retrieve_training_by_id($trainingid);
    $datafromform = $form->get_submitted_data();

    foreach ($datafromform as $key => $value) {
        handle_form_activity($key, $value, $training, $course);
    }
}


/**
 * Handle the process of update of one activity after the form has been
 * submitted (and its valid).
 *
 * @param string $key Input name being computed
 * @param string $value Input value being computed
 * @param integer $training The training where we want manage milestone.
 * @param course $course the course(attestoodle) having activities to change in milestone.
 */
function handle_form_activity($key, $value, $training, $course) {
    $matches = [];
    $regexp = "/attestoodle_activity_id_(.+)/";
    if (preg_match($regexp, $key, $matches)) {
        // There is an activity ID.
        $idactivity = $matches[1];
        if (!empty($idactivity)) {
            // The activity ID is valid.
            if ($training->has_activity($idactivity)) {
                $activity = $training->retrieve_activity($idactivity);
                $oldmarkervalue = $activity->get_milestone();
            } else {
                $activity = $course->retrieve_activity($idactivity);
                $oldmarkervalue = 0;
            }
            if ($activity->set_milestone($value)) {
                try {
                    // Try to persist activity in DB.
                    $activity->persist($training->get_id());
                } catch (\Exception $ex) {
                    // If record in DB failed, re-set the old value.
                    $activity->set_milestone($oldmarkervalue);
                }
            }
        }
    }
}

/**
 * Search for the course that matches the request.
 * If no course or several courses match an error is displayed and the user
 * is redirected to the entry of the search name.
 *
 * @param string $coursename a piece of the name of the course you are looking for.
 * @param integer $categoryid The category ID wich contains the training.
 * @param integer $trainingid The training ID where we want add the course.
 * @return the course (attestoodle) corresponding, or redirect.
 */
function search_course($coursename, $categoryid, $trainingid) {
    $nbcourse = 0;
    $course = "";
    $lstactivities = "";
    $urlreturn = new \moodle_url('/admin/tool/attestoodle/index.php',
                    array('typepage' => 'trainingmanagement',
                        'categoryid' => $categoryid,
                        'trainingid' => $trainingid));

    if (empty($coursename)) {
        \core\notification::warning(get_string('errnothingtosearch', 'tool_attestoodle'));
        redirect($urlreturn);
        return;
    }

    $result = db_accessor::get_instance()->find_course($coursename);
    $nbcourse = count($result);
    foreach ($result as $enreg) {
        $course = $enreg;
    }

    if (isset($course->id)) {
        $coursetoadd = courses_factory::get_instance()->create($course, $trainingid);
        $lstactivities = $coursetoadd->get_activities();
    }

    if (empty($result) || $nbcourse != 1) {
        $errmorecours = get_string('errmorecours', 'tool_attestoodle', $coursename);
        \core\notification::warning($nbcourse . $errmorecours);
        redirect($urlreturn);
    }
    if (count($lstactivities) == 0) {
        $errmsg = get_string('errnoactivity', 'tool_attestoodle', $course->shortname);
        \core\notification::warning($errmsg);
        redirect($urlreturn);
    }

    return $coursetoadd;
}

