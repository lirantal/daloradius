# UNIT-027 — suppression d'opérateurs et de leurs ACL (DB-OPADMIN-001)

## Périmètre

`app/operators/config-operators-del.php` lit désormais la liste des noms proposés avec PDO, puis confie la suppression à `app/operators/library/operator_delete.php`. Le contrôle de session et le contrôle de permission partagé `check_operator_perm.php` restent sur PEAR, hors de la transaction de suppression.

Toute la sélection (nom unique, sélection multiple, doublons, Unicode et caractères réservés) est validée avant écriture. Les noms et les ID sont liés à des requêtes préparées. Les noms sont verrouillés dans un ordre stable sur la connexion PDO et chacun doit correspondre exactement à une seule ligne ; un nom périmé ou ambigu annule la demande entière. Les ACL existantes sont verrouillées puis supprimées avant leur opérateur dans **une seule transaction PDO**. Un échec sur un opérateur ou une ACL tardive restaure les suppressions antérieures. Les deux noms de tables sont limités aux clés de configuration autorisées et les moteurs InnoDB sont vérifiés avant la mutation. La suppression de son propre compte reste permise, comme dans l'ancien parcours ; ses ACL sont révoquées immédiatement.

## Écarts délibérés et limites

- L'ancien code retirait tous les `%` des noms soumis : un nom réel contenant `%` ne pouvait pas être supprimé ; PDO conserve `%`, `+` et les apostrophes littéralement, sans second décodage URL.
- L'ancien parcours pouvait ignorer un nom inexistant puis supprimer les autres, et additionnait un résultat de requête à un autre pour compter les suppressions. Le nouveau parcours refuse la **sélection entière** si une cible manque ou est ambiguë et affiche uniquement les noms réellement supprimés, une seule fois par nom demandé.
- Aucun schéma nouveau n'est nécessaire pour les tables déjà transactionnelles. Le verrouillage du parent coordonne les écritures qui suivent la même discipline (dont l'édition de UNIT-026), mais sans clé étrangère universelle ni contrainte unique sur les noms, des écrivains externes non coordonnés peuvent toujours recréer un nom ou ajouter une ACL concurrente. Les sessions déjà émises ne sont pas supprimées explicitement ; l'ACL de la page est réévaluée au prochain accès. D'autres composants du layout partagé peuvent encore lire avec PEAR : si la table des opérateurs disparaît, ils peuvent échouer avant que cette page n'affiche son erreur de chargement PDO.

## Vérification

Le test `tests/operator_delete_http.py` exécute la page PEAR antérieure avec `OPERATOR_DELETE_BASELINE=1` et le candidat PDO sans cette variable, sur deux exécutions HTTP/PHP/MariaDB isolées à schéma et scénario identiques. L'empreinte de l'état final normal (opérateurs et ACL) concorde. Les cas PDO supplémentaires couvrent : session/ACL/CSRF, sélection malformée ou périmée, caractères `%`/`+`/apostrophe, nom en doublon, rollback après échec sur une ACL ou un parent tardif, refus d'une table non transactionnelle, deux suppressions concurrentes et retrait de son propre compte. Les contrôles de syntaxe PHP et les régressions ciblées restent complémentaires. **Ces essais jetables ne valident pas un environnement de production.**
