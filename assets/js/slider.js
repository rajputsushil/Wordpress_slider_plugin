jQuery(document).ready(function(){
    let slider_index = 0;
    const sliderContainer = $(".slider_container");
    const slides = $(".slider-slide");
    const totalSlider = slides.length;

    function updateSlider() {
        sliderContainer.css("transform", `translateX(-${slider_index * 100}%)`);
    }

    $(".slider_prev_btn").click(function() {
        slider_index = (slider_index > 0) ? slider_index - 1 : totalSlider - 1;
        updateSlider();
    });
    
    $(".slider_next_btn").click(function() {
        slider_index = (slider_index < totalSlider - 1) ? slider_index + 1 : 0;
        updateSlider();
    });
})