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
 * @package    block_attestoodle
 * @copyright  2017 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Returns the courses stored in database
 *
 * @param boolean completionenabled Set to true if we need only the courses where completion is enabled
 * @return array Array of courses retrieved in DB
 */
function block_attestoodle_get_courses() {
    global $DB;
    $completionenabled = true;
    $result = $DB->get_records('course',
                            array('enablecompletion' => (int)$completionenabled),
                            null,
                            'id, fullname, enablecompletion');

    return $result;
}

/**
 * Returns an associated array containing the id and name of the modules in DB
 *
 * @return array The associated array (id => name)
 */
function block_attestoodle_get_modules() {
    global $DB;
    $result = $DB->get_records_menu('modules', null, null, 'id, name');

    return $result;
}

/**
 * Returns an array of modules associated to a list of courses
 *
 * @param array courses List of courses that need to be match before returning the courses_modules
 * @return array The courses modules
 */
function block_attestoodle_get_courses_modules($courses) {
    global $DB;

    // On filtre les courses pour ne récupérer que les id.
    $idcourses = array_map(function ($results) {
        return $results->id;
    }, $courses);

    if (count($idcourses) > 0) {
        $stridcourses = implode(",", $idcourses);

        $request = "SELECT DISTINCT module FROM {course_modules} WHERE course IN ({$stridcourses})";
        $results = $DB->get_records_sql($request);

        $modulesresults = array_map(function ($results) {
            return $results->module;
        }, $results);
        return $modulesresults;
    } else {
        return array();
    }
}

function block_attestoodle_get_activities_with_intro($activities) {
    global $DB;

    $arrayreturn = array();

    foreach ($activities as $tablename) {
        $request = "SELECT * FROM {" . $tablename . "} WHERE intro LIKE '%<span class=\"tps_jalon\">%</span>%'";
        $results = $DB->get_records_sql($request);
        if (count($results) > 0) {
            foreach ($results as $result) {
                array_push($arrayreturn, $result);
            }
        }
    }

    return $arrayreturn;
}

function parse_learners_as_stdclass($data, $trainingid) {
    $newdata = array_map(function($o) {
            global $OUTPUT, $trainingid;
            $stdclass = $o->get_object_as_stdclass();

            $parameters = array(
                    'training' => $trainingid,
                    'user' => $stdclass->id);
            $url = new moodle_url('/blocks/attestoodle/pages/learner_details.php', $parameters);
            $label = get_string('learner_details_btn_text', 'block_attestoodle');
            $options = array('class' => 'attestoodle-button');

            $stdclass->link = $OUTPUT->single_button($url, $label, 'get', $options);

            return $stdclass;
    }, $data);
    return $newdata;
}

function parse_trainings_as_stdclass($data) {
    $newdata = array_map(function($o) {
            global $OUTPUT;
            $stdclass = $o->get_object_as_stdclass();

            $parameters = array('id' => $stdclass->id);
            $url = new moodle_url('/blocks/attestoodle/pages/training_details.php', $parameters);
            $label = get_string('training_details_btn_text', 'block_attestoodle');
            $options = array('class' => 'attestoodle-button');

            $stdclass->link = $OUTPUT->single_button($url, $label, 'get', $options);

            return $stdclass;
    }, $data);
    return $newdata;
}

/**
 * Parse a number of minutes into a hours string
 *
 * @param integer $minutes The number of minutes to parse
 * @return string The hourse corresponding (formating 'XhYY')
 */
function parse_minutes_to_hours($minutes) {
    $h = floor($minutes / 60);
    $m = $minutes % 60;
    $m = $m < 10 ? '0' . $m : $m;

    return $h . "h" . $m;
}

function parse_datetime_to_readable_format($datetime) {
    return $datetime->format("d/m/Y à G:i:s");
}
