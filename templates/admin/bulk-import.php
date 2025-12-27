<div class="wrap lm-admin">
	<h1>Generar Fichas - Listings Manager</h1>
	
	<div class="lm-tabs">
		<nav class="nav-tab-wrapper">
			<a href="#individual" class="nav-tab nav-tab-active">Individual</a>
			<a href="#multiple" class="nav-tab">Múltiple</a>
			<a href="#csv" class="nav-tab">CSV</a>
		</nav>

		<div id="individual" class="tab-content active">
			<div class="lm-card">
				<h3>Generar por Place ID</h3>
				<form id="lm-generate-single">
					<table class="form-table">
						<tr>
							<th scope="row"><label for="place_id">Google Place ID</label></th>
							<td>
								<input type="text" id="place_id" name="place_id" class="regular-text" placeholder="ChIJN1t_tDeuEmsRUsoyG83VY24">
								<select id="lm_niche" name="niche">
									<option value="generic">Genérico</option>
									<option value="restaurant">Restaurante / Comida</option>
									<option value="hotel">Hotel / Alojamiento</option>
									<option value="health">Salud / Bienestar</option>
									<option value="retail">Tienda / Retail</option>
								</select>
								<button type="button" id="lm-btn-generate" class="button button-primary">Generar Ficha Pro</button>
							</td>
						</tr>
					</table>
				</form>
				<div id="lm-result-single"></div>
			</div>
		</div>

		<div id="multiple" class="tab-content" style="display:none;">
			<div class="lm-card">
				<h3>Generar Múltiples Place IDs</h3>
				<textarea id="multiple_place_ids" rows="10" class="large-text" placeholder="Un Place ID por línea"></textarea>
				<p><button type="button" class="button button-primary">Añadir a la cola</button></p>
			</div>
		</div>

		<div id="csv" class="tab-content" style="display:none;">
			<div class="lm-card">
				<h3>Importar desde CSV</h3>
				<input type="file" id="csv_file" accept=".csv">
				<p><button type="button" class="button button-primary">Subir y Procesar</button></p>
			</div>
		</div>
	</div>
</div>
