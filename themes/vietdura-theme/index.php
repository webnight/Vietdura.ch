<?php get_header(); ?>
<section class="content">
	<div class="container">
		<?php
		if (have_posts()) :
			while (have_posts()) : the_post();
				the_title('<h2>', '</h2>');
				the_content();
			endwhile;
		else :
			echo '<p>Keine Inhalte gefunden.</p>';
		endif;
		?>
	</div>
</section>
<?php get_footer(); ?>
<?php
