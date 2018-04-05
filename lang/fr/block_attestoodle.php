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
$string['settings_header'] = "Configuration d'Attestoodle";
$string['settings_description'] = "Vous permet de modifier certains paramètres du plug-in Attestoodle";
$string['settings_student_role_label'] = 'Id du rôle Etudiant';
$string['settings_student_role_helper'] = 'Id du rôle Etudiant dans la base de données';

// Block strings.
$string['plugin_access'] = 'Accès au plug-in';

// Shared strings.
$string['courses_list_btn_text'] = 'Liste des cours Attestoodle';
$string['trainings_list_btn_text'] = 'Liste des formations Attestoodle';
$string['backto_trainings_list_btn_text'] = 'Retour à la liste des formations Attestoodle';
$string['backto_training_detail_btn_text'] = 'Retour aux détails de la formation';
$string['backto_training_learners_list_btn_text'] = 'Retour à la liste des étudiants';
$string['unknown_training_id'] = 'Aucune formation Attestoodle ayant l\'identifiant {$a}';
$string['unknown_learner_id'] = 'Aucun étudiant ayant l\'identifiant {$a}';

// Training list page strings.
$string['trainings_list_page_title'] = 'Moodle - Attestoodle - Liste des formations';
$string['trainings_list_main_title'] = 'Formations Attestoodle';
$string['trainings_list_manage_trainings_link'] = 'Gérer les formations';
$string['trainings_list_table_header_column_id'] = 'ID';
$string['trainings_list_table_header_column_name'] = 'Nom';
$string['trainings_list_table_header_column_hierarchy'] = 'Arborescence';
$string['trainings_list_table_header_column_description'] = 'Description';
$string['trainings_list_table_link_details'] = 'Suivi des étudiants';
$string['trainings_list_warning_no_trainings'] = 'Pas de formations enregistrées';

// Trainings management page strings.
$string['trainings_management_page_title'] = 'Moodle - Attestoodle - Gestion des formations';
$string['trainings_management_main_title'] = 'Gestion des formations';
$string['trainings_management_trainings_list_link'] = 'Retour à la liste des formations';
$string['trainings_management_warning_no_submitted_data'] = 'Aucune donnée envoyée';
$string['trainings_management_warning_invalid_form'] = "Le formulaire n'est pas valide";
$string['trainings_management_info_form_canceled'] = 'Le formulaire a été annulé';

// Training learners list page strings.
$string['training_learners_list_page_title'] = 'Moodle - Attestoodle - Liste des apprenants';
$string['training_learners_list_main_title'] = 'Rapport formation {$a}';
$string['training_learners_list_main_title_error'] = 'Erreur !';
$string['training_learners_list_heading'] = '{$a} apprenants dans la formation';
$string['training_learners_list_edit_training_link'] = 'Gérer la formation';
$string['training_learners_list_table_link_details'] = 'Détails';
$string['training_learners_list_table_header_column_id'] = 'ID';
$string['training_learners_list_table_header_column_firstname'] = 'Prénom';
$string['training_learners_list_table_header_column_lastname'] = 'Nom';
$string['training_learners_list_table_header_column_validated_activities'] = 'Activités validées';
$string['training_learners_list_table_header_column_total_milestones'] = 'Total temps jalons validés';

// Training details page strings.
$string['training_details_page_title'] = "Moodle - Attestoodle - Gestion d'une formation";
$string['training_details_main_title'] = 'Gestion de la formation {$a} : ';
$string['training_details_main_title_error'] = "Erreur !";
$string['training_details_learners_list_btn_text'] = "Retour à la liste des apprenants";
$string['training_details_unknown_training_id'] = "Aucune formation avec l'identifiant : ";
$string['training_details_warning_no_submitted_data'] = 'Aucune donnée envoyée';
$string['training_details_error_invalid_form'] = "Le formulaire n'est pas valide";
$string['training_details_info_form_canceled'] = "Le formulaire a été annulé";
$string['training_details_form_input_suffix'] = "min.";

// Learner details page strings.
$string['learner_details_page_title'] = "Moodle - Attestoodle - Détail d'un apprenant";
$string['learner_details_main_title'] = 'Jalons validés par {$a}';
$string['learner_details_main_title_error'] = "Erreur !";
$string['learner_details_unknown_training_id'] = "Pas de formation avec l'identifiant : ";
$string['learner_details_unknown_learner_id'] = "Pas d'apprenant avec l'identifiant : ";
$string['learner_details_begin_date_label'] = "Date de début : ";
$string['learner_details_end_date_label'] = "Date de fin : ";
$string['learner_details_submit_button_value'] = "Filtrer";
$string['learner_details_no_validated_activities'] = "Aucune activités validées dans la période sélectionnée";
$string['learner_details_table_header_column_name'] = "Jalon";
$string['learner_details_table_header_column_type'] = "Type";
$string['learner_details_table_header_column_training_name'] = "Formation";
$string['learner_details_table_header_column_course_name'] = "Cours";
$string['learner_details_table_header_column_validated_time'] = "Validé le";
$string['learner_details_table_header_column_milestones'] = "Temps jalon crédité";
$string['learner_details_generate_certificate_link'] = "Générer l'attestation";
$string['learner_details_regenerate_certificate_link'] = "Générer une nouvelle attestation";

// Download certificate page strings.
$string['download_certificate_file_link_text'] = "Télécharger le fichier";

// Unknown strings.
// @todo review these strings.
$string['attestoodle:addinstance'] = 'Ajout un block Attestoodle';
$string['attestoodle:myaddinstance'] = 'Ajouter un block attestoodle à mon Moodle';
$string['blockstring'] = 'Block string';
