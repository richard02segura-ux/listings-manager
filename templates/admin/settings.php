<div class="wrap lm-admin">
	<h1>Configuración Pro - Listings Manager</h1>
	
	<form method="post" action="">
		<?php wp_nonce_field('lm_settings_nonce'); ?>
		
		<div class="lm-card">
			<h2>Configuración de APIs</h2>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="ai_provider">Proveedor de IA</label></th>
					<td>
						<select name="ai_provider" id="ai_provider">
							<option value="openai" <?php selected(LM_Settings::get_option('ai_provider'), 'openai'); ?>>OpenAI (GPT-3.5/4)</option>
							<option value="gemini" <?php selected(LM_Settings::get_option('ai_provider'), 'gemini'); ?>>Google Gemini Pro</option>
						</select>
					</td>
				</tr>
				<tr class="provider-openai">
					<th scope="row"><label for="openai_api_key">OpenAI API Key</label></th>
					<td>
						<input name="openai_api_key" type="password" id="openai_api_key" value="<?php echo esc_attr(LM_Settings::get_option('openai_api_key')); ?>" class="regular-text">
					</td>
				</tr>
				<tr class="provider-gemini">
					<th scope="row"><label for="gemini_api_key">Gemini API Key</label></th>
					<td>
						<input name="gemini_api_key" type="password" id="gemini_api_key" value="<?php echo esc_attr(LM_Settings::get_option('gemini_api_key')); ?>" class="regular-text">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="google_places_api_key">Google Places API Key</label></th>
					<td>
						<input name="google_places_api_key" type="password" id="google_places_api_key" value="<?php echo esc_attr(LM_Settings::get_option('google_places_api_key')); ?>" class="regular-text">
					</td>
				</tr>
			</table>
		</div>

		<div class="lm-card">
			<h2>SEO Local y Automatización</h2>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="base_location">Ubicación Base (SEO)</label></th>
					<td>
						<input name="base_location" type="text" id="base_location" value="<?php echo esc_attr(LM_Settings::get_option('base_location')); ?>" class="regular-text" placeholder="Ej: Santo Domingo, RD">
						<p class="description">La IA optimizará el contenido para esta ubicación específica.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="enable_auto_sync">Sincronización Automática</label></th>
					<td>
						<input name="enable_auto_sync" type="checkbox" id="enable_auto_sync" value="yes" <?php checked(LM_Settings::get_option('enable_auto_sync'), 'yes'); ?>>
						<label for="enable_auto_sync">Activar actualización diaria de horarios y teléfonos.</label>
					</td>
				</tr>
			</table>
		</div>

		<div class="lm-card">
			<h2>Preferencias de IA</h2>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="ai_length">Longitud</label></th>
					<td>
						<select name="ai_length" id="ai_length">
							<option value="300" <?php selected(LM_Settings::get_option('ai_length'), '300'); ?>>300 palabras</option>
							<option value="500" <?php selected(LM_Settings::get_option('ai_length'), '500'); ?>>500 palabras</option>
							<option value="800" <?php selected(LM_Settings::get_option('ai_length'), '800'); ?>>800 palabras</option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="ai_tone">Tono</label></th>
					<td>
						<select name="ai_tone" id="ai_tone">
							<option value="profesional" <?php selected(LM_Settings::get_option('ai_tone'), 'profesional'); ?>>Profesional</option>
							<option value="amigable" <?php selected(LM_Settings::get_option('ai_tone'), 'amigable'); ?>>Amigable</option>
							<option value="persuasivo" <?php selected(LM_Settings::get_option('ai_tone'), 'persuasivo'); ?>>Persuasivo</option>
						</select>
					</td>
				</tr>
			</table>
		</div>

		<p class="submit">
			<input type="submit" name="lm_save_settings" id="submit" class="button button-primary" value="Guardar Configuración Pro">
		</p>
	</form>
</div>

<script>
jQuery(document).ready(function($) {
	function toggleProviders() {
		var provider = $('#ai_provider').val();
		$('.provider-openai, .provider-gemini').hide();
		$('.provider-' + provider).show();
	}
	$('#ai_provider').on('change', toggleProviders);
	toggleProviders();
});
</script>
