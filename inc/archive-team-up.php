<?php
#error_reporting( E_ALL );
#ini_set('display_errors', 1);
	/**
	 * Team Up Team Members Archive
	 */

	// Add Body Class
	add_filter( 'body_class', function( $classes ){
		$classes[] = 'team-up-container';

		return $classes;
	});

	// Maybe Force Layout
	if( get_option( '_team_up_force_genesis_full_width' ) == true ){
		add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );
	}

	add_action( 'wp_head', function(){
		$accent_hex = ( $color = get_option( '_team_up_accent_color' ) ) ? $color : '#006191';
		$accent_rgb = Team_Up::hex_to_rgb( $accent_hex ); ?>
		<style>
			.team-up-header {
				background-color: rgba(<?php echo $accent_rgb; ?>, .45) !important;
			}
			.team-up-header img {
				border-color: <?php echo Team_Up::adjust_brightness( $accent_hex, 100 ); ?> !important;
			}
			.team-up-footer {
				background-color: rgba(<?php echo $accent_rgb; ?>, .75);
			}
			.team-up-header:after,
			.team-up-header:before {
				border-color: rgba(<?php echo Team_Up::hex_to_rgb( Team_Up::adjust_brightness( $accent_hex, 150 ) ); ?>, .17) !important;
			}
			.team-up-filter a.button {
				background-color: <?php echo $accent_hex; ?> !important;
			}
			.team-up-filter a.button:hover,
			.team-up-filter a.button.active {
				background-color: <?php echo Team_Up::adjust_brightness( $accent_hex, 20 ); ?> !important;
			}
		</style>
	<?php });

	// Add Popup
	add_action( 'wp_footer', function(){
		Team_Up::team_up_popup( 'Name', plugins_url( '/assets/img/team-up_1.jpg', dirname(__FILE__) ), 'Biography', 'Title' );
	});

	function team_up_custom_loop(){
		$counter = 0;

		if( have_posts() ) :

			do_action( 'team_up_archive_filter' );

			echo '<div class="team-up team-up-grid">';
				while( have_posts() ) : the_post(); ?>
				<?php
					// Initialize Variables
					$post_id = get_the_ID();
					$meta = array();

					$content  = get_the_content();
					$limit    = 110;

					foreach( get_post_custom( $post_id ) as $key => $value ){
						if( substr( $key, 0, 9 ) == '_team_up_' ){
							$meta[$key] = $value[0];
						}
					}

					if( ! $profile_picture = get_the_post_thumbnail_url( $post_id, 'team_up_tall_crop' ) ){
						$profile_picture = 'https://xhynk.com/placeholder/480/640/'.get_the_title();
					}

					$classes = 'team-up-member';

					if( $terms = get_the_terms( $post_id, 'department' )){
						$term_list = '';

						foreach( $terms as $term ){
							$term_list .= "{$term->name}, ";
							$classes   .= " team-up-{$term->slug}";
						}
					} else {
						$term_list = '';
						$classes = 'team-up-member';
					}

					// Allow Departments to be hidden
					$term_list = ( filter_var( get_option( '_team_up_hide_department' ), FILTER_VALIDATE_BOOLEAN ) ) ? false : $term_list;

					// Allow Square Theme Large Images
					$classes  .= ( filter_var( get_option( '_team_up_square_theme' ), FILTER_VALIDATE_BOOLEAN ) ) ? ' team-up-square' : '';
				?>
				<div class="<?php echo $classes; ?>" data-id="<?php echo ++$counter; ?>">
					<?php /* <div class="team-up-overlay" style="background: url(<?php echo $profile_picture; ?>) center no-repeat;"></div> */ ?>
					<div class="team-up-overlay unsplash" style="background: url(<?php echo plugins_url( '/assets/img/team-up_'. mt_rand( 0, 13 ) .'.jpg', dirname(__FILE__) ); ?>) center no-repeat;"></div>
						<meta name="full-bio" content="<?php echo $content; ?>" />
						<meta name="permalink" content="<?php echo get_permalink(); ?>" />
						<div class="team-up-header">
							<?php printf( '<img class="team-up-profile-picture" src="%s" alt="%s\'s Profile Photo" />', $profile_picture, get_the_title() ); ?>
							<?php printf( '<h2 class="team-up-name entry-title" itemprop="name"><span>%s</span></h2>', get_the_title() ); ?>
							<?php if( $meta['_team_up_job_title'] ) printf( '<h4 class="team-up-job-title">%s</h4>', $meta['_team_up_job_title'] ); ?>
							<?php if( $term_list ) echo '<div class="team-up-departments">'. substr( $term_list, 0, -2 ) .'</div>'; ?>
						</div>
						<div class="team-up-footer">
							<?php if( $meta['_team_up_phone'] || $meta['_team_up_email'] ){ ?>
								<div class="team-up-contact">
									<ul>
										<?php if( $meta['_team_up_phone'] ){ ?><li><?php echo Team_Up::display_svg( 'phone' ) ?> <?php echo $meta['_team_up_phone'] ?></li><?php } ?>
										<?php if( $meta['_team_up_email'] ){ ?><li><a href="mailto:<?php echo $meta['_team_up_email']; ?>"><?php echo Team_Up::display_svg( 'email' ); ?> <?php echo $email; ?></a></li><?php } ?>
									</ul>
								</div>
							<?php } else { echo '<div style="height: 6px;"></div>'; } ?>
						<div class="team-up-bio">
							<?php
								$bio_content = strip_tags( $content );

								if( strlen( $bio_content ) > $limit ){
									echo substr( $bio_content, 0, strpos( $bio_content, ' ', $limit ) ).'… '. Team_Up::display_svg( 'arrow-right' );
								} else {
									echo $bio_content;
								}
							?>
						</div>
						<div class="team-up-social">
							<?php
								$social_networks = maybe_unserialize( $meta['_team_up_social'] );

								foreach( Team_Up::$social_networks as $network ){
									$_network = strtolower($network);

									if( $account = $social_networks[$_network] ){
										echo '<a href="'. $account .'" target="_blank" title="'. $network .'">'. Team_Up::display_svg( $_network, 'team-up-social-'.$_network ) .'</a>';
									}
								}
							?>
						</div>
					</div>
				</div>
				<?php if( is_post_type_archive( 'team-up' ) && filter_var( get_post_meta( $post_id, '_team_up_break_after', true ), FILTER_VALIDATE_BOOLEAN ) ) echo '<div style="clear: both;"></div>'; ?>
				<?php endwhile;
			echo '</div>';
		endif;
	}

	if( function_exists( 'genesis' ) ){
		// Remove Default Loop
		remove_action( 'genesis_loop', 'genesis_do_loop' );

		// Hook in a new loop
		add_action( 'genesis_before_loop', function($query){
			$term = get_term_by( 'slug', get_query_var('term'), get_query_var('taxonomy') );
			
			$_term = ( $term ) ? "<span class='post-type-term'>: {$term->name}</span>" : '';

			echo '<header class="entry-header">
				<h1 class="entry-title" itemprop="headline"><span class="post-type-name">Team Members</span>'. $_term .'</h1>
			</header>';
		});

		add_action( 'genesis_loop', 'team_up_custom_loop' );

		genesis();
	} else {
		include_once( dirname(__FILE__).'/custom-archive-template.php' );
	}
?>