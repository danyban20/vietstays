                <li class="nav-item">
                    <a class="nav-link" id="availability-tab" data-toggle="tab" href="#availability" role="tab"><?php vv_e( 'Availability' ); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="staff-tab" data-toggle="tab" href="#staff-assign" role="tab"><?php vv_e( 'Operations Staff' ); ?></a>
                </li>
                <?php if ( $apartment_id > 0 ) { ?>
                <li class="nav-item">
                    <a class="nav-link" id="offer-tab" data-toggle="tab" href="#send-offer" role="tab"><?php vv_e( 'Send Offer' ); ?></a>
                </li>
                <?php } ?>
