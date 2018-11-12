jQuery(document).ready(function ($) {//make sure DOM is loaded and pass $ for use
    $("button.button.save_order.button-primary").on("click", function() {
        $("body").append(" <div class='loading-thailandtopup'> <div id='loader'></div></div>");
        // $(this).attr("disabled", "disabled");
    });
    
    $("input.button.save_order.button-primary").on("click", function() {
        $("body").append(" <div class='loading-thailandtopup'> <div id='loader'></div></div>");
    });
});