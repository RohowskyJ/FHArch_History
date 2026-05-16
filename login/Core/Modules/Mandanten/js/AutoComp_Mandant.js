/** Autocomplete für Mandanten- Abkürzung mir jq-ui-ajax  */
function initMandantenAutocomplete(selector) {
	const debug = window.location.search.includes('debug=1') || $(selector).data('debug') === 1 || $(selector).data('debug') === '1';
	const apiUrl = '../../../Core/Modules/Mandanten/API/MandantenAutoComp_API.php';
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
			$('#mandant_id').val(ui.item.id);
			
        }
    });
}

// Beispiel: Autocomplete auf Eingabefeld mit ID #staat initialisieren
$(function() {
    initMandantenAutocomplete('#mandant');
});