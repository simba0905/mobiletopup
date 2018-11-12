jQuery(document).ready(function ($) {//make sure DOM is loaded and pass $ for use
   
    // set password
    $('#secret_key_api_mobiletopup').prop('type', 'password');
    
    $("#active_mobiletop_plugin").change(function() {
        if(this.checked) {
            $("#required_radiobox").prop('required',true);
        }
        else{
            $("#required_radiobox").prop('required',false);
        }
    });
});