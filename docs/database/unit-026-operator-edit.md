# UNIT-026 — édition d'un opérateur (DB-OPADMIN-002)

## Périmètre

`config-operators-edit.php` lit désormais l'opérateur par PDO et délègue l'édition à `library/operator_edit.php`. Le nom d'opérateur fourni par le formulaire est lié à une requête paramétrée ; le mot de passe n'est jamais chargé pour afficher la fiche. Les champs de profil, la source d'authentification et l'identifiant externe, la modification facultative du mot de passe, la réinitialisation MFA explicite et les ACL soumises sont écrits avec **la même connexion et une seule transaction PDO**. Toute erreur, y compris lors d'une ACL tardive, annule également les changements de profil et MFA. Aucun secret ou hash n'est journalisé.

Le contrôle de session, la lecture d'autorisation dans `check_operator_perm.php` et l'affichage des ACL dans `operator_acls.php` restent sur leurs chemins PEAR indépendants ; ils ne participent pas à cette transaction. L'affichage des données de profil du formulaire utilise la lecture PDO de l'opérateur. L'édition ne migre ni la création (UNIT-025) ni la suppression (UNIT-027).

## Validation et comportement

- Cible unique liée au nom **et à l'ID** du formulaire ; refus d'un nom absent/ambigu ou d'un ID remplacé. Sous verrou `FOR UPDATE`, la source et l'identifiant externe courants sont comparés au snapshot du formulaire avant mutation. Deux conversions d'identité concurrentes sur le même snapshot ne peuvent pas toutes les deux réussir.
- Respect des règles `operator_prepare_update_identity()` : une conversion demande confirmation ; LDAP vers Local demande un nouveau mot de passe ; LDAP ne conserve jamais un mot de passe local ; un mot de passe vide d'un compte Local ne le modifie pas.
- Les champs de contact sont bornés aux tailles du schéma. Les permissions `ACL_*` doivent être des valeurs `0` ou `1` et provenir du catalogue `operators_acl_files` ; les lignes ACL existantes sont modifiées, les nouvelles insérées. Une sélection vide conserve les ACL précédentes, comme l'ancien formulaire.
- La réinitialisation MFA n'a lieu que si la case est explicitement cochée. Elle efface l'état TOTP et les codes de secours au sein de la transaction ; sans case, l'état MFA est conservé, même lors d'une conversion Local/LDAP.
- Noms de tables limités aux clés de configuration prévues, identifiants SQL contrôlés, valeurs liées à des paramètres PDO. Précontrôle InnoDB pour les trois tables avant toute écriture ; les schémas hors du périmètre transactionnel sont refusés.

**Limites :** la protection contre une édition périmée porte sur l'ID et l'identité (source/identifiant externe), pas sur toutes les modifications concurrentes de contact/ACL faites sans conversion. Une édition de contact ultérieure peut encore remplacer une modification de contact précédente. Le rendu partagé des ACL continue à ne montrer que les permissions déjà associées à l'opérateur, même si l'édition sait insérer une ACL soumise valide. Les écritures externes non coordonnées et les changements de schéma concurrents ne sont pas couverts par ce seul formulaire.

## Vérifications isolées

`OPERATOR_EDIT_BASELINE=1 python3 tests/operator_edit_http.py` exécute le formulaire PEAR du commit avant UNIT-026 dans une copie jetable ; `python3 tests/operator_edit_http.py` utilise le candidat PDO. Les tests utilisent un MariaDB temporaire, un PHP HTTP réel, une session synthétique et un réseau Docker interne. La comparaison porte sur un empreinte de l'état métier **sans conserver ni afficher mot de passe, hash ou facteur**. Les cas supplémentaires côté candidat couvrent les contrôles d'accès/CSRF, la validation de la sélection, les conversions et snapshots périmés, le hash vérifié, le rollback sur ACL tardive (avec et sans reset MFA), le refus des tables non transactionnelles, le nom spécial et la concurrence de deux connexions PDO.

Ce banc démontre le comportement de ces fixtures isolées, **pas une validation en production**. Aucune migration de schéma n'est nécessaire si les colonnes LDAP/MFA et le catalogue d'ACL requis par le formulaire existent déjà (voir migrations de l'application pour les installations anciennes).
