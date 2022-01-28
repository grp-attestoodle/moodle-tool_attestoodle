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
$string['attestoodle:viewtraining'] = "Consulter une formation";
$string['attestoodle:displaytrainings'] = "Afficher les formations";
$string['attestoodle:managetraining'] = "Ajouter/Supprimer une formation";
$string['attestoodle:managemilestones'] = "Gérer les jalons d'une formation";
$string['attestoodle:displaylearnerslist'] = "Afficher les détails d'une formation";
$string['attestoodle:downloadcertificate'] = "Générer/Télécharger les attestations";
$string['attestoodle:learnerdetails'] = "Afficher les détails d'un apprenant";
$string['attestoodle:deletetemplate'] = "Supprimer un modèle d'attestation";
$string['attestoodle:managetemplate'] = "Gérer les modèles d'attestation";
$string['attestoodle:viewtemplate'] = "Consulter les modèles d'attestation";

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
$string['training_management_submit_removed'] = 'La formation a été supprimée de la liste des formations Attestoodle';
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
$string['totalminute'] = 'Temps total validé sur la période';
$string['cumulminutes'] = 'Temps total validé';
$string['tabactivities'] = 'Tableau des activités';
$string['font'] = 'Fonte :';
$string['emphasis'] = 'Emphase :';
$string['size'] = 'Taille :';
$string['align'] = 'Align. :';
$string['enregok'] = 'Enregistrement réussi';
$string['preview'] = 'Aperçu';
$string['personalize'] = 'Personnaliser';
$string['activity_header_col_1'] = 'Cours Suivis';
$string['activity_header_col_2'] = 'Total heures';
$string['personalized'] = 'Personnalisé';
$string['rubric'] = 'Libellé : ';
$string['literaux'] = 'Littéraux';
$string['literal'] = 'Littéral';
$string['dateformat'] = 'd/m/Y';
$string['arraysize'] = 'Largeur du tableau : ';
$string['fromdate'] = 'du {$a}';
$string['todate'] = ' au {$a}';
$string['errorformat'] = 'Erreur de format';
$string['activitiesupdated'] = 'Activités mises à jour';
$string['activitiesnoupdated'] = 'Activités non modifiées';
$string['errnotemplatename'] = 'Le nom du modèle doit être renseigné !';
$string['templatename'] = 'Nom du modèle';
$string['listtemplate_title'] = 'Liste des modèles d\'attestation';
$string['confdeltemplate'] = 'Souhaitez-vous vraiment supprimer le modèle "{$a->name}" ?';
$string['updatetraintemplate'] = 'Mise à jour réussie';
$string['pagebreak'] = 'Rupture de page';
$string['nl_never'] = ' Jamais ';
$string['nl_necessary'] = ' Si nécessaire ';
$string['nl_always'] = ' Toujours';
$string['viewpagenumber'] = 'Afficher le numéro de page';
$string['nl_ontotal'] = ' / nombre total';
$string['nl_pagenumber'] = 'Numéro de Page';
$string['nl_background'] = 'Répéter l\'image de fond';
$string['nl_preact'] = 'Informations avant le tableau';
$string['nl_preactch1'] = 'Uniquement sur la 1ere page';
$string['nl_preactch2'] = 'Sur toutes les pages';
$string['nl_postact'] = 'Informations après le tableau';
$string['nl_postactch1'] = 'Uniquement sur la dernière page';
$string['grp_course'] = 'Cours';
$string['grp_activity'] = 'Activité';
$string['grp_type'] = 'Type activité';
$string['grp_level1'] = 'Regroupement niveau 1';
$string['grp_level2'] = 'Regroupement niveau 2';
$string['activity_header_coursename_'] = 'Cours Suivis';
$string['activity_header_coursename_type'] = 'Type d\activité suivies par cours';
$string['activity_header_coursename_name'] = 'Activités suivies par cours';
$string['activity_header_type_'] = 'Types d\'activité Suivie';
$string['activity_header_type_coursename'] = 'Cours suivis par types d\'activité';
$string['activity_header_type_name'] = 'Activités suivies par types';
$string['activity_header_name_'] = 'Activités Suivies';
$string['activity_header_name_coursename'] = 'Cours suivis par activité';
$string['activity_header_name_type'] = 'Type d\'activité suivie par activité';
$string['infonocourses'] = 'Il n\'existe aucun cours avec suivi d\'achèvement à partir de la catégorie associée à cette formation';
$string['infonostudent'] = 'Aucun apprenant présent dans un cours avec suivi d\'achèvement à partir de la catégorie associée à cette formation';
$string['navlevel1'] = 'Formations';
$string['navlevel2'] = 'Paramètres';
$string['navlevel3a'] = 'Apprenants';
$string['navlevel4a'] = 'Détails';
$string['navlevel3b'] = 'Jalons';
$string['navlevel1b'] = 'Liste modèles';
$string['navlevel2b'] = 'Modèle ';
$string['trainingname'] = 'Nom de la Formation';
$string['filtergrouplabel'] = 'Filtrer les modules :';
$string['filtermodulealltype'] = 'de tous types';
$string['filtermoduleactivitytype'] = 'Uniquement les activités';
$string['filtermodulename'] = 'selon leur nom :';
$string['filtermodulevisible'] = 'visibles ou non';
$string['filtermodulevisibleyes'] = 'visibles';
$string['filtermodulevisibleno'] = 'cachés';
$string['filtermodulerestrict'] = 'avec restriction d\'accès ou non';
$string['filtermodulerestrictyes'] = 'avec restriction d\'accès';
$string['filtermodulerestrictno'] = 'sans restriction d\'accès';
$string['filtermodulemilestone'] = 'jalons ou non';
$string['filtermodulemilestoneyes'] = 'jalons';
$string['filtermodulemilestoneno'] = 'non jalons';
$string['filtermodulebtn'] = 'Filtrer';
$string['modulefiltergroup'] = 'Filtrage des modules';
$string['modulefiltergroup_help'] = '<p>Ce filtre permet de choisir quels modules vont être affichés dans la liste,
en fonction d\'une série de critères.</p>
<p>Note : Le critère de nom a priorité sur tous les autres</p>';
$string['orderbylabel'] = 'Classer par :';
$string['orderbycourse'] = 'Espace cours';
$string['orderbymonth'] = 'Mois d\'achèvement attendu';
$string['orderbygroup'] = 'Catégorisation des modules';
$string['orderbygroup_help'] = '<p>Ce réglage détermine comment vont être regroupés
les modules dans l\'affichage ci-dessous.</p>
<ul>
<li>par espaces cours</li>
<li>par date d\'achèvement attendu (ce qui permet une représentation dans le temps)</li>
</ul>';
$string['monthfrom'] = 'Depuis';
$string['orderbybtn'] = 'Réorganiser';
$string['module_expected_date_label'] = 'Achèvement attendu le';
$string['module_expected_date_no'] = 'Sans date d\'achèvement attendu';
$string['module_expected_date_outside'] = 'Avec des date d\'achèvement au delà';
$string['period_form'] = 'Règle sur les dates';
$string['period_form_help'] = 'Indiquer la période à prendre en compte
<br>A partir de la date de début incluse jusqu\'à la date de fin incluse.
<br><b>Exemple :</b> Pour sélectionner la journée du 24 septembre 2018,
 vous devez sélectionner du 24 septembre 2018 au 24 septembre 2018';
$string['errduplicatename'] = 'Ce nom est déjà utilisé !!';
$string['createtemplate'] = 'Créer un nouveau modèle';
$string['stop'] = 'Stop';
$string['confirmation'] = 'Confirmation';
$string['certificategenerate'] = 'Génération des attestations';
$string['msgongoing'] = 'Traitement en cours';
$string['questgenerate'] = 'Souhaitez-vous générer les {$a} certificats ?';
$string['error_same_criteria'] = 'Les regroupements ne peuvent pas être identiques !!';
$string['nottraining'] = 'Cette catégorie n\'est pas une formation';
$string['milestoneorphan'] = 'Liste des jalons en erreurs (leur activité est supprimée)';
$string['timecredited'] = 'Temps crédité';
$string['milestonenews'] = 'Liste des cours de la formation ayant reçus des nouvelles activités';
$string['nbnewactivity'] = 'Nombre de nouvelle activité';
$string['btn_deletemilestonerr'] = 'Supprimer les jalons en erreurs';
$string['btn_deletenotification'] = 'supprimer la notification';
$string['disablecertif'] = 'Exclure';
$string['disablecertiflib'] = ' (Aucune attestation ne sera générée pour cet apprenant)';
$string['customizecertif'] = 'Edition personnalisée';
$string['customizecertiflib'] = ' (Permet de personnaliser l\'attestation de cet apprenant)';
$string['showcompletiondate'] = 'Afficher la date d\'achevement';
// Privacy.
$string['privacy:metadata:core_files'] = 'Attestoodle stocke les fichiers d\'attestations générés, par apprenant-formation-interval de temps .';
$string['privacy:metadata:tool_attestoodle_launch_log'] = 'Journal des lancements de génération d\'attestation';
$string['privacy:metadata:tool_attestoodle_launch_log:operatorid'] = 'Identifiant de l\'utilisateur qui à généré un lot d\'attestation';
$string['privacy:metadata:tool_attestoodle_launch_log:timegenerated'] = 'Date et heure de lancement de la génération du lot d\'attestation';
$string['privacy:metadata:tool_attestoodle_launch_log:begindate'] = 'Début de la période des attestations, saisie par l\'opérateur';
$string['privacy:metadata:tool_attestoodle_launch_log:enddate'] = 'Fin de la période des attestations, saisie par l\'opérateur';
$string['privacy:metadata:tool_attestoodle_certif_log'] = 'Liens entre apprenant et les fichiers d\'attestations le concernant';
$string['privacy:metadata:tool_attestoodle_certif_log:learnerid'] = 'L\'identifiant de l\'apprenant associé aux fichiers d\'attestation';
$string['privacy:metadata:tool_attestoodle_certif_log:filename'] = 'Nom des fichiers d\'attestations de l\'apprenant';
$string['privacy:metadata:tool_attestoodle_user_style'] = 'Personnalisation du modèle d\'attestation par apprenant';
$string['privacy:metadata:tool_attestoodle_user_style:userid'] = 'L\'identifiant de l\'apprenant dont le modèle d\'attestation est personnalisé';
$string['privacy:metadata:tool_attestoodle_user_style:templateid'] = 'L\'identifiant du modèle d\'attestation associé à l\'apprenant';
$string['privacy:metadata:tool_attestoodle_user_style:enablecertificate'] = 'Désigne si l\'apprenant est exclus ou non de la génération d\'attestation';
$string['privacy:metadata:tool_attestoodle_value_log'] = 'Temps crédité par jalons fanchi.';
$string['privacy:metadata:tool_attestoodle_value_log:moduleid'] = 'Identifiant de l\'activité jalon créditée';
$string['privacy:metadata:tool_attestoodle_value_log:creditedtime'] = 'Temps crédité suite à l\'achèvement de l\'activité jalon';
$string['error_unknown_item'] = 'Inconnu (l\'élément a été supprimé)';
$string['totaltimetraining'] = 'Temps total de la formation :';
$string['nomilestone'] = 'Aucun jalon définit';
$string['starttraining'] = 'Date de début de la formation';
$string['endtraining'] = 'Date de fin de la formation';
$string['durationtraining'] = 'Durée théorique de la formation';
$string['onecoursemilestonetitle'] = 'Jalon du cours {$a}';
$string['errmorecours'] = ' cours avec un nom abrégé composé de "{$a}"';
$string['errnoactivity'] = 'Aucune activité avec suivi d\'achèvement présente dans le cours  {$a}';
$string['errnothingtosearch'] = 'Vous devez saisir un nom abrégé de cours';
$string['notifytotaltraining'] = 'Il existe déjà {$a} formations pour cette catégorie dont :';
$string['linktotraininglst'] = 'Accéder à la liste des formations';
$string['selectlearner'] = 'Sélectionner les apprenants';
$string['findlearner'] = 'Rechercher apprenant';
$string['excludeselect'] = 'Exclure la sélection';
$string['keepselect'] = 'Conserver la sélection';
$string['number_learners'] = 'Nombre d\'apprenant : {$a}';
$string['email'] = 'E Mail';
$string['selection'] = 'Sélection';
$string['result'] = 'Résultat';
$string['milestones'] = 'Jalons';
$string['learners'] = 'Apprenants';
$string['fortraining'] = ' pour la formation : {$a}';
$string['confirmtraincreate'] = 'Voulez-vous créer une nouvelle formation ?';
$string['errsendzip'] = 'Erreur : téléchargement du fichier zip impossible !!';
$string['validate'] = 'Valider';
$string['trainingcriteria'] = 'Critère formation';
$string['enrolcriteria'] = 'Critère inscription';
$string['deletetraining'] = 'Supprimer une formation';
$string['deletemodel'] = 'Supprimer un modèle';
$string['errdateend'] = 'La date de fin doit être supérieure à la date du début !';
$string['deadline'] = 'Echéance';
$string['finished'] = 'Terminée';
$string['toplan'] = 'à planifier';
