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
    * Certains flux sont accesibles uniquement avec une authentification GET / htaccess ou POST (typiquement, certains RSS d'une instance OwnCloud)

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

---

## Tout un échange entre Phyks et Marien de FreshRSS

    Je te réponds rapidement, je suis sur le départ pour un long week-end  donc sans doute que je continuerai de répondre quand je reviendrai :)

    Du coup je trouve tes critiques totalement fondées ! Souvent il s'agit  de compromis entre plusieurs choix, ce n'est pas toujours évident à  faire de façon juste, mais je pense que tu le verras par toi-même. Je  réponds quand même en vrac :

    - Pour le thème, je préfère mettre le thème Origine par défaut car il  est à mon sens plus "sobre". Le thème Flat me fatigue sur la longueur  même s'il est plus "joyeux". Pour la démo comme les gens jouent avec les  options de configuration on ne profite pas forcément de l'expérience  "out of the box". J'essaye de réinitialiser les valeurs quand j'y pense  ;)
    - Pour ne pas avoir à cliquer sur le titre, il y a une option pour ça.  Le truc c'est qu'il faut faire des choix et je trouve celui de fermer  les articles pertinent pour savoir d'un coup d'œil la quantité  d'information qu'on a à lire
    - J'ai basé la structure de FRSS sur celle de Google Reader à la base  (colonne des catégories à gauche, header, articles, etc.) mais je  comprends la réflexion
    - Pour la base MySQL, une seule réponse (la bonne ?) : on vient  d'ajouter le support de SQLite dans la branche de développement :)  L'avantage de MySQL c'est sa capacité pour monter en charge : Alexandre  qui a beaucoup bossé sur tout ça monte à 140 000 articles et a encore  patché dernièrement pour réduire les requêtes trop lentes.
    - Pour terminer, je n'utilise jamais "madame Michu" en exemple car il  englobe tout et n'importe qui (de toute façon madame Michu ne sait pas  ce qu'est un flux RSS et s'en fout je pourrais te répondre :p). Je vise  donc les gens qui ont déjà un minimum de bagage technique en  auto-hébergement pour la phase installation / configuration tout en  essayant de leur faciliter la vie au maximum (script d'installation).  Niveau utilisation, je vise les personnes qui connaissent le principe  d'un agrégateur. Si après ça convient à des personnes que je ne vise  pas, tant mieux.
    - Pour tout le reste, c'est noté dans un coin ! Merci pour le retour  très constructif :)

    Je regarderai le pad mercredi prochain je pense. J'aurai des trucs à  ajouter je pense : si FreshRSS n'essaye pas de révolutionner le concept  d'agrégateur, j'ai quand même pas mal d'idées que je ne peux pas  intégrer, donc autant remonter à des personnes intéressées ! Je pourrais  aussi guider sur les points auxquels il faut faire très attention et  souvent délaissés (notamment les phases d'installation ET de mise à  jour... il faut y réfléchir le plus tôt possible !)

    Et j'ai encore fait une réponse super longue ! ><

    Marien

    Le 2014-07-11 13:36, Phyks a écrit :
    Salut !

    Oui, mon article est un peu long… J'aurais du en faire deux peut être…
    (ce que je reproche / ce que je veux). Du coup, j'ai un peu tronqué
    certaines parties, qui peuvent paraître un peu abrupte (le fameux «
    c'est moche » :) ou pas claires.

    Pour FreshRSS, j'ai été un peu vite, en effet. Je suis désolé, je
    corrige ça de suite. J'en avais tellement regardé que mes notes se sont
    toutes emmêlées et que je ne lui rends pas justice… =(

    Je réponds _inline_ sur les points particuliers que tu as soulevé, en
    donnant des précisions.

    --
    Phyks

    Le 11/07/2014 10:49, Marien Fressinaud a écrit :

    Salut !

    Je suis le développeur principal (ou au moins initial) de FreshRSS et  je
    viens de tomber sur ton article
    http://phyks.me/2014/07/lecteur_rss_ideal.html

    Je n'ai pas encore tout lu (un peu long, je regarderai ça ce soir)  mais
    j'ai pris le temps de jeter un coup d'œil à ce que tu dis de FreshRSS.
    Comme je n'ai pas trop de retours négatifs je profite du tien pour te
    demander ce que tu reproches à FRSS plus en détail afin de le faire
    évoluer :)

    J'en profite pour répondre aux remarques que tu écris car je ne suis  pas
    tout à fait d'accord avec toi (je conçois que je ne suis pas très
    objectif ^^) :

            [...] mais requiert des modules PHP particuliers, notamment libxml.

        Ces modules sont la plupart du temps installés par défaut avec Apache,
        on les a indiqué parce que effectivement on les utilise, mais la  plupart
        du temps une installation assez basique fait très bien l'affaire :)

    Oui, libxml est fourni sur la plupart des hébergements. Au temps pour
    moi, j'ai confondu avec XSLT et les extensions sophistiquées de PHP.

    Je m'étais renseigné sur ce que je pouvais utiliser pour un agrégateur,
    et j'étais tombé sur certains trucs qui avaient l'air très sympas,  comme
    XSLT. Seulement, elles ne sont pas inclues dans le package de base et  je
    ne pense pas qu'elles soient disponibles chez tous les hébergeurs PHP.

    Je corrige ça de suite dans mon article.


            C'est pas très beau non plus, trop compact,

        Là rien à dire, les goûts et les couleurs forcément, on ne peut pas  tous
        être d'accord ^^ on fait quand même l'effort de supporter trois thèmes
        officiels et la doc sera bientôt prête pour en écrire de nouveaux :)

    En fait, j'avais en tête le thème d'origine que je n'aime pas trop. Les
    autres sont effectivement très réussis, bien joué (et en particulier le
    flat, dont je suis fan :) !
    (d'ailleurs, vous ne voulez pas le mettre par défaut sur la démo ?  c'est
    beaucoup plus joyeux et tentant que l'interface dark :)

    Ma remarque sur la beauté est plus générale que juste l'aspect  graphique
    par contre. En fait, pour moi, un "beau" logiciel est un logiciel :
        * beau au sens usuel
        * ergonomique et efficace
        * qui marche out of the box

    Concernant l'interface de FreshRSS, je préfère personnellement une vue
    comme ce que propose Leed, avec les articles à la suite.

    Ce comportement a l'air d'être celui de la "reading view", mais il faut
    aller plonger dans les options de configuration, et ça peut être un peu
    rebutant pour Mme Michu qui débarque sur un logiciel nouveau et est
    forcément un peu désorientée.

    Dans les points précis sur l'interface et l'ergonomie :
        * Sur la page d'accueil par défaut, on doit cliquer sur chaque article
    pour le lire (ce qui n'est pas très pratique sur mobile) et la page a
    tendance à scroller un peu toute seule quand on ouvre un article.
        * Sur la vue des articles (reading view), la séparation entre les
    articles n'est pas assez marquée à mon avis, pénalisant la lecture.
        * La mise à jour pourrait afficher plus d'informations sur l'état  actuel
    (nom du flux mis à jour, nombre de nouveaux articles par exemple).
        * Sur smartphones, l'interface peut être grandement simplifiée pour
    ressembler à une app plus qu'à un site web. Par exemple, le thème
    Greeder de Leed a des actions tactiles, des gros boutons faciles à
    cliquer, etc.

    Je suis peut être passé à côté de certains points, n'étant pas un
    utilisateur régulier de FreshRSS, n'hésite pas à me le signaler.


            de la place perdue

        Si tu parles de l'espace occupé par le texte des articles, on a ajouté
        très récemment une option permettant de gérer cet espace (largeur  fine,
        moyenne, large, pas de limite)

            et les infos importantes pas mises en valeur.

        Par exemple ?

    En fait, quand je suis arrivé sur la page de démo, le premier truc que
    j'ai vu, c'est la sidebar avec les dossiers. Ensuite, les boutons en
    haut, et enfin les flux.

    Mon idée du lecteur RSS c'est que les flux sont les plus importants,
    tout doit être fait pour qu'on y aille directement. Le reste est
    accessoire autour.

    Mais je crois, après réflexion, qu'on est dans deux optiques  différentes
    : FreshRSS me fait beaucoup penser à un "Gmail pour les flux RSS",  alors
    que je cherche plus un truc comme Leed, qui présente beaucoup plus  comme
    un blog que comme des mails.


            C'est aussi compliqué à installer, et ce n'est pas Mme Michu qui
            pourra s'en charger…

        Là je ne vois pas où est la difficulté ! Il y a juste à uploader FRSS
        sur un serveur, se rendre sur l'url et suivre les instructions. Je ne
        crois pas que ça soit plus compliqué qu'une appli type Wordpress :?

    Non, ce n'est pas plus compliqué à installer que Wordpress. Mais il y a
    quand même une base MySQL derrière, même si c'est pas compliqué à  mettre
    en place.

    En fait, ce qui m'avait fait écrire ça, c'est essentiellement :
    * « (la partie à exposer au Web est le répertoire ./p/) » => je pense
    que Mme Michu n'a aucune idée de ce qu'est « la partie à exposer au Web  ».
    * Le cron, qui doit être ajouté manuellement, alors qu'il est possible
    de le faire directement depuis PHP (owncloud par exemple gère de façon
    transparente un cron / webcron / ajax)
    * Les installations d'instruction dans le README, pas évident à trouver
    pour quelqu'un qui arrive sur le site et ne va sûrement pas cliquer sur
    "read more about the source code", qui fait peur. Un récapitulatif sur
    la page d'accueil du site insisterait sur le fait que "ça s'installe
    facilement" et que "c'est pour tous".
    * Et enfin, concernant la sauvegarde des articles, elle peut se faire
    depuis PHP directement aussi, sans demander à l'utilisateur d'entrer de
    commandes qu'il ne comprend pas forcément.


---

    En réponse globale, je veux juste préciser que je mets un point
    d'honneur à répondre à toutes les demandes qui me sont faites dans la
    limite du possible (je n'ai refusé que très peu d'évolutions). En plus  à
    partir de mi-septembre je pourrai travailler à temps plein sur FRSS  donc
    ça risque d'évoluer assez vite ;)

    Dans tous les cas, félicitations pour FreshRSS, qui me paraît à l'heure
    actuelle l'alternative la plus viable à Leed. La licence est cool, le
    développeur est cool, et ça évolue, c'est parfait ! :)

    (et il y a des trucs très sympas et originaux comme l'intégration de
    Persona par exemple)


    Concernant quelques points non abordés, j'ai remarqué que la synchro  est
    assez longue. Je ne sais pas d'où ça vient exactement. Je suis loin
    d'avoir un truc parfaitement fonctionnel, mais sur mon kimsufi, en
    utilisant curl en parallèle + feed2array de Bronco + une base sqlite
    loin d'être optimisée, je suis à 3 secondes pour synchroniser 40 flux
    (et 600 articles). Leed est particulièrement lent sur ce point, et je
    pense que c'est une fonctionnalité qui peut beaucoup attirer.


    Si jamais il y a des points peu clairs, ou si j'ai raté certains trucs,
    n'hésite pas à me le dire.

    Mais sinon, je te souhaite bon courage (et t'encourage ! C'est  toujours
    bien d'avoir des alternatives) pour écrire ton agrégateur ! Je  jetterai
    un coup d'œil à l'occasion si tu as besoin de retours. Ça pourrait
    t'aider justement puisque j'ai un œil un peu différent du simple
    utilisateur ;)

    Super pour les retours ! En fait, je ne veux pas simplement recréer un
    agrégateur RSS, ce qui serait réinventer la roue. J'ai d'autres idées  en
    tête, on verra ce que ça donne. Dans tous les cas, je développe avec
    l'objectif de rapidité et d'ergonomie en tête.

    P.S. : On a un petit pad ici avec ce qui nous tient à cœur dans un
    lecteur RSS, n'hésite pas à jeter un œil :)
    http://tools.exppad.com/pad/p/RSS

    Bonne journée !
