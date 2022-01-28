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
 * Allows communication with any additional plugins.
 *
 * @package    tool_attestoodle
 * @copyright  2019 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_attestoodle\utils;




/**
 * This is the singleton class allowing communication with any additional plugins
 *
 * @copyright  2019 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plugins_accessor extends singleton {
    /** @var plugins_accessor Instance of the plugins_accessor singleton */
    protected static $instance;

    /**
     * Provides the html code of the link to the restore form, or an empty string.
     *
     * @return string The link to restore form.
     */
    public static function get_restore_link() {
        $pluginman = \core_plugin_manager::instance();
        $pluginfo = $pluginman->get_plugin_info("tool_save_attestoodle");
        if (!isset($pluginfo)) {
            return "";
        }
        $sourcephp = $pluginfo->rootdir .'/lib.php';

        if (file_exists ($sourcephp)) {
            require_once($sourcephp);
            return lnk_load();
        }
        return "";
    }

    /**
     * Provides the html code of the button save training, or an empty string.
     *
     * @param integer $trainingid The identifier of the training we want to save.
     * @return string The html code of the backup button.
     */
    public static function get_save_btn($trainingid) {
        $pluginman = \core_plugin_manager::instance();
        $pluginfo = $pluginman->get_plugin_info("tool_save_attestoodle");
        if (!isset($pluginfo)) {
            return "";
        }

        $sourcephp = $pluginfo->rootdir .'/lib.php';

        if (file_exists ($sourcephp)) {
            require_once($sourcephp);
            return btn_save($trainingid);
        }
        return "";
    }

    /**
     * Tests the presence of the plugin managing the programming tasks.
     *
     * @return structure conains info on the task plugin, or null.
     */
    public static function get_task_plugin_info() {
        $pluginman = \core_plugin_manager::instance();
        $pluginfo = $pluginman->get_plugin_info("tool_taskattestoodle");
        if (!isset($pluginfo)) {
            return null;
        }
        return $pluginfo;
    }

    /**
     * Provides the url of the attestation planning page.
     *
     * @param int $trainingid The training ID.
     * @return moodle_url Url for planning page ou null.
     */
    public static function get_task_link($trainingid) {
        $pluginman = \core_plugin_manager::instance();
        $pluginfo = self::get_task_plugin_info();
        if (!isset($pluginfo)) {
            return null;
        }
        $sourcephp = $pluginfo->rootdir .'/lib.php';
        if (file_exists ($sourcephp)) {
            require_once($sourcephp);
            return task_link($trainingid);
        }
        return null;
    }

    /**
     * Propagates the suppression of training to subplugins.
     * Subplugins must have the xxx__deletetraining($id) method in their lib.php file.
     *
     * @param int $trainingid The training ID.
     * @return string Provides a description of possible errors in sub plugin, or empty
     * if no errors.
     */
    public static function delete_training($trainingid) {
        $pluginman = \core_plugin_manager::instance();
        $lst = $pluginman->other_plugins_that_require('tool_attestoodle');
        $ret = "";
        if (count($lst) > 0) {
            foreach ($lst as $cur) {
                $pluginfo = $pluginman->get_plugin_info($cur);
                $sourcephp = $pluginfo->rootdir .'/lib.php';
                if (file_exists ($sourcephp)) {
                    require_once($sourcephp);
                    $meth = $cur . "_deletetraining";
                    if (function_exists($meth)) {
                        $ret .= $meth($trainingid);
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * Provides the most appropriate interval for training.
     *
     * @param int $trainingid The training ID.
     * @return stdClass contains the interval d_start and d_end in timestamp.
     */
    public static function get_interval($trainingid) {
        global $DB;
        $now = new \DateTime();

        $ret = new \stdClass();
        $ret->d_start = 0;
        $ret->d_end = 0;

        $pluginman = \core_plugin_manager::instance();
        $pluginfo = self::get_task_plugin_info();
        if (isset($pluginfo)) {
            $sourcephp = $pluginfo->rootdir .'/lib.php';
            if (file_exists ($sourcephp)) {
                require_once($sourcephp);
                $ret = tool_taskattestoodle_get_interval($trainingid);
            }
            if ($ret->d_end > 0) {
                return $ret;
            }
        }

        // If we pass here it is because no valid value of the plugin,
        // by default we take the start and end dates of the training.
        $rec = $DB->get_record('tool_attestoodle_training', array('id' => $trainingid));
        if (isset($rec->startdate)) {
            $ret->d_start = $rec->startdate;
        }
        if (isset($rec->enddate)) {
            $ret->d_end = $rec->enddate;
        }

        // If end not filled in, take current date as end date.
        if ($ret->d_end == 0) {
            $ret->d_end = $now->getTimestamp();
        }

        // Test if there is already a certificate for this training.
        $req = "select c.id, l.begindate, l.enddate
                  from {tool_attestoodle_certif_log} c
                  join {tool_attestoodle_launch_log} l on c.launchid = l.id
                 where trainingid = ?
              order by enddate desc";
        $records = $DB->get_records_sql($req, array($trainingid));
        if (count($records) > 0) {
            $first = true;
            foreach ($records as $record) {
                if ($first) {
                    $thedate = \DateTime::createFromFormat('Y-m-d', $record->begindate);
                    $beg = $thedate->getTimestamp();
                    $thedate = \DateTime::createFromFormat('Y-m-d', $record->enddate);
                    $end = $thedate->getTimestamp();
                    $first = false;
                }
            }
            $diff = $end - $beg;
            $ndiff = $now->getTimestamp() - $end;
            $pcdiff = $ndiff / $diff;
            // If the current date is close to the last interval, the last interval is kept.
            if ($pcdiff < .5) {
                $ret->d_start = $beg;
                $ret->d_end = $end;
            } else {
                // Otherwise we take from the last date (+ 1d 2h) to now.
                $ret->d_start = $end;
                $ret->d_end = $now;
            }
        }
        if ($ret->d_end <= $ret->d_start) {
            $ret->d_end = $ret->d_start + 93600;
        }

        return $ret;
    }
}
