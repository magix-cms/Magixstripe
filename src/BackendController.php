<?php
declare(strict_types=1);

namespace Plugins\Magixstripe\src;

use App\Backend\Controller\BaseController;
use Plugins\Magixstripe\db\StripeDb;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;
use Magepattern\Component\Tool\SmartyTool;

class BackendController extends BaseController
{
    public function run(): void
    {
        SmartyTool::addTemplateDir('admin', ROOT_DIR . 'plugins' . DS . 'Magixstripe' . DS . 'views' . DS . 'admin');

        $action = $_GET['action'] ?? 'index';

        if ($action === 'saveConfig' && Request::isMethod('POST')) {
            $this->saveConfig();
        } else {
            $this->index();
        }
    }

    private function index(): void
    {
        $db = new StripeDb();
        $this->view->assign([
            'stripe_config' => $db->getConfig(),
            'hashtoken'     => $this->session->getToken(),
            'webhook_url'   => rtrim($this->view->getTemplateVars('site_url'), '/') . '/fr/Magixstripe?action=webhook'
        ]);
        $this->view->display('index.tpl');
    }

    private function saveConfig(): void
    {
        if (!$this->session->validateToken($_POST['hashtoken'] ?? '')) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $db = new StripeDb();
        $saved = $db->saveConfig([
            'public_key'     => FormTool::simpleClean($_POST['public_key'] ?? ''),
            'secret_key'     => FormTool::simpleClean($_POST['secret_key'] ?? ''),
            'webhook_secret' => FormTool::simpleClean($_POST['webhook_secret'] ?? '')
        ]);

        if ($saved) {
            $this->jsonResponse(true, 'Configuration Stripe enregistrée avec succès.');
        }
        $this->jsonResponse(false, 'Erreur lors de la sauvegarde.');
    }
}
?>