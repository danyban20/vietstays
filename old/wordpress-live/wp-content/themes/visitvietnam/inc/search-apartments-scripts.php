<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo vv_get_google_map_api_key() ?>" ></script>
<script>
    var apartments_map;
    var apartments_map_locations;
    function initMap() {
        let $ = jQuery.noConflict();

        apartments_map = new google.maps.Map(document.getElementById('apartments_map'), {
            zoom: 12,
            center: { lat: 0, lng: 0 }
        });


        $.ajax({
            url: '<?php echo get_bloginfo('url') ?>?vv_action2=generate_map_pins&' + $('#searchApartments').serialize(), 
            success: function(response) {
                apartments_map_locations = response.locations;
                apartments_map_locations.forEach(function (loc) {
                    var lat = parseFloat(loc.lat);
                    var lng = parseFloat(loc.lng);

                    var marker = new google.maps.Marker({
                        position: { lat: lat, lng: lng },
                        map: apartments_map,
                    });

                    var infoWindow = new google.maps.InfoWindow({
                        content: `
                            <div style="width:250px;color:#000;">
                                <strong>${loc.name}</strong><br>${loc.address}<br>
                                <a href="${loc.url}" target="_blank">View Details</a><br>
                            </div>
                        `
                    });

                    marker.addListener("click", function () {
                        infoWindow.open(apartments_map, marker);
                    });

                    apartments_map.setCenter({ lat: lat, lng: lng });
                });
            },
            error: function(xhr) {
                console.error('Error:', xhr.responseText);
            }
        });


        //apartments_map.setCenter(apartments_map_locations[0]);
    }



    function countRoomsGuests(){
        let $ = jQuery.noConflict();

        rooms = $('input[name="rooms"]').val();
        adults = $('input[name="adults"]').val();
        children = $('input[name="children"]').val();

        guests = parseInt(adults) + parseInt(children);

        label = rooms;
        label += (rooms > 1) ? ' Rooms' : ' Room';
        label += ' / '+guests;
        label += (guests > 1) ? ' Guests' : ' Guest';

        $('.rooms_guests_label').html(label);
            
    }

    function doSearchApartments(){
        let $ = jQuery.noConflict();
        $('#search_result').html('<div class="text-center mt-4 mb-4 alert alert-info">Searching.. <img src="<?php echo vv_plugins_url() ?>admin/images/ajax-loader.gif" ></div>');

        $.ajax({
            url: '<?php echo get_bloginfo('url') ?>?' + $('#searchApartments').serialize(), 
            success: function(response) {
                $('#search_result').html(response);
                initSearchResults();
                initMap();
            },
            error: function(xhr) {
                console.error('Error:', xhr.responseText);
            }
        });
    }


    function initSearchResults(){
        let $ = jQuery.noConflict();


        $('.apartments_tab_btn').click(function (e){
            $('.apartments_tab_btn').removeClass('active');
            $(this).addClass('active');

            if($(this).attr('data-show_map') == 1){
                $('.city_app_wrap').addClass('map_shown');
            }else{
                $('.city_app_wrap').removeClass('map_shown');
            }

            e.preventDefault();

        });

        $('.btnShowApartments').click(function (e){
            district_id = $(this).attr('data-district_id');
            $('.districts_list').hide();
            $('.apartments_list').show();
            $('.district_headers').hide();
            $('.district_headers_'+district_id).show();
            $('.district_apartment').hide();
            $('.district_apartment_'+district_id).show();
            e.preventDefault();
        });

        $('.back_btn').click(function (e){
            $('.districts_list').show();
            $('.apartments_list').hide();
            $('.district_headers').hide();
            $('.district_apartment').hide();
            e.preventDefault();
        });

    }

    $(document).ready(function (){
        let $ = jQuery.noConflict();
        


        $('.btnSetFacility').click(function (e){
            $this = $(this);
            
            if($this.hasClass('active')){
                $this.find('input[name="facility[]"]').prop('checked',false);
                $this.removeClass('active');
            }else{  
                $this.find('input[name="facility[]"]').prop('checked',true);
                $this.addClass('active');
            }

            doSearchApartments();
            e.preventDefault();
        });

        $('.minus').click(function () {
            $input = $(this).parent().find('input');
            count = parseInt($input.val());
            min = $input.attr('data-min');
            if(typeof min == 'undefined') min = 0;

            if(count > min) count--;
            $input.val(count);
            $input.change();
            return false;
        });

        $('.plus').click(function () {
            var $input = $(this).parent().find('input');
            $input.val(parseInt($input.val()) + 1);
            $input.change();
            return false;
        });

        $('.btnUpdateRoomsGuests').click(function (e){
            countRoomsGuests();
            doSearchApartments();
            e.preventDefault();
        });

        $('select[name="city_id"]').change(function (){
            text = $(this).find('option:selected').text();
            $('h1.title').html('All apartments from ' + text);
            doSearchApartments();
        });



        initSearchResults();
        initMap();

    });
  </script>
