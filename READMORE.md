<del>Mon lecteur RSS idéal</del> Freeder !
==========================================

* Leed + Greeder like
	* <3

* Quelles sont les fonctions *essentielles* ?

* Bô

* Hyper user friendly, drag and drop et ça marche
    * Pas d'installation compliquée
    * _checkbox_ “voir les options avancées” pour être redirigé sur la page de conf après l'installation
    * Choix du thème à l'installation, packaging facile

* Multi utilisateur
    *  pratique pour une famille pour ne pas avoir N instances différentes + partage du cache

* i18n

* Système de plugins à garder

* Les thèmes ne doivent pas être dépendants du système actuel
	* Il faudrait des guidelines sur les balises / classes de base pour que les plugins soient indépendants des thèmes (un peu comme reveal.js par exemple)
        * Impose d'avoir un bon choix sémantique dans le thème de base, pour ne pas imposer des contraintes ridicules. Après, il n'y a aucune règle à respecter, mais si les règles sont respectées, les plugins seront très facilement compatibles _out of the box_.
	* Si un fichier PHP (disons `template.inc.php`) est présent dans le dossier du thème, il est inclus. Ça permet de définir simplement quelques fonctions _customs_ pour améliorer l'affichage du thème, et d'avoir un _plugin-like_ directement dans le thème.

* Modes titres / réduits / complets

* Pas dépendant du serveur web (nginx / apache and co)

* Flux privés / publics => à résumé
	* Est-ce utile de se donner ce mal ? Réel besoin ou pas ?
	* Perso, j'aime bien cette idée :)
	* J'aime voir ça suivant l'idée de vues dont tu parles dans ton article : tu coches ce qui doit apparaître sur la vue publique et ce qui apparît sur ta page d'accueil, etc. En fait les vues devraient remplacer les dossiers.
	* Les vues, à la freshRSS ?
	* Je sais pas bien comment c'est dans freshRSS, mais en gros une page où tu dis « Afficher les articles des tags truc et muche non lus dans l'ordre anti-chronologique » par exemple

* Flux avec authentification
    * Certains flux sont accesibles uniquement avec une authentification GET, .htaccess ou POST (typiquement, certains RSS d'une instance OwnCloud)
    * Si le flux nécessite une authentification GET, elle est déjà dans l'adresse : `URL_DU_FLUX?login=…&pass=…`.
    * Si le flux nécessite une authentification par `.htaccess`, cf https://github.com/ldleman/Leed/issues/376
    * Si le flux nécessite une authentification POST, **TODO**

* Privacy
    * Bloquer les redirections / trackers
    * Feedburner est spécialiste

* Favicons des flux

* Logs clair, visible dans l'interface
    * Pour chaque utilisateur, affichage des logs qui le concerne
    * Logs de : connexions / synchronisation / flux en erreur
    * Cf message de je ne sais plus quel script qui dit “Le flux XXX n'a pas eu de nouveaux événements depuis ***, il peut y avoir un problème”.

* <del>Possibilité de rafraîchir uniquement un flux / une catégorie</del>
    * Possible avec le code actuel
    * TODO : Interface

* Bonne documentation
    * Bonne doc en ligne
    * Site bô pour présenter Freeder

* Une bonne version française, sans fautes et avec les accords !

* Gestion des doublons ?
	* On peut intégrer l'idée de shaarli-river au lecteur
		* c'est pas un peu trop spécifique ? Michu s'en bât les flancs de shaarli non ? Perso j'aimerai bien forker shaarli river pour proposer un système de post sur son shaarli directement depuis river :D et donc rejoindre le côté discution dont on parlait

* Pagination *ou* infinite scroll
	* Et si c'est que du mode infini, mais que ça part du haut de la méthode de tri ? Comme ça transparent pour l'utilisateur, on affiche petit à petit, et en descendant c'est déjà chargé et on «démasque» juste.
	* Pas mal ! faut voir les temps de chargement après.
	* je suis d'accord, mais si on fait ça en fond avec une priorité basse, ça peut peut être passer ? Et implémenter le lazy loading pour les images aussi (au fil de l'eau)

* Responsive et tactile sur mobile, en mode webapp

* Alléger au maximum l'affichage
	* Et ne pas laisser de zones vide

* Readability first, mais sans surcharger trop les thèmes des flux

* Actions claires => suppression = message "supprimer [dossier/flux/etc]"

* Navigation au clavier

* Ajax pour faire tourner en mode webapp sur desktop : https://github.com/tmos/greeder/issues/71

* Ordonner comme on veut par drag and drop and so

* De vrais thèmes et plugins supportés
    * Intégration de base des principaux thèmes et plugins, qui sont sûrs d'être compatibles.
	* Commencer par juste une liste de ce qui est officiellement dispo et fonctionnel qui fasse un minimum autorité pour les utilisateurs.

* De l'AJAX tout partout pour que les compteurs se décrémentent etc

* <del>Vue anonyme</del>
    * Une option à activer, rien d'obligatoire
    * Done

* Et le social de RSSv3 (poke Elie)

## Tags => à reprendre

* Favoris ? => vraiment utile ce machin ? non, mais des tags peut-être ? « À lire plus tard » et tout (et s/Lu/Archiver) pas mal, à voir car ça rejoint les dossiers aussi je crois mais vu que t'en veux plus… => s/dossiers/tags oui ^^
	* Le système de tag peut améliorer les favoris en effet, mais peut être pas remplacer er même temps les dossiers…
	* Les tags c'est beau ça fait tout. Faut juste voir si on a besoin de défini explicitement des tags « système » ou pas.
	* Tags dossiers ≠ des tags des flux ? Ensemble ça va pas être le bordel ? [notez que la caractère ≠ est accessible directement sur un clavier bépo, donc voilà, j'abuse : ≠≠≠≠¿×÷«»™‑–® y a pas que bépo qui roxe ! sisi. :D Fr_variante OSS powaa ! ok gg…)
	* Alors oui, des tags de tags si on veut mais je pensais pas à ça. Juste que comme des tags on en met autant qu'on veut sur le même article, on peut très bien définir un tag « Catégorie1 » et dire de l'afficher dans la liste (une sorte de vue — encore ! — pour le menu)

* Les dossiers sont pas une idée géniale
	* Pourquoi ça ? Si non, comment faire pour catégorises ses flux ?
	* En fait, je trouve les dossiers rigides et je ne les utilise pas finalement. Ma sidebar est toute belle, toute triée, mais je ne vais jamais la regarder en détails. Un système de tags seraient plus flexibles je pense. (et tags par articles par exemple, avec possibilité de tagger globalement par flux)
	* Et si tu veux retrouver les dossiers, il suffit de ne pas tager deux articles avec le même tag.
	* Pour moi les dossiers c'est juste des boites pour mettre des sites ensembles, on parle de la même chose ?
	* Ouais ouais. Bah les tags c'est juste des étiquettes à mettre sur les sites. Ou sur les articles en fait mais tu peux dire de mettre tous les articles de tel site dans tel tag.
	* Prévoir des tags affichés et des tags non affichés (genre commençant par un _ ou je-ne-sais-quoi) pour les tambouilles système

* Tri par lus / non lus et/ou chronologie
	* Encore l'idée de vues =)


## Plugins
Ce ne sont pas forcément des plugins en tant que tels, mais des composants annexes, qui seront sûrement intégrés au noyau dur. Juste qu'ils ne sont pas prioritaires et facilement intégrables par la communauté.

* Intégration de rss-bridge pour compléter les flux tronqués
    * https://github.com/sebsauvage/rss-bridge

* Possibilité d'annoter le texte pendant qu'on le lit, pour les réactions à chaud ou corrections de typo
	* joli ça comme idée ! Comment soumettre les typos à l'auteur ?
	* OStatus ! :) (sinon, un mail de contact est censé être mis dans les RSS, c'est dans la spec)
	* Faut reprendre ce qui se passe ici : https://medium.com/@mkozlows/why-atom-cant-replace-vim-433852f4b4d1 En gros dès qu'on sélectionne un morceau de texte une petite bulle apparaît pour commenter explicitement cette partie de l'article.


## À discuter
* Part de JS ? de PHP ? cf le hollandais volant and co…

* Priorité des flux https://github.com/ldleman/Leed/issues/75
	* Se gère par un système de tags/vues aussi non ? Par contre si on utilise ce concept à chaque fois va falloir bien l'implémenter et réfléchir à sa souplesse, et surtout fournir une bonne conf par défaut. Un bon logiciel devrait toujours avoir une bonne conf par défaut (genre un bon thème+1)

* Une licence décente


## Abandonné
* Compatibilité avec plugins / thèmes de Leed ? (autant que possible)
	* ça va être chaud
	* super chaud, laisse tomber je pense, par contre faut pas hésiter à intégrer en natif ce qui a besoin de l'être (cf http://hub.tomcanac.com/liens/?e6PK9w)
	* +1 on droppe

---

## Solutions actuelles à considérer
	* Leed
	* [Fresh RSS](http://demo.freshrss.org/i/)
	* Le lecteur RSS de timo, dans blogotext
	* [KrISS Feed](http://tontof.net/feed/)

---

## Du côté technique
* <del>XSLT est notre ami : http://php.net/manual/fr/function.xslt-process.php
* ; http://bob.developpez.com/phpxslt/</del>
    * Pas dispo de base dans Debian… Dommage =(
    * Idem chez les mutus
* Toutes les librairies etc doivent fonctionner de base chez la plupart des hébergeurs mutualisés
* PHP, pour que ce soit installable facilement et dans un maximum d'endroits
* feed2array https://github.com/broncowdd/feed2array
