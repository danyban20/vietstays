<!-- Jquery JS-->
<script src="<?php echo vv_plugins_url() ?>admin/vendor/jquery-3.2.1.min.js"></script>
<!-- Bootstrap JS-->
<script src="<?php echo vv_plugins_url() ?>admin/vendor/bootstrap-4.1/popper.min.js"></script>
<script src="<?php echo vv_plugins_url() ?>admin/vendor/bootstrap-4.1/bootstrap.min.js"></script>
<!-- Vendor JS       -->
<script src="<?php echo vv_plugins_url() ?>admin/vendor/slick/slick.min.js">
</script>
<script src="<?php echo vv_plugins_url() ?>admin/vendor/wow/wow.min.js"></script>
<script src="<?php echo vv_plugins_url() ?>admin/vendor/animsition/animsition.min.js"></script>
<script src="<?php echo vv_plugins_url() ?>admin/vendor/bootstrap-progressbar/bootstrap-progressbar.min.js">
</script>
<script src="<?php echo vv_plugins_url() ?>admin/vendor/counter-up/jquery.waypoints.min.js"></script>
<script src="<?php echo vv_plugins_url() ?>admin/vendor/counter-up/jquery.counterup.min.js">
</script>
<script src="<?php echo vv_plugins_url() ?>admin/vendor/circle-progress/circle-progress.min.js"></script>
<script src="<?php echo vv_plugins_url() ?>admin/vendor/perfect-scrollbar/perfect-scrollbar.js"></script>
<script src="<?php echo vv_plugins_url() ?>admin/vendor/chartjs/Chart.bundle.min.js"></script>
<script src="<?php echo vv_plugins_url() ?>admin/vendor/select2/select2.min.js"></script>
<script src="<?php echo vv_plugins_url() ?>admin/plugins/summernote/summernote-bs4.min.js"></script>

<script src="<?php echo vv_plugins_url()?>admin/plugins/bootstrap-autocomplete/bootstrap-autocomplete.js"></script>

<script src="https://kit.fontawesome.com/48ccd199be.js" crossorigin="anonymous"></script>

<!-- Main JS-->
<script src="<?php echo vv_plugins_url() ?>admin/js/main.js"></script>



<div class="modal fade" id="alertModal" tabindex="-1" role="dialog" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div id="alertModalContent" class="text-center"></div>
                <div class="text-center mt-4">
                    <button type="button" class="btn btn-info btn-sm" data-dismiss="modal" aria-label="Close">OK</button>                    
                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    function vvAlert(html){
        $('#alertModalContent').html(html);
        $('#alertModal').modal('show');
    }

    function vvCurrencyToDecimal(input) {
        if(vvSiteCurrency == 'VND'){
              if (typeof input !== 'string') return null;
              input = input.replace(/[₫\s,-]+$/g, '');
              input = input.replace(/[^\d.]/g, '');
              input = input.replace(/\./g, '');
              const number = parseInt(input, 10);

              return isNaN(number) ? null : number;
          }else{
            input = input.replace(/\s+/g, ' ').trim();
            input = input.replace(/[\s,-]+$/, '');
            input = input.replace(/\s/g, '');
            if (input.includes(',')) {
            input = input.replace(',', '.');
            }
            let number = parseFloat(input);
            if (isNaN(number)) return null;
    
            return number.toFixed(2);
        }
    }    


    function vvFormatCurrency(value, showSymbol = true) {
        if (vvSiteCurrency === 'VND') {
            return value.toLocaleString('vi-VN', {
                style: showSymbol ? 'currency' : 'decimal',
                currency: 'VND',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        } else {
            return new Intl.NumberFormat('nb-NO', {
                style: showSymbol ? 'currency' : 'decimal',
                currency: 'NOK',
                currencyDisplay: 'code',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(value);
        }
    }
    var vvNotificationTimeout;

    function vvErrorMsg(msg){
        vvShowNotification('<div class="alert alert-danger">'+msg+' <span class="btnCloseNotification">&times;</span></div>');
    }
    function vvSuccessMsg(msg){
        vvShowNotification('<div class="alert alert-success">'+msg+' <span class="btnCloseNotification">&times;</span></div>');
    }

    function vvShowNotification(html){
        $('.notification-bar').html('<div style="display:none">'+html+'</div>');
        $('.notification-bar').children('div').fadeIn(300);
        
        vvNotificationTimeout = setTimeout( function (){ 
                $(".notification-bar").find(".alert").fadeOut(800, function (){
                    $(".notification-bar").find(".alert").remove();
                }); 
                if (typeof triggerSticky === "function") { triggerSticky(); } }
          , 10000); 

        $('.notification-bar').find('.btnCloseNotification').click(function (){
            $(this).parent('.alert').fadeOut(800);
        });
    }
    
    //var $ = jQuery.noConflict();

    $(document).ready(function (){
        <?php 
        if(GET_Request('add_task') == 1){
        }else{
            showAlertMessages();    
        }                        
        ?>

        if($('.wysiwyg_editor').length){
            $('.wysiwyg_editor').summernote({
                height: 250,
                toolbar : [
                    // [groupName, [list of button]]
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    [['strikethrough', 'superscript', 'subscript']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['paragraph', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']],
                    ['codeview']
                ]
            });
        }

    });

</script>
