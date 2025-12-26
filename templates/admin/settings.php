<div class="wrap lm-admin">
	<h1>Configuración - Listings Manager</h1>
	
	<form method="post" action="">
		<?php wp_nonce_field('lm_settings_nonce'); ?>
		
		<div class="lm-card">
			<h2>API Keys</h2>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="google_places_api_key">Google Places API Key</label></th>
					<td>
						<input name="google_places_api_key" type="password" id="google_places_api_key" value="<?php echo esc_attr(LM_Settings::get_option('google_places_api_key')); ?>" class="regular-text">
						<p class="description">Necesaria para obtener datos de negocios.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="openai_api_key">OpenAI API Key</label></th>
					<td>
						<input name="openai_api_key" type="password" id="openai_api_key" value="<?php echo esc_attr(LM_Settings::get_option('openai_api_key')); ?>" class="regular-text">
						<p class="description">Necesaria para generar descripciones con IA.</p>
					</td>
				</tr>
			</table>
		</div>

		<div class="lm-card">
			<h2>Configuración de IA</h2>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="ai_length">Longitud de descripción</label></th>
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
				<tr>
					<th scope="row"><label for="ai_language">Idioma</label></th>
					<td>
						<select name="ai_language" id="ai_language">
							<option value="es" <?php selected(LM_Settings::get_option('ai_language'), 'es'); ?>>Español</option>
							<option value="en" <?php selected(LM_Settings::get_option('ai_language'), 'en'); ?>>Inglés</option>
						</select>
					</td>
				</tr>
			</table>
		</div>

		<p class="submit">
			<input type="submit" name="lm_save_settings" id="submit" class="button button-primary" value="Guardar cambios">
		</p>
	</form>
</div>
