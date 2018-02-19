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
 * @todo To be supress
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
 * @todo To be supress
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
 * @todo To be supress
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

/**
 * @todo To be supress
 *
 * @global type $DB
 * @param type $activities
 * @return array
 */
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

/**
 * @todo To replace with a specific UI class
 *
 * @param type $data
 * @param type $trainingid
 * @return type
 */
function parse_learners_as_stdclass($data, $trainingid) {
    $newdata = array_map(function($o) {
            global $OUTPUT, $trainingid;
            $stdclass = $o->get_object_as_stdclass();

            $parameters = array(
                    'training' => $trainingid,
                    'user' => $stdclass->id);
            $url = new moodle_url('/blocks/attestoodle/pages/learner_details.php', $parameters);
            $label = get_string('training_learners_list_table_link_details', 'block_attestoodle');
            $attributes = array('class' => 'attestoodle-button');

            $stdclass->link = html_writer::link($url, $label, $attributes);

            return $stdclass;
    }, $data);
    return $newdata;
}

/**
 * @todo To replace with a specific UI class
 *
 * @param type $data
 * @return type
 */
function parse_trainings_as_stdclass($data) {
    $newdata = array_map(function($o) {
            global $OUTPUT;
            $stdclass = $o->get_object_as_stdclass();

            $parameters = array('id' => $stdclass->id);
            $url = new moodle_url('/blocks/attestoodle/pages/training_learners_list.php', $parameters);
            $label = get_string('trainings_list_table_link_details', 'block_attestoodle');
            $attributes = array('class' => 'attestoodle-button');

            $stdclass->link = html_writer::link($url, $label, $attributes);

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

/**
 * Parse a datetime object to a readable format
 *
 * @param \DateTime $datetime The datetime object
 * @return string the readable format
 */
function parse_datetime_to_readable_format($datetime) {
    return $datetime->format("d/m/Y à G:i:s");
}

function block_attestoodle_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    // echo "Dans block_attestoodle_pluginfile \n";
    // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.
    if ($context->contextlevel != CONTEXT_USER) {
        /* echo "mauvais context level : {$context->contextlevel} \n";
        die(); */
        return false;
    }

    // Make sure the filearea is one of those used by the plugin.
    if ($filearea !== 'certificates') {
        /* echo "mauvaise filearea : {$filearea} \n";
        die(); */
        return false;
    }

    // Make sure the user is logged in and has access to the module (plugins that are not course modules should leave out the 'cm' part).
    /* require_login($course, true, $cm);
    require_login($course, true);*/

    // Check the relevant capabilities - these may vary depending on the filearea being accessed.
    /* if (!has_capability('blocks/attestoodle:download_certificate', $context)) {
         return false;
    } */

    // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
    // $itemid = array_shift($args); // The first item in the $args array.
    $itemid = 0;

    // Use the itemid to retrieve any relevant data records and perform any security checks to see if the
    // user really does have access to the file in question.

    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args); // The last item in the $args array.
    if (!$args) {
        $filepath = '/'; // $args is empty => the path is '/'
    } else {
        $filepath = '/'.implode('/', $args).'/'; // $args contains elements of the filepath
    }
    // $filepath = "/";

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    // echo "<pre>INFOS A CHERCHER\n";
    // echo "context : " . $context->id . "\n";
    // echo "block : " . 'block_attestoodle' . "\n";
    // echo "filearea : " . $filearea . "\n";
    // echo "itemid : " . $itemid . "\n";
    // echo "filepath : " . $filepath . "\n";
    // echo "filename : " . $filename . "\n";
    $file = $fs->get_file($context->id, 'block_attestoodle', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        // echo "pas de file \n";
        // die();
        return false; // The file does not exist.
    }

    // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
    // From Moodle 2.3, use send_stored_file instead.
    // echo "le file\n";
    // var_dump($file->get_content());
    send_stored_file($file, 1, 0, $forcedownload, $options);
}

function my_autoloader($class) {
    require_once($CFG->dirroot . "/blocks/attestoodle/classes/{$class}.php");
//    include 'classes/' . $class . '.class.php';
}
