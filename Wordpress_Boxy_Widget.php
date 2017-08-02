<?php

// Próbka 2: Wordpressowy widget wyświetlający pełnoekranowe boxy na ekranie
// Boxy pobiera dane z custom post type "frontbox", które z kolei mają różne dodatkowe
// pola wyboru wyglądu / uploadu plików

class pimu_boxy_widget extends WP_Widget {
    
    public $boxes;
    public $selected_box;    

    function __construct() {
        
        $this->get_boxes();        
        add_action( 'wp_head', array( $this, 'output_styles' ), 999 );
        parent::__construct(false, $name = '[Pimu] Boxy Widget');
        
    }

    function output_styles() {
        
        echo '<style>';
        $boxes = $this->boxes;
        
        foreach ($boxes as $box):

            $box_id = $box['id'];

            if (function_exists('pll_get_post') && function_exists('pll_current_language') && function_exists('pll_default_language')) {
                if (pll_current_language() != pll_default_language()) {
                    $box_id = pll_get_post($box_id);
                }
            }                  

            $post = get_post( $box_id );
            $post_thumb_id = get_post_thumbnail_id( $post );
            $thumb_url_array = wp_get_attachment_image_src( (int)$post_thumb_id, 'full', true );
            $background = "";
            $background_w = 0;
            $background_h = 0;
            if ($thumb_url_array) {
                $background = $thumb_url_array[0];
                $background_w = (int)$thumb_url_array[1];
                $background_h = (int)$thumb_url_array[2];
            }
            $background_ratio = round( ($background_w / $background_h), 4 );
            $heightString = 'calc(100vw / '.  $background_ratio .')';                    

        ?>        

            .widget_pimu_boxy .box-inner.style-box-inner-<?php echo $box_id; ?> {
                background-image: url(<?php echo $background; ?>);
                background-position: center center;
                background-size: cover;
            }
                
            @media (min-width: 1250px) {
                .widget_pimu_boxy .box-inner.style-box-inner-<?php echo $box_id; ?> {
                    height: <?php echo $heightString; ?>;
                }
            }
        
        <?php
        
        endforeach;
        echo '</style>';
    }
     
    function widget($args, $instance) {
        
        extract( $args );
        $box = $instance['selected_box'];
        
        echo $before_widget;
        if (function_exists('pll_get_post') && function_exists('pll_current_language') && function_exists('pll_default_language')) {
            if (pll_current_language() != pll_default_language()) {
                $box = pll_get_post($box);            
            }
        }        

        $post = get_post($box);
        $post_content = $post->post_content;

        $meta_link_img_id = get_post_meta( $post->ID, '_frontbox_link_id', true );
        $meta_type = get_post_meta( $post->ID, '_frontbox_type', true );
        $meta_horizontal_center = get_post_meta( $post->ID, '_frontbox_centering', true );
        $meta_copyright = get_post_meta( $post->ID, '_frontbox_copyright', true );
        $img = site_get_thumbnail_src_from_id( $meta_link_img_id, 'full' );
        
        ?>
        
        <div class="box-inner style-box-inner-<?php echo $instance['selected_box']; ?>">
            <div class="box-content container restricted">
            <?php if ($meta_type == 'nopicture'): ?>
                <div class="box-content-full">
                    <div class="make-table">
                        <div class="make-table-cell <?php echo $meta_horizontal_center == 'nocenter' ? 'no-center':''; ?>">
                            <div class="wysiwyg wow slideInUp"><?php echo do_shortcode(apply_filters('the_content', $post_content)); ?></div>
                            <?php if (!empty($meta_copyright)): ?>
                            <div class="copyright"><?php echo $meta_copyright; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="box-content-left">
                    <div class="make-table">
                        <div class="make-table-cell">                            
                            <figure class="box-image">
                                <img alt="<?php echo esc_attr($post->post_title); ?>" src="<?php echo $img; ?>" class="wow bounceInUp" data-wow-offset="150">
                            </figure>
                        </div>
                    </div>
                </div>

                <div class="box-content-right" >
                    <div class="make-table">
                        <div class="make-table-cell">
                            <div class="wysiwyg wow slideInRight"><?php echo do_shortcode(apply_filters('the_content', $post_content)); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            </div>
        </div>
        
        <?php        
                
        echo $after_widget;        
    }
     
    function update($new_instance, $old_instance) {		

		$instance = $old_instance;
        $instance['selected_box'] = strip_tags( $new_instance['selected_box'] );
        return $instance;
    
    }

    function get_boxes() {

        $q = new WP_Query( array(
            'post_type' => 'frontbox', 
            'posts_per_page' => -1,
        ));
        
        $boxes = array();
        
        if ($q->have_posts()) {
            while ($q->have_posts()) {
                $q->the_post();
                $boxes[] = array(
                   'id' => get_the_ID(),
                   'name' => get_the_title(),
                );
            }
        }
        
        wp_reset_query();
        $this->boxes = $boxes;
        
    }
     
    function form( $instance ) {
 
        $boxes = $this->boxes;
        $selected_box = esc_attr($instance['selected_box']);
        ?>

        <p>
            <label for="<?php echo $this->get_field_id('selected_box'); ?>"><?php _e('Box to be shown', 'pimu'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('selected_box'); ?>" name="<?php echo $this->get_field_name('selected_box'); ?>">
            <?php foreach ($boxes as $box): ?>
                <option value="<?php echo $box['id']; ?>" <?php echo ($box['id'] == $selected_box )?'selected':''; ?>><?php echo $box['name']; ?></option>
            <?php endforeach; ?>
            </select>
        </p>
        
        <?php 
    }
  
}

add_action( 'widgets_init', function() {
    
  register_widget( 'pimu_boxy_widget' );
  
});
