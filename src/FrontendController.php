<?php
declare(strict_types=1);

namespace Plugins\Magixstripe\src;

use App\Frontend\Controller\BaseController;
use Plugins\Magixstripe\db\StripeFrontDb;
use Magepattern\Component\Tool\SmartyTool;
use Magepattern\Component\Debug\Logger;

class FrontendController extends BaseController
{
    public function run(): void
    {
        // 1. On charge la librairie Stripe locale au plugin
        $composerAutoload = ROOT_DIR . 'plugins' . DS . 'Magixstripe' . DS . 'vendor' . DS . 'autoload.php';
        if (file_exists($composerAutoload)) {
            require_once $composerAutoload;
        } else {
            die("Erreur critique : La librairie Stripe n'est pas installée dans le plugin.");
        }

        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'pay':
                $this->createCheckoutSession();
                break;
            case 'webhook':
                $this->handleWebhook();
                break;
            case 'success':
                $this->handleSuccess();
                break;
            case 'cancel':
                $this->handleCancel();
                break;
            default:
                http_response_code(404);
                echo "Action Stripe non valide.";
                break;
        }
    }

    /**
     * Crée la session Stripe et redirige le client vers la page de paiement sécurisée de Stripe
     */
    /**
     * Crée la session Stripe et redirige le client vers la page de paiement sécurisée de Stripe
     */
    private function createCheckoutSession(): void
    {
        $db = new StripeFrontDb();
        $config = $db->getConfig();

        if (!$config || empty($config['secret_key'])) {
            die("Erreur : Stripe n'est pas configuré.");
        }

        \Stripe\Stripe::setApiKey($config['secret_key']);

        $view = SmartyTool::getInstance('front');
        $baseUrl = rtrim((string)($view->getTemplateVars('site_url') ?: $view->getTemplateVars('base_url')), '/');
        $lang = $view->getTemplateVars('current_lang')['iso_lang'] ?? 'fr';

        $amountTotal = 0;
        $orderRef = '';
        $itemsName = '';
        $sessionCart = $_COOKIE['mc_cart'] ?? null;
        $isMagixcartpay = '0'; // Par défaut, non

        // VÉRIFICATION DE LA SOURCE (Magixcartpay GET ou Impulsif POST)
        if (isset($_GET['order_ref']) && class_exists('\Plugins\Magixcartpay\db\MagixcartpayFrontDb')) {
            $orderRef = $_GET['order_ref'];
            $cartDb = new \Plugins\Magixcartpay\db\MagixcartpayFrontDb();

            // Cette méthode est celle que nous venons d'ajouter à l'Étape 1 !
            $orderData = $cartDb->getOrderByRef($orderRef);

            if (!$orderData) {
                die("Commande introuvable.");
            }

            $amountTotal = (float)$orderData['total_ttc'];
            $itemsName = 'Commande #' . $orderRef;
            $isMagixcartpay = '1'; // On confirme que c'est une commande Magixcartpay

        } elseif (isset($_POST['impulsive_amount'])) {
            // Mode Impulsif (Achat direct hors panier)
            $amountTotal = (float)$_POST['impulsive_amount'];
            $orderRef = 'DIR-' . time();
            $itemsName = $_POST['impulsive_product_name'] ?? 'Achat direct';
        } else {
            die("Paramètres de paiement manquants.");
        }

        // 2. CRÉATION DE LA SESSION STRIPE
        try {
            $checkout_session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $itemsName,
                        ],
                        'unit_amount' => (int)round($amountTotal * 100), // En centimes !
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $baseUrl . '/' . $lang . '/Magixstripe?action=success&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => $baseUrl . '/' . $lang . '/Magixstripe?action=cancel',
                'metadata' => [
                    'order_ref'       => $orderRef,
                    'session_cart'    => $sessionCart,
                    'is_magixcartpay' => $isMagixcartpay // Correction ici (utilise la variable)
                ]
            ]);

            // Enregistrement de l'historique "en attente" dans la table de Stripe
            $db->insertHistory([
                'session_key_cart'  => $sessionCart,
                'order_ref'         => $orderRef,
                'stripe_session_id' => $checkout_session->id,
                'amount'            => $amountTotal,
                'status'            => 'pending'
            ]);

            // Redirection vers Stripe
            header("HTTP/1.1 303 See Other");
            header("Location: " . $checkout_session->url);
            exit;

        } catch (\Exception $e) {
            Logger::getInstance()->log('Erreur Stripe Create: ' . $e->getMessage(), 'error');
            die("Erreur d'initialisation du paiement.");
        }
    }

    /**
     * Webhook appelé par les serveurs de Stripe (totalement invisible pour le client)
     */
    private function handleWebhook(): void
    {
        $db = new StripeFrontDb();
        $config = $db->getConfig();
        $logger = Logger::getInstance();

        \Stripe\Stripe::setApiKey($config['secret_key']);
        $endpoint_secret = $config['webhook_secret'];

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch(\UnexpectedValueException $e) {
            http_response_code(400); exit();
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            $logger->log('Stripe Webhook Signature Error', 'error');
            http_response_code(400); exit();
        }

        // Si le paiement est réussi
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            // 1. Mettre à jour l'historique Stripe
            $db->updateStatusBySession($session->id, 'paid');

            // 2. Valider la commande Magixcartpay si c'est lié
            if ($session->metadata->is_magixcartpay == '1' && class_exists('\Plugins\Magixcartpay\db\MagixcartpayFrontDb')) {
                $cartDb = new \Plugins\Magixcartpay\db\MagixcartpayFrontDb();
                // Cette méthode devra passer la commande de "pending" à "paid"
                $cartDb->updateOrderStatus($session->metadata->order_ref, 'paid', 'Stripe');
            }
        }

        http_response_code(200);
    }

    private function handleSuccess(): void
    {
        // Redirection vers la page de remerciement de Magixcartpay
        $view = SmartyTool::getInstance('front');
        $lang = $view->getTemplateVars('current_lang')['iso_lang'] ?? 'fr';
        header('Location: /' . $lang . '/magixcartpay/success');
        exit;
    }

    private function handleCancel(): void
    {
        // Redirection vers le panier en cas d'annulation
        $view = SmartyTool::getInstance('front');
        $lang = $view->getTemplateVars('current_lang')['iso_lang'] ?? 'fr';
        header('Location: /' . $lang . '/magixcartpay/checkout?error=cancel');
        exit;
    }
}