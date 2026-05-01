/** Autocomplete für Staaten- Abkürzung mir jq-ui-ajax  */
function initBenutzerAutocomplete(selector) {
    $(selector).autocomplete({
        source: function(request, response) {
            $.ajax({
                url: '../common/API/FS_BenutzerAutoComp_API.php',
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
			console.log('ID :', ui.item.id );
            // Optional: z.B. ID in verstecktes Feld schreiben
            // $(this).next('input[type=hidden]').val(ui.item.id);
			$('#ben_id').val(ui.item.id);
        }
    });
}

// Beispiel: Autocomplete auf Eingabefeld mit ID #staat initialisieren
$(function() {
    initBenutzerAutocomplete('#benutzer');
});