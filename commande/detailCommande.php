<?php
require_once("../auth/FunctionLogin.php");
require_once("gestionCommandes.php");

if (!isset($_GET['id'])) {
	die('ID de commande manquant');
}

$commande_id = (int)$_GET['id'];
$details = getCommandeDetails($commande_id);

if (!$details['commande']) {
	die('Commande introuvable');
}

$historique = getCommandeHistorique($commande_id);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Détail de la commande #<?php echo $commande_id; ?></title>
	<link rel="stylesheet" href="../css/commandes.css">
</head>

<body>
	<div class="container">
		<div class="sidebar">
			<h2>Mon Profil</h2>
			<ul>
				<li><a href="../admin/dashboard.php">Produits</a></li>
				<li><a href="../admin/employe.php">Employés</a></li>
				<li><a href="commandes.php">Commandes</a></li>
				<li><a href="../config/logout.php" class="logout-btn">Se Déconnecter</a></li>
			</ul>
		</div>

		<div class="main-content">
			<div class="commande-detail">
				<h1>Détail de la commande #<?php echo $commande_id; ?></h1>

				<!-- Informations générales -->
				<div class="info-section">
					<h2>Informations générales</h2>
					<div class="info-grid">
						<div class="info-item">
							<label>Client:</label>
							<span><?php echo htmlspecialchars($details['commande']['nom_client']); ?></span>
						</div>
						<div class="info-item">
							<label>Email:</label>
							<span><?php echo htmlspecialchars($details['commande']['email_client']); ?></span>
						</div>
						<div class="info-item">
							<label>Date de commande:</label>
							<span><?php echo date('d/m/Y H:i', strtotime($details['commande']['commandee_le'])); ?></span>
						</div>
						<div class="info-item">
							<label>Statut de la commande:</label>
							<span class="statut-badge <?php echo $details['commande']['statut']; ?>">
								<?php echo htmlspecialchars($details['commande']['statut']); ?>
							</span>
						</div>
						<div class="info-item">
							<label>Statut du paiement:</label>
							<span class="statut-badge <?php echo $details['commande']['statut_paiement']; ?>">
								<?php echo htmlspecialchars($details['commande']['statut_paiement']); ?>
							</span>
						</div>
						<?php if ($details['commande']['transaction_id']): ?>
							<div class="info-item">
								<label>ID Transaction:</label>
								<span><?php echo htmlspecialchars($details['commande']['transaction_id']); ?></span>
							</div>
						<?php endif; ?>
					</div>
				</div>

				<!-- Produits de la commande -->
				<div class="product-list">
					<h3>Produits commandés</h3>
					<?php foreach ($details['produits'] as $produit): ?>
						<div class="product-item">
							<img src="../<?php echo htmlspecialchars($produit['image_url']); ?>"
								alt="<?php echo htmlspecialchars($produit['produit_nom']); ?>"
								class="product-image">
							<div class="product-details">
								<h4><?php echo htmlspecialchars($produit['produit_nom']); ?></h4>
								<p>Taille: <?php echo htmlspecialchars($produit['taille']); ?></p>
								<p>Couleur: <?php echo htmlspecialchars($produit['couleur']); ?></p>
								<p>Quantité: <?php echo $produit['quantite']; ?></p>
								<?php
								// Récupérer le prix original depuis la table produits
								$conn = connectsDB();
								$stmt = $conn->prepare("SELECT prix FROM produits WHERE id = ?");
								$stmt->execute([$produit['produit_id']]);
								$prix_original = $stmt->fetchColumn();

								// Si le prix unitaire est différent du prix original, c'est qu'il y avait une promotion
								if ($prix_original != $produit['prix_unitaire']):
									$reduction = round((($prix_original - $produit['prix_unitaire']) / $prix_original) * 100);
								?>
									<div class="cart-item-price">
										<span class="price-original"><?php echo number_format($prix_original, 2); ?> €</span>
										<span class="price-promo"><?php echo number_format($produit['prix_unitaire'], 2); ?> €</span>
										<span class="reduction-badge">-<?php echo $reduction; ?>%</span>
									</div>
								<?php else: ?>
									<p class="price"><?php echo number_format($produit['prix_unitaire'], 2); ?> €</p>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>

					<div class="order-total">
						<p>Total: <?php echo number_format($details['commande']['montant_total'], 2); ?> €</p>
					</div>
				</div>

				<!-- Historique des modifications -->
				<div class="historique-section">
					<h2>Historique des modifications</h2>
					<div class="historique-timeline">
						<?php if (empty($historique)): ?>
							<p class="no-history">Aucun historique disponible pour cette commande</p>
						<?php else: ?>
							<?php foreach ($historique as $entree): ?>
								<div class="timeline-item" data-status="<?php echo htmlspecialchars($entree['status']); ?>">
									<div class="timeline-status">
										<span class="status-badge statut-<?php echo htmlspecialchars($entree['status']); ?>">
											<?php echo htmlspecialchars($entree['status']); ?>
										</span>
									</div>
									<div class="timeline-info">
										<span class="timeline-date">
											<?php echo date('d/m/Y H:i', strtotime($entree['modifie_le'])); ?>
										</span>
										<span class="timeline-user">
											<?php echo htmlspecialchars($entree['modifie_par_nom']); ?>
										</span>
									</div>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</div>

				<!-- Actions -->
				<div class="actions-section">
					<a href="commandes.php" class="btn-back"> Retour à la liste</a>
					<button onclick="updateStatus(<?php echo $commande_id; ?>, '<?php echo $details['commande']['statut']; ?>')"
						class="btn-update">Modifier le statut</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal pour la mise à jour du statut -->
	<div id="statusModal" class="modal" style="display: none;">
		<div class="modal-content">
			<span class="close">&times;</span>
			<h2>Modifier le statut de la commande</h2>
			<form id="updateStatusForm">
				<input type="hidden" id="commande_id" value="<?php echo $commande_id; ?>">
				<select id="newStatus">
					<option value="en_attente">En attente</option>
					<option value="payee">Payée</option>
					<option value="en_preparation">En préparation</option>
					<option value="expediee">Expédiée</option>
					<option value="livree">Livrée</option>
					<option value="annulee">Annulée</option>
				</select>
				<button type="submit">Mettre à jour</button>
			</form>
		</div>
	</div>

	<script>
		function updateStatus(commandeId, currentStatus) {
			const modal = document.getElementById('statusModal');
			const statusSelect = document.getElementById('newStatus');

			modal.style.display = 'block';
			statusSelect.value = currentStatus;

			document.getElementById('updateStatusForm').onsubmit = function(e) {
				e.preventDefault();
				const newStatus = statusSelect.value;

				fetch('updateCommandeStatus.php', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded',
						},
						body: `commande_id=${commandeId}&status=${newStatus}`
					})
					.then(response => response.json())
					.then(data => {
						if (data.success) {
							alert('Statut mis à jour avec succès');
							location.reload();
						} else {
							alert('Erreur lors de la mise à jour du statut');
						}
					});
			};

			document.querySelector('.close').onclick = function() {
				modal.style.display = 'none';
			}

			window.onclick = function(event) {
				if (event.target == modal) {
					modal.style.display = 'none';
				}
			}
		}
	</script>
</body>

</html>