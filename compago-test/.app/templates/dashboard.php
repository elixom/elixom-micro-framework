<div class="inner-bg">
    <div class="container">
        <div class="row">
                <h1 style="color:  blueviolet;"><strong><?php echo app()->get('title',''); ?></strong> Welcome!</h1>
                <div class="description">
                	<p>
                    <?php
                        if (isset($_SESSION['name'])){
                            echo "<h2 class='text-success' >{$_SESSION['name']}</h2>";
                        }

                    ?>	
                	</p>
                </div>
        </div>
        <div class="row">
                <?php
                    if (isset($message)){
                        echo "<p class='text-success' >$message</p>";
                    }

            ?><br />

    	<p> <a class="btn btn-danger" href="./logout">Log out</a></p>
        </div>
        
    </div>
</div>
