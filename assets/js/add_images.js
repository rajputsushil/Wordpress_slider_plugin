jQuery(document).ready(function($){
    let selectedFiles = []; 

    $('#uploadButton').on('click', function(e){
        e.preventDefault();
        $('#sliderImageUpload').click(); 
    });

    $('#sliderImageUpload').on('change', function(e){
        let files = this.files;
        let show_img = $('.input_image'); 

        if (files.length > 0) {
            for (let i = 0; i < files.length; i++) { 
                let file = files[i];
                selectedFiles.push(file); 
                
                let reader = new FileReader();
                reader.onload = function(e){
                    let input_img = $('<img>')
                        .attr('src', e.target.result)
                        .addClass('show_img_input');

                    input_img.on('click', function(){
                        $(this).remove();
                        removeFileFromList(file); 
                    });

                    show_img.append(input_img);
                };
                reader.readAsDataURL(file);
            }
        }
    });

    $('#save_image').on('click', function(e){
        e.preventDefault();

        if (selectedFiles.length === 0) {
            alert('No images selected!');
            return;
        }
        console.log(selectedFiles);
        let formdata = new FormData();
        formdata.append('action', 'slider');
        formdata.append('nonce', save_img.nonce);

        for (let i = 0; i < selectedFiles.length; i++) {
            formdata.append('images[]', selectedFiles[i]);  
        }

        $.ajax({
            url: save_img.ajax_url,
            type: 'POST',
            data: formdata,
            contentType: false,
            processData: false,
            success: function(response) {
                // console.log(response);
                alert('Images saved successfully');
                selectedFiles = [];
            },
            error: function(error) {
                console.error("Error:", error);
                alert('Failed to save images. Check the console for details.');
            }
        });
    });

    function removeFileFromList(fileToRemove) {
        selectedFiles = selectedFiles.filter(file => file !== fileToRemove);
    }


    $('.slider_del_btn').on('click',function(){
        $id = $(this).data('id');
        $.ajax({
            url: delete_slider.ajax_url,
            type: 'POST',
            data: {
                action:'delete_slider',
                id: $id,
                nonce: delete_slider.nonce
            },
            success: function(response){
                console.log(response);
                alert(response.data.message);
            },
            error: function(xhr,err){
                console.log('erorr');
            }
        });
    });

   $('button[name="edit_btn"]').on('click',function(e){
        e.preventDefault();
        //Find the the get the edit button for specific row and show the image_update feature
        $get_row = $(this).closest('tr');
        $add_class = $get_row.addClass('edit_slider_list');
        $edit_form = $(this).closest('tr').find('.edit_image_update');
        $edit_form.css('display','block');

        $couresl_id = $(this).data('id');
        $slider_images = $get_row.find('.admin_slider_images');

        $slider_images.hover(
            function() {
                $(this).css('border', '1px solid red');
            },
            function() {
                $(this).css('border', 'none');
            }
        );
        $image_ids = [];

        $slider_images.each(function(){
            $(this).on('click',function(){
                $image_id = $(this).data('id');
                $image_ids.push($image_id);
                $(this).remove();
                
                $.ajax({
                    url :update_images.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'update_slider_image',
                        image_id: $image_ids,
                        carousel_id:$couresl_id,
                        nonce:update_images.nonce
                    },
                    success: function(response){
                        console.log(response);
                    },
                    error : function(xhr,error){
                        console.log(xhr.status);
                    }
                });
            });
        });     
    });

    
    $('.update_more_image').on('click', function(e){
        e.preventDefault();
        $button = $(this);
        let row = $button.closest('tr');
        let form = row.find('form');
        
        let formData = new FormData(form[0]);
        formData.append('action', 'update_slider');
        formData.append('nonce', update_images.nonce);
        formData.append('carousel_id', row.find('button[name="edit_btn"]').data('id'));
        console.log(formData);
        $.ajax({
            url: update_images.ajax_url,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response){
                console.log(response);
                alert('Slider updated successfully');
            },
            error: function(error){
                console.error("Error:", error);
                alert('Failed to update slider. Check the console for details.');
            }
        });
    });
});