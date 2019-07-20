<?php
if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function formulaires_desincription_charger_dist($id_auteur, $redirect = ''){

	if (! intval($id_auteur)) {
		return false;
	}

	$row = sql_fetsel('login, alea_actuel, alea_futur', 'spip_auteurs', 'id_auteur='.intval($id_auteur));

	$valeurs = array(
		'login'        => $row['login'],
		'_alea_actuel' => isset($row['alea_actuel']) ? $row['alea_actuel'] : '',
		'_alea_futur'  => isset($row['alea_futur']) ? $row['alea_futur'] : '',
	);

	return $valeurs;
}

function formulaires_desincription_verifier_dist($id_auteur, $redirect = ''){

	$erreurs = array();

	if (! intval($id_auteur)) {
		$erreurs['message_erreur'] = _T('moncompte:action_interdite');
		return $erreurs;
	}

	//champs obligatoire
	foreach (array ('password') as $obligatoire) {
		if (!_request($obligatoire)) $erreurs[$obligatoire] = _T('obligatoire');
	}

	return $erreurs;
}

function formulaires_desincription_traiter_dist($id_auteur, $redirect = ''){
	$chaine = _request('password');
	$chaine = substr($chaine,1, -1);

	$password_a_tester = explode(';', $chaine)[0];
	$password          = sql_getfetsel('pass', 'spip_auteurs', 'id_auteur='.intval($id_auteur));

	$retour = array();
	if (
		!$password or
		$password === $password_a_tester
	) {
		supprimer_ce_compte($id_auteur);
		$retour['message_ok'] = _T('moncompte:compte_supprime');
	} else {
		echo "Ne pas Supprimer";
		$retour['message_erreur'] = _T('moncompte:compte_pas_supprime');
	}

	$retour['redirect'] = "spip.php";
	if ($redirect) {
		$retour = $redirect;
	}

	return $retour;
}

function supprimer_ce_compte($id_auteur){

	// suppression definitive du compte
	if (lire_config('moncompte/choix_suppression_compte') === 'supprimer' ) {
		sql_delete('spip_auteurs','id_auteur='.intval($id_auteur));
		sql_delete('spip_forum','id_auteur='.intval($id_auteur));

		$from = array(
			'spip_auteurs_liens'
		);
		$where = array(
			'id_auteur='.intval($id_auteur),
			'objet="article"',
		);
		$T_id_article = sql_allfetsel('id_objet',$from, $where);
		$in = sql_in('id_article', array_column($T_id_article, 'id_objet'));
		sql_updateq('spip_articles', array('statut' => 'poubelle'), $in);

	}	else {
		// on passe à la poubelle le compte
		sql_updateq('spip_auteurs', array('statut' => '5poubelle'), 'id_auteur='.intval($id_auteur));
		sql_updateq('spip_forum', array('auteur' => '', 'email_auteur' => ''), 'id_auteur='.intval($id_auteur));
	}

	// on deconnecte
	$logout = charger_fonction('logout','action');
	set_request('url', 'spip.php');
	$logout();


	// On invalide le cache
	include_spip('inc/invalideur');
	suivre_invalideur("id='id_auteur/$id_auteur'");

}
