<?php
/**
 * Render backend sidebar navigation from menu definition arrays.
 */

if ( ! function_exists( 'vv_render_sidebar_nav' ) ) {
	/**
	 * @param array<int, array<string, mixed>> $menus
	 */
	function vv_render_sidebar_nav( array $menus ) {
		echo '<ul class="list-unstyled navbar__list">';
		foreach ( $menus as $item ) {
			$type = gArrayItem( $item, 'type', 'link' );

			if ( $type === 'section' ) {
				echo '<li class="vv-sidebar-section">';
				echo '<span class="vv-sidebar-section__label">' . esc_html( vv__( gArrayItem( $item, 'label' ) ) ) . '</span>';
				echo '</li>';
				continue;
			}

			if ( $type === 'group' ) {
				$icon     = esc_attr( gArrayItem( $item, 'icon', 'fas fa-folder' ) );
				$label    = esc_html( vv__( gArrayItem( $item, 'label' ) ) );
				$children = gArrayItem( $item, 'children', [] );
				if ( ! is_array( $children ) || empty( $children ) ) {
					continue;
				}
				echo '<li class="has-sub">';
				echo '<a href="#" class="js-arrow"><i class="' . $icon . '"></i>' . $label . '</a>';
				echo '<ul class="list-unstyled navbar__sub-list js-sub-list">';
				foreach ( $children as $child ) {
					vv_render_sidebar_link( $child, true );
				}
				echo '</ul></li>';
				continue;
			}

			vv_render_sidebar_link( $item, false );
		}
		echo '</ul>';
	}
}

if ( ! function_exists( 'vv_render_sidebar_link' ) ) {
	/**
	 * @param array<string, mixed> $item
	 */
	function vv_render_sidebar_link( array $item, $is_sub = false ) {
		$url   = gArrayItem( $item, 'url', '#' );
		$label = esc_html( vv__( gArrayItem( $item, 'label' ) ) );
		$icon  = gArrayItem( $item, 'icon', '' );
		$soon  = ( strpos( $url, 'coming-soon' ) !== false );

		if ( $is_sub ) {
			echo '<li><a href="' . esc_url( $url ) . '"' . ( $soon ? ' class="vv-menu-soon"' : '' ) . '>' . $label . '</a></li>';
			return;
		}

		echo '<li>';
		echo '<a href="' . esc_url( $url ) . '"' . ( $soon ? ' class="vv-menu-soon"' : '' ) . '>';
		if ( $icon !== '' ) {
			echo '<i class="' . esc_attr( $icon ) . '"></i>';
		}
		echo $label . '</a></li>';
	}
}
