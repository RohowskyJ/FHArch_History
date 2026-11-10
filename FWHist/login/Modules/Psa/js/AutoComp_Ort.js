/** Autocomplete für Ort- Abkürzung mir jq-ui-ajax  */
function initOrtAutocomplete(selector) {
	const debug = window.location.search.includes('debug=1') || $(selector).data('debug') === 1 || $(selector).data('debug') === '1';
	const apiUrl = '../../../Core/Modules/Psa/API/OrtAutoComp_API.php';
	console.log('autocomp url ', apiUrl);
    $(selector).autocomplete({
        source: function(request, response) {
            $.ajax({
                url: apiUrl,
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
			console.log('uid item id', ui.item.id );
            // Optional: z.B. ID in verstecktes Feld schreiben
            // $(this).next('input[type=hidden]').val(ui.item.id);
			$('#ort_id').val(ui.item.id);
			
        }
    });
}

// Beispiel: Autocomplete auf Eingabefeld mit ID #staat initialisieren
$(function() {
    initOrtAutocomplete('#ort');
});