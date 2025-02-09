<?php 
    class Admin_pannel{
        public function __construct(){  
            add_action('admin_menu',[$this,'show_admin_slider']);
            add_action('admin_enqueue_scripts',[$this,'load_assets']);
            add_action('wp_ajax_slider',[$this,'slider']);
            add_action('wp_ajax_delete_slider',[$this,'delete_slider']);
            add_action('wp_ajax_update_slider_image',[$this,'update_slider_image']);
            add_action('wp_ajax_update_slider',[$this,'update_slider']); 
        }

        public function load_assets(){
            wp_enqueue_style(
                'slider_css',
                plugins_url('../assets/css/slider_admin.css', __FILE__)
            );
            wp_enqueue_script(
                'add_images',
                plugins_url('../assets/js/add_images.js',__FILE__),
                ['jquery']
            );

            wp_localize_script('add_images','save_img',[
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('slider_nonce')
                ]
            );
            wp_localize_script('add_images','delete_slider',[
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('delete_slider')
            ]);
            wp_localize_script('add_images','update_images',[
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('update_images')
            ]);
        }
        
        public function show_admin_slider(){
            add_menu_page(
                "Slider",
                "Slider",
                "manage_options",
                "slider",
                [$this,'add_image'],
                "dashicons-format-gallery"
            );
        }

        public function add_image(){
           ?>
             <div class="image_slider_container">
                <form action="" enctype="multipart/form-data">
                    <h2>Upload Image for Slider</h2>
                    <div class="input_image"></div>
                    <input type="file" id="sliderImageUpload" name="" multiple>
                    
                    <button id="uploadButton" class="upload_btn">Add Images</button>

                    <button id="save_image">Save</button>
                </form>
            </div>
           <?php

           global $wpdb;

           $table_name = $wpdb->prefix .'slider_img';

           $sliders = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name"
           ));
           
           $grouped_sliders = [];
            foreach ($sliders as $slide) {
                $grouped_sliders[$slide->carousel_id][] = $slide;
            }

           ?> 
                <div class="slider_list">
                    <table border="1">
                        <thead>
                            <tr>
                                <th>Slider Image</th>
                                <th>Short Code</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grouped_sliders as $carousel_id => $carousel_images): ?>
                                    <tr>
                                        <td>
                                            <div class="slider_img">
                                                <?php foreach ($carousel_images as $slide): ?>
                                                    <img src="<?php echo $slide->images; ?>" class="admin_slider_images" alt="Slider Image" data-id="<?php echo $slide->id;?>">
                                                <?php endforeach; ?> 
                                            </div>
                                            <form action="" enctype="multipart/form-data" class="edit_image_update">
                                                <input type="file" id="edit_slider_browse" class="edit_browse" name="images[]" multiple>
                                                <button type="submit" class="update_more_image">Update</button>
                                            </form>   
                                        </td>
                                        <td>
                                            <div class="short_code">
                                                <span>[slider id =<?php echo $carousel_id;?>]</span>
                                            </div>
                                        </td>
                                        
                                        <td>
                                            <div class="slider_btn_del">
                                                <button class="slider_del_edit" name="edit_btn" data-id="<?php echo $carousel_id; ?>" data-slide-id="<?php echo $slide->id; ?>">Edit</button>
                                                <button class="slider_del_btn" data-id="<?php echo $carousel_id; ?>" data-slide-id="<?php echo $slide->id; ?>">Delete</button>
                                            </div>
                                        </td>
                                    </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php
           
        }

        public function slider(){
            global $wpdb;
        
            
            if(!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'slider_nonce')) {
                wp_send_json_error('Invalid Nonce');
            }
        
            
            $images = isset($_FILES['images']) ? $_FILES['images'] : [];
            if(empty($images)){
                wp_send_json_error(['No images received' => 'No images received']);
            }
            
            
            $carousel_id = time();
            
           
            $uploaded_images = [];
            
           
            foreach ($images['name'] as $key => $name) {
                if ($images['error'][$key] === 0) {
                    $file = [
                        'name' => $name,
                        'type' => $images['type'][$key],
                        'tmp_name' => $images['tmp_name'][$key],
                        'error' => $images['error'][$key],
                        'size' => $images['size'][$key]
                    ];
                    
                    
                    $upload_handle = wp_handle_upload($file, ['test_form' => false]);
        
                    
                    if (isset($upload_handle['file'])) {
                        
                        $attachment = [
                            'post_mime_type' => $upload_handle['type'],
                            'post_title'     => sanitize_file_name($name),
                            'post_content'   => '',
                            'post_status'    => 'inherit',
                        ];
        
                        
                        $attach_id = wp_insert_attachment($attachment, $upload_handle['file']);
                        $attach_data = wp_generate_attachment_metadata($attach_id, $upload_handle['file']);
                        wp_update_attachment_metadata($attach_id, $attach_data);
        
                        
                        $image_url = wp_get_attachment_url($attach_id);
        
                        
                        $table_name = $wpdb->prefix . 'slider_img';
                        $wpdb->insert(
                            $table_name,
                            [
                                'images' => $image_url,
                                'carousel_id' => $carousel_id
                            ]
                        );
        
                       
                        $uploaded_images[] = $image_url;
                    } else {
                        
                        wp_send_json_error(['error' => $upload_handle['error']]);
                    }
                }
            }
        
          
            wp_send_json_success(['message' => 'Images uploaded successfully', 'uploaded_images' => $uploaded_images]);
        }
        
        public function delete_slider(){
            global $wpdb;

            if(!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'],'delete_slider')){
                wp_send_json_error(['message'=> 'Invalide Message']);
            }

            $id = isset($_POST['id']) ? $_POST['id'] : '';

            $table_name = $wpdb->prefix . 'slider_img';
            
            $table_details = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_name WHERE carousel_id=%d",
                $id
            ));

            $delete_carousel_id = $wpdb->delete(
                $table_name,
                [
                    'carousel_id' => $table_details->carousel_id
                ],
                ['%d']
            );
            if($delete_carousel_id){
                wp_send_json_success(['message'=>'Slider Deleted']);
            }else{
                wp_send_json_error(['message'=> 'Not Deleted']);
            }

        }

        public function update_slider_image(){
            global $wpdb;
            if(!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'],'update_images')){
                wp_send_json_error(['message'=>'Invalid nonce']);
            }
            $table_name = $wpdb->prefix .'slider_img';
            $image_ids = isset($_POST['image_id'])? $_POST['image_id'] : [];
            
            foreach($image_ids as $img_id){
                if($img_id){
                    $delete_img = $wpdb->delete(
                        $table_name,
                        [
                            'id'=>$img_id
                        ],
                        ['%d']
                    );

                    if($delete_img){
                        wp_send_json_success(['message'=>'deleted']);
                    }
                }
            }
            
        }

        public function update_slider(){
            global $wpdb;
        
            if(!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'update_images')) {
                wp_send_json_error('Invalid Nonce');
            }
        
            $carousel_id = isset($_POST['carousel_id']) ? $_POST['carousel_id'] : '';
            $images = isset($_FILES['images']) ? $_FILES['images'] : [];
            
            if(empty($images)){
                wp_send_json_error(['No images received' => 'No images received']);
            }
        
            $uploaded_images = [];
        
            foreach ($images['name'] as $key => $name) {
                if ($images['error'][$key] === 0) {
                    $file = [
                        'name' => $name,
                        'type' => $images['type'][$key],
                        'tmp_name' => $images['tmp_name'][$key],
                        'error' => $images['error'][$key],
                        'size' => $images['size'][$key]
                    ];
        
                    $upload_handle = wp_handle_upload($file, ['test_form' => false]);
        
                    if (isset($upload_handle['file'])) {
                        $attachment = [
                            'post_mime_type' => $upload_handle['type'],
                            'post_title'     => sanitize_file_name($name),
                            'post_content'   => '',
                            'post_status'    => 'inherit',
                        ];
        
                        $attach_id = wp_insert_attachment($attachment, $upload_handle['file']);
                        $attach_data = wp_generate_attachment_metadata($attach_id, $upload_handle['file']);
                        wp_update_attachment_metadata($attach_id, $attach_data);
        
                        $image_url = wp_get_attachment_url($attach_id);
        
                        $table_name = $wpdb->prefix . 'slider_img';
                        $wpdb->insert(
                            $table_name,
                            [
                                'images' => $image_url,
                                'carousel_id' => $carousel_id
                            ]
                        );
        
                        $uploaded_images[] = $image_url;
                    } else {
                        wp_send_json_error(['error' => $upload_handle['error']]);
                    }
                }
            }
        
            wp_send_json_success(['message' => 'Slider updated successfully', 'uploaded_images' => $uploaded_images]);
        }
    }
?>