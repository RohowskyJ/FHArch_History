 /**
 * Fotos aus den Foto- Verzeichnissen zur Auswahl herunterladen
 * Auswahl entweder nach Aufnahmedatum für Berichte
 * oder nach Sammlung und Eigentümer (des Objektes) für Objektbeschreibungen
 * 
 * Neu 2025, Josef Rohowsky
 * 
 * wird von VF_FO_Ber_Edit_ph0.inc.php eingebunden  
 */

const bildArr = {};
const pictList = []; // Liste der Bildauwahl nach Eingabe Page und Pos

const pictSelected = []; // Liste der ausgewählten Bilder => Page und Pos sind eingetragen

// Funktion, um Bilder basierend auf der Auswahl zu laden
function startAjax(biNr) {
    // Beispiel: Daten sammeln
 
    const sammlg = $('#sammlung').val().trim();
    const eigner = $('#eigner').val();
    const vbDat =$('#vb_datum').val();
	const date = new Date(vbDat);
	//const year = date.getFullYear();
	//const month = ("0" + (date.getMonth() + 1)).slice(-2); // Monat mit führender Null
	//const day = ("0" + date.getDate()).slice(-2); // Tag mit führender Null
	const aufnDat = date.getFullYear() + ("0" + (date.getMonth() + 1)).slice(-2) + ("0" + date.getDate()).slice(-2);
	const berID   = $('#vb_flnr').val();

    console.log('Eigner ',eigner);
    console.log('aufnDat ',aufnDat);
	console.log('berID ',berID);

    const unterSeiten = $('#vb_unterseiten').val();
    const fzgBeschr  = $('#vb_fzg_beschr').val(); 
 
    // console.log('unterseiten ',unterSeiten);
    const bilderAuswahl = $('#bilderAuswahl');
    // const analyse = $('#analyse');

    // AJAX-Anfrage
    $.ajax({
        url: 'login/Core/Services/API/SelPictLibAPI.php',
        method: 'POST',
        data: {'aufnDat': aufnDat,
			   'berID' : berID
		},
        // dataType: 'json',
        success: function(response) {
            console.log('Upload response typeof:', typeof response, response);
            // If response is a string, try to parse as JSON
            if (typeof response === 'string') {
                try {
                    response = JSON.parse(response);
                    console.log('Parsed JSON response:', response);
                } catch(e) {
                    console.log('JSON parse error:', e, response);
                }
            }
            // Galerie im Dialog füllen
            console.log(response);
            if (response && response.files) {
                pictCountList = response.files.length;
                for (let i=0; i<response.files.length; i++) {
                    let b = response.files[i]; 
                    // console.log('b ',b);
					// console.log('vaiable b.pos typeof ',typeof b.pos, b.pos );
		            let bild =  `<div class="bild-item w3-half" data-index="${i}" style="cursor:pointer; border:1px solid #ccc; padding:5px;">	
						  <img src="${b.pfad}" alt="${b.dateiname}" height="300"><br>${b.dateiname}<br>
						  <div id='inField'></div>
					      <label>Seite: <input type="text" id="bPage_${i}" value="${b.page}" size="3" ></label>
					      <label>Position: <input type="text" id="bPos_${i}" value="${b.pos}" size="3" ></label><br>
					      <label>Sub-Seiten-Titel: <input type="text" id="bTitel_${i}" value="${b.titel}" size="50" ></label><br>
						  <label>Text: <textarea id="bText_${i}" rows="3" cols="50" placeholder="Beschreibung">${b.beschreibung || ''}</textarea></label>
						  <label>Fortl. Nr:<input type="text" id="bflNr_${i}" value="${b.flNr}" readonly size="10"></label><br>
					   </div>`;
					pictList[i] = { bPage: b.page, bPos: b.pos ,  dsn: b.dateiname, path: b.pfad , bText: b.beschreibung, btitel: b.titel, bflNr: b.flNr };
					$('#bilderAuswahl').append(bild);
                }
            }
        },
        error: function(xhr) {
            alert('Fehler beim Upload: ' + xhr.statusText);
        }
    });
}



function startAnal() {
    let cntPict = pictList.length;
    console.log('pict List ',pictList); 
    console.log('pict List length ', cntPict);
   
    for (let i=0; i<pictList.length; i++) { 
        const bPageEl = $('#bPage_' + i); 
        const bPosEl = $('#bPos_' + i); 
        const bTextEl = $('#bText_' + i);
		const bTitelEl  = $('#bTitel_' + i)
		//const bflNrEl  = $('#b.flNr_' + i);
        const bPageVal = bPageEl.val(); 
        const bPosVal = bPosEl.val();
        const bTextVal = bTextEl.val().trim();
		const bTitelVal = bTitelEl.val().trim();
	
		
        console.log('bPageVal ',bPageVal);
        console.log('bPosVal ' , bPosVal);
        
        pictList[i].bPos = bPosVal; 
        pictList[i].bPage = bPageVal;
        pictList[i].bText = bTextVal;
		pictList[i].bTitel = bTitelVal;
		//pictList[i].bflNr = bflNrVal;
     
        console.log('Auswertete Liste:', pictList);
   }
    let j = 0;
    for (let i=0;i<pictList.length;i++) {
        if (pictList[i].bPos > 0 || pictList[i].bPage > 0 || pictList[i].bflNr > 0  ) { // Eintragung vorhanden/gewünscht 
            if (pictList[i].bPage > 0 ){
				pictSelected[j] =   pictList[i]; 
			    j++;
            }
             
        }
        console.log('pict Selected ',pictSelected);
    }

    selCnt = pictSelected.length;
    console.log('Sel Cnt ',selCnt);

    $('.inputFields').empty(); // Vorher leeren!

    for (let i=0;i<pictSelected.length;i++) {
        const bildData = pictSelected[i].bPage +'|'+ pictSelected[i].bPos +'|'+pictSelected[i].dsn  +'|'+pictSelected[i].bText +'|'+pictList[i].bTitel  +'|'+pictSelected[i].bflNr ;
        console.log('bildData ',bildData);
        const inputHtml = `<input type="text" name="bild_${i}" id="bild_${i}" value='${bildData}' size="3" />`;
        $('.inputFields').append(inputHtml);
    }
    var fields = $(':input'); // umfasst input, textarea, select, button (wir filtern unten ggf. aus)
    console.log('Fields ',fields); 
	
	// Automatisches Submit auslösen
	$('<input>').attr({
	     type: 'hidden',
	     name: 'phase',
	     value: '1'
	}).appendTo('#myform');
	$('#myform').submit();

}


// A $( document ).ready() block.
$( document ).ready(function() {
	var berID   = $('#vb_flnr').val();
    console.log( "ready!, berID", berID );
	
	if (berID >= 0) {
		startAjax(berID);
	}
});
