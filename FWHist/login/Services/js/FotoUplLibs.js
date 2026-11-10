var bilder = {}; // globale Variable für die Bilderliste
var fileRotations = {}; // globale Variable für Drehungen (Block-Nr -> array of rotation degrees)
// Enable debugging when URL contains ?debug=1 or ?debug=true
var DEBUG = (function() {
    try {
        var p = new URLSearchParams(window.location.search || '');
        var v = (p.get('debug') || '').toString().toLowerCase();
        return v === '1' || v === 'true';
    } catch (e) {
        return false;
    }
})();
/* 
// Funktion zum Umschalten zwischen Bibliothek und Upload --- sollte nicht mehr nätig sein
function toggleGruppen(biNr) {
    console.log('toggle Gruppen');
    const selLibsYes = $('#sel_libs_ja' + biNr);
    const selLibsNo = $('#sel_libs_nein' + biNr);
    const groupSearch = $('#sel_lib_suche' + biNr);
    const groupUpload = $('#sel_lib_upload' + biNr);
    console.log('grupl ',groupUpload);
     
    const auswahlBild = $('#auswahl-bild_' + biNr);

    if (selLibsYes.is(':checked')) {
        groupSearch.show();
        groupUpload.hide();
        auswahlBild.show();
        startAjax(biNr);
    } else if (selLibsNo.is(':checked')) {
        groupSearch.hide();
        groupUpload.show();
        auswahlBild.hide();
    }
}
*/
// Funktion, um Bilder basierend auf der Auswahl zu laden
function startAjax(biNr) {
    // Beispiel: Daten sammeln
    var sammlg = $('#sammlung').val().trim();
    // var eigner = $('#eigner').val();
	var eigner = $('#recEigner').val();
    var aufnDat =$('#aufnDat').val();
    console.log('Sammlg ',sammlg);
    console.log('Eigner ',eigner);
    console.log('AufnDatum ',aufnDat);

    // Level-Filter (hast du ggf. in deiner Seite);
    var level1 = $('#level1').val() || '';
    var level2 = $('#level2').val() || '';
    var level3 = $('#level3').val() || '';
    var level4 = $('#level4').val() || '';
    var level5 = $('#level5').val() || '';
    var level6 = $('#level6').val() || '';

    // Auswahl anhand der Level
    if (level6 && level6.toLowerCase() !== 'nix' && sammlg.length < level6.length) {
        sammlg = level6;
    } else if (level5 && level5.toLowerCase() !== 'nix' && sammlg.length < level5.length) {
        sammlg = level5;
    } else if (level4 && level4.toLowerCase() !== 'nix' && sammlg.length < level4.length) {
        sammlg = level4;
    } else if (level3 && level3.toLowerCase() !== 'nix' && sammlg.length < level3.length) {
        sammlg = level3;    
    } else if (level2 && level2.toLowerCase() !== 'nix' && sammlg.length < level2.length) {
        sammlg = level2;
    }    
console.log('abfr sammlg ',sammlg);

    // AJAX-Anfrage
    $.ajax({
        url: 'common/API/VF_SelPictLib.API.php',
        method: 'POST',
        data: {'sammlg' : sammlg,
               'eigner' : eigner,
               'aufnDat' : aufnDat
         },
        dataType: 'json', 
        success: function(response) {
            console.log('Success ',response);
            bilder[biNr] = response.files;
            // Galerie im Dialog füllen
            var dialog = $('#dialog-bilder_' + biNr);
            var galerieHtml = '<div style="display:flex; flex-wrap:wrap; gap:10px;">';
            for (let i=0; i<response.files.length; i++) {
                let b = response.files[i];
                galerieHtml += `<div class="bild-item" data-index="${i}" style="cursor:pointer; border:1px solid #ccc; padding:5px;">
                        <img src="${b.pfad}" alt="${b.dateiname}" width="200"><br>${b.dateiname}
                    </div>`;
            }
            galerieHtml += '</div>';
            dialog.find('#bild-vorschau-dialog_' + biNr).html(galerieHtml);
            dialog.find('#dateiname-dialog_' + biNr).text('');
            dialog.find('#bild-datei-dialog_' + biNr).val('');
            dialog.find('#bild-nummer-dialog_' + biNr).val(1);
            // Dialog öffnen
            dialog.dialog({ width: 800, modal: true });
        },
        error: function(xhr) {
            alert('Fehler beim Upload: ' + xhr.statusText);
        }
    });
}


// Klick auf Bild in Galerie
$(document).on('click', '[id^=bild-galerie_] .bild-item', function() {
    // This handler is now obsolete, as gallery is only in dialog. No action needed.
});

// Click handler for image selection in dialog gallery
$(document).on('click', '[id^=dialog-bilder_] .bild-item', function() {
    const biNr = $(this).closest('[id^=dialog-bilder_]').attr('id').split('_')[1];
    const index = $(this).data('index');
    if (bilder[biNr]) {
        const bild = bilder[biNr][index];
        // Vorschau im Dialog setzen (optional, or just highlight selection)
        // $('#bild-vorschau-dialog_' + biNr).html(`<img src="${bild.pfad}" width="250">`);
        $('#dateiname-dialog_' + biNr).text(bild.dateiname);
        $('#bild-datei-dialog_' + biNr).val(bild.dateiname);

        // Auch in der Auswahl-Box anzeigen
        $('#bild-vorschau-auswahl_' + biNr).html(`<img src="${bild.pfad}" width="250">`);
        $('#dateiname-auswahl_' + biNr).text(bild.dateiname);
        $('#bild-datei-auswahl_' + biNr).val(bild.dateiname);

        // Zeige den Auswahl-Bereich explizit an (falls versteckt)
        $('#auswahl-bild_' + biNr).show();
        // Optional: Scrolle zum Auswahl-Bereich
        document.getElementById('auswahl-bild_' + biNr).scrollIntoView({behavior: 'smooth', block: 'center'});

        // Dialog schließen
        $('#dialog-bilder_' + biNr).dialog('close');
    }
});	
/* 
$(document).ready(function() {
    <?php for ($j = 1; $j <= $pic_cnt; $j++): ?>
        // Initiales Umschalten
        toggleGruppen(<?php echo $j; ?>);
        // Event für Radio-Buttons
        $('#sel_libs_ja<?php echo $j; ?>, #sel_libs_nein<?php echo $j; ?>').change(function() {
            toggleGruppen(<?php echo $j; ?>);
        });
    <?php endfor; ?>
});
*/    
// Reusable upload logic for each upload block
function setupUploadBlock(j) {
    console.log('==== setupUploadBlock(' + j + ') STARTED ====');
    console.log('uploadblock ',j);
    // Make file input accept multiple files
    const fileInput = $('#upload_file_' + j);
    console.log('fileInput (#upload_file_' + j + ') found:', fileInput.length);
    fileInput.attr('multiple', 'multiple');
    // Remove any previous preview/upload UI to avoid duplicates
    $('#preview_' + j).remove();
    $('#upload_btn_' + j).remove();
    $('#filename_' + j).remove();
    $('#rotate_left_' + j).remove();
    $('#rotate_right_' + j).remove();

   // Preview and controls container
    const previewDiv = $('<div id="preview_' + j + '" style="margin:10px 0;"></div>');
    // Upload button is now hidden (will be triggered on form submit)
    const uploadBtn = $('<button type="button" id="upload_btn_' + j + '" style="color: #cc0000; display:none;">zum Hochladen der Datei nach dem Ausfüllen anklicken</button>');   
    fileInput.after(previewDiv);
    previewDiv.after(uploadBtn);
    
    // Store selected files and their rotation
    let selectedFiles = [];
    let rotations = [];
    
    // Initialize global rotation storage for this block
    fileRotations[j] = [];

    // File input change handler
    fileInput.on('change', function(e) {
        selectedFiles = Array.from(e.target.files);
        rotations = selectedFiles.map(() => 0);
        fileRotations[j] = [...rotations]; // Kopiere zu globaler Variable
        renderPreviews();
    });

    function renderPreviews() {
        previewDiv.html('');
        if (!selectedFiles.length) return;
        selectedFiles.forEach((file, idx) => {
            const fileRow = $('<div style="margin-bottom:10px;"></div>');
            const filenameInput = $('<input type="text" readonly style="width:200px; margin-right:10px;">').val(file.name);
            const rotateLeftBtn = $('<button type="button" style="margin-right:5px;">⟲</button>');
            const rotateRightBtn = $('<button type="button" style="margin-right:5px;">⟳</button>');
            const imgHolder = $('<span></span>');

            rotateLeftBtn.on('click', function() {
                rotations[idx] = (rotations[idx] - 90) % 360;
                fileRotations[j][idx] = rotations[idx]; // Speichere in globale Variable
                console.log('Rotation links Block ' + j + ' Datei ' + idx + ':', rotations[idx]);
                renderImage(file, rotations[idx], imgHolder);
            });
            rotateRightBtn.on('click', function() {
                rotations[idx] = (rotations[idx] + 90) % 360;
                fileRotations[j][idx] = rotations[idx]; // Speichere in globale Variable
                console.log('Rotation rechts Block ' + j + ' Datei ' + idx + ':', rotations[idx]);
                renderImage(file, rotations[idx], imgHolder);
            });

            fileRow.append(filenameInput, rotateLeftBtn, rotateRightBtn, imgHolder);
            previewDiv.append(fileRow);
            renderImage(file, rotations[idx], imgHolder);
        });
    }

    function renderImage(file, rotation, imgHolder) {
        if (!file) { imgHolder.html(''); return; }
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = $('<img>').attr('src', e.target.result).css({
                'max-width': '200px',
                'transform': 'rotate(' + rotation + 'deg)'
            });
            imgHolder.html(img);
        };
        reader.readAsDataURL(file);
    }

    // Upload button handler
    // Add error spans if not present
    if ($('#aufnDat_' + j).next('.error-msg').length === 0) {
        $('#aufnDat_' + j).after('<span class="error-msg" style="color:red;display:none;margin-left:8px;"></span>');
    }
    if ($('#eigentuemer_' + j).next('.error-msg').length === 0) {
        $('#eigentuemer_' + j).after('<span class="error-msg" style="color:red;display:none;margin-left:8px;"></span>');
    }
    
    uploadBtn.on('click', function() {
    console.log('uploadBtn (deprecated - use form submit instead)');
        let hasError = false;
        const formData = new FormData();
        selectedFiles.forEach((file, idx) => {
            formData.append('file[]', file);
            if(rotations[idx] != 0) {
            rotation = rotations[idx] * -1 ;
            }
            formData.append('rotation', rotation); // []
        });
        formData.append('urhNr', urhNr);
        formData.append('urhName', urhName);
        formData.append('urhEinfgJa', urhEinfg);
        formData.append('aufnDat', aufnDat);
        formData.append('reSize', reSize);
        formData.append('aOrd', aOrd); 
        formData.append('eigner', eigner); 

        console.log('formData ',formData);

        $.ajax({
            url: 'common/API/VF_C_MassUp.API.php',   //    common/API/VF_Upload.API.php
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
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
                if (response && response.files) {
                    console.log('Upload response files:', response.files);
                    response.files.forEach(function(fileObj, idx) {
                        var hiddenInput = $("#bild-datei-auswahl_" + j);
                        if (hiddenInput.length) {
                            hiddenInput.val(fileObj.filename);
                        }
                    });
                } else {
                    console.log('No files in response:', response);
                }
                alert('Upload erfolgreich!');
            },
            error: function(xhr) {
                alert('Fehler beim Upload: ' + xhr.statusText);
            }
        });
    });

    // Remove error on input change
    $('#aufnDat_' + j).on('input', function() {
        if ($(this).val() !== '') {
            $(this).removeClass('input-error');
            $(this).next('.error-msg').hide();
        }
    });
    $('#eigentuemer_' + j).on('input', function() {
        if ($(this).val() !== '') {
            $(this).removeClass('input-error');
            $(this).next('.error-msg').hide();
        }
    });

    // Add CSS for error highlight
    if ($('style#input-error-style').length === 0) {
        $('head').append('<style id="input-error-style">.input-error { border: 2px solid red !important; }</style>');
    }
}
 
// Setup all upload blocks on page load
$(document).ready(function() {        
	console.log('==== BS_Foto_Upl.js: document.ready() aufgerufen ====');
	let bildAnz = $('#bildAnz').val();
	console.log('bildAnz Element gefunden:', $('#bildAnz').length);
	console.log('bildAnz Wert:', bildAnz);
	console.log('bildAnz Typ:', typeof bildAnz);
    console.log('Bildanzahl ',bildAnz);
	for (let i = 1; i <= bildAnz; i++) {
		console.log('Rufe setupUploadBlock auf für i =', i);
		setupUploadBlock(i);
	}

	// Intercept form submit to perform uploads BEFORE saving
	let uploadInProgress = false;
    $('#myform').on('submit', function(e) {
		// Only intercept if upload button is visible or files are selected
		let hasFiles = false;
        console.log('upl started');
		for (let i = 1; i <= bildAnz; i++) {
			let fi = $('#upload_file_' + i);
            console.log('File ',fi)
			if (fi.length && fi[0].files && fi[0].files.length > 0) {
				hasFiles = true;
				break;
			}
		}
		if (!hasFiles || uploadInProgress) {
			return true; // Allow normal submit
		}

		e.preventDefault();
		uploadInProgress = true;
		console.log('Form submit intercepted: performing uploads...');

		// Collect all upload blocks and perform sequentially
		let uploadPromises = [];
		for (let i = 1; i <= bildAnz; i++) {
			uploadPromises.push(performUploadForBlock(i));
		}

		// After all uploads complete, submit form
        Promise.all(uploadPromises).then(function() {
            console.log('All uploads complete, preparing form submit');
            uploadInProgress = false;

            // Ensure the required hidden input 'phase' exists and is set to '1'
            if ($('input[name="phase"]').length === 0) {
                $('<input>').attr({ type: 'hidden', name: 'phase', value: '1' }).appendTo('#myform');
            } else {
                $('input[name="phase"]').val('1');
            }

            // Ensure each bild_datei_N hidden input is present inside the form and log its value
            try {
                for (let k = 1; k <= bildAnz; k++) {
                    let inp = $('input[name="bild_datei_' + k + '"]');
                    if (inp.length === 0) {
                        // If an element with the known id exists, fix its name and move it into the form
                        let alt = $('#bild-datei-auswahl_' + k);
                        if (alt.length) {
                            alt.attr('name', 'bild_datei_' + k);
                            $('#myform').append(alt);
                            inp = $('input[name="bild_datei_' + k + '"]');
                        } else {
                            $('<input>').attr({ type: 'hidden', name: 'bild_datei_' + k, value: '' }).appendTo('#myform');
                            inp = $('input[name="bild_datei_' + k + '"]');
                        }
                    } else {
                        // If input exists but not inside the submitting form, move it in
                        if (inp.closest('form').length === 0 || inp.closest('form').attr('id') !== 'myform') {
                            $('#myform').append(inp);
                        }
                    }
                    console.log('Before submit: bild_datei_' + k + ' =', inp.val());
                }
            } catch (e) {
                console.log('Error while preparing hidden inputs before submit:', e);
            }

            // Finally submit the form
            $('#myform')[0].submit();
        }).catch(function(err) {
			console.error('Upload error:', err);
			uploadInProgress = false;
			alert('Fehler beim Hochladen der Dateien.');
		});
	});
});

// Global function: perform upload for a specific block
// Returns a Promise that resolves when upload is complete (or immediately if no files)
window.performUploadForBlock = function(j) {
	return new Promise(function(resolve, reject) {
		let fileInput = $('#upload_file_' + j);
		if (!fileInput.length || !fileInput[0].files || fileInput[0].files.length === 0) {
			console.log('Block ' + j + ': no files to upload');
			resolve(); // No files, skip
			return;
		}

	console.log('Block ' + j + ': uploading files...');
	let selectedFiles = Array.from(fileInput[0].files);
	let rotations = fileRotations[j] || selectedFiles.map(() => 0); // Hole gespeicherte Rotationswerte
	console.log('Rotationswerte für Block ' + j + ':', rotations);
    let subMod = $('#subMod').val();
	// Collect additional data
	const aufnDat = $('#aufnDat_' + j).val() || '';
	const urhEinfg = $('#urhEinfgJa_' + j).is(':checked') ? 'J' : 'N';
	// For each block j, read eigentuemer_j from the autocomplete input; fallback to global #urhNr if empty
	let urhNr = $('#eigentuemer_' + j).val() || $('#urhNr').val() || '';
	const urhName = $('#urhName').val() || '';
	const reSize = $('#reSize').val() || '800';
	const aOrd = $('#aOrd').val() || '';		// Build FormData
		const formData = new FormData();
		selectedFiles.forEach((file, idx) => {
			formData.append('file[]', file);
			let rotation = rotations[idx] || 0;
			if (rotation !== 0) {
				rotation = rotation * -1; // Invertiere für API
			}
			console.log('Datei ' + idx + ' wird hochgeladen mit Rotation:', rotation, 'Grad');
			formData.append('rotation', rotation);
		});
		formData.append('urhNr', urhNr);
		formData.append('urhName', urhName);
		formData.append('urhEinfgJa', urhEinfg);
		formData.append('aufnDat', aufnDat);
		formData.append('reSize', reSize);
		formData.append('aOrd', aOrd);
		formData.append('subMod',subMod);

        console.log('Uploading block ' + j + ':', selectedFiles.length + ' files');
        // Debug: list FormData entries (helps inspect rotation and other fields)
        if (DEBUG) {
            try {
                for (const entry of formData.entries()) {
                    console.log('FormData entry:', entry[0], entry[1]);
                }
            } catch (err) {
                console.log('Could not iterate FormData entries:', err);
            }
        }

		// Perform AJAX upload
		$.ajax({
			url: 'common/API/VF_C_MassUp.API.php',
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function(response) {
				console.log('Block ' + j + ' upload success:', response);
				if (typeof response === 'string') {
					try {
						response = JSON.parse(response);
					} catch(e) {
						console.log('JSON parse error:', e);
					}
				}
				// Store filenames in hidden inputs for later processing
				if (response && response.files) {
                    // The API returns files for this block; map the first returned filename to the block's hidden input
                    // Use the block index `j` (not the response index) because PHP generates inputs per block: bild_datei_1..N
                    if (response.files.length > 0) {
                        var firstFile = response.files[0];
                        let hiddenInputBlock = $('input[name="bild_datei_' + j + '"]');
                        if (hiddenInputBlock.length) {
                            hiddenInputBlock.val(firstFile.filename);
                            console.log('Block ' + j + ': bild_datei_' + j + ' gesetzt auf', firstFile.filename);
                        } else {
                            console.log('Hidden input "bild_datei_' + j + '" not found — file:', firstFile.filename);
                        }
                        // If multiple files returned and you want to store them elsewhere, extend this logic accordingly.
                    }
				}
				resolve();
			},
			error: function(xhr) {
				console.error('Block ' + j + ' upload error:', xhr.statusText);
				reject(new Error('Upload failed: ' + xhr.statusText));
			}
		});
	});
};    

// DEBUG: block only `#myform` submit so developer can inspect console logs
// This blocker is active only when ?debug=1 or ?debug=true is present in the URL.
if (DEBUG) {
    console.log('DEBUG MODE ENABLED: Upload will proceed with detailed console logs. FormData entries will be logged.');
    // Instead of blocking, just log when form is submitted
    $(document).on('submit', '#myform', function(e) {
        console.log('DEBUG: #myform submit event fired');
    });
} else {
    console.log('Debug disabled (use ?debug=1 to enable upload debug).');
}

