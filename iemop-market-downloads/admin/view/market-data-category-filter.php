<select name="category" id="category-filter">
	<option value=""><?php _e('All Category'); ?></option>
	<?php
	    $current = isset($_GET['category'])? $_GET['category']:'';
	    foreach ($cats as $label => $cat) {
	        printf('<option value="%s"%s>%s</option>',
	      			$cat->term_id,
	                $cat->term_id == $current? ' selected="selected"':'',
	                $cat->name
	        	);
	    }
	?>
</select>