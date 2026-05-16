/** Autocomplete für Staaten mit neuer AutocompleteAPI Klasse */
function initStaatenAutocomplete(selector) {
    const debug = window.location.search.includes('debug=1') || $(selector).data('debug') === 1 || $(selector).data('debug') === '1';
    const apiUrl = '../API/StaatenAutoComp_API.php';

    $(selector).autocomplete({
        source: function(request, response) {
            const requestData = { term: request.term };
            if (debug) {
                requestData.debug = 1;
            }

            if (debug) {
                console.debug('Autocomplete request', {url: apiUrl, data: requestData});
            }

            $.ajax({
                url: apiUrl,
                dataType: 'json',
                data: requestData,
                success: function(data, textStatus, jqXHR) {
                    if (debug) {
                        console.debug('Autocomplete success', {status: textStatus, response: data});
                    }
                    response(data.results ?? data);
                },
                error: function(xhr, status, error) {
                    console.error('Autocomplete AJAX error', {status: status, error: error, responseText: xhr.responseText});
                    response([]);
                }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            console.log('Ausgewählt:', ui.item);
            console.log('Staat ID:', ui.item.id);
            console.log('Staat Wert:', ui.item.value);
            // ID in verstecktes Feld schreiben
            $('#staat_id').val(ui.item.id);
        }
    });
}

// Beispiel: Autocomplete auf Eingabefeld mit ID #staat initialisieren
$(function() {
    initStaatenAutocomplete('#staat');
});