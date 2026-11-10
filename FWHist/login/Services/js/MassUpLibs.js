// JS/jQuery-Skript für Einzel-Upload mit Preview, Auswahl, Wasserzeichen, Rotation und Fortschritt
$(function() {
	// Globale Variablen
	let selectedFiles = [];
	let meta = {
		urhNr: $('#urhNr').val() || '',
		urhName: $('#urhName').val() || '',
		aufnDat: $('#aufnDat').val() || '',
		aOrd: $('#aOrd').val() || '',
		reSize: $('#reSize').val() || '800',
		eigner: $('#eiId').val() || '' ,
		subMod: $('#subMod').val() || ''
	};
console.log('Meta ',meta);
	// Vorschau und Auswahl generieren
	$('#fileInput').on('change', function() {
		selectedFiles = Array.from(this.files);
		$('#preview').empty();
		selectedFiles.forEach((file, idx) => {
			let isImage = file.type.startsWith('image/');
			let html = `<div class='file-preview' style='border:1px solid #ccc; margin:8px; padding:8px;'>` +
				`<strong>${file.name}</strong> (${Math.round(file.size/1024)} KB)<br>` +
				`<input type='checkbox' class='uploadCheckbox' data-index='${idx}' checked> Hochladen<br>`;
			// Wasserzeichen-Checkbox nur bei Bildern anzeigen
			if(isImage) {
				html += `<input type='checkbox' class='watermarkCheckbox' data-index='${idx}' checked> Wasserzeichen<br>`;
				let reader = new FileReader();
				reader.onload = function(e) {
					let imgHtml = `<img src='${e.target.result}' style='max-width:180px; max-height:180px;'><br>` +
						`Rotation: ` +
						`<label><input type='radio' name='rotation_${idx}' value='0' checked>0°</label>` +
						`<label><input type='radio' name='rotation_${idx}' value='90'>90°</label>` +
						`<label><input type='radio' name='rotation_${idx}' value='180'>180°</label>` +
						`<label><input type='radio' name='rotation_${idx}' value='270'>270°</label><br>`;
					$('#preview').append(html + imgHtml + `</div>`);
				};
				reader.readAsDataURL(file);
			} else {
				// Kein Bild und kein Wasserzeichen für Dokumente
				$('#preview').append(html + `</div>`);
			}
		});
	});

	// Upload-Button
	$('#uploadButton').on('click', function() {
		if(selectedFiles.length === 0) { alert('Bitte Dateien auswählen!'); return; }
		$('#progressContainer').show();
		$('#uploadProgress').val(0);
		$('#progressText').text('');
		$('#message').empty();
		$('#inPutFields').empty();

		// Filter: nur markierte Dateien
		let filesToUpload = [];
		selectedFiles.forEach((file, idx) => {
			if($(`.uploadCheckbox[data-index='${idx}']`).is(':checked')) {
				let watermark = $(`.watermarkCheckbox[data-index='${idx}']`).is(':checked') ? 'J' : 'N';
				let rotation = $(`input[name='rotation_${idx}']:checked`).val() || '0';
				filesToUpload.push({file, idx, watermark, rotation});
			}
		});
		uploadNext(0, filesToUpload);
	});

	// Einzel-Upload mit Fortschritt und Response
	function uploadNext(i, files) {
		if(i >= files.length) {
			$('#message').append('Hochladen abgeschlossen.<br>');
			$('#progressContainer').hide();
			// Automatischer Submit der Form, falls gewünscht

			if($('#myform').length) {
				$('<input>').attr({
				        type: 'hidden',
				        name: 'phase',
				        value: '3'
				   }).appendTo('#myform');
 				$('#myform').submit();
			}
			return;
		}
		let item = files[i];
		let formData = new FormData();
		formData.append('file', item.file);
		formData.append('urhNr', meta.urhNr);
		formData.append('urhName', meta.urhName);
		formData.append('aufnDat', meta.aufnDat);
		formData.append('aOrd', meta.aOrd);
		formData.append('reSize', meta.reSize);
		formData.append('urhEinfgJa', item.watermark);
		formData.append('rotation', item.rotation);
		formData.append('eigner',meta.eigner);
		formData.append('subMod',meta.subMod);
console.log('formData ',formData);
		$.ajax({
			url: 'lofin/Core7services/MassUpLibAPI.php',
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			xhr: function() {
				let xhr = new window.XMLHttpRequest();
				xhr.upload.addEventListener('progress', function(e) {
					if(e.lengthComputable) {
						let perc = Math.round((e.loaded/e.total)*100);
						$('#uploadProgress').val(perc);
						$('#progressText').text(`Datei ${i+1}/${files.length}: ${perc}%`);
					}
				}, false);
				return xhr;
			},
			success: function(resp) {
				console.log('Resp ' , resp);
				let response;			
				try { response = typeof resp === 'string' ? JSON.parse(resp) : resp; } catch(e) { response = null; }
				console.log('Response ' , response);
				if(response && response.status === 'ok' && response.files && response.files.length > 0) {
					let fname = response.files[0].filename || response.files[0].name;
					$('#message').append(`Datei ${fname} erfolgreich hochgeladen.<br>`);
					// Hidden-Input für spätere Speicherung
					$('<input>', {
						type: 'text',
						name: `name_${i}`,
						value: fname
					}).appendTo( $('#inPutFields') ) ;
				} else {
					$('#message').append('Fehler oder keine Datei im Response.<br>');
				}
				uploadNext(i+1, files);
			},
			error: function() {
				$('#message').append(`Fehler beim Hochladen von ${item.file.name}<br>`);
				uploadNext(i+1, files);
			}
		});
	}
});
