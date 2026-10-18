# Magixstripe - Passerelle de paiement Stripe pour Magix CMS 4

[![Release](https://img.shields.io/github/release/magix-cms/Magixstripe.svg)](https://github.com/magix-cms/Magixstripe/releases/latest)
[![License](https://img.shields.io/github/license/magix-cms/Magixstripe.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D%208.2-blue.svg)](https://php.net/)
[![Magix CMS](https://img.shields.io/badge/Magix%20CMS-4.x-success.svg)](https://www.magix-cms.com/)

Magixstripe est le module de paiement officiel par Carte Bancaire pour Magix CMS 4. Basé sur l'API moderne et ultra-sécurisée **Stripe Checkout**, il délègue la saisie des coordonnées bancaires aux serveurs de Stripe (conformité PCI/DSP2/3D-Secure).

Conçu comme un `addon`, il s'intègre automatiquement au moteur e-commerce `Magixcartpay`, mais peut également être utilisé de manière totalement autonome pour des paiements directs (achats impulsifs).

## 🚀 Fonctionnalités principales

* **Intégration Addon Native** : S'injecte automatiquement dans la page de commande de `Magixcartpay` (Hooks dynamiques) sans modifier le code de votre thème.
* **Synchronisation Webhook** : Valide automatiquement et instantanément les commandes en arrière-plan, même si le client ferme son navigateur juste après le paiement.
* **Mode "Paiement Impulsif"** : Permet d'encaisser des paiements directs depuis n'importe quelle page du site via un simple formulaire HTML (sans panier).
* **Stripe Checkout Session** : Interface de paiement optimisée par Stripe, supportant Apple Pay et Google Pay nativement.

## 📦 Installation

1. Placez le dossier `Magixstripe` dans le répertoire `plugins/` de votre CMS.
2. **Étape cruciale :** Ce plugin requiert le SDK officiel de Stripe. Ouvrez un terminal dans le dossier `plugins/Magixstripe` et exécutez la commande suivante :
   ```bash
   composer require stripe/stripe-php
   ```
3. Rendez-vous dans l'administration de Magix CMS (Menu *Extensions & Plugins*), repérez l'Addon **Magixstripe** et cliquez sur **Installer**.
4. Allez dans la configuration du plugin et renseignez vos clés API (Publique, Secrète et Webhook).

## 🎨 Utilisation et Intégration (Frontend)

### 1. Avec Magixcartpay (Automatique)

Vous n'avez **rien à coder**. Une fois le plugin installé et activé en tant qu'Addon pour Magixcartpay, l'option de paiement "Carte Bancaire (Stripe)" apparaîtra automatiquement à l'étape du choix de paiement. 
L'instruction de paiement sera également automatiquement ajoutée à l'email de confirmation du client.

### 2. Mode "Paiement Impulsif" (Autonome)

Vous souhaitez vendre un e-book, faire payer une facture ou recevoir un don direct depuis une page standard, sans forcer le client à passer par le tunnel de commande `Magixcartpay` ?

Placez simplement ce formulaire n'importe où dans vos templates Smarty (ex: `page.tpl`) :

```html
<form action="/fr/magixstripe?action=pay" method="post">
    <!-- Montant exact à encaisser (Format numérique standard avec point) -->
    <input type="hidden" name="impulsive_amount" value="15.00">
    <!-- Nom du produit qui s'affichera sur la page de paiement Stripe -->
    <input type="hidden" name="impulsive_product_name" value="Guide PDF Exclusif">
    
    <button type="submit" class="btn btn-success">Acheter ce PDF pour 15,00 €</button>
</form>
```
*Note : Assurez-vous d'adapter le `/fr/` dans l'attribut `action` si la langue courante est différente.*

## ⚙️ Configuration du Webhook (Stripe Dashboard)

Pour que les commandes soient marquées comme "Payées" automatiquement :
1. Allez sur votre tableau de bord Stripe > **Développeurs** > **Webhooks**.
2. Ajoutez un point de terminaison avec l'URL fournie dans l'administration de Magixstripe (ex: `https://votre-site.com/fr/magixstripe?action=webhook`).
3. Écoutez l'événement : `checkout.session.completed`.
4. Copiez la "Clé secrète de signature" (commençant par `whsec_`) et collez-la dans la configuration du plugin Magix CMS.

## 📄 Licence

Ce projet est sous licence **GPLv3**. Voir le fichier [LICENSE](LICENSE) pour plus de détails.
Copyright (C) 2008 - 2026 Gerits Aurelien (Magix CMS)