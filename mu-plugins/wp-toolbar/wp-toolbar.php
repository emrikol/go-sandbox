<?php

class WP_Toolbar {

	function __construct() {
		add_action( 'admin_bar_init', array( $this, 'init' ) );
		add_action( 'wp_ajax_set_superadmin_toggle', array( $this, 'save_superadmin_toggle' ) );
	}

	function init() {
		global $super_admin_bar;

		/**
		 * Includes the A8C_Admin_Bar class that extends WPCOM_Admin_Bar.
		 */
		require_once( plugin_dir_path( __FILE__ ) . '/class-a8c-admin-bar.php' );
		$super_admin_bar = new A8C_Admin_Bar();

		add_action( 'admin_bar_menu', array( $this, 'shuffle_super_admin_nodes' ), 1101 );
		// If user is a super admin, let's display the super admin bar
		$user = wp_get_current_user();
		if ( is_super_admin( $user->ID ) ) {
			add_action( 'wp_after_admin_bar_render', array( $super_admin_bar, 'render' ) );
			add_action( 'wp_enqueue_scripts',        array( $this, 'add_super_admin_scripts' ) );
			add_action( 'admin_enqueue_scripts',     array( $this, 'add_super_admin_scripts' ) );
		}
	}

	/**
	 * Shuffles nodes from global $wp_admin_bar to cloned WP_Admin_Bar object.
	 *
	 * @todo: Have super admin menus added directly to $super_admin_bar global instead of shuffling.
	 * @param $wp_admin_bar
	 */
	function shuffle_super_admin_nodes( $wp_admin_bar ) {
		global $super_admin_bar;

		$filter_list     = array( 'vipgo-admin', 'superadmin', 'debug-bar', 'wp-blog-dashboard' );
		$admin_bar_nodes = $wp_admin_bar->get_nodes();

		if ( ! empty( $admin_bar_nodes ) ) {
			$color = 'FFFFFF';
			$user  = wp_get_current_user();
			$toggle_class = 'noticon-minus';
			$toggle_state = get_user_meta( $user->ID, 'superadmin_bar_closed', true );
			if ( 'closed' === $toggle_state ) {
				$toggle_class = 'noticon-wordpress';
			}

			$super_admin_bar->add_node(
				array(
					'id' => 'a11n-toggle',
					'title' => "<span title='toggle with &apos;shift + w&apos;' style='color: #{$color};' class='noticon {$toggle_class}'></span>",
					'parent' => false,
					'href' => '',
					'meta' => array(
						'class' => 'noselect leave-open',
					),
				)
			);

			// Now we can remove nodes that match $filter_list and add them to our $super_admin_bar clone
			foreach ( $admin_bar_nodes as $node ) {

				$parent_in_list = in_array( $node->parent, $filter_list, true );
				if ( $parent_in_list ) {
					$filter_list[] = $node->id;
				}

				if ( $parent_in_list || in_array( $node->id, $filter_list, true ) ) {
					$wp_admin_bar->remove_node( $node->id );

					if ( 'top-secondary' === $node->parent ) {
						$node->parent = false;
					}
					$super_admin_bar->add_node( (array) $node );
				}
			}
		}
	}

	/**
	 * Enqueues Super Admin Bar CSS.
	 */
	function add_super_admin_scripts() {
		wp_enqueue_style(
			'super-admin-bar',
			plugins_url( 'super-admin-bar.css', __FILE__ )
		);

		wp_enqueue_script(
			'wp-toolbar',
			plugins_url( 'wp-toolbar.js', __FILE__ ),
			array( 'jquery', 'utils' )
		);

		wp_localize_script(
			'wp-toolbar',
			'a8cToolbar',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'superadmin_bar_toggle' ),
			)
		);
	}

	/**
	 * Saves the toggle state of the Super Admin Bar.
	 */
	function save_superadmin_toggle() {
		check_ajax_referer( 'superadmin_bar_toggle', 'nonce' );

		$user = wp_get_current_user();
		$toggle_state = sanitize_text_field( $_POST['toggleState'] );
		update_user_meta( $user->ID, 'superadmin_bar_closed', $toggle_state );
		die();
	}
}

new WP_Toolbar();



/**
 * Use the $wp_admin_bar global to add a menu for site admins and administrator controls.
 */
function wpcom_adminbar_superadmin_menus( $wp_admin_bar ) {
	global $admin_bar_query_count, $current_blog, $wpdb;

	if ( ! is_object( $wp_admin_bar ) || ! is_super_admin() ) {
		return false;
	}

	/*
	 Add the "Super Admin" settings sub menu */
	// wpcom_adminbar_superadmin_settings_menu();
	/* Add the "Blog Dashboard" settings sub menu */
	wpcom_adminbar_superadmin_dashboard_menu();
}

add_action( 'admin_bar_menu', 'wpcom_adminbar_superadmin_menus', 210 );

function wpcom_adminbar_superadmin_dashboard_menu() {
	global $wp_admin_bar, $current_blog, $current_user;

	$domain = get_option( 'home' );

	if ( class_exists( 'Jetpack_Options' ) ) {
		$jetpack_id = Jetpack_Options::get_option( 'id' );
	}

	if ( ! is_object( $wp_admin_bar ) || ! current_user_can( 'vip_support' ) ) {
		//return false;
	}

	$wp_admin_bar->add_menu( array(
		'id' => 'wp-blog-dashboard',
		'title' => __( 'Dash' ),
		'href' => admin_url(),
		'parent' => 'top-secondary',
	) );

	$wp_admin_bar->add_menu( array(
		'parent' => 'wp-blog-dashboard',
		'id' => 'wpcom-blog-visit',
		'title' => __( 'View Site' ),
		'href' => $domain,
	) );

	$wp_admin_bar->add_menu( array(
		'parent' => 'wp-blog-dashboard',
		'id' => 'wpcom-blog-customizer',
		'title' => __( 'Customizer' ),
		'href' => $domain . '/wp-admin/customize.php',
	) );

	$wp_admin_bar->add_menu( array(
		'parent' => 'wp-blog-dashboard',
		'id' => 'wpcom-blog-menus',
		'title' => __( 'Menus' ),
		'href' => $domain . '/wp-admin/nav-menus.php',
	) );

	$wp_admin_bar->add_menu( array(
		'parent' => 'wp-blog-dashboard',
		'id' => 'wpcom-blog-widgets',
		'title' => __( 'Widgets' ),
		'href' => $domain . '/wp-admin/widgets.php',
	) );

	// ---
	$wp_admin_bar->add_menu( array(
		'parent' => 'wp-blog-dashboard',
		'id' => 'wpcom-blog-posts',
		'title' => __( 'Posts' ),
		'href' => $domain . '/wp-admin/edit.php',
	) );

	$wp_admin_bar->add_menu( array(
		'parent' => 'wp-blog-dashboard',
		'id' => 'wpcom-blog-pages',
		'title' => __( 'Pages' ),
		'href' => $domain . '/wp-admin/edit.php?post_type=page',
	) );

	$wp_admin_bar->add_menu( array(
		'parent' => 'wp-blog-dashboard',
		'id' => 'wpcom-blog-media',
		'title' => __( 'Media' ),
		'href' => $domain . '/wp-admin/upload.php',
	) );

	$wp_admin_bar->add_menu( array(
		'parent' => 'wp-blog-dashboard',
		'id' => 'wpcom-blog-comments',
		'title' => __( 'Comments' ),
		'href' => $domain . '/wp-admin/edit-comments.php',
	) );

	// ---
	$wp_admin_bar->add_menu( array(
		'parent' => 'wp-blog-dashboard',
		'id' => 'wpcom-blog-settings',
		'title' => __( 'Settings' ),
		'href' => $domain . '/wp-admin/options-general.php',
	) );

	if ( is_single() ) {
		$wp_admin_bar->add_menu( array(
			'parent' => 'wp-blog-dashboard',
			'id' => 'wpcom-blog-edit-post',
			'title' => __( 'Edit Post' ),
			'href' => $domain . '/wp-admin/post.php?post=' . get_the_ID() . '&action=edit',
		) );
	} elseif ( is_page() ) {
		$wp_admin_bar->add_menu( array(
			'parent' => 'wp-blog-dashboard',
			'id' => 'wpcom-blog-edit-page',
			'title' => __( 'Edit Page' ),
			'href' => $domain . '/wp-admin/post.php?post=' . get_the_ID() . '&action=edit',
		) );
	}

	/* Add the main superadmin menu item */
	$wp_admin_bar->add_menu( array(
		'id'     => 'superadmin',
		'title'  => '&mu;',
		'href'   => '',
		'parent' => 'top-secondary',
	) );

	if ( is_multisite() ) {
		$wp_admin_bar->add_menu(
			array(
				'parent' => 'superadmin',
				'id'     => 'blog-id',
				'title'  => __( 'MS Blog ID: ' ) . '<code>' . $current_blog->blog_id . '</code>',
			)
		);

		/* Add the submenu items to the Super Admin menu */
		$wp_admin_bar->add_menu( array( 'parent' => 'superadmin', 'id' => 'wpcom-blog-network-admin', 'title' => __( "Blog's Network Admin" ), 'href' => network_admin_url( "sites.php?s={$current_blog->blog_id}" ), 'position' => 30 ) );
		$wp_admin_bar->add_menu( array( 'parent' => 'wpcom-blog-network-admin', 'id' => 'wpcom-blog-network-admin-info', 'title' => __( 'Blog Info' ), 'href' => network_admin_url( "site-info.php?id={$current_blog->blog_id}" ) ) );
		$wp_admin_bar->add_menu( array( 'parent' => 'wpcom-blog-network-admin', 'id' => 'wpcom-blog-network-admin-users', 'title' => __( 'Blog Users' ), 'href' => network_admin_url( "site-users.php?id={$current_blog->blog_id}" ) ) );
		$wp_admin_bar->add_menu( array( 'parent' => 'wpcom-blog-network-admin', 'id' => 'wpcom-blog-network-admin-themes', 'title' => __( 'Blog Themes' ), 'href' => network_admin_url( "site-themes.php?id={$current_blog->blog_id}" ) ) );
	}

	if ( $jetpack_id ) {
		// Stats page
		$wp_admin_bar->add_menu( array( 'parent' => 'superadmin', 'id' => 'wpcom-blog-stats', 'title' => __( 'Blog Stats' ), 'href' => 'https://wordpress.com/my-stats/?blog_id=' . $jetpack_id ) );
	}


	// VIP
	//$wp_admin_bar->add_menu( array( 'parent' => 'superadmin', 'id' => 'vipgo-dashboard', 'title' => __( 'VIP' ), 'href' => admin_url("admin.php?page=vip-dashboard") ) );

	if ( defined( 'VIP_GO_APP_ID' ) ) {
		$wp_admin_bar->add_menu( array( 'parent' => 'top-secondary', 'id' => 'vipgo-admin', 'title' => ( defined( 'VIP_GO_APP_ENVIRONMENT' ) ? '(Env: <span style="font-style: italic; color: ' . ( 'production' === VIP_GO_APP_ENVIRONMENT ? '#81e481' : 'orange' ) . '">' . VIP_GO_APP_ENVIRONMENT : 'Local' ) . '</span>)', 'href' => "https://mc.a8c.com/vip/admin/#/sites/" . VIP_GO_APP_ID . "/" ) );

		$wp_admin_bar->add_menu( array(
			'id' => 'vipgo-admin-php-logs',
			'parent' => 'vipgo-admin',
			'title' => 'PHP Logs',
			'href' => "https://logstash.a8c.com/kibana6/app/kibana#/dashboard/%5Bvipv2-php-errors%5D-VIP-GO-php-errors-dashboard?_a=(query:(query_string:(analyze_wildcard:!t,query:'client_site_id:" . VIP_GO_APP_ID . "')))",
		) );

		$wp_admin_bar->add_menu( array(
			'id' => 'vipgo-admin-fatal-errors',
			'parent' => 'vipgo-admin',
			'title' => 'Fatal Errors',
			'href' => "https://logstash.a8c.com/kibana6/app/kibana#/dashboard/%5Bvipv2-php-errors%5D-VIP-GO-php-errors-dashboard?_a=(query:(query_string:(analyze_wildcard:!t,query:'client_site_id:" . VIP_GO_APP_ID . "%20AND%20(severity:%20%22Fatal%20error%22%20OR%20severity:%20%22Parse%20error%22)')))",
		) );

		$wp_admin_bar->add_menu( array(
			'id' => 'vipgo-admin-origin-requests',
			'parent' => 'vipgo-admin',
			'title' => 'Origin Requests',
			'href' => "https://logstash.a8c.com/kibana6/app/kibana#/dashboard/%5Bvipv2-nginx%5D-WEB-nginx-logs-dashboard?_a=(filters:!(('$state':(store:appState),meta:(alias:!n,disabled:!f,index:'vipv2-nginx-*',key:client_site_id,negate:!f,params:(query:'" . VIP_GO_APP_ID . "'),type:phrase,value:'Site+ID+" . VIP_GO_APP_ID . "'),query:(match:(client_site_id:(query:'" . VIP_GO_APP_ID . "',type:phrase))))),query:(language:lucene,query:'*'),timeRestore:!f,title:'%5Bvipv2-nginx%5D%20Origin%20logs%20dashboard',viewMode:view)",
		) );

		$wp_admin_bar->add_menu( array(
			'id' => 'vipgo-admin-lb-requests',
			'parent' => 'vipgo-admin',
			'title' => 'LB Requests',
			'href' => "https://logstash.a8c.com/kibana6/app/kibana#/dashboard/%5Bvipv2-nginx%5D-LB-logs-dashboard?_a=(filters:!(('$state':(store:appState),meta:(alias:!n,disabled:!f,index:'vipv2-nginx-*',key:client_site_id,negate:!f,params:(query:'" . VIP_GO_APP_ID . "'),type:phrase,value:'Site+ID+" . VIP_GO_APP_ID . "'),query:(match:(client_site_id:(query:'" . VIP_GO_APP_ID . "',type:phrase))))),query:(language:lucene,query:'*'),timeRestore:!f,title:'%5Bvipv2-nginx%5D+LB+logs+dashboard',viewMode:view)",
		) );

		$wp_admin_bar->add_menu( array(
			'id' => 'vipgo-admin-mail-logs',
			'parent' => 'vipgo-admin',
			'title' => 'Mail Logs',
			'href' => "https://logstash.a8c.com/kibana6/app/kibana#/dashboard/%5Bwpmail%5D-WordPress.com-mail-dashboard?_a=(query:(query_string:(analyze_wildcard:!t,query:'(project_id:1%20AND%20client_site_id:" . VIP_GO_APP_ID . ")')))",
		) );
	}

	$wp_admin_bar->add_menu( array(
		'id' => 'vipgo-admin-jetpack-debug',
		'parent' => 'vipgo-admin',
		'title' => 'Jetpack Debug',
		'href' => "https://jetpack.com/support/debug/?url=" . parse_url( $domain, PHP_URL_HOST ),
	) );

	$wp_admin_bar->add_menu( array(
		'id' => 'vipgo-admin-jetpack-site-profiles',
		'parent' => 'vipgo-admin',
		'title' => 'Jetpack Site Profiles',
		'href' => "https://mc.a8c.com/site-profiles/?q=" . parse_url( $domain, PHP_URL_HOST ),
	) );

}
