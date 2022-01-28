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
$string['attestoodle:viewtraining'] = "View a training";
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
$string['training_management_submit_removed'] = 'Training removed from the Attestoodle trainings list';
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
$string['cumulminutes'] = 'Total time validated';
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
$string['fromdate'] = 'from {$a}';
$string['todate'] = ' to {$a}';
$string['errorformat'] = 'Wrong format';
$string['activitiesupdated'] = 'Activities updated';
$string['activitiesnoupdated'] = 'Activities not updated';
$string['errnotemplatename'] = 'template must have a name !';
$string['templatename'] = 'Name of template';
$string['listtemplate_title'] = 'List of attestation templates';
$string['confdeltemplate'] = 'Are you sure you want to delete the template "{$a->name}" ?';
$string['updatetraintemplate'] = 'Update the training';
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
$string['grp_course'] = 'Course';
$string['grp_activity'] = 'Activity';
$string['grp_type'] = 'Type of activity';
$string['grp_level1'] = 'Level 1 grouping';
$string['grp_level2'] = 'Level 2 grouping';
$string['activity_header_coursename_'] = 'Courses taken';
$string['activity_header_coursename_type'] = 'Type of activity followed by course';
$string['activity_header_coursename_name'] = 'Activities followed by courses';
$string['activity_header_type_'] = 'Type of activity followed';
$string['activity_header_type_coursename'] = 'Courses followed by types of activity';
$string['activity_header_type_name'] = 'Activities followed by types';
$string['activity_header_name_'] = 'Followed Activities';
$string['activity_header_name_coursename'] = 'Courses followed by activity';
$string['activity_header_name_type'] = 'Type of activity followed by activity';
$string['infonocourses'] = 'There are no courses with completion monitoring from the category associated with this training';
$string['infonostudent'] = 'No learner in a course with completion tracking from the category associated with this training';
$string['navlevel1'] = 'Trainings';
$string['navlevel2'] = 'Setting';
$string['navlevel3a'] = 'Learners';
$string['navlevel4a'] = 'Details';
$string['navlevel3b'] = 'Milestones';
$string['navlevel1b'] = 'List templates';
$string['navlevel2b'] = 'Template ';
$string['trainingname'] = 'Training name';
$string['filtergrouplabel'] = 'Filter modules :';
$string['filtermodulealltype'] = 'All types';
$string['filtermoduleactivitytype'] = 'Only Activities';
$string['filtermodulename'] = 'on their name :';
$string['filtermodulevisible'] = 'visibles or not';
$string['filtermodulevisibleyes'] = 'visibles';
$string['filtermodulevisibleno'] = 'hidden';
$string['filtermodulerestrict'] = 'with access restriction or not';
$string['filtermodulerestrictyes'] = 'with access restriction';
$string['filtermodulerestrictno'] = 'without access restriction';
$string['filtermodulemilestone'] = 'milestones or not';
$string['filtermodulemilestoneyes'] = 'milestones';
$string['filtermodulemilestoneno'] = 'not milestones';
$string['filtermodulebtn'] = 'Filter';
$string['modulefiltergroup'] = 'Modules filtering';
$string['modulefiltergroup_help'] = '<p>This filter define which modules will be displayed in the following list,
 depending on a serie of criterias.</p>
<p>Note : the name criteria has priority over all others.</p>';
$string['orderbylabel'] = 'Categorized by :';
$string['orderbycourse'] = 'Course';
$string['orderbymonth'] = 'Expected completion month';
$string['orderbygroup'] = 'Modules categorization';
$string['orderbygroup_help'] = '<p>This define the way modules will be categorized on the display below.</p>
<p>Will they be grouped </p>
<ul>
<li>by course</li>
<li>by expected completion date (this allow a time related representation)</li>
</ul>';
$string['monthfrom'] = 'From';
$string['orderbybtn'] = 'Reorder';
$string['module_expected_date_label'] = 'Completion expected on';
$string['module_expected_date_no'] = 'Without expected completion date';
$string['module_expected_date_outside'] = 'Outside expected completion date';
$string['period_form'] = 'Rule on dates';
$string['period_form_help'] = 'Indicate the period
 from the included start date
 until the end date
 included.';
$string['errduplicatename'] = 'Duplicate nane detected !!';
$string['createtemplate'] = 'Create new template';
$string['stop'] = 'Stop';
$string['confirmation'] = 'Confirmation';
$string['certificategenerate'] = 'Certification generation';
$string['msgongoing'] = 'Ongoing treatment';
$string['questgenerate'] = 'Do you want to generate {$a} certificates ?';
$string['error_same_criteria'] = 'Groupings can not be identical !!';
$string['nottraining'] = 'it\'s category is not training';
$string['milestoneorphan'] = 'List of milestones in error (their activity is deleted)';
$string['timecredited'] = 'Credited duration';
$string['milestonenews'] = 'List of training courses that have received new activities';
$string['nbnewactivity'] = 'Number of new activity';
$string['btn_deletemilestonerr'] = 'Remove milestones in error';
$string['btn_deletenotification'] = 'Delete the notification';
$string['disablecertif'] = 'Exclude';
$string['disablecertiflib'] = ' (No certificate will be generated for this learner)';
$string['customizecertif'] = 'Custom edition';
$string['customizecertiflib'] = ' Customize the attestation of this learner';
$string['showcompletiondate'] = 'View the completion date';

// Url help (if they not exist no help icon was display).
$string['UrlHlpTo_training_management'] = 'https://github.com/grp-attestoodle/moodle-tool_attestoodle/wiki/training_parameters';
$string['UrlHlpTo_sitecertificate'] = 'https://github.com/grp-attestoodle/moodle-tool_attestoodle/wiki/template';
$string['UrlHlpTo_listtemplate'] = 'https://github.com/grp-attestoodle/moodle-tool_attestoodle/wiki/templates_list';
$string['UrlHlpTo_trainings_list'] = 'https://github.com/grp-attestoodle/moodle-tool_attestoodle/wiki/trainings_list';
$string['UrlHlpTo_detailled_report'] = 'https://github.com/grp-attestoodle/moodle-tool_attestoodle/wiki/detailled_report';
$string['UrlHlpTo_global_report'] = 'https://github.com/grp-attestoodle/moodle-tool_attestoodle/wiki/global_report';
$string['UrlHlpTo_manage_milestones'] = 'https://github.com/grp-attestoodle/moodle-tool_attestoodle/wiki/manage_milestones';
$string['UrlHlpTo_selectlearners'] = 'https://github.com/grp-attestoodle/moodle-tool_attestoodle/wiki/manage_learners';
// Privacy.
$string['privacy:metadata:core_files'] = 'Attestoodle stores generated attestation files, per learner-training-interval of time';
$string['privacy:metadata:tool_attestoodle_launch_log'] = 'Log of certificate generation launches';
$string['privacy:metadata:tool_attestoodle_launch_log:operatorid'] = 'Identifies the user who generated a certification batch.';
$string['privacy:metadata:tool_attestoodle_launch_log:timegenerated'] = 'Date and time of launch of the generation of the certificate batch';
$string['privacy:metadata:tool_attestoodle_launch_log:begindate'] = 'Beginning of the certification period, entered by the operator';
$string['privacy:metadata:tool_attestoodle_launch_log:enddate'] = 'End of the certification period, entered by the operator';
$string['privacy:metadata:tool_attestoodle_certif_log'] = 'Links between the learner and the certification files ';
$string['privacy:metadata:tool_attestoodle_certif_log:learnerid'] = 'The learner\'s identifier associated with the attestation files';
$string['privacy:metadata:tool_attestoodle_certif_log:filename'] = 'Name of the learner\'s attestation files';
$string['privacy:metadata:tool_attestoodle_user_style'] = 'Personalization of the attestation template per learner';
$string['privacy:metadata:tool_attestoodle_user_style:userid'] = 'The identifier of the learner whose attestation model is personalised';
$string['privacy:metadata:tool_attestoodle_user_style:templateid'] = 'The identifier of the attestation model assigned to the learner';
$string['privacy:metadata:tool_attestoodle_user_style:enablecertificate'] = 'Refers to whether or not the learner is excluded from the generation of the certificate';
$string['privacy:metadata:tool_attestoodle_value_log'] = 'Time credited per completed milestone.';
$string['privacy:metadata:tool_attestoodle_value_log:moduleid'] = 'Identifier of the credited milestone activity';
$string['privacy:metadata:tool_attestoodle_value_log:creditedtime'] = 'Time credited for completion of the milestone';
$string['error_unknown_item'] = 'Unknown (the item has been deleted)';
$string['totaltimetraining'] = 'Total time of training :';
$string['nomilestone'] = 'No milestone sets';
$string['starttraining'] = 'Start date of the training';
$string['endtraining'] = 'End date of the training';
$string['durationtraining'] = 'Theoretical duration of the training';
$string['onecoursemilestonetitle'] = 'milestone of the "{$a}" course';
$string['errmorecours'] = ' courses with a short name including "{$a}"';
$string['errnoactivity'] = 'No activities with completion present in the course {$a}';
$string['errnothingtosearch'] = 'You must enter a short course name';
$string['notifytotaltraining'] = 'There are already {$a} training courses for this category including : ';
$string['linktotraininglst'] = 'Access the list of training courses';
$string['selectlearner'] = 'Select learners';
$string['findlearner'] = 'Search learner';
$string['excludeselect'] = 'Exclude selection';
$string['keepselect'] = 'Keep the selection';
$string['number_learners'] = 'Number of learners : {$a}';
$string['email'] = 'E Mail';
$string['selection'] = 'Selection';
$string['result'] = 'Result';
$string['milestones'] = 'Milestones';
$string['learners'] = 'Learners';
$string['fortraining'] = ' for training : {$a}';
$string['confirmtraincreate'] = 'Do you want to create a new formation ?';
$string['errsendzip'] = 'An error occured: impossible to send ZIP file.';
$string['validate'] = 'Validate';
$string['trainingcriteria'] = 'Training criteria';
$string['enrolcriteria'] = 'Enrol criteria';
$string['deletetraining'] = 'Delete a training';
$string['deletemodel'] = 'Delete a template';
$string['errdateend'] = 'The end date must be greater than the start date';
$string['deadline'] = 'Deadline';
$string['finished'] = 'Finished';
$string['toplan'] = 'To plan';
