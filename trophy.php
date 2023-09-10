<?php

$pageName = "Trophy";
include 'header.php';
include 'sidebar.html';

?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">

            <div class="row">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-body" style="background: #fff; direction: ltr;">
                            <div class="row">
                                <div class="col-sm-12 text-center" style="padding: 500px 10px 10px 10px;">

                                    <div class="owl-carousel">
                                        
                                        <div class="item text-center" data-hash="front">
                                            <div class="row">
                                                <div class="col-1 col-md-4"></div>
                                                <div class="col-10 col-md-4"><div class="plaque" style="height:300px">SUNTOWN<br />FANTASY FOOTBALL<br />LEAGUE</div></div>
                                            </div>
                                        </div>
                                        <div class="item" data-hash="right">
                                            <div class="row text-center">
                                                <div class="col-1 col-md-3"></div>
                                                <div class="col-5 col-md-3"><div class="plaque">2008 CHAMPION<br />TYLER JAQUISH</div></div>
                                                <div class="col-5 col-md-3"><div class="plaque">2006 CHAMPION<br />AJ SARTIN</div></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-1 col-md-3"></div>
                                                <div class="col-5 col-md-3"><div class="plaque">2009 CHAMPION<br />MATT REID</div></div>
                                                <div class="col-5 col-md-3"><div class="plaque">2007 CHAMPION<br />JUSTIN DIDIER</div></div>
                                            </div>
                                            <hr />
                                            <div class="row">
                                                <div class="col-1 col-md-3"></div>
                                                <div class="col-5 col-md-3"><div class="plaque">2020 CHAMPION<br />MATT REID</div></div>
                                                <div class="col-5 col-md-3"><div class="plaque">2018 CHAMPION<br />JUSTIN DIDIER</div></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-1 col-md-3"></div>
                                                <div class="col-5 col-md-3"><div class="plaque">2021 CHAMPION<br />JUSTIN DIDIER</div></div>
                                                <div class="col-5 col-md-3"><div class="plaque">2019 CHAMPION<br />CAMERON BOBOTH</div></div>
                                            </div>
                                        </div>
                                        <div class="item" data-hash="back">
                                            <div class="row">
                                                <div class="col-1 col-md-3"></div>
                                                <div class="col-5 col-md-3"><div class="plaque">2012 CHAMPION<br />AJ SARTIN</div></div>
                                                <div class="col-5 col-md-3"><div class="plaque">2010 CHAMPION<br />CAMERON BOBOTH</div></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-1 col-md-3"></div>
                                                <div class="col-5 col-md-3"><div class="plaque">2013 CHAMPION<br />ANDY STAMSCHROR</div></div>
                                                <div class="col-5 col-md-3"><div class="plaque">2011 CHAMPION<br />BEN BARDELL</div></div>
                                            </div>
                                            <hr />
                                            <div class="row">
                                                <div class="col-1 col-md-3"></div>
                                                <div class="col-5 col-md-3"></div>
                                                <div class="col-5 col-md-3"><div class="plaque">2022 CHAMPION<br />JUSTIN DIDIER</div></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-5 col-md-3"></div>
                                                <div class="col-5 col-md-3"></div>
                                                <div class="col-5 col-md-3"></div>
                                            </div>
                                        </div>
                                        <div class="item" data-hash="left">
                                            <div class="row">
                                                <div class="col-1 col-md-3"></div>
                                                <div class="col-5 col-md-3"><div class="plaque">2016 CHAMPION<br />COLE BOBOTH</div></div>
                                                <div class="col-5 col-md-3"><div class="plaque">2014 CHAMPION<br />JUSTIN DIDIER</div></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-1 col-md-3"></div>
                                                <div class="col-5 col-md-3"><div class="plaque">2017 CHAMPION<br />COLE BOBOTH</div></div>
                                                <div class="col-5 col-md-3"><div class="plaque">2015 CHAMPION<br />JUSTIN DIDIER</div></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>  
                            </div>
                            <div class="row">
                                <div class="col-sm-12 text-center">
                                    <a class="btn btn-secondary url" href="#front">front</a> 
                                    <a class="btn btn-secondary url" href="#right">right</a> 
                                    <a class="btn btn-secondary url" href="#back">back</a> 
                                    <a class="btn btn-secondary url" href="#left">left</a> 
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script type="text/javascript">

    $(document).ready(function(){
        $(".owl-carousel").owlCarousel({
            loop:true,
            margin:10,
            // dots:true,
            center: true,
            // nav:true,
            animateOut: 'fadeOut',
            responsive:{
                0:{
                    items:1
                },
                600:{
                    items:1
                },
                1000:{
                    items:1
                }
            }
        });
    });

</script>