<?php
require_once("../auth/FunctionLogin.php");
require_once("gestionCommandes.php");

// Récupération des filtres
$filters = [];
if (isset($_GET['status']) && !empty($_GET['status'])) {
	$filters['status'] = $_GET['status'];
}
if (isset($_GET['date_debut']) && !empty($_GET['date_debut'])) {
	$filters['date_debut'] = $_GET['date_debut'];
}
if (isset($_GET['date_fin']) && !empty($_GET['date_fin'])) {
	$filters['date_fin'] = $_GET['date_fin'];
}

$commandes = getAllCommandes($filters);
$user_role = $_SESSION['connectedUser']['role']; // Récupérer le rôle de l'utilisateur connecté

?>

<!DOCTYPE html>
<html lang="fr">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Gestion des Commandes</title>
	<link rel="stylesheet" href="../css/commandes.css">
</head>

<body>
	<div class="container">
		<div class="sidebar">
			<h2>Mon Profil</h2>
			<ul>
				<li><a href="../admin/dashboard.php">Chaussures</a></li>
				<?php if ($user_role === 'admin'): ?>
					<li><a href="../admin/employe.php">Employés</a></li>
				<?php endif; ?>
				<li><a class="active" href="commandes.php" class="active">Commandes</a></li>

				<li><a href="../config/logout.php" class="logout-btn">Se Déconnecter</a></li>
			</ul>
		</div>

		<div class="main-content">
			<h1>Gestion des Commandes</h1>

			<!-- Filtres -->
			<div class="filters">
				<form method="GET" action="">
					<select name="status">
						<option value="">Tous les statuts</option>
						<option value="en_attente" <?php echo (isset($filters['status']) && $filters['status'] === 'en_attente') ? 'selected' : ''; ?>>En attente</option>
						<option value="payee" <?php echo (isset($filters['status']) && $filters['status'] === 'payee') ? 'selected' : ''; ?>>Payée</option>
						<option value="en_preparation" <?php echo (isset($filters['status']) && $filters['status'] === 'en_preparation') ? 'selected' : ''; ?>>En préparation</option>
						<option value="expediee" <?php echo (isset($filters['status']) && $filters['status'] === 'expediee') ? 'selected' : ''; ?>>Expédiée</option>
						<option value="livree" <?php echo (isset($filters['status']) && $filters['status'] === 'livree') ? 'selected' : ''; ?>>Livrée</option>
						<option value="annulee" <?php echo (isset($filters['status']) && $filters['status'] === 'annulee') ? 'selected' : ''; ?>>Annulée</option>
					</select>
					<input type="date" name="date_debut" value="<?php echo isset($filters['date_debut']) ? $filters['date_debut'] : ''; ?>" placeholder="Date début">
					<input type="date" name="date_fin" value="<?php echo isset($filters['date_fin']) ? $filters['date_fin'] : ''; ?>" placeholder="Date fin">
					<button type="submit">Filtrer</button>
					<a href="commandes.php" class="reset-btn">Réinitialiser</a>
				</form>
			</div>

			<!-- Liste des commandes -->
			<div class="commandes-list">
				<table>
					<thead>
						<tr>
							<th>ID Commande</th>
							<th>Client</th>
							<th>Date</th>
							<th>Montant</th>
							<th>Statut Commande</th>
							<th>Statut Paiement</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($commandes)): ?>
							<tr>
								<td colspan="7" class="text-center">Aucune commande trouvée</td>
							</tr>
						<?php else: ?>
							<?php foreach ($commandes as $commande): ?>
								<tr>
									<td>#<?php echo htmlspecialchars($commande['id']); ?></td>
									<td><?php echo htmlspecialchars($commande['nom_client']); ?></td>
									<td><?php echo date('d/m/Y H:i', strtotime($commande['commandee_le'])); ?></td>
									<td><?php echo number_format($commande['montant_total'], 2); ?> €</td>
									<td>
										<span class="statut-<?php echo htmlspecialchars($commande['statut']); ?>">
											<?php echo htmlspecialchars($commande['statut']); ?>
										</span>
									</td>
									<td>
										<span class="statut-paiement-<?php echo htmlspecialchars($commande['statut_paiement']); ?>">
											<?php echo htmlspecialchars($commande['statut_paiement']); ?>
										</span>
									</td>
									<td>
										<a href="detailCommande.php?id=<?php echo $commande['id']; ?>"
											class="btn-detail">Détails</a>
										<button onclick="updateStatus(<?php echo $commande['id']; ?>, '<?php echo $commande['statut']; ?>')"
											class="btn-update">Modifier statut</button>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<!-- Modal pour la mise à jour du statut -->
	<div id="statusModal" class="modal" style="display: none;">
		<div class="modal-content">
			<span class="close">&times;</span>
			<h2>Modifier le statut</h2>
			<form id="updateStatusForm">
				<input type="hidden" id="commande_id">
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
			// Afficher la modal
			const modal = document.getElementById('statusModal');
			const commandeIdInput = document.getElementById('commande_id');
			const statusSelect = document.getElementById('newStatus');

			modal.style.display = 'block';
			commandeIdInput.value = commandeId;
			statusSelect.value = currentStatus;

			// Gestionnaire pour la soumission du formulaire
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

			// Fermer la modal
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