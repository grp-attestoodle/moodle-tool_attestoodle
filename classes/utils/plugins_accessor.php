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

defined('MOODLE_INTERNAL') || die;



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
}
