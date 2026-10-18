{* Option de paiement Stripe injectée dans le formulaire Magixcartpay *}
<div class="border rounded p-3 mb-2 bg-light border-primary border-opacity-50">
    <div class="form-check mb-0">
        <input class="form-check-input mt-1 payment-method-radio" type="radio" name="payment_method" id="stripe_card" value="stripe" required style="cursor:pointer;">
        <label class="form-check-label fw-bold w-100 ps-1" for="stripe_card" style="cursor:pointer;">
            <i class="bi bi-credit-card-2-front text-primary me-2 fs-5 align-middle"></i> Carte Bancaire (Sécurisé par Stripe)
            <div class="text-muted small fw-normal mt-1 d-flex align-items-center mt-2">
                <img src="https://img.shields.io/badge/Powered_by-Stripe-blue?style=flat-square&logo=stripe" alt="Stripe" style="height: 20px;">
                <span class="ms-2">Vos données bancaires sont chiffrées.</span>
            </div>
        </label>
    </div>
</div>