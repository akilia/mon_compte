<?php
/**
 * Utilisations de pipelines par Mon Compte
 *
 * @plugin     Mon Compte
 * @copyright  2018
 * @author     Collectif SPIP
 * @licence    GNU/GPL
 * @package    SPIP\Moncompte\Pipelines
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function moncompte_affichage_final($page){

	$_T_deconnecte = _T('icone_deconnecter');
	$url           = generer_url_public('mon_compte');
	$replace       = "$_T_deconnecte</a> | <a href='$url'> Mon compte </a> ";

	$page = str_replace($_T_deconnecte, $replace, $page);
	return $page;
}
