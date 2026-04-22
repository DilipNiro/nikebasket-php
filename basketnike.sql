-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 27 mars 2025 à 19:02
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `basketnike`
--

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

CREATE TABLE `categorie` (
  `id` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `categorie`
--

INSERT INTO `categorie` (`id`, `nom`) VALUES
(1, 'Lifestyle'),
(2, 'Running'),
(3, 'Basketball'),
(4, 'Training'),
(5, 'Casual');

-- --------------------------------------------------------

--
-- Structure de la table `commande`
--

CREATE TABLE `commande` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `montant_total` decimal(10,2) NOT NULL,
  `commandee_le` timestamp NOT NULL DEFAULT current_timestamp(),
  `statut` varchar(50) NOT NULL DEFAULT 'en attente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `commande`
--

INSERT INTO `commande` (`id`, `user_id`, `montant_total`, `commandee_le`, `statut`) VALUES
(11, 35, 240.00, '2024-11-16 12:39:13', 'en_preparation'),
(12, 35, 240.00, '2024-12-02 15:20:37', 'en_preparation'),
(13, 35, 100.00, '2024-12-05 18:55:52', 'annulee'),
(14, 35, 340.00, '2025-03-05 14:16:23', 'payee'),
(15, 43, 480.00, '2025-03-27 11:31:00', 'payee');

-- --------------------------------------------------------

--
-- Structure de la table `commande_historique`
--

CREATE TABLE `commande_historique` (
  `id` int(11) NOT NULL,
  `commande_id` int(11) NOT NULL,
  `ancien_statut` varchar(50) DEFAULT NULL,
  `status` varchar(50) NOT NULL,
  `modifie_par` int(11) NOT NULL,
  `modifie_le` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `commande_historique`
--

INSERT INTO `commande_historique` (`id`, `commande_id`, `ancien_statut`, `status`, `modifie_par`, `modifie_le`) VALUES
(2, 11, NULL, 'en_preparation', 26, '2024-11-16 14:08:19'),
(3, 11, NULL, 'payee', 26, '2024-11-16 14:08:32'),
(4, 11, NULL, 'en_preparation', 26, '2024-11-16 14:08:39'),
(5, 12, NULL, 'en_preparation', 26, '2024-12-03 10:36:19'),
(6, 12, NULL, 'en_preparation', 26, '2024-12-07 08:23:02'),
(7, 12, NULL, 'expediee', 26, '2024-12-07 08:23:22'),
(8, 12, NULL, 'en_preparation', 36, '2024-12-07 18:28:05'),
(9, 13, NULL, 'en_preparation', 36, '2024-12-07 18:28:30'),
(10, 13, 'en_preparation', 'expediee', 26, '2025-02-04 16:42:39'),
(11, 13, 'expediee', 'livree', 26, '2025-02-11 12:12:21'),
(12, 13, 'livree', 'annulee', 26, '2025-02-18 09:39:09');

-- --------------------------------------------------------

--
-- Structure de la table `commande_produits`
--

CREATE TABLE `commande_produits` (
  `id` int(11) NOT NULL,
  `commande_id` int(11) NOT NULL,
  `produit_id` int(11) NOT NULL,
  `taille_id` int(11) NOT NULL,
  `couleur_id` int(11) NOT NULL,
  `quantite` int(11) NOT NULL DEFAULT 1,
  `prix_unitaire` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `commande_produits`
--

INSERT INTO `commande_produits` (`id`, `commande_id`, `produit_id`, `taille_id`, `couleur_id`, `quantite`, `prix_unitaire`) VALUES
(14, 11, 34, 23, 3, 1, 120.00),
(15, 11, 36, 20, 2, 1, 120.00),
(16, 12, 34, 20, 3, 1, 120.00),
(17, 12, 34, 23, 3, 1, 120.00),
(18, 13, 36, 20, 2, 1, 100.00),
(19, 14, 33, 18, 3, 2, 170.00),
(20, 15, 35, 18, 1, 3, 160.00);

-- --------------------------------------------------------

--
-- Structure de la table `couleur`
--

CREATE TABLE `couleur` (
  `id` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `couleur`
--

INSERT INTO `couleur` (`id`, `nom`) VALUES
(1, 'Rouge'),
(2, 'Noir'),
(3, 'Blanc'),
(4, 'Bleu');

-- --------------------------------------------------------

--
-- Structure de la table `paiement`
--

CREATE TABLE `paiement` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `commande_id` int(11) NOT NULL,
  `mode_paiement` varchar(50) NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `date_paiement` datetime NOT NULL DEFAULT current_timestamp(),
  `statut_paiement` varchar(50) NOT NULL,
  `transaction_id` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `paiement`
--

INSERT INTO `paiement` (`id`, `user_id`, `commande_id`, `mode_paiement`, `montant`, `date_paiement`, `statut_paiement`, `transaction_id`) VALUES
(12, 35, 11, 'carte', 244.99, '2024-11-16 13:39:13', 'completed', 'pi_3QLlHQP2n2YjjvGk0pzwCOEW'),
(13, 35, 12, 'carte', 244.99, '2024-12-02 16:20:37', 'completed', 'pi_3QRbQOP2n2YjjvGk0x5mdssE'),
(14, 35, 13, 'carte', 103.99, '2024-12-05 19:55:52', 'completed', 'pi_3QSkDLP2n2YjjvGk1jLM1Olk'),
(15, 35, 14, 'carte', 340.00, '2025-03-05 15:16:23', 'completed', 'pi_3QzIk7P2n2YjjvGk0YHDRSKz'),
(16, 43, 15, 'carte', 480.00, '2025-03-27 12:31:00', 'completed', 'pi_3R7EeFP2n2YjjvGk1EfVTKZ0');

-- --------------------------------------------------------

--
-- Structure de la table `panier`
--

CREATE TABLE `panier` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `produit_id` int(11) NOT NULL,
  `couleur_id` int(11) NOT NULL,
  `taille_id` int(11) NOT NULL,
  `quantite` int(11) NOT NULL DEFAULT 1,
  `prix` decimal(10,2) NOT NULL,
  `ajout_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `panier`
--

INSERT INTO `panier` (`id`, `user_id`, `produit_id`, `couleur_id`, `taille_id`, `quantite`, `prix`, `ajout_date`) VALUES
(14, 40, 36, 4, 29, 1, 120.00, '2025-01-05 23:09:49'),
(21, 41, 34, 3, 20, 1, 120.00, '2025-03-26 14:11:52');

-- --------------------------------------------------------

--
-- Structure de la table `produits`
--

CREATE TABLE `produits` (
  `id` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `categorie_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `prix` decimal(10,2) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `image_hover_url` varchar(255) NOT NULL,
  `date_sortie` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `quantite` int(11) NOT NULL DEFAULT 0,
  `statut` enum('actif','archive','en_rupture') NOT NULL DEFAULT 'actif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `produits`
--

INSERT INTO `produits` (`id`, `nom`, `categorie_id`, `description`, `prix`, `image_url`, `image_hover_url`, `date_sortie`, `quantite`, `statut`) VALUES
(33, 'air ma', 1, 'aix maa', 170.00, 'images/NIKE+AIR+MAX+90.png', 'images/airmaxH.png', '2025-03-05 14:16:23', 48, 'actif'),
(34, 'air Force 1', 5, 'air force', 120.00, 'images/W+AF1+SAGE+LOW.png', 'images/imagesH.jpeg', '2025-03-27 18:01:02', 44, 'actif'),
(35, 'nike Pegasus', 3, 'pegasus', 160.00, 'images/nikePega.jpg', 'images/nikePegaH.png', '2025-03-27 18:01:51', 40, 'actif'),
(36, 'Air force 1', 5, 'black air force', 120.00, 'images/airForce.webp', 'images/airforceH.jpg', '2025-03-27 18:01:12', 66, 'actif'),
(37, 'air jordan', 3, 'air jordan mid', 190.00, 'images/WMNS+AIR+JORDAN+1+MID.png', 'images/AIR+JORDAN+1+MID+(GS).png', '2024-10-30 17:22:12', 0, 'en_rupture'),
(38, 'vapormax', 5, 'vapoooor', 300.00, 'images/nike-air-vapormax-glacier-blue-1.jpg', 'images/nike-air-vapormax-flyknit-day-to-night-blue-orbit-849558-402.jpg', '2025-03-27 18:00:35', 60, 'actif'),
(46, 'Air max 90', 5, 'Marche en plein air', 120.00, 'images/airmax90.jpg', 'images/airmax90h.jpg', '2025-03-27 17:45:19', 0, 'en_rupture'),
(47, 'air max dn', 3, 'For play', 180.00, 'images/airmaxd.png', 'images/airmaxdnH.png', '2025-03-27 17:47:28', 0, 'en_rupture'),
(48, 'P9000', 2, 'Tout le monde l\'adore', 100.00, 'images/p9000.jpg', 'images/p9000H.jpg', '2025-03-27 17:59:53', 60, 'actif'),
(49, 'Nike Romaleos 4 SE', 4, 'Wooow', 200.00, 'images/romelo.jpg', 'images/romeloH.jpg', '2025-03-27 18:01:38', 120, 'actif'),
(50, 'Nike Air Max Plus III', 1, 'Nike Air Max Plusss III', 170.00, 'images/aimaxpluss.jpg', 'images/airmaxplussH.png', '2025-03-27 17:55:15', 150, 'actif');

-- --------------------------------------------------------

--
-- Structure de la table `produit_images`
--

CREATE TABLE `produit_images` (
  `id` int(11) NOT NULL,
  `produit_id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `ordre` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `produit_images`
--

INSERT INTO `produit_images` (`id`, `produit_id`, `image_url`, `ordre`) VALUES
(58, 34, 'imagesC/6722a3f13093b_1730323441.webp', 1),
(59, 34, 'imagesC/6722a3f8288e4_1730323448.jpg', 2),
(60, 34, 'imagesC/6722a3fd2ecdb_1730323453.png', 3),
(61, 34, 'imagesC/6722a402e910b_1730323458.webp', 4),
(62, 35, 'imagesC/672bb72ab105f_1730918186.webp', 1),
(63, 35, 'imagesC/672bb7303cb8c_1730918192.jpg', 2),
(64, 35, 'imagesC/672bb735c7ad5_1730918197.jpeg', 3);

-- --------------------------------------------------------

--
-- Structure de la table `stock`
--

CREATE TABLE `stock` (
  `id` int(11) NOT NULL,
  `produit_id` int(11) NOT NULL,
  `taille_id` int(11) NOT NULL,
  `couleur_id` int(11) NOT NULL,
  `quantite` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `stock`
--

INSERT INTO `stock` (`id`, `produit_id`, `taille_id`, `couleur_id`, `quantite`) VALUES
(20, 36, 18, 2, 19),
(21, 36, 20, 2, 7),
(22, 36, 29, 4, 20),
(23, 34, 23, 3, 17),
(24, 34, 20, 3, 7),
(25, 35, 33, 3, 0),
(26, 35, 18, 1, 20),
(31, 33, 18, 3, 48),
(33, 50, 18, 1, 20),
(34, 50, 19, 1, 20),
(35, 50, 20, 1, 20),
(36, 50, 21, 1, 20),
(37, 50, 22, 1, 20),
(38, 50, 21, 2, 20),
(39, 50, 22, 4, 10),
(40, 50, 32, 3, 20),
(41, 49, 23, 3, 20),
(42, 49, 26, 3, 20),
(43, 49, 24, 4, 20),
(44, 49, 27, 4, 20),
(45, 49, 28, 2, 20),
(46, 48, 28, 3, 20),
(47, 48, 29, 3, 20),
(48, 48, 31, 3, 20),
(49, 38, 33, 4, 20),
(50, 38, 18, 4, 20),
(51, 38, 28, 4, 20),
(52, 34, 25, 4, 20),
(53, 36, 25, 2, 20),
(54, 49, 30, 1, 20),
(55, 35, 30, 2, 20);

-- --------------------------------------------------------

--
-- Structure de la table `taille`
--

CREATE TABLE `taille` (
  `id` int(11) NOT NULL,
  `valeur` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `taille`
--

INSERT INTO `taille` (`id`, `valeur`) VALUES
(18, '40'),
(19, '40.5'),
(20, '41'),
(21, '42'),
(22, '42.5'),
(23, '43'),
(24, '44'),
(25, '44.5'),
(26, '45'),
(27, '45.5'),
(28, '46'),
(29, '47'),
(30, '47.5'),
(31, '48.5'),
(32, '49.5'),
(33, '50.5');

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('client','admin','employe') DEFAULT 'client',
  `secret` varchar(32) DEFAULT NULL,
  `reset_token_hash` varchar(64) DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL,
  `password_changed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `nom`, `email`, `password`, `created_at`, `role`, `secret`, `reset_token_hash`, `reset_token_expires_at`, `password_changed`) VALUES
(26, 'admin1', 'admin1@gmail.com', '$2y$10$xdz08g9nPz9ZL0n2R2k8W.2Yus7akhv3CMqCG9EgTCR4W8vLVH.1y', '2024-10-01 14:40:38', 'admin', 'RP3JMWYGIUJCFJY4', NULL, NULL, 0),
(35, 'niro', 'niro@gmail.com', '$argon2id$v=19$m=65536,t=4,p=3$d09lRjV5cnNXcnYwbmRBSg$MuQTRpIu83OyyY8RzRxOFYg2WEj6MRW4Mlom/gBtkBg', '2024-11-16 12:19:16', 'client', 'NICLRTXDDAEJ6NBC', '39e15e0738266419fd422a421e77a76612a3566e3cfc0a8e5c6bcf7691c54d0c', '2024-12-01 16:26:25', 0),
(36, 'Thomas', 'roxozorkal@gmail.com', '$argon2id$v=19$m=65536,t=4,p=3$QXdMRHk3UUNacS5tRFJDRQ$4n5oPhWh6wR7NSJb4KtyqydxAwEQo/HrrAMiv+UrUBg', '2024-11-18 17:17:42', 'employe', 'PU2FYI5XQUM2XAPV', NULL, NULL, 1),
(39, 'employe99', 'employe99@gmail.com', '$argon2id$v=19$m=65536,t=4,p=3$L1hIUFZRRk40b2haeldhWQ$75mzHivdjpOPajn/+UF8CPMvXSBJfC1+m+239HpsstE', '2025-01-05 22:39:54', 'employe', 'EM6BX5DBGSG7IY2K', NULL, NULL, 1),
(40, 'niro', 'dilipniro.dev@gmail.com', '$argon2id$v=19$m=65536,t=4,p=3$WlF2RDZRUUZtM0hjdERqYg$jPUQk5iIlK+b5CauNSNUraPqvde/xW2X+sKqGJ6I2Q4', '2025-01-05 23:09:00', 'client', '32JEVY5MBZYWALRU', NULL, NULL, 0),
(41, 'yamal', 'busta92000@gmail.com', '$argon2id$v=19$m=65536,t=4,p=3$dlFiM0J3LzhIa1BDMDI2MQ$hRb7skMXXbpLsKDm/fI4Vu6i2eqHhVOD8KgzdMlzO6w', '2025-03-05 14:20:10', 'client', 'MCDVDA537GPKD67K', NULL, NULL, 0),
(42, 'admin', 'admin@gmail.com', '$argon2id$v=19$m=65536,t=4,p=3$RDZtcVJ6amVsaFVJMmh2Qw$homYJmvqYuO8onN73xZ4LxljNAE3t6mlFoAdUUv5T6A', '2025-03-05 15:46:22', 'employe', NULL, NULL, NULL, 0),
(43, 'rodriguez', 'absalome92000@gmail.com', '$argon2id$v=19$m=65536,t=4,p=3$UkJ2NHVMQkxDb3RKLktXWg$AFDxc8zl3Csskd4YKa7LIY+2vYGN4KIiyRVTKahKSPk', '2025-03-27 11:24:25', 'client', 'AU5M7UFXFF4M2Y3I', NULL, NULL, 0);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `categorie`
--
ALTER TABLE `categorie`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `commande`
--
ALTER TABLE `commande`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `commande_historique`
--
ALTER TABLE `commande_historique`
  ADD PRIMARY KEY (`id`),
  ADD KEY `commande_id` (`commande_id`),
  ADD KEY `modifie_par` (`modifie_par`);

--
-- Index pour la table `commande_produits`
--
ALTER TABLE `commande_produits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `commande_id` (`commande_id`),
  ADD KEY `produit_id` (`produit_id`),
  ADD KEY `fk_commande_produits_taille` (`taille_id`),
  ADD KEY `fk_commande_produits_couleur` (`couleur_id`);

--
-- Index pour la table `couleur`
--
ALTER TABLE `couleur`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `paiement`
--
ALTER TABLE `paiement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paiement_user_fk` (`user_id`),
  ADD KEY `paiement_commande_fk` (`commande_id`);

--
-- Index pour la table `panier`
--
ALTER TABLE `panier`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `produit_id` (`produit_id`),
  ADD KEY `couleur_id` (`couleur_id`),
  ADD KEY `taille_id` (`taille_id`);

--
-- Index pour la table `produits`
--
ALTER TABLE `produits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produits_ibfk_1` (`categorie_id`);

--
-- Index pour la table `produit_images`
--
ALTER TABLE `produit_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produit_id` (`produit_id`);

--
-- Index pour la table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `taille_id` (`taille_id`),
  ADD KEY `couleur_id` (`couleur_id`),
  ADD KEY `produit_id` (`produit_id`);

--
-- Index pour la table `taille`
--
ALTER TABLE `taille`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `reset_token_hash` (`reset_token_hash`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `categorie`
--
ALTER TABLE `categorie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `commande`
--
ALTER TABLE `commande`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `commande_historique`
--
ALTER TABLE `commande_historique`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `commande_produits`
--
ALTER TABLE `commande_produits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pour la table `couleur`
--
ALTER TABLE `couleur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `paiement`
--
ALTER TABLE `paiement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `panier`
--
ALTER TABLE `panier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT pour la table `produits`
--
ALTER TABLE `produits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT pour la table `produit_images`
--
ALTER TABLE `produit_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT pour la table `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT pour la table `taille`
--
ALTER TABLE `taille`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `commande`
--
ALTER TABLE `commande`
  ADD CONSTRAINT `commande_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `commande_historique`
--
ALTER TABLE `commande_historique`
  ADD CONSTRAINT `commande_historique_ibfk_1` FOREIGN KEY (`commande_id`) REFERENCES `commande` (`id`),
  ADD CONSTRAINT `commande_historique_ibfk_2` FOREIGN KEY (`modifie_par`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `commande_produits`
--
ALTER TABLE `commande_produits`
  ADD CONSTRAINT `commande_produits_ibfk_1` FOREIGN KEY (`commande_id`) REFERENCES `commande` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `commande_produits_ibfk_2` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_commande_produits_couleur` FOREIGN KEY (`couleur_id`) REFERENCES `couleur` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_commande_produits_taille` FOREIGN KEY (`taille_id`) REFERENCES `taille` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `paiement`
--
ALTER TABLE `paiement`
  ADD CONSTRAINT `paiement_commande_fk` FOREIGN KEY (`commande_id`) REFERENCES `commande` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `paiement_user_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `panier`
--
ALTER TABLE `panier`
  ADD CONSTRAINT `panier_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `panier_ibfk_2` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `panier_ibfk_3` FOREIGN KEY (`couleur_id`) REFERENCES `couleur` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `panier_ibfk_4` FOREIGN KEY (`taille_id`) REFERENCES `taille` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `produits`
--
ALTER TABLE `produits`
  ADD CONSTRAINT `produits_ibfk_1` FOREIGN KEY (`categorie_id`) REFERENCES `categorie` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `produit_images`
--
ALTER TABLE `produit_images`
  ADD CONSTRAINT `produit_images_ibfk_1` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `stock_ibfk_2` FOREIGN KEY (`taille_id`) REFERENCES `taille` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `stock_ibfk_3` FOREIGN KEY (`couleur_id`) REFERENCES `couleur` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
