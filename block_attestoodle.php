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
 * Attestoodle block definition extending moodle block_base class.
 *
 * @package    block_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_attestoodle extends block_base {

    /**
     * Block initialisation.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_attestoodle');
    }

    /**
     * Method called to retrieve the HTML content of the block.
     *
     * @return string The HTML content
     */
    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';
        $this->content->text = '';

        // File user/index.php expect course context, so get one if page has module context.
        $currentcontext = $this->page->context->get_course_context(false);

        if (!empty($this->config->text)) {
            $this->content->text = $this->config->text;
        }

        if (empty($currentcontext)) {
            return $this->content;
        }

        // Link to the plug-in main page.
        $parameters = array('page' => 'trainingslist');
        $url = new moodle_url('/blocks/attestoodle/index.php', $parameters);
        $label = get_string('plugin_access', 'block_attestoodle');
        $attributes = array('class' => 'attestoodle-link');
        $this->content->text .= html_writer::link($url, $label, $attributes);

        return $this->content;
    }

    /**
     * ??
     *
     * @return array An array...
     */
    public function applicable_formats() {
        return array('all' => false,
            'site' => true,
            'site-index' => true,
            'course-view' => true,
            'course-view-social' => false,
            'mod' => true,
            'mod-quiz' => false);
    }

    /**
     * Define if there can be multiple Attestoodle block in a single page.
     *
     * @return boolean False
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Define if the block has a configuration.
     *
     * @return boolean True
     */
    public function has_config() {
        return true;
    }

    /**
     * Define if the block has a cron.
     *
     * @return boolean False
     */
    public function cron() {
        return false;
    }

}
