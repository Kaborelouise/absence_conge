# Diagrammes UML - Gestion des Demandes d'Absence et Congé

Ce dossier contient les diagrammes PlantUML complets de l'application ANPTIC.

## 📊 Diagrammes de Séquence

### Processus Métier (Demandes)
- `seq_demande_absence.puml` : Création et validation d'une demande d'absence avec circuit multi-acteurs
- `seq_demande_conge.puml` : Création et compilation d'une demande de congé administratif par l'Agent RH
- `seq_demande_jouissance.puml` : Demande de jouissance avec validation et upload de certificats

### Gestion Administrative
- `seq_admin_utilisateurs.puml` : CRUD utilisateurs (création, modification, suppression avec validations)
- `seq_admin_roles.puml` : CRUD rôles avec vérifications de dépendances
- `seq_admin_organisations.puml` : CRUD directions et départements avec cascades de dépendances

## 🔄 Diagrammes d'Activité

### Processus Métier
- `act_demande_absence.puml` : Flux décisionnel complet de la demande d'absence
- `act_demande_conge.puml` : Flux simplifié de la demande de congé
- `act_demande_jouissance.puml` : Flux avec upload certificats et clôture

## 🛠️ Utilisation

1. **Visualisation dans VS Code**
   - Installer l'extension PlantUML
   - Ouvrir un fichier `.puml` et aperçu en direct

2. **Export en image**
   - Clic droit → PlantUML Export → PNG/SVG/PDF

## 📋 Notes Importantes

- **Diagrammes séparé** : Chaque processus a son propre diagramme pour éviter l'encombrement
- **Lignes de vie** : Tous les acteurs et systèmes sont représentés avec leurs interactions
- **Flux complets** : Cas nominal + cas exceptionnels (abandon, rejet, suppression)
- **Validations** : Les contrôles de cohérence (dépendances, droits) sont explicitement montrés
