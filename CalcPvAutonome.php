<?php 
include('./lib/Fonction.php');
$config_ini = parse_ini_file('./config.ini', true); 
?>
<script src="./lib/jquery-3.1.1.slim.min.js"></script> 
<?php
/*
 * ####### Résultat #######
*/

if (isset($_GET['submit'])) {
	echo '<div class="part result">';
	// Détection des erreurs de formulaires
	$erreurDansLeFormulaire=null;
	if (empty($_GET['Bj']) || $_GET['Bj'] < 0) {
		$erreurDansLeFormulaire=$erreurDansLeFormulaire.erreurPrint('Bj', 'Le besoin journalier n\'est pas correcte car < 0');
	}
	if ($_GET['ModPv'] == 'perso') {
		if (empty($_GET['PersoPvV']) || $_GET['PersoPvV'] < 0) {
			$erreurDansLeFormulaire=$erreurDansLeFormulaire.erreurPrint('PersoPvV', 'La tension du panneau personalisé n\'est pas correcte car < 0');
		}
		if (empty($_GET['PersoPvW']) || $_GET['PersoPvW'] < 0) {
			$erreurDansLeFormulaire=$erreurDansLeFormulaire.erreurPrint('PersoPvW', 'La puissance du panneau personalisé n\'est pas correcte car < 0');
		}
		if (empty($_GET['PersoPvVdoc']) || $_GET['PersoPvVdoc'] < 0) {
			$erreurDansLeFormulaire=$erreurDansLeFormulaire.erreurPrint('PersoPvVdoc', 'La tension en circuit ouvert (Vdoc) du panneau personalisé n\'est pas correcte car < 0');
		}
		if (empty($_GET['PersoPvIsc']) || $_GET['PersoPvIsc'] < 0) {
			$erreurDansLeFormulaire=$erreurDansLeFormulaire.erreurPrint('PersoPvIsc', 'Le courant de court circuit (Isc) du panneau personalisé n\'est pas correcte car < 0');
		}
	}
	if ($_GET['ModBat'] == 'perso') {
		if (empty($_GET['PersoBatV']) || $_GET['PersoBatV'] < 0) {
			$erreurDansLeFormulaire=$erreurDansLeFormulaire.erreurPrint('PersoBatV', 'La tension de la batterie personalisée n\'est pas correcte car < 0');
		}
		if (empty($_GET['PersoBatAh']) || $_GET['PersoBatAh'] < 0) {
			$erreurDansLeFormulaire=$erreurDansLeFormulaire.erreurPrint('PersoBatAh', 'La capacité de la batterie personalisée n\'est pas correcte car < 0');
		}
	}
	if ($_GET['ModRegu'] == 'perso') {
		if (empty($_GET['PersoReguVmaxPv']) || $_GET['PersoReguVmaxPv'] < 0) {
			$erreurDansLeFormulaire=$erreurDansLeFormulaire.erreurPrint('PersoReguVmaxPv', 'La tension du régulateur personalisé n\'est pas correcte car < 0');
		}
		if (empty($_GET['PersoReguPmaxPv']) || $_GET['PersoReguPmaxPv'] < 0) {
			$erreurDansLeFormulaire=$erreurDansLeFormulaire.erreurPrint('PersoReguPmaxPv', 'La puissance du régulateur personalisé n\'est pas correcte car < 0');
		}
		if (empty($_GET['PersoReguImaxPv']) || $_GET['PersoReguImaxPv'] < 0) {
			$erreurDansLeFormulaire=$erreurDansLeFormulaire.erreurPrint('PersoReguImaxPv', 'Le courant de court-circuit du régulateur personalisé n\'est pas correcte car < 0');
		}
	}
	if (empty($_GET['Aut']) || $_GET['Aut'] < 0) {
		$erreurDansLeFormulaire=$erreurDansLeFormulaire.erreurPrint('Aut', 'Le nombre de jour d\'autonomie n\'est pas correcte car < 0');
	}
	if (empty($_GET['Rb']) || $_GET['Rb'] < 0) {
		$erreurDansLeFormulaire=$erreurDansLeFormulaire.erreurPrint('Rb', 'Le rendement électrique des batteries n\'est pas correcte car < 0');
	}
	if (empty($_GET['Ri']) || $_GET['Ri'] < 0) {
		$erreurDansLeFormulaire=$erreurDansLeFormulaire.erreurPrint('Ri', 'Le rendement électrique de l\'installation n\'est pas correcte car < 0');
	}
	if (empty($_GET['DD']) || $_GET['DD'] < 0) {
		$erreurDansLeFormulaire=$erreurDansLeFormulaire.erreurPrint('DD', 'Le degré de décharge n\'est pas correcte car < 0');
	}
	if (empty($_GET['reguMargeIcc']) || $_GET['reguMargeIcc'] < 0) {
		$erreurDansLeFormulaire=$erreurDansLeFormulaire.erreurPrint('reguMargeIcc', 'La marge de sécurité Icc du régulateur de charge n\'est pas correcte car < 0');
	}
	
	
	if ($erreurDansLeFormulaire !== null) {
		echo '<div class="erreurForm">';
		echo '<p>Il y a des erreurs dans le formulaire qui empêche de continuer, merci de corriger ::</p>';
		echo '<ul>'.$erreurDansLeFormulaire.'</ul>';
		echo '</div>';
	} else {
	// Pas d'erreur
	?>

	<h2 class="titre">Résultat du dimensionnement</h2>
	<p><b>Avertissement</b>: Les résultats sont donnés à titre indicatif, nous vous conseillons de vous rapprocher d'un professionnel pour l'achat du matériel, celui-ci pourra valider votre installation. </p>
	<!-- 
		Les PV
	-->
	<h3>Les panneaux photovoltaïques</h3>
	<div id="resultCalcPv" class="calcul">
		<p>On cherche ici la puissance (crête exprimée en W) des panneaux photovoltaïques à installer pour satisfaire vos besoins en fonction de votre situation géographique. La formule est la suivante : </p>
		<p>Pc = Bj / (Rb X Ri X Ej)</p>
		<ul>
			<li>Pc (Wc) : Puissance crête</li>
			<li>Bj (Wh/j) : Besoins journaliers</li>
			<li>Rb : rendement électrique des batteries</li>
			<li>Ri : rendement électrique du reste de l’installation (régulateur de charge…)</li>
			<li>Ej : rayonnement moyen quotidien du mois le plus défavorable dans le plan du panneau (kWh/m²/j)</li>
			<?php 
			if (empty($_GET['Ej']) && isset($_GET['ZoneId'])) {
				$Ej = $config_ini['irradiation']['zone'.$_GET['ZoneId'].'_'.$_GET['Deg']];
				echo '<ul><li>Vous avez sélectionné la Zone '.$_GET['ZoneId'].' avec un angle de '.$_GET['Deg'].'°, nous allons considérer Ej égale à '.$Ej.'</li></ul>';
			} else {
				$Ej = $_GET['Ej'];
			}
			?>
		</ul>
		<p>Dans votre cas ça nous fait : </p>
		<?php 
		$Pc = convertNumber($_GET['Bj'])/(convertNumber($_GET['Rb'])*convertNumber($_GET['Ri'])*convertNumber($Ej));
		?>
		<p><a class="more" id="resultCalcPvHide">Cacher le calcul</a></p>
		<p>Pc = <?= $_GET['Bj'] ?> / (<?= $_GET['Rb'] ?> * <?= $_GET['Ri'] ?> * <?= $Ej ?>) = <b><?= convertNumber($Pc, 'print') ?> Wc</b></p>
	</div>
	
	<p>Vous auriez besoin d'une puissance de panneau photovoltaïque équivalente à <b><?= convertNumber($Pc, 'print') ?>Wc</b>.</p>
	<p><a id="resultCalcPvShow">Voir, comprendre la démarche, le calcul</a></p>
	
	<?php
	/*
	 * ####### Recherche d'une Config panneux : #######
	*/
	/* Personnaliser */
	if ($_GET['ModPv'] == 'perso') {
		// Combien de panneau ?
		$nbPv=intval($Pc / $_GET['PersoPvW'])+1;
		// Capacité déduite
		// Capacité déduite
		$PcParcPv=$_GET['PersoPvW']*$nbPv;
		// Différence avec la capacité souhauté
		$diffPcParc=$PcParcPv-$Pc;
		$meilleurParcPv['nbPv'] = $nbPv;
		$meilleurParcPv['diffPcParc'] = round($diffPcParc);
		$meilleurParcPv['W'] = $_GET['PersoPvW'];
		$meilleurParcPv['V'] = $_GET['PersoPvV'];
		$meilleurParcPv['Vdoc'] = $_GET['PersoPvVdoc'];
		$meilleurParcPv['Isc'] = $_GET['PersoPvIsc'];
	/* Automatique selon les info's */
	} else {
		$meilleurParcPv['nbPv'] = 99999;
		$meilleurParcPv['diffPcParc'] = 99999;
		$meilleurParcPv['W'] = 0;
		debug('<ul type="1">');
		foreach ($config_ini['pv'] as $idPv => $pv) {
			// Gestion du mode automatique dans le type :
			if ($_GET['ModPv'] == 'auto' && $_GET['TypePv'] != 'auto') {
				if ($_GET['TypePv'] != $pv['type']) {
					continue;
				}
			}
			if ($_GET['ModPv'] != 'auto' && $_GET['ModPv'] != $idPv) {
				continue;
			}
			// Calcul du nombre de panneaux nessésaire 
			$nbPv=intval($Pc / $pv['W'])+1;
			// Capacité déduite
			$PcParcPv=$pv['W']*$nbPv;
			// Différence avec la capacité souhauté
			$diffPcParc=$PcParcPv-$Pc;
			// Debug
			debug('<li>');
			debug('Test de config pour '.$pv['W'].' ::: nb pv: '.$nbPv);
			debug(' | puissance total (W) : '.$PcParcPv);
			debug(' | diff puissance souhaité : '.$diffPcParc);
			
			$savMeilleurParcPv = false;

			$diffNbPvAvecMeilleurParc=$meilleurParcPv['nbPv']-$nbPv;
			
			// Si la différence de puissance & que le nombre de PV est inférieur 
			if ($diffPcParc <= $meilleurParcPv['diffPcParc'] && $nbPv < $meilleurParcPv['nbPv']
			// Si la différence dans le nombre de panneaux avec la meilleur config n'est pas un (même critère que précédent)
			 || $diffNbPvAvecMeilleurParc != 1 && $diffPcParc <= $meilleurParcPv['diffPcParc']
			 || $diffNbPvAvecMeilleurParc != 1 && $nbPv < $meilleurParcPv['nbPv']) {
				$savMeilleurParcPv = true;
			}
			
			if ($savMeilleurParcPv) {
				# Nouvelle meilleur config
				// Debug
				debug(' | * nouvelle meilleur config');
				$meilleurParcPv['nbPv'] = $nbPv;
				$meilleurParcPv['diffPcParc'] = round($diffPcParc);
				$meilleurParcPv['W'] = $pv['W'];
				$meilleurParcPv['V'] = $pv['V'];
				$meilleurParcPv['Vdoc'] = $pv['Vdoc'];
				$meilleurParcPv['Isc'] = $pv['Isc'];
				$meilleurParcPv['type'] = $pv['type'];
				$meilleurParcPv['nbPv'] = $nbPv;
			}
			debug('</li>');
		}
		debug('</ul>');
	}
	if ($_GET['ModPv'] == 'auto') {
		echo '<p>Une hypothèse serait d\'avoir <b>'.$meilleurParcPv['nbPv'].' panneau(x)</b> '.$meilleurParcPv['type'].' de <b>'.$meilleurParcPv['W'].'Wc</b> chacun en '.$meilleurParcPv['V'].'V (<a rel="tooltip" class="bulles" title="Caractéristique du panneau : <br />P = '.$meilleurParcPv['W'].'W<br />U = '.$meilleurParcPv['V'].'V<br />Vdoc ='.$meilleurParcPv['Vdoc'].'V<br />Isc = '.$meilleurParcPv['Isc'].'A">?</a>) ce qui pousse la capacité du parc à '.$meilleurParcPv['W']*$meilleurParcPv['nbPv'].'W :</p>';
	}elseif ($_GET['ModPv'] == 'perso') {
		echo '<p>Avec votre panneau personnalisé (<a rel="tooltip" class="bulles" title="Caractéristique du panneau : <br />P = '.$meilleurParcPv['W'].'W<br />U = '.$meilleurParcPv['V'].'V<br />Vdoc ='.$meilleurParcPv['Vdoc'].'V<br />Isc = '.$meilleurParcPv['Isc'].'A">détail ici</a>) l\'hypothèse serait d\'avoir <b>'.$meilleurParcPv['nbPv'].' panneau(x)</b> de <b>'.$meilleurParcPv['W'].'Wc</b> chacun en '.$meilleurParcPv['V'].'V ce qui pousse la capacité du parc à '.$meilleurParcPv['W']*$meilleurParcPv['nbPv'].'W :</p>';
	} else {
		echo '<p>Avec le panneau '.$meilleurParcPv['type'].' sélectionné de <b>'.$meilleurParcPv['W'].'Wc</b> en '.$meilleurParcPv['V'].'V , une hypothèse serait d\'avoir <b>'.$meilleurParcPv['nbPv'].' de ces panneau(x)</b> (<a rel="tooltip" class="bulles" title="Caractéristique du panneau : <br />P = '.$meilleurParcPv['W'].'W<br />U = '.$meilleurParcPv['V'].'V<br />Vdoc ='.$meilleurParcPv['Vdoc'].'V<br />Isc = '.$meilleurParcPv['Isc'].'A">?</a>) ce qui pousse la capacité du parc à '.$meilleurParcPv['W']*$meilleurParcPv['nbPv'].'W :</p>';
	}
	?>
	<p>Le budget est estimé entre <?= convertNumber($config_ini['prix']['pv_bas']*$meilleurParcPv['W']*$meilleurParcPv['nbPv'], 'print') ; ?>€ et <?= convertNumber($config_ini['prix']['pv_haut']*$meilleurParcPv['W']*$meilleurParcPv['nbPv'], 'print') ; ?>€ (<a rel="tooltip" class="bulles" title="Pour du matériel neuf, avec un coût estimé de <?= $config_ini['prix']['pv_bas'] ?>€/Wc en fourchette basse & <?= $config_ini['prix']['pv_haut'] ?>€/Wc en haute">?</a>)</p>
	<!-- 
		Les batteries
	-->
	<h3>Les batteries</h3>
	<div id="resultCalcBat" class="calcul">
		<p>On cherche ici la capacité nominale des batteries exprimée en ampères heure (Ah)</p>
		<?php 
		// Si le niveau est débutant on choisie pour lui
		if ($_GET['Ni'] == 1) {
			$_GET['Aut'] = 5;
			$_GET['DD'] = 80;
		} 
		// Si la tension U à été mise en automatique ou que le niveau n'est pas expert
		if ($_GET['U'] == 0 || $_GET['Ni'] != 3) {
			if (convertNumber($Pc) < 800) {
				$U = 12;
			} elseif (convertNumber($Pc) > 1600) {
				$U = 48;
			} else {	
				$U = 24;
			}
		} else {
			$U = $_GET['U'];
		}
		?>
		<p>Cap = (Bj x Aut) / (DD x U)</p>
		<ul>
			<li>Cap (Ah) : Capacité nominale des batteries</li>
			<li>Bj (Wh/j) : Besoins journaliers</li>
			<li>Aut : Nombre de jour d'autonomie (sans soleil)</li>
			<li>DD (%) : <a rel="tooltip" class="bulles" title="Avec la technologie AGM il ne faut pas passer sous le seuil critique des 50%">Degré de décharge maximum</a></li>
			<li>U (V) : <a rel="tooltip" class="bulles" title="En mode automatique la tension des batteries sera déduite du besoin en panneaux<br />De 0 à 800Wc : 12V<br />De 800 à 1600 Wc : 24V<br />Au dessus de 1600 Wc : 48V">Tension finale du parc de batterie</a></li>
		</ul>
		<p>Dans votre cas ça nous fait : </p>
		<?php 
		$Cap = (convertNumber($_GET['Bj'])*convertNumber($_GET['Aut']))/(convertNumber($_GET['DD'])*0.01*convertNumber($U));
		?>
		<p><a class="more" id="resultCalcBatHide">Cacher le calcul</a></p>
		<p>Cap = (<?= $_GET['Bj'] ?> x <?= $_GET['Aut'] ?>) / (<?= $_GET['DD']*0.01 ?> x <?= $U ?>) = <b><?= convertNumber($Cap, 'print') ?> Ah</b></p>
	</div>
	<p>Vous auriez besoin d'un parc de batteries de <b><?= convertNumber($Cap, 'print') ?> Ah en <?= $U ?> V</b>.</p>
	<p><a id="resultCalcBatShow">Voir, comprendre la démarche, le calcul</a></p>	
	
	<?php
	$CourantChargeDesPanneaux=$meilleurParcPv['W']*$meilleurParcPv['nbPv']/$U;
	$CourantChargeMax = $Cap*$_GET['IbatCharge']/100;
	$CourantDechargeMax = $Cap*$_GET['IbatDecharge']/100;
	// Si le courant de charge n'est pas respecté par rapport à la taille de la batterie
	if ($CourantChargeDesPanneaux > $CourantChargeMax) {
		echo '<p>Le courant de charge d\'une batterie ne doit pas dépasser '.$_GET['IbatCharge'].'%, ce qui fait <a rel="tooltip" class="bulles" title="'.convertNumber($Cap, 'print').'Ah * '.$_GET['IbatCharge'].'/100">'.convertNumber($CourantChargeMax, 'print').'A</a> dans notre cas. Hors vos panneaux peuvent monter jusqu`à un courant de charge de <a rel="tooltip" class="bulles" title="'.$meilleurParcPv['W']*$meilleurParcPv['nbPv'].'W / '.$U.'V">'.convertNumber($CourantChargeDesPanneaux, 'print').'A</a>. Si votre régulateur le permet vous pouvez le brider ou augmenter votre parc de batterie à ';
		$Cap=$CourantChargeDesPanneaux*100/$_GET['IbatCharge'];
		echo '<b>'.convertNumber($Cap, 'print').'Ah</b>.';
	}
	echo $CourantDechargeMax;
	?>
	
	
	
		
	
	<?php 
	/*
	 * ####### Recherche d'une Config batterie : #######
	*/
	$meilleurParcBatterie['nbBatterieParallele'] = 99999;
	$meilleurParcBatterie['diffCap'] = 99999;
	$meilleurParcBatterie['nom'] = 'Impossible à déterminer';
	$meilleurParcBatterie['V'] = 0;
	$meilleurParcBatterie['Ah'] = 0;
	debug('<ul type="1">');
	foreach ($config_ini['batterie'] as $idBat => $batterie) {
		// En mode personnalisé on force et on stop la boucle à la fin 
		if ($_GET['ModBat'] == 'perso') {
			// plus loin, la même condition avec un break
			$batterie['Ah'] = $_GET['PersoBatAh'];
			$batterie['V'] = $_GET['PersoBatV'];
		// En mode auto on utilise les batteires 2V si on est au dessus des 550Ah
		} else if ($_GET['ModBat'] == 'auto') {
			if ($Cap > 550 && $batterie['V'] >= 12) {
				continue;
			} else if ($Cap < 550 && $batterie['V'] < 12) {
				continue;
			}
		// Si on est en mode manuel on fait le calcul uniquement sur le bon modèl 
		} else if ($_GET['ModBat'] != $idBat) {
			continue;
		}
		// Calcul du nombre de batterie nessésaire 
		// ENT(capacité recherché / capcité de la batterie)+1
		$nbBatterie=intval($Cap / $batterie['Ah'])+1;
		// Capacité déduite
		$capParcBatterie=$batterie['Ah']*$nbBatterie;
		// Différence avec la capacité souhauté
		$diffCap=$capParcBatterie-$Cap;
		// Debug
		debug('<li>');
		debug('Test de config pour '.$batterie['nom'].' ::: nb de batterie: '.$nbBatterie);
		debug(' | total (Ah) : '.$capParcBatterie);
		debug(' | diff capacité souhaité : '.$diffCap);
		if ($_GET['ModBat'] == 'perso' 
		|| $nbBatterie < $meilleurParcBatterie['nbBatterieParallele']
		|| $nbBatterie == $meilleurParcBatterie['nbBatterieParallele'] && $diffCap <= $meilleurParcBatterie['diffCap']) {
			# Nouvelle meilleur config
			// Debug
			debug(' | * nouvelle meilleur config');
			$meilleurParcBatterie['diffCap'] = round($diffCap);
			$meilleurParcBatterie['nom'] = $batterie['nom'];
			$meilleurParcBatterie['V'] = $batterie['V'];
			$meilleurParcBatterie['Ah'] = $batterie['Ah'];
			$meilleurParcBatterie['nbBatterieParallele'] = $nbBatterie;
			$meilleurParcBatterie['nbBatterieSerie'] = $U/$meilleurParcBatterie['V'];
			$meilleurParcBatterie['nbBatterieTotal'] = $meilleurParcBatterie['nbBatterieSerie'] * $meilleurParcBatterie['nbBatterieParallele'];
		}
		debug('</li>');
		// En mode personnalisé stop la boucle après avoir forcé 
		if ($_GET['ModBat'] == 'perso') {
			break;
		}
	}
	debug('</ul>');
	if ($_GET['ModBat'] == 'auto') {
		echo '<p>Une hypothèse de câblage serait d\'avoir <b>'.$meilleurParcBatterie['nbBatterieTotal'].' batterie(s)</b> de type <b>'.$meilleurParcBatterie['nom'].'</b> ce qui pousse la capacité du parc à '.$meilleurParcBatterie['Ah']*$meilleurParcBatterie['nbBatterieParallele'].'Ah :</p>';
	} else if ($_GET['ModBat'] == 'perso') {
		echo '<p>Vous avez choisi de travailler avec des batterie(s) personnalisé à '.$meilleurParcBatterie['Ah'].'Ah en '.$meilleurParcBatterie['V'].'V. Voici une hypothèse de câblage avec <b>'.$meilleurParcBatterie['nbBatterieTotal'].'</b> de ces batteries ce qui pousse la capacité du parc à '.$meilleurParcBatterie['Ah']*$meilleurParcBatterie['nbBatterieParallele'].'Ah :</p>';
	} else {
		echo '<p>Vous avez choisi de travailler avec des batterie(s) de type <b>'.$meilleurParcBatterie['nom'].'</b>. Voici une hypothèse de câblage avec <b>'.$meilleurParcBatterie['nbBatterieTotal'].'</b> de ces batteries ce qui pousse la capacité du parc à '.$meilleurParcBatterie['Ah']*$meilleurParcBatterie['nbBatterieParallele'].'Ah :</p>';
	}
	echo '<ul><li><b>'.$meilleurParcBatterie['nbBatterieSerie'].' batterie(s) en série</b> (<a rel="tooltip" class="bulles" title="Tension de la batterie ('.$meilleurParcBatterie['V'].'V) * '.$meilleurParcBatterie['nbBatterieSerie'].' série(s)">pour une tension de '.$U.'V</a>) <b>sur '.$meilleurParcBatterie['nbBatterieParallele'].' parallèle(s)</b> (<a rel="tooltip" class="bulles" title="Capacité de la batterie ('.$meilleurParcBatterie['Ah'].'Ah) * '.$meilleurParcBatterie['nbBatterieParallele'].' parallèle(s)">pour une la capacité à '.$meilleurParcBatterie['Ah']*$meilleurParcBatterie['nbBatterieParallele'].'Ah</a>)<a rel="tooltip" class="bulles" target="_blank" title="Pour comprendre le câblage des batteries cliquer ici" href="http://www.solarmad-nrj.com/cablagebatterie.html">?</a></li></ul>';
	?>
	<p>Le budget est estimé entre <?= convertNumber($config_ini['prix']['bat'.$meilleurParcBatterie['V'].'V_bas']*$meilleurParcBatterie['Ah']*$meilleurParcBatterie['nbBatterieParallele']*$meilleurParcBatterie['nbBatterieSerie'], 'print') ; ?>€ et <?= convertNumber($config_ini['prix']['bat'.$meilleurParcBatterie['V'].'V_haut']*$meilleurParcBatterie['Ah']*$meilleurParcBatterie['nbBatterieParallele']*$meilleurParcBatterie['nbBatterieSerie'], 'print') ; ?>€ (<a rel="tooltip" class="bulles" title="Pour du matériel neuf, avec un coût estimé de <?= $config_ini['prix']['bat'.$meilleurParcBatterie['V'].'V_bas'] ?>€/Ah en fourchette basse & <?= $config_ini['prix']['bat'.$meilleurParcBatterie['V'].'V_haut'] ?>€/Ah en haute">?</a>)</p>
	
	<!-- 
		Régulateur
	-->
	<h3>Régulateur de charge</h3>
	<p>Le régulateur de charge est entre les batteries et les panneaux, c'est lui qui gère la charge des batteries en fonction de ce que peuvent fournir les panneaux. </p>
	<?php 
	/*
	 * ####### Recherche d'une Config régulateur : #######
	*/
	// Courant de charge max avec les batteries
	$batICharge = $meilleurParcBatterie['Ah']*$meilleurParcBatterie['nbBatterieParallele'] * $_GET['IbatCharge'] / 100;
	// D'abord on test avec 1 régulateur
	// Ensuite on test tout en série
	// Si on trouve pas, on divise en parallèle
	// Si ça marche toujours pas on test avec plusieurs régulateur (10  max)
	for ($nbRegulateur = 1; $nbRegulateur <= 10; $nbRegulateur++) {	
		// On check toutes les possibilités en série puis en divisant en parallèles
		if ($meilleurParcPv['nbPv'] == 1) {
			$nbPvConfigFinal=1;
		} else {
			$nbPvConfigFinal=round($meilleurParcPv['nbPv']/$nbRegulateur);
		}
		$nbPvSerie = $nbPvConfigFinal;
		$nbPvParalele = 1;
		while ($nbPvSerie >= 1) {
			debug('<p>En considérant '.$nbRegulateur.' régulateur, on test avec '.$nbPvSerie.' panneaux en série sur '.$nbPvParalele.' parallèle</p>');
			$VdocParcPv=$meilleurParcPv['Vdoc']*$nbPvSerie;
			$IscParcPv=$meilleurParcPv['Isc']*$nbPvParalele;
			$parcPvW = $nbPvConfigFinal * $meilleurParcPv['W'];
			$parcPvV = $VdocParcPv;
			$parcPvI = $IscParcPv*$_GET['reguMargeIcc']/100+$IscParcPv;
			
			$meilleurRegulateur = chercherRegulateur();
					
			// Solutaion trouvé
			if ($meilleurRegulateur['nom']) {
				break;
			}
			
			// Pour la suite 
			if ($nbPvSerie != 1) {
				$nbPvSerie=round($nbPvSerie/2);
				$nbPvParalele =round($nbPvConfigFinal / $nbPvSerie);
			} else {
				$nbPvSerie = 0;
			}
		}
		// Solutaion trouvé
		if ($meilleurRegulateur['nom']) {
			break;
		}
	}

	if (!$meilleurRegulateur['nom']) {
		echo '<p>Désolé nous n\'avons pas réussi à faire une hypothèse de câblage panneaux/régulateur. ';
		if ($_GET['ModRegu'] != 'auto') {
			echo 'Nous vous encourageons à passer le modèle du régulateur et/ou les panneaux en automatique. ';
		}
		echo '</p>';
	} else {
		if ($meilleurParcPv['nbPv'] != $nbPvSerie*$nbPvParalele*$nbRegulateur) {
			echo '<p><i>Attention : pour cette hypothèse nous sommes passé à '.$nbPvSerie*$nbPvParalele*$nbRegulateur.' panneaux</i></p>';
		}
		if ($_GET['ModRegu'] == 'perso') {
			echo '<p>Avec votre régulateur personélisé, une ';
			$meilleurRegulateur['nom'] = '';
		} else if ($_GET['ModRegu'] != 'auto') {
			echo '<p>Vous forcé la sélection du régulateur '.$meilleurRegulateur['nom'].', une ';
		} else {
			echo '<p>Une ';
		}
		if ($nbRegulateur != 1) {
			echo 'hypothèse de câblage serait d\'avoir <b>'.$nbRegulateur.' régulateur type '.$meilleurRegulateur['nom'].'</b> (<a rel="tooltip" class="bulles" title="Avec caractéristiques similaires : <br />Tension de la batterie : '.$meilleurRegulateur['Vbat'].'V<br />Puissance maximale PV : '.$meilleurRegulateur['PmaxPv'].'W<br />Tension PV circuit ouvert : '.$meilleurRegulateur['VmaxPv'].'V<br />Courant PV court circuit : '.$meilleurRegulateur['ImaxPv'].'A">?</a>) et sur chacun d\'entre eux connecter <b>'.$nbPvSerie.' panneau(x) en série';
			if ($nbPvParalele != 1) {
				echo ' sur '.$nbPvParalele.' parallèle(s)</b></p>';
			} else {
				echo '</b></p>';
			}
		} else {
			echo 'hypothèse de câblage serait d\'avoir un <b>régulateur type '.$meilleurRegulateur['nom'].'</b> (<a rel="tooltip" class="bulles" title="Avec caractéristiques similaires : <br />Tension de la batterie : '.$meilleurRegulateur['Vbat'].'V<br />Puissance maximale PV : '.$meilleurRegulateur['PmaxPv'].'W<br />Tension PV circuit ouvert : '.$meilleurRegulateur['VmaxPv'].'V<br />Courant PV court circuit : '.$meilleurRegulateur['ImaxPv'].'A">?</a>) sur lequel serait connecté(s) <b>'.$nbPvSerie.' panneau(x) en série';
			if ($nbPvParalele != 1) {
				echo ' sur '.$nbPvParalele.' parallèle(s)</b></p>';
			} else {
				echo '</b></p>';
			}
		}
		?>
		<div id="resultCalcRegu" class="calcul">
			<p>Un régulateur type <?= $meilleurRegulateur['nom'] ?>, avec un parc de batterie(s) en <b><?= $meilleurRegulateur['Vbat'] ?>V</b>, accepte  : </p>
			<ul>
				<li><b><?= $meilleurRegulateur['PmaxPv'] ?>W</b> de puissance maximum de panneaux : </li>
					<ul><li>Avec un total de <?= $nbPvSerie*$nbPvParalele ?> panneau(x) en <?= $meilleurParcPv['W'] ?>W, on monte à <b><?= $meilleurParcPv['W']*$nbPvParalele*$nbPvSerie ?>W</b> (<a rel="tooltip" class="bulles" title="<?= $meilleurParcPv['W'] ?>W x <?= $nbPvParalele*$nbPvSerie ?> panneau(x) ">?</a>)</li></ul>
				<li><b><?= $meilleurRegulateur['VmaxPv'] ?>V</b> de tension PV maximale de circuit ouvert : </li>
					<ul><li>Avec <?= $nbPvSerie ?> panneau(x) en série ayant une tension (Vdoc) de <?= $meilleurParcPv['Vdoc'] ?>V, on monte à <b><?= $nbPvSerie*$meilleurParcPv['Vdoc'] ?>V</b> (<a rel="tooltip" class="bulles" title="<?= $meilleurParcPv['Vdoc'] ?>V (Vdoc) x <?= $nbPvSerie ?> panneau(x) en série">?</a>)</li></ul>
				<li><b><?= $meilleurRegulateur['ImaxPv'] ?>A</b> de courant de court-circuit PV maximal : </li>
					<ul><li>Avec <?= $nbPvParalele ?> panneau(x) en parallèle(s) ayant une intensité (Isc) de <?= $meilleurParcPv['Isc'] ?>A et une marge de sécurité de <?= $_GET['reguMargeIcc'] ?>%, on monte à <b><?= $nbPvParalele*($meilleurParcPv['Isc']+$meilleurParcPv['Isc']*$_GET['reguMargeIcc']/100) ?>A</b> (<a rel="tooltip" class="bulles" title="(<?= $meilleurParcPv['Isc'] ?>A d'Isc * <?= $_GET['reguMargeIcc'] ?>/100 de marge + <?= $meilleurParcPv['Isc'] ?>A d'Isc) x <?= $nbPvParalele ?> panneau(x) en parallèle(s)">?</a>)</li></ul>
			</ul>
			<p>Note : La mise en série multiple la tension (V) et la mise en parallèle multiplie l'intensité (I)</p>
			<p>Toutes ces caractéristiques sont disponibles dans la fiche technique du produit. Vous pouvez personnaliser les caractéristiques de votre régulateur en mode <i>Expert</i>.</p>
			<p><a class="more" id="resultCalcReguHide">Cacher la démarche</a></p>
			<p> </p>
		</div>
		<p><a id="resultCalcReguShow">Voir, comprendre la démarche</a></p>	
		<?php
	}
	?>
	<h3>Le reste de l'équipement</h3>
	<p>Il vous reste encore à choisir :</p>
	<ul>
		<li><a href="http://www.solarmad-nrj.com/convertisseur.html">Le convertisseur</a> : il est là pour convertir le signal continu des batteries <?= $U ?>V en signal alternatif 230V. Il se choisit avec le voltage d’entrée (ici <?= $U ?>V venus des batteries) et sa puissance maximum en sortie. Pour la puissance maximum de sortie il faut prendre votre appareil qui consomme le plus ou la somme de la puissance des appareils qui seront allumés en même temps ;</li>
		<li>Le câblage, les éléments de protection (disjoncteur, coup circuit)...</li>
	</ul>
	<!-- Afficher ou non les informations complémentaire du formulaire -->
	<script type="text/javascript">
		$( "#resultCalcPvShow" ).click(function() {
			$( "#resultCalcPv" ).show( "slow" );
			$( "#resultCalcPvShow" ).hide( "slow" );
		});
		$( "#resultCalcPvHide" ).click(function() {
			$( "#resultCalcPv" ).hide( "slow" );
			$( "#resultCalcPvShow" ).show( "slow" );
		});
		$( "#resultCalcBatShow" ).click(function() {
			$( "#resultCalcBat" ).show( "slow" );
			$( "#resultCalcBatShow" ).hide( "slow" );
		});
		$( "#resultCalcBatHide" ).click(function() {
			$( "#resultCalcBat" ).hide( "slow" );
			$( "#resultCalcBatShow" ).show( "slow" );
		});
		$( "#resultCalcReguShow" ).click(function() {
			$( "#resultCalcRegu" ).show( "slow" );
			$( "#resultCalcReguShow" ).hide( "slow" );
		});
		$( "#resultCalcReguHide" ).click(function() {
			$( "#resultCalcRegu" ).hide( "slow" );
			$( "#resultCalcReguShow" ).show( "slow" );
		});
		$( "#resultCalcPvHide" ).click();
		$( "#resultCalcBatHide" ).click();
		$( "#resultCalcReguHide" ).click();
	</script>
	<?php
	} 
	echo '</div>';
}

/*
 * ####### Formulaire #######
*/
?>
<form method="get" action="#" id="formulaireCalcPvAutonome">
	
	<div class="form Ni">
		<label>Votre degré de connaisance en photovoltaïque : </label>
		<select id="Ni" name="Ni">
			<option value="1"<?php echo valeurRecupSelect('Ni', 1); ?>>Débutant</option>
			<option value="2"<?php echo valeurRecupSelect('Ni', 2); ?>>Eclairé</option>
			<option value="3"<?php echo valeurRecupSelect('Ni', 3); ?>>Expert</option>
		</select>
	</div>
	
	<h2 class="titre vous">Votre consommation :</h2>	
			
		<p>C'est l'étape la plus importante pour votre dimensionnement. Si vous ne connaissez pas cette valeur rendez-vous sur notre <b><a href="<?= $config_ini['formulaire']['UrlCalcConsommation'] ?>&from=CalcPvAutonome" id="DemandeCalcPvAutonome">interface de calcul de besoins journaliers</a></b></p>
		
		<div class="form Bj">
			<label>Vos besoins électriques journaliers :</label>
			<input id="Bj" type="number" min="1" max="99999" style="width: 100px;" value="<?php echo valeurRecup('Bj'); ?>" name="Bj" />  Wh/j
		</div>
		<?php
		function ongletActif($id) {
			if ($_GET['Ej'] != '' && $id == 'valeur') {
				echo ' class="actif"';
			} elseif ($_GET['Ej'] == '' && $id == 'carte') {
				echo ' class="actif"';
			}
		}
		?>

	<div class="part pv">
		<h2 class="titre pv">Dimensionnement des panneaux photovoltaïques</h2>
	
		<p>Rayonnement en fonction de votre situation géographique : </p>
		<ul id="onglets">
			<li<?php echo ongletActif('carte'); ?>>Carte par zone (simple)</li>
			<li<?php echo ongletActif('valeur'); ?>>Valeur (précis)</li>
		</ul>
		<div id="contenu">
			
			<div class="modeCarte item">
				<div class="form ZoneId">
					<p>Cette simulation simple part du principe que vous êtes orienté plein sud (0°) sans zone d'ombre :</p>
					<label>Sélectionner votre zone (en fonction de la carte ci-après) : </label>
					<select name="ZoneId">
						<option value="1" style="background-color: #98e84f"<?php echo valeurRecupSelect('ZoneId', 1); ?>>Zone 1 : Lille</option>
						<option value="2" style="background-color: #ccee53"<?php echo valeurRecupSelect('ZoneId', 2); ?>>Zone 2 : Paris, Rennes, Strasbourg</option>
						<option value="3" style="background-color: #f9ef58"<?php echo valeurRecupSelect('ZoneId', 3); ?>>Zone 3 : Nantes, Orléans, Besançon</option>
						<option value="4" style="background-color: #f7cd3a"<?php echo valeurRecupSelect('ZoneId', 4); ?>>Zone 4 : Limoges, Clermont-Ferrand</option>
						<option value="5" style="background-color: #ed8719"<?php echo valeurRecupSelect('ZoneId', 5); ?>>Zone 5 : Lyon, Bordeaux, Toulouse</option>
						<option value="6" style="background-color: #e16310"<?php echo valeurRecupSelect('ZoneId', 6); ?>>Zone 6 : Carcasonne, Aubnas</option>
						<option value="7" style="background-color: #c9490c"<?php echo valeurRecupSelect('ZoneId', 7); ?>>Zone 7 : Montpellier, Nimes, Perpignan</option>
						<option value="8" style="background-color: #b61904"<?php echo valeurRecupSelect('ZoneId', 8); ?>>Zone 8 : Marseille</option>
					</select>
				</div>
				
				<div class="form Deg">
					<label>Donner l'inclinaison des panneaux <a rel="tooltip" class="bulles" title="En site isolé on choisie l'inclinaison optimum pour le pire mois de l'année niveau ensoleillement, en France souvent décembre ~65°">(~65° conseillé)</a></label>
					
					<select name="Deg">
						<option value="0"<?php echo valeurRecupSelect('Deg', 0); ?>>0°</option>
						<option value="35"<?php echo valeurRecupSelect('Deg', 35); ?>>35°</option>
						<option value="65"<?php echo valeurRecupSelect('Deg', 65); ?>>65°</option>
					</select>
				</div>
				<p>Pour plus d'options et de précisions, vous pouvez passer en mode valeur.</p>
			</div>
		
			<div class="modeInput item">
				<div class="form Ej">
					<label>Donner la valeur du rayonnement moyen quotidien du mois le plus défavorable dans le plan (l'inclinaison) du panneau :</label>
					<input maxlength="4" size="4" id="Ej" type="number" step="0.01" min="0" max="10" style="width: 100px;" value="<?php echo valeurRecup('Ej'); ?>" name="Ej" /> kWh/m²/j
					<p>Pour obtenir cette valeur rendez vous sur le site de <a href="http://ines.solaire.free.fr/gisesol_1.php" target="_blank">INES</a>, choisir votre ville, l'inclinaison & l'orientation des panneaux puis valider. Il s'agit ensuite de prendre la plus basse valeur de la ligne "Globale (IGP)" (dernière ligne du second tableau) Plus d'informations en bas de cette page : <a href="http://www.photovoltaique.guidenr.fr/cours-photovoltaique-autonome/VI_calcul-puissance-crete.php">Comment obtenir la valeur de Ei, Min sur le site de l'INES ?</a></p>
				</div>
			</div>
			
		</div>
		
		<div class="form ModPv">
			<label>Modèle de panneau : </label>
			<select id="ModPv" name="ModPv">
				<option value="auto">Automatique</option>
				<option value="perso" style="font-weight: bold"<?php echo valeurRecupSelect('ModPv', 'perso'); ?>>Personnaliser</option>
				<?php 
				foreach ($config_ini['pv'] as $pvModele => $pvValeur) {
					echo '<option value="'.$pvModele.'"';
					echo valeurRecupSelect('ModPv', $pvModele);
					echo '>'.ucfirst($pvValeur['type']).' '.$pvValeur['W'].'Wc en '.$pvValeur['V'].'V</option>';
					echo "\n";
				}
				?>
			</select> 
		</div>
		<div class="form TypePv">
			<label>Technologie préféré de panneau : </label>
			<select id="TypePv" name="TypePv">
				<option value="monocristalin"<?php echo valeurRecupSelect('TypePv', 'monocristalin'); ?>>Monocristalin</option>
				<option value="polycristallin"<?php echo valeurRecupSelect('TypePv', 'polycristallin'); ?>>Polycristallin</option>
			</select> 
		</div>
		
		<div class="form PersoPv">
			<p>Vous pouvez détailler les caractéristiques techniques de votre panneau : </p>
			<ul>
				<li>
					<label>Puissance maximum (Pmax)  : </label>
					<input type="number" min="1" max="9999" style="width: 70px;" value="<?php echo valeurRecup('PersoPvW'); ?>"  name="PersoPvW" />Wc
				</li>
				<li>
					<label>Tension : </label>
					<input type="number" min="1" max="999" style="width: 70px;" value="<?php echo valeurRecup('PersoPvV'); ?>" name="PersoPvV" />V
				</li>
				<li>
					<label>Tension en circuit ouvert (Voc) </label>
					<input type="number" step="0.01" min="1" max="99" style="width: 70px;" value="<?php echo valeurRecup('PersoPvVdoc'); ?>"  name="PersoPvVdoc" />V
				</li>
				<li>
					<label>Courant de court circuit (Isc)</label>
					<input type="number" step="0.01" min="0,01" max="99" style="width: 70px;" value="<?php echo valeurRecup('PersoPvIsc'); ?>"  name="PersoPvIsc" />A
				</li>
			</ul>
		</div>
			
		<div class="form Rb">
			<label>Rendement électrique des batteries : </label>
			<input maxlength="4" size="4" id="Rb" type="number" step="0.01" min="0" max="1" style="width: 70px;" value="<?php echo valeurRecup('Rb'); ?>" name="Rb" />
		</div>
		<div class="form Ri">
			<label>Rendement électrique du reste de l’installation (régulateur de charge…) : </label>
			<input maxlength="4" size="4" id="Ri" type="number" step="0.01" min="0" max="1" style="width: 70px;" value="<?php echo valeurRecup('Ri'); ?>" name="Ri" />
		</div>
	</div>
	
	<div class="part bat">
		<h2 class="titre bat">Dimensionnement du parc de batteries</h2>
		<p>Cette application est pré-paramétrée pour des batteries plomb AGM/Gel/OPzV</p>
		<div class="form Aut">
			<label>Nombre de jours d'autonomie : </label>
			<input maxlength="2" size="2" id="Aut" type="number" step="1" min="0" max="50" style="width: 50px" value="<?php echo valeurRecup('Aut'); ?>" name="Aut" />
		</div>
		<div class="form U">
			<label>Tension finale du parc de batteries : </label>
			<select id="U" name="U">
				<option value="0"<?php echo valeurRecupSelect('U', 0); ?>>Auto</option>
				<option value="12"<?php echo valeurRecupSelect('U', 12); ?>>12</option>
				<option value="24"<?php echo valeurRecupSelect('U', 24); ?>>24</option>
				<option value="48"<?php echo valeurRecupSelect('U', 48); ?>>48</option>
			</select> V <a rel="tooltip" class="bulles" title="En mode automatique la tension des batteries sera déduite du besoin en panneaux<br />De 0 à 800Wc : 12V<br />De 800 à 1600 Wc : 24V<br />Au dessus de 1600 Wc : 48V">(?)</a>
		</div>
		<div class="form DD">
			<label>Degré de décharge limite : </label>
			<input maxlength="2" size="2" id="DD" type="number" step="1" min="0" max="100" style="width: 70px" value="<?php echo valeurRecup('DD'); ?>" name="DD" /> %
		</div>
		<div class="form ModBat">
			<label>Modèle de batterie (<a href="http://www.batterie-solaire.com/batterie-delestage-electrique.htm" target="_blank">donné en C10</a>) : </label>
			<select id="ModBat" name="ModBat">
				<option value="auto">Automatique</option>
				<option value="perso" style="font-weight: bold"<?php echo valeurRecupSelect('ModBat', 'perso'); ?>>Personnaliser</option>
				<?php 
				foreach ($config_ini['batterie'] as $batModele => $batValeur) {
					echo '<option value="'.$batModele.'"';
					echo valeurRecupSelect('ModBat', $batModele);
					echo '>'.$batValeur['nom'].'</option>';
					echo "\n";
				}
				?>
			</select> <a rel="tooltip" class="bulles" title="En mode automatique, au dessus de 550A, il sera utilisé des batteries GEL OPzV 2V">(?)</a>
		</div>
		<div class="form PersoBat">
			<p>Vous pouvez détailler les caractéristiques techniques de votre batterie : </p>
			<ul>
				<li>
					<label>Capacité (C10) : </label>
					<input type="number" min="1" max="9999" style="width: 70px;" value="<?php echo valeurRecup('PersoBatAh'); ?>"  name="PersoBatAh" />Ah
				</li>
				<li>
					<label>Tension : </label>
					<select id="PersoBatV" name="PersoBatV">
						<option value="2"<?php echo valeurRecupSelect('PersoBatV', 2); ?>>2</option>
						<option value="12"<?php echo valeurRecupSelect('PersoBatV', 12); ?>>12</option>
					</select> V
				</li>
			</ul>
		</div>
		<div class="form IbatCharge">
			<label>Capacité de courant de charge max : </label>
			<input maxlength="2" size="2" id="IbatCharge" type="number" step="1" min="0" max="100" style="width: 70px" value="<?php echo valeurRecup('IbatCharge'); ?>" name="IbatCharge" /> %
		</div>
		<div class="form IbatDecharge">
			<label>Capacité de courant de décharge max : </label>
			<input  maxlength="2" size="2" id="IbatDecharge" type="number" step="1" min="0" max="100" style="width: 70px" value="<?php echo valeurRecup('IbatDecharge'); ?>" name="IbatDecharge" /> %
		</div>
	</div>
	
	<div class="part regu">
		<h2 class="titre regu">Regulateur de charge</h2>
		<div class="form ModRegu">
			<label>Modèle de régulateur : </label>
			<select id="ModRegu" name="ModRegu">
				<option value="auto">Automatique</option>
				<option value="perso" style="font-weight: bold"<?php echo valeurRecupSelect('ModRegu', 'perso'); ?>>Personnaliser</option>
				<?php 
				$ReguModeleDoublonCheck[]=null;
				foreach ($config_ini['regulateur'] as $ReguModele => $ReguValeur) {
					if (!in_array(substr($ReguModele, 0, -3), $ReguModeleDoublonCheck)) {	
						echo '<option value="'.substr($ReguModele, 0, -3).'"';
						echo valeurRecupSelect('ModRegu', substr($ReguModele, 0, -3));
						echo '>'.$ReguValeur['nom'].'</option>';
						echo "\n";
						$ReguModeleDoublonCheck[]=substr($ReguModele, 0, -3);
					}
				}
				?>
			</select>
		</div>
		<div class="form PersoRegu">
			<p>Vous pouvez détailler les caractéristiques techniques de votre régulateur solair : </p>
			<ul>
				<li>
					<label>Tension finale des batteries : <a rel="tooltip" class="bulles" title="Cette valeur se change dans 'Dimensionnement du parc batteries'"><span id="PersoReguVbat"></span>V</a></label>
				</li>
				<li>
					<label>Puissance maximale PV : </label>
					<input type="number" min="1" max="9999" style="width: 70px;" value="<?php echo valeurRecup('PersoReguPmaxPv'); ?>"  name="PersoReguPmaxPv" />W
				</li>
				<li>
					<label>Tension PV maximale de circuit ouvert : </label>
					<input type="number" min="1" max="9999" style="width: 70px;" value="<?php echo valeurRecup('PersoReguVmaxPv'); ?>" name="PersoReguVmaxPv" />V
				</li>
				<li>
					<label>Max. PV courant (Puissance / Tension) :</label>
					<input type="number" step="0.01" min="0,01" max="999" style="width: 70px;" value="<?php echo valeurRecup('PersoReguImaxPv'); ?>"  name="PersoReguImaxPv" />A
				</li>
			</ul>
		</div>
		<div class="form reguMargeIcc">
			<label>Marge de sécurité du courant de court-circuit Icc des panneaux : </label>
			<input maxlength="2" size="2" id="reguMargeIcc" type="number" step="1" min="0" max="100" style="width: 70px" value="<?php echo valeurRecup('reguMargeIcc'); ?>" name="reguMargeIcc" /> %
		</div>
	</div>
		
	<div class="form End">
		<input id="Reset" type="button" value="Remise à 0" name="reset" />
		<input id="Submit" type="submit" value="Lancer le calcul" name="submit" />
	</div>
</form>

<div id="CarteZone">
	<a href="./lib/Zone-solar-map-fr.png" target="_blank"><img src="./lib/Zone-solar-map-fr.png" /></a>
</div>

<!-- Détection des changement dans le formulaire -->
<input type="hidden" value="0" id="ModificationDuFormulaire" />

<script type="text/javascript">
// Détection des changement dans le formulaire
$( "input" ).change(function () {
	if ($( "#ModificationDuFormulaire" ).val() == 0) {
		$( "#ModificationDuFormulaire" ).val(1);		
	}
});
$( "select" ).change(function () {
	if ($( "#ModificationDuFormulaire" ).val() == 0) {
		$( "#ModificationDuFormulaire" ).val(1);		
	}
});
$('#DemandeCalcPvAutonome').click(function() {
	if ($( "#ModificationDuFormulaire" ).val() == 1) {
		return confirm("Vous avez commencé à remplir ce formulaire, vous allez perdre ces informations en continuant.");
	}
});
$( "#ModPv" ).change(function () {
	modPvChange();
});
$( "#ModBat" ).change(function () {
	modBatChange();
});
$( "#ModRegu" ).change(function () {
	modReguChange();
});
$( "#U" ).change(function () {
	$( "#PersoReguVbat" ).text($( "#U" ).val());
});

// Bouton Submit activation / désactivation
function sumbitEnable() {
	if ($( "#Bj" ).val() > 0) {
		$( "#Submit" ).prop('disabled', false);
	} else {
		$( "#Submit" ).prop('disabled', true);
	}
}
$( "#Bj" ).change(function() {
	sumbitEnable();
});

// Changement de modèle de PV
function modPvChange() {
	if ($( "#ModPv" ).val() == 'auto') {
		$( ".form.TypePv" ).show();
		$( ".form.PersoPv" ).hide();
	} else if ($( "#ModPv" ).val() == 'perso') {
		$( ".form.TypePv" ).hide();
		$( ".form.PersoPv" ).show();
	} else {
		$( ".form.TypePv" ).hide();
		$( ".form.PersoPv" ).hide();
	}
}
// Changement de modèle de batterie
function modBatChange() {
	if ($( "#ModBat" ).val() == 'auto') {
		$( ".form.PersoBat" ).hide();
	} else if ($( "#ModBat" ).val() == 'perso') {
		$( ".form.PersoBat" ).show();
	} else {
		$( ".form.PersoBat" ).hide();
	}
}
// Changement modèle régulateur
function modReguChange() {
	if ($( "#ModRegu" ).val() == 'auto') {
		$( ".form.TypeRegu" ).show();
		$( ".form.PersoRegu" ).hide();
		$("#U").append('<option value="0">Auto</option>');
		$("#U").val('0');
	} else if ($( "#ModRegu" ).val() == 'perso') {
		$( ".form.TypeRegu" ).hide();
		$( ".form.PersoRegu" ).show();
		$("#U option[value='0']").remove();
		$( "#PersoReguVbat" ).text($( "#U" ).val());
	} else {
		$( ".form.TypeRegu" ).hide();
		$( ".form.PersoRegu" ).hide();
		$("#U").append('<option value="0">Auto</option>');
		$("#U").val('0');
	}
	
}

// Changement de niveau
$( "#Ni" ).change(function () {
	changeNiveau();
});
function changeNiveau() {
	// Debutant (1)
	if ($( "#Ni" ).val() == 1) {
		$( ".form.Ri" ).hide();
		$( ".form.Rb" ).hide();
		$( ".form.AUT" ).hide();
		$( ".form.U" ).hide();
		$( ".form.DD" ).hide();
		$( ".part.bat" ).hide();
		$( ".part.regu" ).hide();
		$( ".form.ModBat" ).hide();
		$( ".form.IbatCharge" ).hide();
		$( ".form.IbatDecharge" ).hide();
		$( ".form.ModPv" ).hide();
		$( ".form.TypePv" ).hide();
	// Eclaire (2)
	} else if  ($( "#Ni" ).val() == 2) {
		$( ".form.Ri" ).hide();
		$( ".form.Rb" ).hide();
		$( ".form.AUT" ).show();
		$( ".form.U" ).hide();
		$( ".form.DD" ).hide();
		$( ".part.bat" ).show();
		$( ".part.regu" ).hide();
		$( ".form.ModBat" ).hide();
		$( ".form.IbatCharge" ).hide();
		$( ".form.IbatDecharge" ).hide();
		$( ".form.ModPv" ).hide();
		$( ".form.TypePv" ).show();
	// Expert (3)
	} else if ($( "#Ni" ).val() == 3) {
		$( ".form.Ri" ).show();
		$( ".form.Rb" ).show();
		$( ".form.AUT" ).show();
		$( ".form.U" ).show();
		$( ".form.DD" ).show();
		$( ".part.bat" ).show();
		$( ".part.regu" ).show();
		$( ".form.ModBat" ).show();
		$( ".form.IbatCharge" ).show();
		$( ".form.IbatDecharge" ).show();
		$( ".form.ModPv" ).show();
		$( ".form.TypePv" ).show();
	}
}

// Onglet carte zone
// http://dmouronval.developpez.com/tutoriels/javascript/mise-place-navigation-par-onglets-avec-jquery/
$(function() {
	$('#onglets').css('display', 'block');
	$('#onglets').click(function(event) {
		var actuel = event.target;
		if (!/li/i.test(actuel.nodeName) || actuel.className.indexOf('actif') > -1) {
			//alert(actuel.nodeName)
			return;
		}
		$(actuel).addClass('actif').siblings().removeClass('actif');
		setDisplay();
		$( "#Ej" ).val('');
	});
	function setDisplay() {
		var modeAffichage;
		$('#onglets li').each(function(rang) {
			modeAffichage = $(this).hasClass('actif') ? '' : 'none';
			$('.item').eq(rang).css('display', modeAffichage);
		});
	}
	setDisplay();
});

// Reset form
$( "#Reset" ).click(function() {
	window.location = 'http://<?php echo $_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"] ?>';
});

$(document).ready(function() {
	// Init formulaire 
	changeNiveau();
	modPvChange(); 
	modBatChange();
	modReguChange(); 
	sumbitEnable();	
	
	/* infobulles http://javascript.developpez.com/tutoriels/javascript/creer-info-bulles-css-et-javascript-simplement-avec-jquery/ */
    // Sélectionner tous les liens ayant l'attribut rel valant tooltip
    $('a[rel=tooltip]').mouseover(function(e) {
		// Récupérer la valeur de l'attribut title et l'assigner à une variable
		var tip = $(this).attr('title');   
		// Supprimer la valeur de l'attribut title pour éviter l'infobulle native
		$(this).attr('title','');
		// Insérer notre infobulle avec son texte dans la page
		$(this).append('<div id="tooltip"><div class="tipBody">' + tip + '</div></div>');    
		// Ajuster les coordonnées de l'infobulle
		$('#tooltip').css('top', e.pageY + 10 );
		$('#tooltip').css('left', e.pageX + 20 );
		// Faire apparaitre l'infobulle avec un effet fadeIn
	}).mousemove(function(e) {
		// Ajuster la position de l'infobulle au déplacement de la souris
		$('#tooltip').css('top', e.pageY + 10 );
		$('#tooltip').css('left', e.pageX + 20 );
	}).mouseout(function() {
		// Réaffecter la valeur de l'attribut title
		$(this).attr('title',$('.tipBody').html());
		// Supprimer notre infobulle
		$(this).children('div#tooltip').remove();
	});
}); 


</script>

