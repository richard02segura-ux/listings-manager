<div class="wrap lm-admin">
	<h1>Dashboard - Listings Manager</h1>
	
	<div class="lm-dashboard-grid">
		<div class="lm-card">
			<h3>Resumen de Listings</h3>
			<p>Total de fichas generadas: <strong><?php echo wp_count_posts('listing')->publish; ?></strong></p>
		</div>
		
		<div class="lm-card">
			<h3>Estado de APIs</h3>
			<ul>
				<li>Google Places: <?php echo LM_Settings::get_option('google_places_api_key') ? '<span class="status-ok">Conectado</span>' : '<span class="status-error">Falta API Key</span>'; ?></li>
				<li>OpenAI: <?php echo LM_Settings::get_option('openai_api_key') ? '<span class="status-ok">Conectado</span>' : '<span class="status-error">Falta API Key</span>'; ?></li>
			</ul>
		</div>
	</div>

	<div class="lm-card">
		<h3>Últimas Fichas Creadas</h3>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th>Título</th>
					<th>Place ID</th>
					<th>Fecha</th>
					<th>Estado</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$recent_posts = get_posts(array('post_type' => 'listing', 'posts_per_page' => 5));
				if ($recent_posts) :
					foreach ($recent_posts as $post) :
						$place_id = get_post_meta($post->ID, '_place_id', true);
						?>
						<tr>
							<td><strong><a href="<?php echo get_edit_post_link($post->ID); ?>"><?php echo $post->post_title; ?></a></strong></td>
							<td><code><?php echo $place_id; ?></code></td>
							<td><?php echo get_the_date('', $post->ID); ?></td>
							<td><?php echo $post->post_status; ?></td>
						</tr>
						<?php
					endforeach;
				else :
					echo '<tr><td colspan="4">No hay fichas creadas aún.</td></tr>';
				endif;
				?>
			</tbody>
		</table>
	</div>
</div>
