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
 * Attestoodle translations, language 'en'
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Main strings.
$string['pluginname'] = 'Attestoodle';

// Configuration page strings.
$string['settings_header'] = 'Attestoodle config';
$string['settings_description'] = 'Allow you to modify some configuration of the Attestoodle plugin';
$string['settings_student_role_label'] = 'Student role ID';
$string['settings_student_role_helper'] = 'Id of the student role in database';

// Capabilities strings.
$string['attestoodle:displaytrainings'] = "Display trainings list";
$string['attestoodle:managetraining'] = "Add/Remove training";
$string['attestoodle:managemilestones'] = "Manage training milestones";
$string['attestoodle:displaylearnerslist'] = "Display training details";
$string['attestoodle:downloadcertificate'] = "Generate/Download certificates";
$string['attestoodle:learnerdetails'] = "Display learner details";
$string['attestoodle:deletetemplate'] = "Delete certificate's template";
$string['attestoodle:managetemplate'] = "Manage certificate's template";
$string['attestoodle:viewtemplate'] = "View certificat's template";

// Block strings.
$string['plugin_access'] = 'Access to plugin';

// Shared strings.
$string['courses_list_btn_text'] = 'Attestoodle courses list';
$string['trainings_list_btn_text'] = 'Back to the training main page';
$string['backto_trainings_list_btn_text'] = 'Back to Attestoodle trainings list';
$string['backto_training_detail_btn_text'] = 'Back to the training details';
$string['backto_training_learners_list_btn_text'] = 'Back to global report';
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

// Training management page strings.
$string['training_management_page_title'] = 'Moodle - Attestoodle - Training management';
$string['training_management_main_title'] = 'Attestoodle - {$a}';
$string['training_management_main_title_no_category'] = 'Attestoodle - Error';
$string['training_management_backto_category_link'] = "Back to category";
$string['training_management_training_details_link'] = "Training global report";
$string['training_management_manage_training_link'] = "Manage milestones";
$string['training_management_no_category_id'] = "No category ID specified.";
$string['training_management_unknow_category_id'] = "Invalid category ID.";
$string['training_management_checkbox_label'] = 'This category is a training';
$string['training_management_warning_no_submitted_data'] = 'No submitted data';
$string['training_management_warning_invalid_form'] = 'The form is not valid';
$string['training_management_info_form_canceled'] = 'The form has been canceled';
$string['training_management_submit_added'] = 'Category added to the Attestoodle trainings list';
$string['training_management_submit_removed'] = 'Category removed from the Attestoodle trainings list';
$string['training_management_submit_unchanged'] = 'No modification.';
$string['training_management_submit_error'] = 'An error occured while saving in DB. Try again later';

// Training learners list page strings.
$string['training_learners_list_page_title'] = 'Moodle - Attestoodle - Learners list';
$string['training_learners_list_main_title'] = 'Attestoodle - Global report "{$a}"';
$string['training_learners_list_main_title_error'] = 'Attestoodle - Error';
$string['training_learners_list_edit_training_link'] = 'Manage milestones';
$string['training_learners_list_download_zip_link'] = 'Download existing certificates';
$string['training_learners_list_generate_certificates_link'] = 'Generate all certificates';
$string['training_learners_list_heading'] = '{$a} students in the training';
$string['training_learners_list_table_link_details'] = 'Details';
$string['training_learners_list_table_header_column_id'] = 'ID';
$string['training_learners_list_table_header_column_firstname'] = 'Firstname';
$string['training_learners_list_table_header_column_lastname'] = 'Lastname';
$string['training_learners_list_table_header_column_validated_activities'] = 'Validated activities';
$string['training_learners_list_table_header_column_total_milestones'] = 'Total milestones within period';
$string['training_learners_list_notification_message_no_file'] = 'No file created';
$string['training_learners_list_notification_message_error_one'] = 'An error occured while attempting to generate certificates, try again later';
$string['training_learners_list_notification_message_error_two'] = 'All {$a} files in error';
$string['training_learners_list_notification_message_success_one'] = 'Certificates generated:';
$string['training_learners_list_notification_message_success_two'] = '{$a} new files';
$string['training_learners_list_notification_message_success_three'] = '{$a} files overwritten';
$string['training_learners_list_notification_message_with_error_one'] = 'Certificates generated with errors:';
$string['training_learners_list_notification_message_with_error_two'] = '{$a} new files';
$string['training_learners_list_notification_message_with_error_three'] = '{$a} files overwritten';
$string['training_learners_list_notification_message_with_error_viva_algerie'] = '{$a} errors';

// Training milestones management page strings.
$string['training_milestones_page_title'] = "Moodle - Attestoodle - Training details";
$string['training_milestones_main_title'] = 'Management of the training {$a}: ';
$string['training_milestones_main_title_error'] = "Error!";
$string['training_milestones_learners_list_btn_text'] = "Back to global report";
$string['training_milestones_unknown_training_id'] = "No training with the ID: ";
$string['training_milestones_warning_no_submitted_data'] = 'No submitted data';
$string['training_milestones_error_invalid_form'] = 'The form is not valid';
$string['training_milestones_info_form_canceled'] = 'The form has been canceled';
$string['training_milestones_form_input_suffix'] = 'min.';

// Learner details page strings.
$string['learner_details_page_title'] = "Moodle - Attestoodle - Learner details";
$string['learner_details_main_title'] = 'Attestoodle - Milestones validated by "{$a}"';
$string['learner_details_main_title_error'] = "Attestoodle - Error";
$string['learner_details_unknown_training_id'] = "No training with the ID: ";
$string['learner_details_unknown_learner_id'] = "No student with the ID: ";
$string['learner_details_begin_date_label'] = "Begin date: ";
$string['learner_details_end_date_label'] = "End date: ";
$string['learner_details_submit_button_value'] = "Filter";
$string['learner_details_no_training_registered'] = "Selected learner is not registered to any training.";
$string['learner_details_no_validated_activities'] = "No validated activity within the specified period for this training.";
$string['learner_details_table_header_column_name'] = "Milestone";
$string['learner_details_table_header_column_type'] = "Type";
$string['learner_details_table_header_column_training_name'] = "Training";
$string['learner_details_table_header_column_course_name'] = "Course";
$string['learner_details_table_header_column_validated_time'] = "Validated time";
$string['learner_details_table_header_column_milestones'] = "Milestone credit";
$string['learner_details_generate_certificate_link'] = "Generate certificate";
$string['learner_details_regenerate_certificate_link'] = "Generate a new certificate";
$string['learner_details_download_certificate_link'] = "Download certificate";
$string['learner_details_notification_message_error'] = "An error occured while attempting to create the file on the server, try again later";
$string['learner_details_notification_message_new'] = "The new certificate has been create on the server";
$string['learner_details_notification_message_overwritten'] = "A new certificate has been create on the server, the old file has been overwritten";


$string['training_list_link'] = "List of trainings";
$string['student_list_link'] = "List of students";
$string['training_setting_link'] = "setting";
$string['milestone_manage_link'] = "Monitor activity milestones";

// Unknown strings.
// @TODO review these strings.
$string['attestoodle:addinstance'] = 'Add an attestoodle block';
$string['attestoodle:myaddinstance'] = 'Add an attestoodle block to my moodle';
$string['blockstring'] = 'Block string';

$string['template_certificate'] = 'Template of certificate';
$string['actions'] = 'Actions';
$string['add_training'] = 'Add a training from a category';
$string['background'] = 'Background image';
$string['learner'] = 'Learner\'s fullname';
$string['training'] = 'Designation of the formation';
$string['period'] = 'Period';
$string['totalminute'] = 'Total time validated over the period';
$string['tabactivities'] = 'Table of activities';
$string['font'] = 'Font :';
$string['emphasis'] = 'Emphasis :';
$string['size'] = 'Size :';
$string['align'] = 'Align. :';
$string['enregok'] = 'Save';
$string['preview'] = 'preview';
$string['personalize'] = 'Personalize';
$string['activity_header_col_1'] = 'Courses taken';
$string['activity_header_col_2'] = 'total hours';
$string['personalized'] = 'Personalized';
$string['rubric'] = 'Rubric : ';
$string['literaux'] = 'Tags';
$string['literal'] = 'Label ';
$string['dateformat'] = 'Y-m-d';
$string['arraysize'] = 'Width of the table : ';
$string['fromdate'] = 'From {$a}';
$string['todate'] = ' to {$a}';
$string['errorformat'] = 'Wrong format';
$string['activitiesupdated'] = 'Activities updated';
$string['activitiesnoupdated'] = 'Activities not updated';
$string['errnotemplatename'] = 'template must have a name !';
$string['templatename'] = 'Name of template';
$string['listtemplate_title'] = 'List of attestation templates';
$string['confdeltemplate'] = 'Are you sure you want to delete the template "{$a->name}" ?';
$string['updatetraitemplate'] = 'Update the certificate template';
$string['pagebreak'] = 'Page Break';
$string['nl_never'] = ' Never ';
$string['nl_necessary'] = ' If Necessary ';
$string['nl_always'] = ' Always';
$string['viewpagenumber'] = 'Display the page number';
$string['nl_ontotal'] = ' / total page';
$string['nl_pagenumber'] = 'Page Number';
$string['nl_background'] = 'Repeat background';
$string['nl_preact'] = 'Informations before table';
$string['nl_preactch1'] = 'Only on the 1st page';
$string['nl_preactch2'] = 'On all pages';
$string['nl_postact'] = 'Informations after table';
$string['nl_postactch1'] = 'Only on the last page';