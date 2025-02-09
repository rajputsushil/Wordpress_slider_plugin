<?php 

    class Slider{
        public function __construct(){
            add_action('wp_enqueue_script',[$this,'load_assets']);
            add_action('init', [$this,'show_slider']);
        }
       
        public function load_assets(){
            wp_enqueue_style('slider_style',
            plugins_url('../assets/css/style.css',__FILE__));

            wp_enqueue_script('script',
            plugins_url('../assets/js/slider.js',__FILE__),
            ['jquery']
             );
        }
        public function show_slider(){
            add_shortcode('slider',[$this,'show_img_slider']);
        }
        
        public function show_img_slider($attr){
            ob_start();
            $attr = shortcode_atts([
                'id' => 0
            ], $attr, 'slider');
        
            global $wpdb;
            
            $table_name = $wpdb->prefix . 'slider_img';
            $image_details = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_name WHERE carousel_id = %d",
                $attr['id']
            ));
            // echo '<pre>';
            // print_r($image_details);
            // echo '</pre>';
            if (!empty($image_details)) {
                ?>
                <div class="custom_slider">
                    <button class="slider_btn slider_prev_btn">&lt;</button>
                    <div class="slider_container">
                        <?php foreach ($image_details as $image) { ?>
                            <div class="slider-slide">
                                <img src="<?php echo esc_url($image->images); ?>" alt="">
                            </div>
                        <?php } ?>
                    </div>
                    <button class="slider_btn slider_next_btn">&gt;</button>
                </div>
                <?php
            }
        
            return ob_get_clean();
        }
        
    }
?>