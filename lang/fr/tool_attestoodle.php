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
 * Attestoodle translations, language 'fr'
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Main strings.
$string['pluginname'] = 'Attestoodle';

// Configuration page strings.
$string['settings_header'] = "Configuration d'Attestoodle";
$string['settings_description'] = "Vous permet de modifier certains paramètres du plug-in Attestoodle";
$string['settings_student_role_label'] = 'Id du rôle Etudiant';
$string['settings_student_role_helper'] = 'Id du rôle Etudiant dans la base de données';

// Capabilities strings.
$string['attestoodle:displaytrainings'] = "Afficher les formations";
$string['attestoodle:managetraining'] = "Ajouter/Supprimer une formation";
$string['attestoodle:managemilestones'] = "Gérer les jalons d'une formation";
$string['attestoodle:displaylearnerslist'] = "Afficher les détails d'une formation";
$string['attestoodle:downloadcertificate'] = "Générer/Télécharger les attestations";
$string['attestoodle:learnerdetails'] = "Afficher les détails d'un apprenant";

// Block strings.
$string['plugin_access'] = 'Accès au plug-in';

// Shared strings.
$string['courses_list_btn_text'] = 'Liste des cours Attestoodle';
$string['trainings_list_btn_text'] = 'Retour à la page principale de la formation';
$string['backto_trainings_list_btn_text'] = 'Retour à la liste des formations Attestoodle';
$string['backto_training_detail_btn_text'] = 'Retour aux détails de la formation';
$string['backto_training_learners_list_btn_text'] = 'Retour au rapport global';
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

// Training management page strings.
$string['training_management_page_title'] = "Moodle - Attestoodle - Gestion d'une formation";
$string['training_management_main_title'] = 'Attestoodle - {$a}';
$string['training_management_main_title_no_category'] = "Attestoodle - Erreur";
$string['training_management_backto_category_link'] = "Retour à la catégorie";
$string['training_management_training_details_link'] = "Rapport global de la formation";
$string['training_management_manage_training_link'] = "Gérer les jalons";
$string['training_management_no_category_id'] = "Vous devez spécifier un ID de catégorie.";
$string['training_management_unknow_category_id'] = "Identifiant de catégorie invalide.";
$string['training_management_checkbox_label'] = 'Cette catégorie est une formation';
$string['training_management_warning_no_submitted_data'] = 'Aucune donnée envoyée';
$string['training_management_warning_invalid_form'] = "Le formulaire n'est pas valide";
$string['training_management_info_form_canceled'] = 'Le formulaire a été annulé';
$string['training_management_submit_added'] = 'La catégorie a été ajoutée à la liste des formations Attestoodle';
$string['training_management_submit_removed'] = 'La catégorie a été retirée de la liste des formations Attestoodle';
$string['training_management_submit_unchanged'] = 'Aucune modification sur la catégorie';
$string['training_management_submit_error'] = "Une erreur est survenue pendant l'enregistrement en BDD. Veuillez réessayer plus tard.";

// Training learners list page strings.
$string['training_learners_list_page_title'] = 'Moodle - Attestoodle - Rapport global';
$string['training_learners_list_main_title'] = 'Attestoodle - Rapport global "{$a}"';
$string['training_learners_list_main_title_error'] = 'Attestoodle - Erreur';
$string['training_learners_list_edit_training_link'] = 'Gérer les jalons';
$string['training_learners_list_download_zip_link'] = 'Télécharger les attestations existantes';
$string['training_learners_list_generate_certificates_link'] = 'Générer toutes les attestations';
$string['training_learners_list_heading'] = '{$a} apprenants dans la formation';
$string['training_learners_list_table_link_details'] = 'Détails';
$string['training_learners_list_table_header_column_id'] = 'ID';
$string['training_learners_list_table_header_column_firstname'] = 'Prénom';
$string['training_learners_list_table_header_column_lastname'] = 'Nom';
$string['training_learners_list_table_header_column_validated_activities'] = 'Activités validées';
$string['training_learners_list_table_header_column_total_milestones'] = 'Total temps jalons validés sur la période';
$string['training_learners_list_notification_message_no_file'] = 'Aucun fichier créé';
$string['training_learners_list_notification_message_error_one'] = 'Une erreur est survenue lors de la tentative de génération des attestations, veuillez réessayer plus tard';
$string['training_learners_list_notification_message_error_two'] = 'Tous les {$a} fichiers en erreur';
$string['training_learners_list_notification_message_success_one'] = 'Attestations générées avec succès :';
$string['training_learners_list_notification_message_success_two'] = '{$a} nouveau fichier';
$string['training_learners_list_notification_message_success_three'] = '{$a} fichiers écrasés';
$string['training_learners_list_notification_message_with_error_one'] = 'Attestations générées avec des erreurs :';
$string['training_learners_list_notification_message_with_error_two'] = '{$a} nouveaux fichiers';
$string['training_learners_list_notification_message_with_error_three'] = '{$a} fichiers écrasés';
$string['training_learners_list_notification_message_with_error_viva_algerie'] = '{$a} fichiers en erreur';

// Training milestones management page strings.
$string['training_milestones_page_title'] = "Moodle - Attestoodle - Gestion d'une formation";
$string['training_milestones_main_title'] = 'Gestion de la formation {$a} : ';
$string['training_milestones_main_title_error'] = "Attestoodle - Erreur";
$string['training_milestones_learners_list_btn_text'] = "Retour au rapport global";
$string['training_milestones_unknown_training_id'] = "Aucune formation avec l'identifiant : ";
$string['training_milestones_warning_no_submitted_data'] = 'Aucune donnée envoyée';
$string['training_milestones_error_invalid_form'] = "Le formulaire n'est pas valide";
$string['training_milestones_info_form_canceled'] = "Le formulaire a été annulé";
$string['training_milestones_form_input_suffix'] = "min.";

// Learner details page strings.
$string['learner_details_page_title'] = "Moodle - Attestoodle - Détail d'un apprenant";
$string['learner_details_main_title'] = 'Attestoodle - Jalons validés par "{$a}"';
$string['learner_details_main_title_error'] = "Attestoodle - Erreur";
$string['learner_details_unknown_training_id'] = "Pas de formation avec l'identifiant : ";
$string['learner_details_unknown_learner_id'] = "Pas d'apprenant avec l'identifiant : ";
$string['learner_details_begin_date_label'] = "Date de début : ";
$string['learner_details_end_date_label'] = "Date de fin : ";
$string['learner_details_submit_button_value'] = "Filtrer";
$string['learner_details_no_training_registered'] = "L'apprenant n'est inscrit à aucune formation.";
$string['learner_details_no_validated_activities'] = "Aucune activité validée dans la période sélectionnée pour cette formation.";
$string['learner_details_table_header_column_name'] = "Jalon";
$string['learner_details_table_header_column_type'] = "Type";
$string['learner_details_table_header_column_training_name'] = "Formation";
$string['learner_details_table_header_column_course_name'] = "Cours";
$string['learner_details_table_header_column_validated_time'] = "Validé le";
$string['learner_details_table_header_column_milestones'] = "Temps jalon crédité";
$string['learner_details_generate_certificate_link'] = "Générer l'attestation";
$string['learner_details_regenerate_certificate_link'] = "Générer une nouvelle attestation";
$string['learner_details_download_certificate_link'] = "Télécharger l'attestation";
$string['learner_details_notification_message_error'] = "Une erreur est survenue lors de la création du fichier sur le serveur, veuillez réessayer plus tard.";
$string['learner_details_notification_message_new'] = "L'attestation a été créé sur le serveur";
$string['learner_details_notification_message_overwritten'] = "Une nouvelle attestation a été créée sur le serveur, l'ancien fichier a été écrasé";


$string['training_list_link'] = "Liste des formations";
$string['student_list_link'] = "Liste des apprenants";
$string['training_setting_link'] = "Paramètres de la formation";
$string['milestone_manage_link'] = "Gestion des jalons";

// Unknown strings.
// @todo review these strings.
$string['attestoodle:addinstance'] = 'Ajout un block Attestoodle';
$string['attestoodle:myaddinstance'] = 'Ajouter un block attestoodle à mon Moodle';
$string['blockstring'] = 'Block string';

$string['template_certificate'] = 'Modèle d\'attestation';
$string['actions'] = 'Actions';
$string['add_training'] = 'Ajouter une formation à partir d\'une catégorie';
$string['background'] = 'Image en fond de page';
$string['learner'] = 'Nom de l\'apprenant';
$string['training'] = 'Désignation de la formation';
$string['period'] = 'Période';
$string['totalminute'] =  'Temps total validé sur la période';
$string['tabactivities'] =  'Tableau des activités';
$string['font'] =  'Fonte :';
$string['emphasis'] =  'Emphase :';
$string['size'] =  'Taille :';
$string['align'] =  'Align. :';
$string['enregok'] =  'Enregistrement réussie';
$string['preview'] =  'Aperçu';
$string['personalize'] = 'Personnaliser';
$string['personalized'] = 'Personnalisé';