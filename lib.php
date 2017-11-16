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


/**
 * Returns the courses stored in database
 *
 * @param boolean completionenabled Set to true if we need only the courses where completion is enabled
 * @return array Array of courses retrieved in DB
 */
function block_attestoodle_get_courses($completionenabled = true) {
    global $DB;
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

    // on filtre les courses pour ne récupérer que les id
    $id_courses = array_map(function ($results) {
            return $results->id;
        }, $courses);

    if (count($id_courses) > 0) {


        $str_id_courses = implode(",", $id_courses);
        // on spécifie une clause WHERE particuliere
        $where_clause = "course IN ({$str_id_courses})";

        // $results = $DB->get_records_select('course_modules', $where_clause);
        $request = "SELECT DISTINCT module FROM {course_modules} WHERE course IN ({$str_id_courses})";
        $results = $DB->get_records_sql($request);

        $modules_results = array_map(function ($results) {
                return $results->module;
            }, $results);
        return $modules_results;
        //return $results;
    } else {
        return array();
    }
}

function block_attestoodle_get_activities_with_intro($activities) {
    global $DB;

    $array_return = array();

    foreach ($activities as $table_name) {
        $request = "SELECT * FROM {" . $table_name . "} WHERE intro LIKE '%<span class=\"tps_jalon\">%</span>%'";
        $results = $DB->get_records_sql($request);
        if (count($results) > 0) {
            foreach ($results as $result) {
                array_push($array_return, $result);
            }
        }
    }

    return $array_return;
}
