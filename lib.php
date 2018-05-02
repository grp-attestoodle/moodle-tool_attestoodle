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
    return $datetime->format("d/m/Y");
}

function block_attestoodle_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.
    if ($context->contextlevel != CONTEXT_USER) {
        return false;
    }

    // Make sure the filearea is one of those used by the plugin.
    if ($filearea !== 'certificates') {
        return false;
    }

    // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
    // $itemid = array_shift($args); // The first item in the $args array.
    $itemid = 0;

    // Use the itemid to retrieve any relevant data records and perform any security checks to see if the
    // user really does have access to the file in question.

    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args); // The last item in the $args array.
    if (!$args) {
        $filepath = '/'; // If $args is empty => the path is '/'.
    } else {
        $filepath = '/'.implode('/', $args).'/'; // Var $args contains elements of the filepath.
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'block_attestoodle', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false; // The file does not exist.
    }

    // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
    // From Moodle 2.3, use send_stored_file instead.
    send_stored_file($file, 1, 0, $forcedownload, $options);
}
