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

	// Generación Individual
	$('#lm-btn-generate').on('click', function() {
		var placeId = $('#place_id').val();
		if (!placeId) {
			alert('Por favor, introduce un Place ID');
			return;
		}

		var $btn = $(this);
		$btn.prop('disabled', true).text('Generando...');
		$('#lm-result-single').html('<p>Procesando, por favor espera...</p>');

		$.ajax({
			url: lm_ajax.ajax_url,
			type: 'POST',
			data: {
				action: 'lm_generate_single',
				place_id: placeId,
				_ajax_nonce: lm_ajax.nonce
			},
			success: function(response) {
				if (response.success) {
					$('#lm-result-single').html('<div class="updated inline"><p>' + response.data.message + '</p></div>');
				} else {
					$('#lm-result-single').html('<div class="error inline"><p>' + response.data.message + '</p></div>');
				}
			},
			error: function() {
				$('#lm-result-single').html('<div class="error inline"><p>Error de conexión.</p></div>');
			},
			complete: function() {
				$btn.prop('disabled', false).text('Generar Ficha');
			}
		});
	});
});
