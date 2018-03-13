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

// Importation de la config $CFG qui importe égalment $DB et $OUTPUT.
// @todo create an autoloader.
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/blocks/attestoodle/lib.php');
//require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/trainings_factory.php');
//require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/categories_factory.php');
//require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/courses_factory.php');
//require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/activities_factory.php');
//require_once($CFG->dirroot.'/blocks/attestoodle/classes/factories/learners_factory.php');

//require_once($CFG->dirroot.'/blocks/attestoodle/classes/category.php');
//require_once($CFG->dirroot.'/blocks/attestoodle/classes/course.php');
//require_once($CFG->dirroot.'/blocks/attestoodle/classes/activity.php');
//require_once($CFG->dirroot.'/blocks/attestoodle/classes/validated_activity.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/output/renderable/renderable_trainings_list.php');
require_once($CFG->dirroot.'/blocks/attestoodle/classes/output/renderable/renderable_training_learners_list.php');

use block_attestoodle\factories\trainings_factory;
use block_attestoodle\factories\categories_factory;
use block_attestoodle\output\renderable\renderable_trainings_list;
use block_attestoodle\output\renderable\renderable_training_learners_list;

$page = optional_param('page', '', PARAM_ALPHA);

$context = context_system::instance();
$PAGE->set_context($context);

require_login();

// Always create categories.
categories_factory::get_instance()->create_categories();

switch($page) {
    case 'trainingsmanagement':
        $PAGE->set_url(new moodle_url('/blocks/attestoodle/index.php',
                ['page' => $page]));
        $PAGE->set_title(get_string('trainings_management_page_title', 'block_attestoodle'));
        $PAGE->set_heading(get_string('trainings_management_main_title', 'block_attestoodle'));

        $userhascapability = has_capability('block/attestoodle:managetrainings', $context);
        require_capability('block/attestoodle:managetrainings', $context);

        $output = $PAGE->get_renderer('block_attestoodle');
        $renderable = new renderable_trainings_management(trainings_factory::get_instance()->get_trainings());
        break;
    case 'learners':
        $trainingid = required_param('training', PARAM_INT);
        $PAGE->set_url(new moodle_url('/blocks/attestoodle/index.php',
                ['page' => $page, 'training' => $trainingid]));
        $PAGE->set_title(get_string('training_learners_list_page_title', 'block_attestoodle'));

        $userhascapability = has_capability('block/attestoodle:displaylearnerslist', $context);
        require_capability('block/attestoodle:displaylearnerslist', $context);

        $training = null;
        $trainingexist = trainings_factory::get_instance()->has_training($trainingid);
        if ($trainingexist) {
            $training = trainings_factory::get_instance()->retrieve_training($trainingid);
            $PAGE->set_heading(get_string('training_learners_list_main_title', 'block_attestoodle', $training->get_name()));
        } else {
            $PAGE->set_heading(get_string('training_learners_list_main_title_error', 'block_attestoodle'));
        }

        $output = $PAGE->get_renderer('block_attestoodle');
        $renderable = new renderable_training_learners_list($training);
        break;
    case 'trainingslist':
    default:
        $PAGE->set_url(new moodle_url('/blocks/attestoodle/index.php'));
        $PAGE->set_title(get_string('trainings_list_page_title', 'block_attestoodle'));
        $PAGE->set_heading(get_string('trainings_list_main_title', 'block_attestoodle'));

        $userhascapability = has_capability('block/attestoodle:displaytrainings', $context);
        require_capability('block/attestoodle:displaytrainings', $context);

        $output = $PAGE->get_renderer('block_attestoodle');
        $renderable = new renderable_trainings_list(trainings_factory::get_instance()->get_trainings());
}


echo $OUTPUT->header();

// ... to be callable by the output->render method bellow.
// Note: the method automagically call the method "render_[renderable_class]"
// ...defined in the renderer object (here $output)
echo $output->render($renderable);

echo $OUTPUT->footer();
