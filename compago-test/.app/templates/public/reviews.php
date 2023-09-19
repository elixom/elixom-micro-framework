<div class="col-sm-9 content">
<?php
$main = HTML::build('div');
if(!env('is_demo')){
    echo $main;
}
//TODO
$main->append(" 
      	 <div class='panel panel-default'>
           <div class='panel-heading'><a href='?segment=reviews' class='pull-right'>View all</a> <h4>User reviews</h4></div>
   			<div class='panel-body'>
              <img src='//placehold.it/150' class='img-circle pull-right'> <a href='#'>Articles</a>
              <div class='clearfix'></div>
              <hr>
              <div class='clearfix'></div>
              <img src='http://placehold.it/120x90/3333CC/FFF' class='img-responsive img-thumbnail pull-right'>
              <p>The more powerful (and 100% fluid) Bootstrap 3 grid now comes in 4 sizes (or 'breakpoints'). Tiny (for smartphones), Small (for tablets), Medium (for laptops) and Large (for laptops/desktops).</p>
              <div class='clearfix'></div>
              <hr>
              <div class='clearfix'></div>
              <img src='http://placehold.it/120x90/33CC33/FFF' class='img-responsive img-thumbnail pull-left' style='margin-right:5px;'>
              <p>Mobile first' is a responsive Web design practice that prioritizes consideration of smart phones and mobile devices when creating Web pages.</p>
              
              
            </div>
   		 </div>"); 

 

echo $main;
?>
</div> <!-- content -->