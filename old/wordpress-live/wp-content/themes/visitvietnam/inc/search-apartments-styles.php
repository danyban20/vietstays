<style type="text/css">

.facilities{
    margin-bottom:20px;
}
.facilities ul{
    margin:0;
    padding: 0;
}
.facilities ul li{
    display: inline-block;
    vertical-align: top;
    margin-right:20px;
    min-width: 80px;
}

.facilities ul li .icon{
    display:block;
    text-align: center;
    border:1px solid #004041;
    border-radius: 50%;
    width:40px;
    height:40px;
    margin:0px auto;

}
.facilities ul li .icon img{
    width:20px;
    height:20px;
    margin-top:10px;
}
.facilities ul li .name{
    display:block;
    text-align: center;
    font-size:12px;
    font-weight: 400;
    color:#004041 ;
    margin-top:3px;
}

.facilities ul li a:hover{
    text-decoration: none;
}
.facilities ul li a:hover .name, .facilities ul li a.active .name{
    color:#FC770E;
}
.facilities ul li a:hover .icon, .facilities ul li a.active .icon{
    background-color: #FC770E;
    color:#fff;
}


.city_tab img{
    max-width:unset;
}

.city_app_wrap.map_shown .list_wrap{
    float:left;
    width:65%;
}
.city_app_wrap .map_wrap{
    display: none;
    float:right;
    width:35%;
}
.city_app_wrap.map_shown .map_wrap{
    display:block;
}
.district_block_1, .city_app_block_1{
    width:calc(25% - 11px);
    display: inline-block;
    vertical-align: top;
    margin-right:10px !important;
}
.district_block_1:nth-child(4), .city_app_block_1:nth-child(4){
    margin-right:0px !important;
}


.city_app_wrap.map_shown .district_block_1, .city_app_wrap.map_shown .city_app_block_1{
    width:calc(33% - 15px);
}

.district_block_1_inner a{
    color:#fff;
}


.city_filter .dropdown_wrap{
    border: 2px solid #555555;
    border-radius: 100px;
    width: auto;
    padding: 6px 46px 6px 18px;
    background-position: center right 14px;
    margin: 0 20px 0 0;
    font-size: 16px;
    color: #013735;
    position: relative;
    overflow: visible;
    cursor: pointer;
}

.city_filter .dropdown_wrap a{
    color:#013735;
}
.city_filter .dropdown_wrap a.btn{
    color: #fff;
}
.city_filter .dropdown_wrap a:hover{
    text-decoration: none;
}
.city_filter .dropdown_wrap i.fas{
    position:absolute;
    right:15px;
    top:8px;
}
.city_filter .dropdown_wrap:hover .rooom_dropdown{
    display:block !important;
    z-index: 9;
    top:15px;
    z-index: 9;
}

#city_wrap .city_filter .clear_all_btn{
    display: block;
}

#search_result h3{
    font-family: 'TrajanProRegular';
    font-weight: 400;
    font-size: 24px;
}

.apartments_list{
    display: none;
}

</style>
