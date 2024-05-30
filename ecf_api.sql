-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : jeu. 30 mai 2024 à 12:45
-- Version du serveur : 8.2.0
-- Version de PHP : 8.2.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `ecf_api`
--

-- --------------------------------------------------------

--
-- Structure de la table `trainings`
--

DROP TABLE IF EXISTS `trainings`;
CREATE TABLE IF NOT EXISTS `trainings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `activity_name` varchar(255) DEFAULT NULL,
  `time` int DEFAULT NULL,
  `comment` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `trainings`
--

INSERT INTO `trainings` (`id`, `date`, `activity_name`, `time`, `comment`) VALUES
(11, '2024-05-29', 'Test', 5, 'Test'),
(8, '2024-05-29', 'Test', 56, 'Test'),
(10, '2024-05-29', 'Test', 50, 'Test'),
(4, '2024-05-28', 'Natations', 60, 'Bonne séance, amélioration de la technique de crawl.'),
(5, '2024-05-27', 'Vélo', 60, 'Parcours vallonné, très bon pour endurance.'),
(6, '2024-05-26', 'Yoga', 40, 'Séance de relaxation et de stretching, très bénéfique.'),
(7, '2024-05-25', 'Musculation', 50, 'Focus sur les bras et le dos, augmentation des charges.'),
(12, '2024-05-29', 'Test', 50, 'Test'),
(13, '2024-05-29', 'Testoo', 32, 'Tesoo');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(250) NOT NULL,
  `password` varchar(250) NOT NULL,
  `lastname` varchar(250) NOT NULL,
  `firstname` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `lastname`, `firstname`) VALUES
(1, 'Jean@email.comm', '$2y$10$c0DL/jLUPTSGAw6VZ6DTYurR2CsG8OmQtCz/rVSVsMfLrC1siMNny', 'Carlos de la ves', 'Juan');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
