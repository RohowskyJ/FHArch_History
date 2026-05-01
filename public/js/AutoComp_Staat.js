/** Autocomplete für Staaten- Abkürzung mir jq-ui-ajax  */
function initStaatenAutocomplete(selector) {
    $(selector).autocomplete({
        source: function(request, response) {
            $.ajax({
                url: '../common/API/FS_StaatenAutoComp_API.php',
                dataType: 'json',
                data: { term: request.term },
                success: function(data) {
                    response(data);
                },
                error: function() {
                    response([]);
                }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            console.log('Ausgewählt:', ui.item);
			console.log('uid item iabk', ui.item.abk );
            // Optional: z.B. ID in verstecktes Feld schreiben
            // $(this).next('input[type=hidden]').val(ui.item.id);
			$('#staat_id').val(ui.item.abk);
			
        }
    });
}

// Beispiel: Autocomplete auf Eingabefeld mit ID #staat initialisieren
$(function() {
    initStaatenAutocomplete('#staat');
});