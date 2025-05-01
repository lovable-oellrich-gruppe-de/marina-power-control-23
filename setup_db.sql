SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Datenbank: `marina_power`
--
-- --------------------------------------------------------
--
-- Tabellenstruktur für Tabelle `benutzer`
--

CREATE TABLE `benutzer` (
  `id` int(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `passwort_hash` varchar(255) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `rolle` enum('admin','user') DEFAULT 'user',
  `status` enum('active','pending') DEFAULT 'pending',
  `erstellt_am` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Daten für Tabelle `benutzer`
--

INSERT INTO `benutzer` (`id`, `email`, `passwort_hash`, `name`, `rolle`, `status`, `erstellt_am`) VALUES
(1, 'admin@marina-power.de', '$2y$10$SsKSkrbMoIoZwKvU6nptqOsRH6JiiaPBvA1gxBBGeLDU1RYYFxdym', 'Administrator', 'admin', 'active', '2025-04-26 07:52:55');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `bereiche`
--

CREATE TABLE `bereiche` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `beschreibung` text DEFAULT NULL,
  `aktiv` tinyint(1) DEFAULT 1,
  `erstellt_am` timestamp NULL DEFAULT current_timestamp(),
  `aktualisiert_am` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Daten für Tabelle `bereiche`
--

INSERT INTO `bereiche` (`id`, `name`, `beschreibung`, `aktiv`, `erstellt_am`, `aktualisiert_am`) VALUES
(1, 'Außenlager', 'alle Steckdosen auf dem Außengelände', 1, '2025-04-26 07:52:55', '2025-04-28 11:07:48'),
(2, 'Bootshalle', 'alle Steckdosen in der Bootshalle', 1, '2025-04-26 07:52:55', '2025-04-28 11:07:51'),
(3, 'Hafen', 'alle Steckdosen auf dem Anleger', 1, '2025-04-27 16:54:35', '2025-04-28 11:07:54');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `mieter`
--

CREATE TABLE `mieter` (
  `id` int(11) NOT NULL,
  `vorname` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `strasse` varchar(100) DEFAULT NULL,
  `hausnummer` varchar(20) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `telefon` varchar(50) DEFAULT NULL,
  `mobil` varchar(50) DEFAULT NULL,
  `hinweis` text DEFAULT NULL,
  `bootsname` varchar(100) DEFAULT NULL,
  `stellplatzNr` varchar(20) DEFAULT NULL,
  `vertragStart` date DEFAULT NULL,
  `vertragEnde` date DEFAULT NULL,
  `liegeplatz_nr` varchar(20) DEFAULT NULL,
  `aktiv` tinyint(1) DEFAULT 1,
  `erstellt_am` timestamp NULL DEFAULT current_timestamp(),
  `aktualisiert_am` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `steckdosen`
--

CREATE TABLE `steckdosen` (
  `id` int(11) NOT NULL,
  `bezeichnung` varchar(100) NOT NULL,
  `status` enum('aktiv','inaktiv','defekt') NOT NULL DEFAULT 'aktiv',
  `bereich_id` int(11) DEFAULT NULL,
  `mieter_id` int(11) DEFAULT NULL,
  `letzte_ablesung` datetime DEFAULT NULL,
  `hinweis` text DEFAULT NULL,
  `erstellt_am` timestamp NULL DEFAULT current_timestamp(),
  `aktualisiert_am` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Daten für Tabelle `steckdosen`
--

INSERT INTO `steckdosen` (`id`, `bezeichnung`, `status`, `bereich_id`, `mieter_id`, `letzte_ablesung`, `hinweis`, `erstellt_am`, `aktualisiert_am`) VALUES
(1, 'Steckdose Nr. 01', 'aktiv', 1, NULL, NULL, 'Testdatensdasdasd', '2025-04-26 07:52:55', '2025-04-28 11:08:16'),
(2, 'Steckdose Nr. 02', 'aktiv', 1, NULL, NULL, 'Frei verfügbar', '2025-04-26 07:52:55', '2025-05-01 15:54:26'),
(3, 'Steckdose Nr. 03', 'aktiv', 1, NULL, NULL, 'Testdaten', '2025-04-26 07:52:55', '2025-05-01 15:55:13'),
(4, 'Steckdose Nr. 04', 'aktiv', 1, NULL, NULL, 'Frei verfügbar', '2025-04-26 07:52:55', '2025-04-28 11:08:28'),
(5, 'Steckdose Nr. 05', 'aktiv', 1, NULL, NULL, 'a', '2025-04-27 14:01:25', '2025-04-29 04:33:29'),
(6, 'Steckdose Nr. 06', 'aktiv', 1, NULL, NULL, 'Testdatensdasdasd', '2025-04-26 07:52:55', '2025-04-29 04:33:32'),
(7, 'Steckdose Nr. 07', 'aktiv', 1, NULL, NULL, 'Frei verfügbar', '2025-04-26 07:52:55', '2025-05-01 15:55:13'),
(8, 'Steckdose Nr. 08', 'aktiv', 1, NULL, NULL, 'Testdaten', '2025-04-26 07:52:55', '2025-05-01 15:55:13'),
(9, 'Steckdose Nr. 09', 'aktiv', 1, NULL, NULL, 'Frei verfügbar', '2025-04-26 07:52:55', '2025-04-29 04:33:43'),
(10, 'Steckdose Nr. 10', 'aktiv', 1, NULL, NULL, 'a', '2025-04-27 14:01:25', '2025-04-29 04:33:46'),
(11, 'Steckdose Nr. 11', 'aktiv', 1, NULL, NULL, 'Testdatensdasdasd', '2025-04-26 07:52:55', '2025-04-29 04:34:54'),
(12, 'Steckdose Nr. 12', 'aktiv', 1, NULL, NULL, 'Frei verfügbar', '2025-04-26 07:52:55', '2025-05-01 15:55:13'),
(13, 'Steckdose Nr. 13', 'aktiv', 1, NULL, NULL, 'Testdaten', '2025-04-26 07:52:55', '2025-05-01 15:55:13'),
(14, 'Steckdose Nr. 14', 'aktiv', 1, NULL, NULL, 'Frei verfügbar', '2025-04-26 07:52:55', '2025-04-29 04:37:09'),
(15, 'Steckdose Nr. 15', 'aktiv', 1, NULL, NULL, 'a', '2025-04-27 14:01:25', '2025-04-29 04:37:11'),
(16, 'Steckdose Nr. 16', 'aktiv', 1, NULL, NULL, 'Testdatensdasdasd', '2025-04-26 07:52:55', '2025-04-29 04:34:40'),
(17, 'Steckdose Nr. 17', 'aktiv', 1, NULL, NULL, 'Frei verfügbar', '2025-04-26 07:52:55', '2025-05-01 15:55:13'),
(18, 'Steckdose Nr. 18', 'aktiv', 1, NULL, NULL, 'Testdaten', '2025-04-26 07:52:55', '2025-05-01 15:55:13'),
(19, 'Steckdose Nr. 01', 'aktiv', 2, NULL, NULL, 'Testdatensdasdasd', '2025-04-26 07:52:55', '2025-04-29 04:34:54'),
(20, 'Steckdose Nr. 02', 'aktiv', 2, NULL, NULL, 'Frei verfügbar', '2025-04-26 07:52:55', '2025-05-01 15:54:32'),
(21, 'Steckdose Nr. 03', 'aktiv', 2, NULL, NULL, 'Testdaten', '2025-04-26 07:52:55', '2025-05-01 15:55:13'),
(22, 'Steckdose Nr. 04', 'aktiv', 2, NULL, NULL, 'Frei verfügbar', '2025-04-26 07:52:55', '2025-04-29 04:34:45'),
(23, 'Steckdose Nr. 05', 'aktiv', 2, NULL, NULL, 'a', '2025-04-27 14:01:25', '2025-04-29 04:34:43'),
(24, 'Steckdose Nr. 06', 'aktiv', 2, NULL, NULL, 'Testdatensdasdasd', '2025-04-26 07:52:55', '2025-04-29 04:34:40'),
(25, 'Steckdose Nr. 07', 'aktiv', 2, NULL, NULL, 'Frei verfügbar', '2025-04-26 07:52:55', '2025-05-01 15:55:13'),
(26, 'Steckdose Nr. 08', 'aktiv', 2, NULL, NULL, 'Testdaten', '2025-04-26 07:52:55', '2025-05-01 15:55:13'),
(27, 'Steckdose Nr. 09', 'aktiv', 2, NULL, NULL, 'Frei verfügbar', '2025-04-26 07:52:55', '2025-04-29 04:34:30'),
(28, 'Steckdose Nr. 10', 'aktiv', 2, NULL, NULL, 'a', '2025-04-27 14:01:25', '2025-04-29 04:40:52'),
(29, 'Steckdose Nr. 11', 'aktiv', 2, NULL, NULL, '', '2025-04-29 04:39:20', '2025-04-29 04:42:03'),
(30, 'Steckdose Nr. 12', 'aktiv', 2, NULL, NULL, '', '2025-04-29 04:39:27', '2025-04-29 04:42:06'),
(31, 'Steckdose Nr. 13', 'aktiv', 2, NULL, NULL, '', '2025-04-29 04:39:35', '2025-04-29 04:42:09'),
(32, 'Steckdose Nr. 14', 'aktiv', 2, NULL, NULL, '', '2025-04-29 04:39:42', '2025-04-29 04:42:11'),
(33, 'Steckdose Nr. 15', 'aktiv', 2, NULL, NULL, '', '2025-04-29 04:39:48', '2025-04-29 04:42:13'),
(34, 'Steckdose Nr. 16', 'aktiv', 2, NULL, NULL, '', '2025-04-29 04:39:53', '2025-04-29 04:42:17'),
(35, 'Steckdose Nr. 17', 'aktiv', 2, NULL, NULL, '', '2025-04-29 04:39:59', '2025-04-29 04:42:25'),
(36, 'Steckdose Nr. 18', 'aktiv', 2, NULL, NULL, '', '2025-04-29 04:40:06', '2025-04-29 04:42:30'),
(37, 'Steckdose Nr. 01', 'aktiv', 3, NULL, NULL, '', '2025-04-29 04:40:13', '2025-04-29 04:44:32'),
(38, 'Steckdose Nr. 02', 'aktiv', 3, NULL, NULL, '', '2025-04-29 04:44:13', '2025-04-29 04:44:26'),
(39, 'Steckdose Nr. 03', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(40, 'Steckdose Nr. 04', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(41, 'Steckdose Nr. 05', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(42, 'Steckdose Nr. 06', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(43, 'Steckdose Nr. 07', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(44, 'Steckdose Nr. 08', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(45, 'Steckdose Nr. 09', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(46, 'Steckdose Nr. 10', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(47, 'Steckdose Nr. 11', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(48, 'Steckdose Nr. 12', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(49, 'Steckdose Nr. 13', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(50, 'Steckdose Nr. 14', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(51, 'Steckdose Nr. 15', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(52, 'Steckdose Nr. 16', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(53, 'Steckdose Nr. 17', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(54, 'Steckdose Nr. 18', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(55, 'Steckdose Nr. 19', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(56, 'Steckdose Nr. 20', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(57, 'Steckdose Nr. 21', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(58, 'Steckdose Nr. 22', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(59, 'Steckdose Nr. 23', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(60, 'Steckdose Nr. 24', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(61, 'Steckdose Nr. 25', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(62, 'Steckdose Nr. 26', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(63, 'Steckdose Nr. 27', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(64, 'Steckdose Nr. 28', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(65, 'Steckdose Nr. 29', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(66, 'Steckdose Nr. 30', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(67, 'Steckdose Nr. 31', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(68, 'Steckdose Nr. 32', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(69, 'Steckdose Nr. 33', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(70, 'Steckdose Nr. 34', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(71, 'Steckdose Nr. 35', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59'),
(72, 'Steckdose Nr. 36', 'aktiv', 3, NULL, NULL, NULL, '2025-04-29 04:52:59', '2025-04-29 04:52:59');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `zaehler`
--

CREATE TABLE `zaehler` (
  `id` int(11) NOT NULL,
  `zaehlernummer` varchar(50) NOT NULL,
  `steckdose_id` int(11) DEFAULT NULL,
  `typ` varchar(50) DEFAULT 'Stromzähler',
  `hersteller` varchar(100) DEFAULT NULL,
  `modell` varchar(100) DEFAULT NULL,
  `installiert_am` date NOT NULL,
  `letzte_wartung` date DEFAULT NULL,
  `seriennummer` varchar(100) DEFAULT NULL,
  `max_leistung` int(11) DEFAULT NULL,
  `ist_ausgebaut` tinyint(1) DEFAULT 0,
  `hinweis` text DEFAULT NULL,
  `erstellt_am` timestamp NULL DEFAULT current_timestamp(),
  `aktualisiert_am` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Daten für Tabelle `zaehler`
--

INSERT INTO `zaehler` (`id`, `zaehlernummer`, `steckdose_id`, `typ`, `hersteller`, `modell`, `installiert_am`, `letzte_wartung`, `seriennummer`, `max_leistung`, `ist_ausgebaut`, `hinweis`, `erstellt_am`, `aktualisiert_am`) VALUES
(1, 'Z-001B-Aussen', 1, 'Stromzähler', 'unbekannt', '', '2025-04-28', NULL, '', 3600, 0, '', '2025-04-28 12:44:58', '2025-04-28 12:45:12'),
(2, 'Z-002B-Aussen', 2, 'Stromzähler', '', '', '2025-04-29', NULL, '', NULL, 0, '', '2025-04-29 04:53:53', '2025-04-29 04:54:05'),
(3, 'Z-003B-Aussen', 3, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 04:56:28', '2025-04-29 04:58:33'),
(4, 'Z-004B-Aussen', 4, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 04:58:02', '2025-04-29 04:58:02'),
(5, 'Z-005B-Aussen', 5, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 04:58:02', '2025-04-29 04:58:02'),
(6, 'Z-006B-Aussen', 6, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 04:58:02', '2025-04-29 04:58:02'),
(7, 'Z-007B-Aussen', 7, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 04:58:02', '2025-04-29 04:58:02'),
(8, 'Z-008B-Aussen', 8, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 04:58:02', '2025-04-29 04:58:02'),
(9, 'Z-009B-Aussen', 9, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 04:58:02', '2025-04-29 04:58:02'),
(10, 'Z-010B-Aussen', 10, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 04:58:02', '2025-04-29 04:58:02'),
(11, 'Z-011B-Aussen', 11, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 04:58:02', '2025-04-29 04:58:02'),
(12, 'Z-012B-Aussen', 12, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 04:58:02', '2025-04-29 04:58:02'),
(13, 'Z-013B-Aussen', 13, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 04:58:02', '2025-04-29 04:58:02'),
(14, 'Z-014B-Aussen', 14, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 04:58:02', '2025-04-29 04:58:02'),
(15, 'Z-015B-Aussen', 15, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 04:58:02', '2025-04-29 04:58:02'),
(16, 'Z-016B-Aussen', 16, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 04:58:02', '2025-04-29 04:58:02'),
(17, 'Z-017B-Aussen', 17, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 04:58:02', '2025-04-29 04:58:02'),
(18, 'Z-018B-Aussen', 18, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 04:58:02', '2025-04-29 04:58:02'),
(19, 'Z-001B-Bootshalle', 19, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:01:23', '2025-04-29 05:01:23'),
(20, 'Z-002B-Bootshalle', 20, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:01:23', '2025-04-29 05:01:23'),
(21, 'Z-003B-Bootshalle', 21, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:01:23', '2025-04-29 05:01:23'),
(22, 'Z-004B-Bootshalle', 22, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:01:23', '2025-04-29 05:01:23'),
(23, 'Z-005B-Bootshalle', 23, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:01:23', '2025-04-29 05:01:23'),
(24, 'Z-006B-Bootshalle', 24, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:01:23', '2025-04-29 05:01:23'),
(25, 'Z-007B-Bootshalle', 25, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:01:23', '2025-04-29 05:01:23'),
(26, 'Z-008B-Bootshalle', 26, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:01:23', '2025-04-29 05:01:23'),
(27, 'Z-009B-Bootshalle', 27, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:01:23', '2025-04-29 05:01:23'),
(28, 'Z-010B-Bootshalle', 28, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:01:23', '2025-04-29 05:01:23'),
(29, 'Z-011B-Bootshalle', 29, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:01:23', '2025-04-29 05:01:23'),
(30, 'Z-012B-Bootshalle', 30, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:01:23', '2025-04-29 05:01:23'),
(31, 'Z-013B-Bootshalle', 31, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:01:23', '2025-04-29 05:01:23'),
(32, 'Z-014B-Bootshalle', 32, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:01:23', '2025-04-29 05:01:23'),
(33, 'Z-015B-Bootshalle', 33, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:01:23', '2025-04-29 05:01:23'),
(34, 'Z-016B-Bootshalle', 34, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:01:23', '2025-04-29 05:01:23'),
(35, 'Z-017B-Bootshalle', 35, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:01:23', '2025-04-29 05:01:23'),
(36, 'Z-018B-Bootshalle', 36, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:01:23', '2025-04-29 05:01:23'),
(37, 'Z-001B-Hafen', 37, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:03', '2025-04-29 05:04:03'),
(38, 'Z-002B-Hafen', 38, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:03', '2025-04-29 05:04:03'),
(39, 'Z-003B-Hafen', 39, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(40, 'Z-004B-Hafen', 40, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(41, 'Z-005B-Hafen', 41, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(42, 'Z-006B-Hafen', 42, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(43, 'Z-007B-Hafen', 43, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(44, 'Z-008B-Hafen', 44, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(45, 'Z-009B-Hafen', 45, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(46, 'Z-010B-Hafen', 46, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(47, 'Z-011B-Hafen', 47, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(48, 'Z-012B-Hafen', 48, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(49, 'Z-013B-Hafen', 49, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(50, 'Z-014B-Hafen', 50, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(51, 'Z-015B-Hafen', 51, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(52, 'Z-016B-Hafen', 52, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(53, 'Z-017B-Hafen', 53, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(54, 'Z-018B-Hafen', 54, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(55, 'Z-019B-Hafen', 55, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(56, 'Z-020B-Hafen', 56, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(57, 'Z-021B-Hafen', 57, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(58, 'Z-022B-Hafen', 58, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(59, 'Z-023B-Hafen', 59, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(60, 'Z-024B-Hafen', 60, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(61, 'Z-025B-Hafen', 61, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(62, 'Z-026B-Hafen', 62, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(63, 'Z-027B-Hafen', 63, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(64, 'Z-028B-Hafen', 64, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(65, 'Z-029B-Hafen', 65, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(66, 'Z-030B-Hafen', 66, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(67, 'Z-031B-Hafen', 67, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(68, 'Z-032B-Hafen', 68, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(69, 'Z-033B-Hafen', 69, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(70, 'Z-034B-Hafen', 70, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(71, 'Z-035B-Hafen', 71, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04'),
(72, 'Z-036B-Hafen', 72, 'Stromzähler', NULL, NULL, '2025-04-28', NULL, NULL, NULL, 0, NULL, '2025-04-29 05:04:04', '2025-04-29 05:04:04');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `zaehlerstaende`
--

CREATE TABLE `zaehlerstaende` (
  `id` int(11) NOT NULL,
  `zaehler_id` int(11) NOT NULL,
  `steckdose_id` int(11) DEFAULT NULL,
  `mieter_name` varchar(255) DEFAULT NULL,
  `datum` date NOT NULL,
  `stand` decimal(10,2) NOT NULL,
  `vorheriger_id` int(11) DEFAULT NULL,
  `verbrauch` decimal(10,2) DEFAULT NULL,
  `abgelesen_von_id` varchar(36) DEFAULT NULL,
  `foto_url` text DEFAULT NULL,
  `ist_abgerechnet` tinyint(1) DEFAULT 0,
  `hinweis` text DEFAULT NULL,
  `erstellt_am` timestamp NULL DEFAULT current_timestamp(),
  `aktualisiert_am` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `benutzer`
--
ALTER TABLE `benutzer`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indizes für die Tabelle `bereiche`
--
ALTER TABLE `bereiche`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `mieter`
--
ALTER TABLE `mieter`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `steckdosen`
--
ALTER TABLE `steckdosen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bereich_id` (`bereich_id`),
  ADD KEY `mieter_id` (`mieter_id`);

--
-- Indizes für die Tabelle `zaehler`
--
ALTER TABLE `zaehler`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `zaehlerstaende`
--
ALTER TABLE `zaehlerstaende`
  ADD PRIMARY KEY (`id`),
  ADD KEY `zaehler_id` (`zaehler_id`),
  ADD KEY `steckdose_id` (`steckdose_id`),
  ADD KEY `vorheriger_id` (`vorheriger_id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `benutzer`
--
ALTER TABLE `benutzer`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT für Tabelle `bereiche`
--
ALTER TABLE `bereiche`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT für Tabelle `mieter`
--
ALTER TABLE `mieter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `steckdosen`
--
ALTER TABLE `steckdosen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT für Tabelle `zaehler`
--
ALTER TABLE `zaehler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT für Tabelle `zaehlerstaende`
--
ALTER TABLE `zaehlerstaende`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `steckdosen`
--
ALTER TABLE `steckdosen`
  ADD CONSTRAINT `steckdosen_ibfk_1` FOREIGN KEY (`bereich_id`) REFERENCES `bereiche` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `steckdosen_ibfk_2` FOREIGN KEY (`mieter_id`) REFERENCES `mieter` (`id`) ON DELETE SET NULL;

--
-- Constraints der Tabelle `zaehlerstaende`
--
ALTER TABLE `zaehlerstaende`
  ADD CONSTRAINT `zaehlerstaende_ibfk_1` FOREIGN KEY (`zaehler_id`) REFERENCES `zaehler` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `zaehlerstaende_ibfk_2` FOREIGN KEY (`steckdose_id`) REFERENCES `steckdosen` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `zaehlerstaende_ibfk_3` FOREIGN KEY (`vorheriger_id`) REFERENCES `zaehlerstaende` (`id`) ON DELETE SET NULL;
COMMIT;
