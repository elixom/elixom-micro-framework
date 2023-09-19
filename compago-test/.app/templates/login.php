<!-- Top content -->
        <div class="top-content">

            <div class="inner-bg">
                <div class="container">
                    <div class="row text-center">
                            <h1 class="page-title" style="color:  blueviolet;"><strong><?php echo app()->get('title',''); ?></strong> Login</h1>
                            <div class="description">
                            	<p>
	                            	Use your email address or <strong> Username</strong> to login!
                            	</p>
                            </div>

                            
                    </div>
                    <div class="row">
                        <div class="col-sm-6 col-sm-offset-3 form-box">
                        	<div class="form-top">
                        		<div class="form-top-left">
                        			<h3>Login to <?php echo app()->get('title',''); ?></h3>
                            		<p>Enter your username and password to log on:</p>
                                    <?php
                                        if (isset($error)){
                                            echo "<p class='text-danger' >$error</p>";
                                        }
	
    
	if (isset($_GET['failed'])){
	   echo "
       <p class='text-danger'>
	                            	login failed!
                            	</p>
       ";
	}
?>
                        		</div>
                        		<div class="form-top-right">
                        			<i class="fa fa-key"></i>
                        		</div>
                            </div>
                            <div class="form-bottom">
			                    <form role="form" action="" method="post" class="login-form">
			                    	<div class="form-group">
			                    		<label class="sr-only" for="form-username">Username</label>
			                        	<input type="text" name="form-username" placeholder="Username..." class="form-username form-control" id="form-username">
			                        </div>
			                        <div class="form-group">
			                        	<label class="sr-only" for="form-password">Password</label>
			                        	<input type="password" name="form-password" placeholder="Password..." class="form-password form-control" id="form-password">
			                        </div>
			                        <button type="submit" class="btn">Sign in!</button>
			                    </form>
		                    </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 col-sm-offset-3 social-login">
                        	<h3>...or login with:</h3>
                        	<div class="social-login-buttons">
	                        	<a class="btn btn-link-1 btn-link-1-facebook" href="#">
	                        		<i class="fa fa-facebook"></i> Facebook
	                        	</a>
	                        	<a class="btn btn-link-1 btn-link-1-google-plus" href="#">
	                        		<i class="fa fa-google-plus"></i> Google Plus
	                        	</a>
                        	</div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>