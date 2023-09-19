<div class="container">
    <div class="row">
    <h1 class="page-title" style="color:  blueviolet;"><?php echo app()->get('title',''); ?></h1>
    </div>
</div>


<div class="container">
    <div class="row text-center">
        <div class="col-sm-6">
            <div class='panel panel-success '>
           <div class='panel-heading'> <h4 class="panel-title">Sign up</h4></div>
        	<div class='panel-body'>
              <div class='well'> 
                     <form class='form' method="POST">
                      <div class='form-group'>
                      <input type='text' name='name' required class='form-control input-lg' placeholder='Your name'>
                      </div>
                      <div class='form-group'>
                      <input type='email' name='email' required class='form-control input-lg' placeholder='Enter your email address'>                      
                      </div>
                      
                      <div class='form-group'>
                      <input type='text' name='access_code' maxlength=15 class='form-control input-lg' placeholder='enter your access code if you have one'>                      
                      </div>
                      
                      <div class='form-group text-center'>
                        <button class='btn btn-lg btn-primary' type='submit'>Join</button>
                      </div>
                    </form>
                  </div>
            </div>
            </div>
         </div>
    </div>
</div> <!-- container -->