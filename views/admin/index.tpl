{extends file="layout.tpl"}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-credit-card me-2 text-primary"></i> Magixstripe</h1>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-primary">Configuration de l'API</h6>
        </div>
        <div class="card-body p-4">
            <form action="index.php?controller=Magixstripe&action=saveConfig" method="post" class="validate_form">
                <input type="hidden" name="hashtoken" value="{$hashtoken}">

                <div class="mb-3">
                    <label class="form-label fw-bold">Clé Publique (Public Key)</label>
                    <input type="text" class="form-control" name="public_key" value="{$stripe_config.public_key|default:''}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Clé Secrète (Secret Key)</label>
                    <input type="password" class="form-control" name="secret_key" value="{$stripe_config.secret_key|default:''}" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Clé Secrète du Webhook</label>
                    <input type="text" class="form-control" name="webhook_secret" value="{$stripe_config.webhook_secret|default:''}" required>
                    <div class="form-text text-muted mt-2">
                        <i class="bi bi-info-circle me-1"></i> <strong>URL du Webhook à déclarer dans Stripe :</strong> <br>
                        <code class="user-select-all bg-light p-1 rounded border">{$webhook_url}</code><br>
                        Écoutez l'événement : <code>checkout.session.completed</code>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Sauvegarder</button>
            </form>
        </div>
    </div>
{/block}