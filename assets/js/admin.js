jQuery(document).ready(function($) {
	// Manejo de Tabs
	$('.lm-admin .nav-tab').on('click', function(e) {
		e.preventDefault();
		$('.lm-admin .nav-tab').removeClass('nav-tab-active');
		$(this).addClass('nav-tab-active');
		
		var target = $(this).attr('href');
		$('.lm-admin .tab-content').hide();
		$(target).show();
	});

	// Generación Individual con Pre-visualización
	$('#lm-btn-generate').on('click', function() {
		var placeId = $('#place_id').val();
		var niche = $('#lm_niche').val();
		if (!placeId) {
			alert('Por favor, introduce un Place ID');
			return;
		}

		var $btn = $(this);
		$btn.prop('disabled', true).text('Buscando en Google...');
		$('#lm-result-single').html('<div class="lm-loading">Obteniendo datos y generando contenido con IA...</div>');

		$.ajax({
			url: lm_ajax.ajax_url,
			type: 'POST',
			data: {
				action: 'lm_preview_listing',
				place_id: placeId,
				niche: niche,
				_ajax_nonce: lm_ajax.nonce
			},
			success: function(response) {
				if (response.success) {
					var data = response.data;
					var html = '<div class="lm-preview-card">';
					html += '<h3>Pre-visualización del Listing</h3>';
					html += '<p><strong>Nombre:</strong> ' + data.name + '</p>';
					html += '<p><strong>Dirección:</strong> ' + data.address + '</p>';
					html += '<p><strong>Teléfono:</strong> ' + data.phone + '</p>';
					html += '<div class="lm-ai-content-preview">';
					html += '<h4>Descripción Generada (IA):</h4>';
					html += '<textarea id="lm-edit-desc" style="width:100%; height:200px;">' + data.description + '</textarea>';
					html += '</div>';
					html += '<div class="lm-preview-actions" style="margin-top:15px;">';
					html += '<button type="button" id="lm-confirm-import" class="button button-primary" data-id="' + data.place_id + '">Confirmar e Importar a la Web</button>';
					html += ' <button type="button" class="button lm-cancel-preview">Cancelar</button>';
					html += '</div>';
					html += '</div>';
					$('#lm-result-single').html(html);
				} else {
					$('#lm-result-single').html('<div class="error inline"><p>' + response.data.message + '</p></div>');
				}
			},
			error: function() {
				$('#lm-result-single').html('<div class="error inline"><p>Error de conexión.</p></div>');
			},
			complete: function() {
				$btn.prop('disabled', false).text('Generar Ficha Pro');
			}
		});
	});

	// Confirmar Importación
	$(document).on('click', '#lm-confirm-import', function() {
		var $btn = $(this);
		var placeId = $btn.data('id');
		var description = $('#lm-edit-desc').val();

		$btn.prop('disabled', true).text('Importando...');

		$.ajax({
			url: lm_ajax.ajax_url,
			type: 'POST',
			data: {
				action: 'lm_confirm_import',
				place_id: placeId,
				description: description,
				_ajax_nonce: lm_ajax.nonce
			},
			success: function(response) {
				if (response.success) {
					$('#lm-result-single').html('<div class="updated inline"><p>' + response.data.message + ' <a href="' + response.data.url + '" target="_blank">Ver en WordPress</a></p></div>');
				} else {
					alert('Error: ' + response.data.message);
					$btn.prop('disabled', false).text('Confirmar e Importar');
				}
			}
		});
	});

	$(document).on('click', '.lm-cancel-preview', function() {
		$('#lm-result-single').empty();
	});
});
