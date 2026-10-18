<h3 style="color: #444444; font-size: 18px;">{#stripe_email_title#|default:'Paiement en ligne'}</h3>
<div style="background: #e7f1ff; border-left: 4px solid #0d6efd; padding: 15px; font-size: 14px;">
    {#stripe_email_desc1#|default:'Votre paiement par Carte Bancaire via notre plateforme sécurisée Stripe est en cours de traitement.'}<br>
    {#stripe_email_desc2#|default:'Si vous avez déjà validé votre paiement sur la page sécurisée, il sera automatiquement associé à la commande'} <strong>{$stripe_reference|escape}</strong>.
</div>