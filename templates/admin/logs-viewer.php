<div class="wrap lm-admin">
	<h1>Visor de Logs - Listings Manager</h1>
	
	<div class="lm-card">
		<div class="log-controls">
			<select id="log-file-select">
				<option value="processing.log">Procesamiento</option>
				<option value="api-errors.log">Errores de API</option>
			</select>
			<button class="button" id="refresh-logs">Actualizar</button>
			<button class="button" id="clear-logs">Limpiar Logs</button>
		</div>
		
		<div class="log-viewer-container" style="background: #000; color: #0f0; padding: 15px; height: 500px; overflow-y: scroll; font-family: monospace; margin-top: 15px;">
			<pre id="log-content">Cargando logs...</pre>
		</div>
	</div>
</div>
