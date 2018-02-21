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
 * Strings for component 'block_attestoodle', language 'en'
 *
 * @package    block_attestoodle
 * @copyright  Guillaume GIRARD <dev.guillaume.girard@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Main strings.
$string['pluginname'] = 'Attestoodle';

// Configuration page strings.
$string['settings_header'] = 'Attestoodle config';
$string['settings_description'] = 'Allow you to modify some configuration of the Attestoodle plugin';
$string['settings_student_role_label'] = 'Student role ID';
$string['settings_student_role_helper'] = 'Id of the student role in database';

// Block strings.
$string['plugin_access'] = 'Access to plugin';

// Shared strings.
$string['courses_list_btn_text'] = 'Attestoodle courses list';
$string['trainings_list_btn_text'] = 'Attestoodle trainings list';
$string['backto_trainings_list_btn_text'] = 'Back to Attestoodle trainings list';
$string['backto_training_detail_btn_text'] = 'Back to the training details';
$string['backto_training_learners_list_btn_text'] = 'Back to the students list';
$string['unknown_training_id'] = 'No training with the ID: {$a}';
$string['unknown_learner_id'] = 'No student with the ID: {$a}';

// Trainings list page strings.
$string['trainings_list_page_title'] = 'Moodle - Attestoodle - Trainings list';
$string['trainings_list_main_title'] = 'Attestoodle trainings';
$string['trainings_list_manage_trainings_link'] = 'Manage trainings';
$string['trainings_list_table_header_column_id'] = 'ID';
$string['trainings_list_table_header_column_name'] = 'Name';
$string['trainings_list_table_header_column_hierarchy'] = 'Tree';
$string['trainings_list_table_header_column_description'] = 'Description';
$string['trainings_list_table_link_details'] = 'Learners monitoring';
$string['trainings_list_warning_no_trainings'] = 'No trainings registered';

// Trainings management page strings.
$string['trainings_management_page_title'] = 'Moodle - Attestoodle - Trainings management';
$string['trainings_management_main_title'] = 'Trainings management';
$string['trainings_management_trainings_list_link'] = 'Back to trainings list';
$string['trainings_management_warning_no_submitted_data'] = 'No submitted data';
$string['trainings_management_warning_invalid_form'] = 'The form is not valid';
$string['trainings_management_info_form_canceled'] = 'The form has been canceled';

// Training learners list page strings.
$string['training_learners_list_page_title'] = 'Moodle - Attestoodle - Learners list';
$string['training_learners_list_main_title'] = 'Training {$a} report';
$string['training_learners_list_main_title_error'] = 'Error!';
$string['training_learners_list_heading'] = '{$a} students in the training';
$string['training_learners_list_edit_training_link'] = 'Manage training';
$string['training_learners_list_table_link_details'] = 'Details';
$string['training_learners_list_table_header_column_id'] = 'ID';
$string['training_learners_list_table_header_column_firstname'] = 'Firstname';
$string['training_learners_list_table_header_column_lastname'] = 'Lastname';
$string['training_learners_list_table_header_column_validated_activities'] = 'Validated activities';
$string['training_learners_list_table_header_column_total_milestones'] = 'Total milestones';

// Training details page strings.
$string['training_details_page_title'] = "Moodle - Attestoodle - Training details";
$string['training_details_main_title'] = 'Management of the training {$a}: ';
$string['training_details_main_title_error'] = "Error!";
$string['training_details_learners_list_btn_text'] = "Training's students";
$string['training_details_unknown_training_id'] = "No training with the ID: ";
$string['training_details_warning_no_submitted_data'] = 'No submitted data';
$string['training_details_error_invalid_form'] = 'The form is not valid';
$string['training_details_info_form_canceled'] = 'The form has been canceled';
$string['training_details_form_input_suffix'] = 'min.';

// Learner details page strings.
$string['learner_details_page_title'] = "Moodle - Attestoodle - Learner details";
$string['learner_details_main_title'] = 'Milestones validated by {$a}';
$string['learner_details_main_title_error'] = "Error!";
$string['learner_details_unknown_training_id'] = "No training with the ID: ";
$string['learner_details_unknown_learner_id'] = "No student with the ID: ";
$string['learner_details_begin_date_label'] = "Begin date: ";
$string['learner_details_end_date_label'] = "End date: ";
$string['learner_details_submit_button_value'] = "Filter";
$string['learner_details_no_validated_activities'] = "No validated activities within the specified period";
$string['learner_details_table_header_column_name'] = "Milestone";
$string['learner_details_table_header_column_type'] = "Type";
$string['learner_details_table_header_column_training_name'] = "Training";
$string['learner_details_table_header_column_validated_time'] = "Validated time";
$string['learner_details_table_header_column_milestones'] = "Milestone credit";
$string['learner_details_generate_certificate_link'] = "Generate certificate";

// Download certificate page strings.
$string['download_certificate_file_link_text'] = "Download file";

// Unknown strings.
// @TODO review these strings.
$string['attestoodle:addinstance'] = 'Add an attestoodle block';
$string['attestoodle:myaddinstance'] = 'Add an attestoodle block to my moodle';
$string['blockstring'] = 'Block string';
