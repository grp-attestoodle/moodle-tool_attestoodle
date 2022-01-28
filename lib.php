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
 * Useful global functions for Attestoodle.
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


use tool_attestoodle\utils\plugins_accessor;
/**
 * Parse a number of minutes into a readable hours string.
 *
 * @param integer $minutes The number of minutes to parse
 * @return string The hourse corresponding (formatted as 'XhYY')
 */
function parse_minutes_to_hours($minutes) {
    $h = floor($minutes / 60);
    $m = $minutes % 60;
    $m = $m < 10 ? '0' . $m : $m;

    return $h . "h" . $m;
}

/**
 * Parse a DateTime object into a readable format like "DD/MM/YYYY".
 *
 * @param \DateTime $datetime The DateTime object to parse
 * @return string The date in a readable format
 */
function parse_datetime_to_readable_format($datetime) {
    return $datetime->format(get_string('dateformat', 'tool_attestoodle'));
}

/**
 * Function automagically called by moodle to add a setting navigation entry.
 * @param navigation_node $parentnode The navigation node to extend.
 * @param context_coursecat $context The context for extention.
 */
function tool_attestoodle_extend_navigation_category_settings(navigation_node $parentnode, context_coursecat $context) {
    global $PAGE, $CFG;
    $userhascapability = has_capability('tool/attestoodle:managetraining', $context);
    if (!$userhascapability) {
        $userhascapability = has_capability('tool/attestoodle:viewtraining', $context);
    }
    $toolpath = $CFG->wwwroot. "/" . $CFG->admin . "/tool/attestoodle";
    if ($userhascapability) {
        $categoryid = $PAGE->context->instanceid;
        $url = new moodle_url($toolpath . '/index.php',
                array(
                        "typepage" => "trainingmanagement",
                        "categoryid" => $categoryid,
                        "call" => "categ"
                ));
        $node = navigation_node::create(
                "Attestoodle",
                $url,
                navigation_node::NODETYPE_LEAF,
                'admincompetences',
                'admincompetences',
                new pix_icon('navigation', "Attestoodle", "tool_attestoodle"));
        $node->showinflatnavigation = false;
        $parentnode->add_node($node);
    }
}

/**
 * Function automagically called by moodle to retrieve a file on the server that
 * the plug-in can interact with.
 * @param object $course course allow to acces filemanager
 * @param object $cm course module allow to access filemanager
 * @param object $context where we can access filemanager
 * @param object $filearea where filemanager stock file.
 * @param object $args arguments of path
 * @param bool $forcedownload if force donwload or not.
 * @param array $options optional parameter for form's component.
 * @link See doc at https://docs.moodle.org/dev/File_API#Serving_files_to_users
 */
function tool_attestoodle_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    if ($course && $cm) {
        $cm = $cm;
        $course = $course;
    }

    // Make sure the filearea is one of those used by the plugin.
    if ($filearea !== 'certificates' && $filearea !== 'fichier') {
        return false;
    }

    // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
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
    $file = $fs->get_file($context->id, 'tool_attestoodle', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false; // The file does not exist.
    }

    // Force non image formats to be downloaded.
    if ($file->is_valid_image()) {
        $forcedownload = false;
    }

    // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
    // From Moodle 2.3, use send_stored_file instead.
    send_stored_file($file, 1, 0, $forcedownload, $options);
}

/**
 * Add nodes to myprofile page.
 *
 * @param \core_user\output\myprofile\tree $tree Tree object
 * @param stdClass $user user object
 * @param bool $iscurrentuser
 * @param stdClass $course Course object
 *
 * @return bool
 */
function tool_attestoodle_myprofile_navigation(\core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    global $CFG, $USER;

    $context = context_user::instance($user->id);

    $viewlinkattestoodle = has_capability('tool/attestoodle:managetraining', $context);
    $viewlinkattestoodle = $viewlinkattestoodle || has_capability('tool/attestoodle:displaytrainings', $context);
    $viewlinkattestoodle = $viewlinkattestoodle || has_capability('tool/attestoodle:viewtemplate', $context);

    if ($USER->id == $user->id && has_capability('tool/attestoodle:viewtraining', $context)) {
        $category = new core_user\output\myprofile\category('attestoodle', 'Attestoodle', null);
        $tree->add_category($category);

        if (has_capability('tool/attestoodle:managetraining', $context)) {
            $urladdtrain = new moodle_url("$CFG->wwwroot/course/");
            $content = \html_writer::link($urladdtrain, get_string('add_training', 'tool_attestoodle'), array());
            $localnode = new core_user\output\myprofile\node('attestoodle', 'newtrain', null, null, null, $content);
            $tree->add_node($localnode);
        }

        if (has_capability('tool/attestoodle:displaytrainings', $context)) {
            $url = new moodle_url('/admin/tool/attestoodle/index.php', array());
            $content = \html_writer::link($url, get_string('training_list_link', 'tool_attestoodle'), array());
            $localnode = new core_user\output\myprofile\node('attestoodle', 'listtrain', null, null, null, $content);
            $tree->add_node($localnode);
        }

        if (has_capability('tool/attestoodle:viewtemplate', $context)) {
            $urllisttemplate = new moodle_url("$CFG->wwwroot/$CFG->admin/tool/attestoodle/classes/gabarit/listtemplate.php");
            $content = \html_writer::link($urllisttemplate, get_string('template_certificate', 'tool_attestoodle'), array());
            $localnode = new core_user\output\myprofile\node('attestoodle', 'lsttemplate', null, null, null, $content);
            $tree->add_node($localnode);
        }

        if (has_capability('tool/attestoodle:managetraining', $context)) {
            $lnk = plugins_accessor::get_instance()->get_restore_link();
            if (!empty($lnk)) {
                $localnode = new core_user\output\myprofile\node('attestoodle', 'restoretemplate', null, null, null, $lnk);
                $tree->add_node($localnode);
            }
        }
    }
}
