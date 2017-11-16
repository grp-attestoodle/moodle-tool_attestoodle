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
 * Attestoodle block caps.
 *
 * @package    block_attestoodle
 * @copyright  Guillaume GIRARD <dev.guillaume.girard@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_attestoodle extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_attestoodle');
    }

    // Méthode appelée au moment de l'affichage.
    public function get_content() {
        global $CFG, $OUTPUT, $USER;

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

        // File user/index.php expect course context, so get one if page has module context.
        $currentcontext = $this->page->context->get_course_context(false);

        if (!empty($this->config->text)) {
            $this->content->text = $this->config->text;
        }

        $this->content = '';
        if (empty($currentcontext)) {
            return $this->content;
        }
        if ($this->page->course->id == SITEID) {
            $this->content->text .= "site context";
        }

        if (!empty($this->config->text)) {
            $this->content->text .= $this->config->text;
        }

        $this->content->text = get_string('hello', 'block_attestoodle') . ' ' . $USER->firstname;

        $parameters = array();
        $url = new moodle_url('/blocks/attestoodle/course_list_page.php', $parameters);
        $label = get_string('course_list_btn_text', 'block_attestoodle');
        $options = array('class' => 'attestoodle-button');
        // Bouton vers un liens vers une autre page //  $this->content->text .=
        // ...$OUTPUT->single_button($url, $label, 'post', $options);
        // ...mode get offre la visu de l'url (debug).
        $this->content->text .= $OUTPUT->single_button($url, $label, 'get', $options);

        return $this->content;
    }

    // My moodle can only have SITEID and it's redundant here, so take it away.
    public function applicable_formats() {
        return array('all' => false,
            'site' => true,
            'site-index' => true,
            'course-view' => true,
            'course-view-social' => false,
            'mod' => true,
            'mod-quiz' => false);
    }

    public function instance_allow_multiple() {
        return true;
    }

    public function has_config() {
        return true;
    }

    public function cron() {
        return true;
    }

}
