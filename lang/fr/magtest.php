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
 * @package    mod_magtest
 * @category   mod
 * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
 * @contributors   Etienne Roze
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */
defined('MOODLE_INTERNAL') || die();

$string['magtest:addinstance'] = 'Ajouter une instance';
$string['magtest:manage'] = 'Configurer le test';
$string['magtest:multipleattempts'] = 'Rejouer le test';
$string['magtest:viewgeneralstat'] = 'Voir les statistiques de réponse';
$string['magtest:viewotherresults'] = 'Voir les résultats de tous';

$string['errorinvalidcategory'] = 'Catégorie invalide.';
$string['erroraddcategory'] = 'Erreur lors de l\'ajout d\'une catégorie.';

$string['<<'] = '<<';
$string['>>'] = '>>';
$string['addcategories'] = 'Ajouter des categories';
$string['addcategory'] = 'Ajouter une categorie';
$string['addone'] = 'Ajouter une categorie supplémentaire';
$string['addquestion'] = 'Ajouter une question';
$string['addthree'] = 'Ajouter trois catégories supplémentaires';
$string['allowreplay'] = 'Autoriser plusieurs essais';
$string['answercount'] = 'Nombre de réponses';
$string['answerquestions'] = 'Test&nbsp;:&ensp;';
$string['answers'] = 'Réponses';
$string['answerweights'] = 'Poids des réponses'; 
$string['answertext'] = 'Réponse pour la catégorie&nbsp;: {$a}';
$string['backtocourse'] = 'Revenir au parcours';
$string['categories'] = 'Catégories';
$string['category'] = 'Catégorie';
$string['categoryresult'] = 'Le texte résultat pour cette catégorie';
$string['categoryshortname'] = 'Nom court';
$string['choosecategoryforanswer'] = 'Choisissez une catégorie pour cette question.';
$string['closed'] = 'Ce test est fermé. Vous ne pouvez plus y participer.';
$string['closedtestadvice'] = 'Ce test est fermé. Il n\'est pas possible de le rejouer';
$string['commands'] = 'Commandes';
$string['configshowmymoodle'] = 'Montrer les tests magazine sur les pages personnalisées';
$string['configshowmymoodledesc'] = 'Si cette option est activée, alors les tests magazine à faire ne seront pas visible dans les pages personnalisées';
$string['confirmdeletemessage'] = 'En supprimant cette question, vous supprimez également les réponses associées et tous les choix des utilisateurs déjà effectués pour cette question. Voulez-vous continuer ?';
$string['delcategory'] = 'Supprimer cette catégorie';
$string['delquestion'] = 'Supprimer cette question';
$string['descresult'] = 'Résultats du test';
$string['description'] = 'Description';
$string['doit'] = 'Faire le test';
$string['editquestions'] = 'Modifier les questions';
$string['endtime'] = 'Date de clôture';
$string['erroremptyanswers'] = '(Toutes les réponses aux questions n\'ont pas été exprimées. Le test peut ne pas fonctionner correctement).';
$string['errornotallowed'] = 'Vous n\'avez pas la permission de faire ce test';
$string['errorquestionupdate'] = 'Erreur lors de la mise à jour de la question {$a}';
$string['erroranswerinsert'] = 'Erreur d\'insertion d\'une nouvelle réponse pmour la question : {$a}';
$string['errorupdatecategory'] = 'Erreur de mise à jour de la catégorie {$a}';
$string['eventanswersubmitted'] = 'Réponse soumise';
$string['guestcannotuse'] = 'Les invités ne peuvent accéder à ce test';
$string['helpertext'] = 'Texte d\'aide pour la catégorie {$a}';
$string['helpnavigationquestion'] = 'Aide';
$string['clearalldata'] = 'Supprimer les anciennes questions';
$string['intro'] = 'Introduction';
$string['importfile'] = 'Fichier d\'import';
$string['importformat'] = 'Format d\'import';
$string['import'] = 'Importer';
$string['importquestions'] = 'Importer des questions dans le test magazine';
$string['cleardata'] = 'Supprimer toutes les anciennes données';
$string['clearalladvice'] = 'Attention, supprimer les données supprimera aussi les réponses utilisateur';
$string['helper'] = 'Consulter l\'aide';
$string['magtest:doit'] = 'Faire le test';
$string['default'] = 'Test type magazine par defaut';
$string['makegroups'] = 'Générer les groupes du cours à partir des résultats';
$string['modulename'] = 'Test type magazine';
$string['magtestattempted'] = 'Test magazine effectué le&ensp;';
$string['magtestaccesses'] = 'Visualisations : {$a} accès';
$string['pluginname'] = 'Test type magazine';
$string['modulenameplural'] = 'Tests type magazine';
$string['nocategories'] = 'Aucune catégorie créée.';
$string['nocategories'] = 'Aucune catégorie pour l\'instant.';
$string['nogroupcreationadvice'] = 'La fonction de création de groupes à partir des résultats du test nécessite que vous ayez au préalable supprimé tous les groupes préexistants du cours.';
$string['noquestions'] = 'Aucune question disponible.';
$string['notopened'] = 'Ce test n\'est pas encore ouvert.';
$string['nouseranswer'] = 'Aucune réponse n\'a été saisie.';
$string['nousersinthisgroup'] = 'Aucun utilisateur ne s\'est placé dans cette catégorie';
$string['noanswerusers'] = 'Sans réponse ';
$string['outputgroupdesc'] = 'Description du groupe généré';
$string['outputgroupname'] = 'Nom du groupe généré';
$string['singlechoice'] = 'Choix simple';
$string['singlechoice_help'] = 'Si actif, seule la première catégorie. Weights apply to all other categories if this questions is enabled. Magtest is necessarily weighted in this case.';
$string['pagenotcomplete'] = 'Toutes les réposnes n\'ont pas été données';
$string['pagesize'] = 'Nombre de questions par page';
$string['preview'] = 'Prévisualisation';
$string['question'] = 'Question';
$string['questionneedattention'] = 'Cette question nécessite votre attention : elle n\'est pas complété correctement.';
$string['questions'] = 'Questions';
$string['questiontext'] = 'Texte de la question';
$string['reset'] = 'Recommencer le test';
$string['resetting_data'] = 'Effacement des réponses aux tests';
$string['resetting_magtests'] = 'Réinitialisation des tests';
$string['result'] = 'Résultat';
$string['resultformattexttypehelp'] = 'Le format est identique à celui choisi pour la description';
$string['results'] = 'Résultats';
$string['resultsbycats'] = 'Résultats par catégories';
$string['resultsbyusers'] = 'Résultats par utilisateur';
$string['resulttext'] = 'Texte de conclusion';
$string['save'] = 'Enregistrer la réponse';
$string['score'] = 'Score';
$string['notsubmittedyet'] = 'Pas encore soumis.';
$string['selections'] = 'Sélections';
$string['submitted'] = 'Soumis.';
$string['sortorder'] = 'Rang';
$string['starttime'] = 'Date de début';
$string['stat'] = 'Statistiques';
$string['symbol'] = 'Symbole';
$string['singlechoicemode'] = 'Ce test magazine est en mode réponse simple. You cannot write answer texts in this mode. the sudent will just answer "yes" or "no" to the question, and distribute weights in categories.'; 
$string['testfinish'] = 'Vous avez répondu à toutes les questions : le test est fini.';
$string['testnotallok'] = 'Vous ne pouvez faire ce test car sa configuration n\'est pas terminée';
$string['unanswered'] = 'Sans réponse';
$string['updatecategory'] = 'Modifier une catégorie';
$string['usemakegroups'] = 'Utiliser pour générer des groupes de cours';
$string['userchoices'] = 'Réponses des utilisateurs';
$string['weight'] = 'Pondération';
$string['weightfor'] = 'Poids pour "{$a}"'; 
$string['weighted'] = 'Mode pondéré';
$string['youneedcreatingcategories'] = 'Il faut créer au moins deux catégories avant de pouvoir créer des questions';
$string['you_have_to_create_categories'] = 'Vous devez créer au moins une catégorie de réponses avant de pouvoir créer une question.';
$string['question_text'] = 'Texte de la question';
$string['answer'] = 'Réponse';
$string['helpertext'] = 'Aide';
$string['updatecategories'] = 'Modifier la catégorie';
$string['updatequestion'] = 'Modifier la question';
// Help strings //

$string['importformat_help'] = '
### Format d\'import des questions

Le fichier d\import doit être encodé en UTF8, séparé par des ";", une question par ligne et doit avoir une première colonne avec le texte de la question.

Si l\'option "Choix simple" est active, alors les colonnes suivantes donnent dans l\'ordre les poids de catégorie (entier).

Si l\'option "Choix simple" est désactivée (par défaut), alors les colonnes suivantes viennent par groupe de trois et donnent dans
l\'ordre et pour chaque catégorie, le texte de réponse, le poids et le texte d\'aide de la réponse.
';

$string['modulename_help'] = "Le Test de Type Magazine permet de créer une évaluation multicritère additionnant un score
sur plusieurs évaluateurs à la fois. A la fin du test, l'utilisateur est rangé dans la catégorie correspondant à son évaluateur
dominant. L'enseignant peut constituer des groupes de cours à partir de ces résultats.
";

$string['pagesize_help'] = '
### Taille des pages de test

Ce paramètre permet de régler le nombre de questions que vous voulez afficher sur chaque page du test. Si vous le réglez sur 1,
le test affichera chaque question sur une nouvelle page.
';

$string['weighted_help'] = '
### Mode pondéré

Si vous activez le mode pondéré, chaque réponse peut apporter un nombre non égal de points `la note de sa catégorie associée.

Ceci permet de créer des tests qui permettent de faire varier l\'influence de certaines questions dans le "placement"
des participants dans le score final.

En mode pondéré, la pondération par défaut est de 1, ce qui revient à ne pas utiliser de pondération';

$string['magtest_help'] = '
Le module "Test de magazine" propose un test qui se base sur un ensemble de catégories de réponses, comme les tests de psychologie
qu l\'on trouve dans les magazines. Le test permet d\'établir les catégories de classement du résultat, et propose de définir un
jeu de questions �oser aux participants. A chaque question doit correspondre un certain nombre de propositions (une par catégorie)
permettant au participant de faire son choix. 

Lorsque le participant fait le test, il devra choisir pour chaque question la réponse appropriée ce qui rapportera un certain
nombre de points à la catégorie correspondante.

A la fin du test, la catégorie gagnante est celle qui a collecté de meilleur score. Une conclusion est alors montrée aux utilisateurs,
laquelle souligne la conclusion "gagnante", suivie éventuellement par une conclusion plus générale.

Les groupes de participants issus du test et classés par catégorie gagnante peuvent être convertis en groupes Moodle du cours courant.
Ce module peut donc servir comme "discriminateur à critère" pour former des groupes par l\'action des participants eux-mêmes.
';

$string['usemakegroups_help'] = '
### Génération de groupes Moodle

Le module "Test de magazine" permet de segmenter les participants en plusieurs groupes par leur simple participation au test et
la catégorie de proposition qu\'ils vont choisir par leur réponses.

Le module permet d\'activer la fonction de génération de groupes qui définit des groupes Moodle de cours `partir de ces résultats.

Le test peut ne pas être complet et les groupes seront générés avec les réponses disponibles. Par contre, il n\'est pas possible de
générer les groupes s\'il existe déj`des groupes définis dans le cours. Vous devrez donc détruire les groupes après une première
tentative si vous désirez `nouveau générer les groupes `partir de nouveaux résultats.
';

$string['allowreplay_help'] = '
### Autoriser plusieurs tentatives

Ce paramètre permet de commuter la possibilité de rejouer le test au niveau de l\'instance. Les utilisateurs doivent en plus disposer
de la capacité "Rejouer le test" dans leur profil pour pouvoir exécuter le test `nouveau.
';
