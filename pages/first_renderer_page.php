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

// Standard GPL and phpdocs
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/blocks/attestoodle/pages/renderer_with_template_page.php');
require_once($CFG->dirroot.'/blocks/attestoodle/pages/renderer_simple_page.php');

use block_attestoodle\output\renderer_with_template_page;
use block_attestoodle\output\renderer_simple_page;
use block_attestoodle\factories\trainings_factory;

$trainingid = optional_param('trainingid', null, PARAM_INT);
$renderwithtemplate = optional_param('renderwithtemplate', false, PARAM_BOOL);
$training = null;
if (isset($trainingid)) {
    $training = trainings_factory::get_instance()->retrieve_training($trainingid);
}

// Set up the page.
$title = "Un titre dans un render";
$pagetitle = $title;
$url = new moodle_url('/blocks/attestoodle/pages/first_renderer_page.php');
$PAGE->set_url($url);
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_title($title);
$PAGE->set_heading($title . "(heading)");

// Automagically search in block_attestoodle/output/renderer.php
$output = $PAGE->get_renderer('block_attestoodle');

echo $output->header();
echo $output->heading($pagetitle);

if ($renderwithtemplate) {
    // The renderable need to implement "renderable" interface...
    $renderable = new renderer_with_template_page('Le texte à mettre dans le renderer avec template', $training);
} else {
    $renderable = new renderer_simple_page('Le texte à mettre dans le renderer', $training);
}
// ... to be callable by the output->render method bellow.
// Note: the method automagically call the method "render_[renderable_class]"
// ...defined in the renderer object (here $output)
echo $output->render($renderable);

echo $output->footer();
