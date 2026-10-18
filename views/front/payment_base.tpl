<form action="/{$current_lang.iso_lang|default:'fr'}/Magixstripe?action=pay" method="post">
    <!-- Montant explicite à envoyer -->
    <input type="hidden" name="impulsive_amount" value="5.00">
    <input type="hidden" name="impulsive_product_name" value="Guide PDF Exclusif">

    <button type="submit" class="btn btn-success">Acheter ce PDF pour 5 €</button>
</form>