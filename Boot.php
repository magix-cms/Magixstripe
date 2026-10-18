<?php
declare(strict_types=1);

namespace Plugins\Magixstripe;

use App\Component\Hook\HookManager;
use Magepattern\Component\Tool\SmartyTool;
use App\Component\Db\PluginDb;

class Boot
{
    public function register(): void
    {
        // ==========================================
        // 1. COUPE-CIRCUIT POUR ADDON (mc_plugins_module)
        // ==========================================
        $pluginDb = new PluginDb();

        // On demande au CMS : "Est-ce que je (Magixstripe) suis lié et activé pour Magixcartpay ?"
        if (!$pluginDb->isAddonActiveForModule('Magixstripe', 'Magixcartpay')) {
            // Non ? Alors on s'arrête là silencieusement, aucun hook ne sera chargé !
            return;
        }

        // ==========================================
        // 2. ENREGISTREMENT DES HOOKS (Si activé)
        // ==========================================

        // A. Bouton sur la page de paiement
        HookManager::register('displayCartPaymentOptions', 'Magixstripe', function(array $params) {
            $smarty = SmartyTool::getInstance('front');
            $file = ROOT_DIR . 'plugins' . DS . 'Magixstripe' . DS . 'views' . DS . 'front' . DS . 'payment_option.tpl';
            return $smarty->templateExists($file) ? $smarty->fetch($file) : '';
        });

        // B. Bloc de texte (TPL) pour l'email de confirmation
        HookManager::register('displayCartPaymentInstructions', 'Magixstripe', function(array $params) {
            // Si la méthode de paiement n'est pas "stripe", on ne fait rien !
            if (($params['payment_method'] ?? '') !== 'stripe') {
                return '';
            }

            $smarty = SmartyTool::getInstance('front');
            $smarty->assign('stripe_reference', $params['reference'] ?? '');

            $file = ROOT_DIR . 'plugins' . DS . 'Magixstripe' . DS . 'views' . DS . 'front' . DS . 'emails' . DS . 'payment_instruction.tpl';
            return $smarty->templateExists($file) ? $smarty->fetch($file) : '';
        });
    }
}