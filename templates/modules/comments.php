<?php
/**
 * User comments list.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="vtd-comments">
	<?php if ( empty( $comments ) ) : ?>
		<?php echo VTD_Templates::module( 'alert', array( 'message' => __( 'No comments found.', 'vetra-dashboard' ), 'type' => 'info' ) ); // phpcs:ignore ?>
	<?php else : ?>
		<div class="vtd-comment-feed">
			<?php foreach ( $comments as $comment ) : ?>
				<article class="vtd-comment">
					<header>
						<span class="vtd-pill <?php echo 1 == $comment->comment_approved ? 'vtd-status-closed' : 'vtd-status-pending'; ?>">
							<?php echo 1 == $comment->comment_approved ? esc_html__( 'Approved', 'vetra-dashboard' ) : esc_html__( 'Pending', 'vetra-dashboard' ); ?>
						</span>
						<time><?php echo esc_html( vtd_time_ago( $comment->comment_date ) ); ?></time>
					</header>
					<div class="vtd-comment-body"><?php echo wp_kses_post( $comment->comment_content ); ?></div>
					<footer>
						<?php esc_html_e( 'On:', 'vetra-dashboard' ); ?>
						<a href="<?php echo esc_url( get_comment_link( $comment ) ); ?>"><?php echo esc_html( get_the_title( $comment->comment_post_ID ) ); ?></a>
					</footer>
				</article>
			<?php endforeach; ?>
		</div>
		<?php
		$vtd_pages = (int) ceil( $total / max( 1, $per_page ) );
		if ( $vtd_pages > 1 ) :
			?>
			<div class="vtd-pagination">
				<?php
				echo paginate_links(
					array(
						'base'    => add_query_arg( 'tpage', '%#%' ),
						'format'  => '',
						'current' => max( 1, $paged ),
						'total'   => $vtd_pages,
					)
				);
				?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>
