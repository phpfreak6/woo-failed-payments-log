<form action="?page=trello-settings" method="post">
<h3>Connect Your Trello Account</h3>

<table class="form-table" style="width:450px">
    <tbody>
       <tr>
			<td style="width:147px;"><h4>Enter Your App Key</h4></td>
			<td style="width:262px"><input name="app-key" <?php echo ($app_key) ? 'readonly' :  '' ; ?> value="<?php echo ($app_key) ? $app_key :  '' ; ?>" required="" style="width: 100%;" type="text"></td>
		</tr>
    </tbody>
</table>
    <p class="submit"><input type="submit" value="Submit" class="button-primary" name="trellsubmit"></p>
</form>


<?php if($app_key) { ?>


	  <div class="container-fluid">
        <h1 class="text-center">Get your Trello API Token!</h1>
        <p class="text-center">You need to login into Trello to get a token key and start using Trello API.</p>
        <p class="text-center">All Trello actions done by API will appears like made by the user used on this authentication</p>
        <div class="col-md-6 col-md-offset-3">
          <?php if($trello_app_token){ ?>	
            <div class="form-group get">
                <label for="appname">Here is your API Token! Enjoy!</label>
                <input class="form-control" type="text" placeholder="" id="apiToken" style="width: 500px;" readonly value="<?php echo $trello_app_token; ?>">
            </div>
			
		  <?php }else{ ?>
			  
			   <p class="submit">  <button type="button" class="button-primary" id="getButton">Get your API key!</button></p>
			  
		  <?php } ?>
         

            <small class="text-center disclaimer">You'll be redirected to Trello's authentication page and we'll be back with your token!</small>
        </div>
    </div>
<?php 
}
?>
<?php if($trello_app_token) { ?>
 <form action="?page=trello-settings" method="post">
<h3>Enter Your Board ID and List ID:</h3>
<?php if(isset($board_error)){
	
	echo '<p>Invalied Board ID.</p>';
	
} ?>
<table class="form-table">
    <tbody>
       <tr>
			<td><h4> <label for="appname"> Board ID:</label></h4></td>
			<td> <input required class="form-control" type="text" placeholder="" name="board_id" id="board_id" style="width:250px;" <?php echo ($board_id) ? 'readonly' : ''; ?> value="<?php echo isset($board_id) ? $board_id : ''; ?>"></td>
		</tr>
		  <tr>
			<td><h4>  <label for="appname"> List ID:</label></h4></td>
			<td><input required class="form-control" type="text" placeholder="" name="list_id" id="list_id" style="width: 250px;" <?php echo ($list_id) ? 'readonly' : ''; ?> value="<?php echo isset($list_id) ? $list_id : ''; ?>"></td>
		</tr>
    </tbody>
</table>
<?php if($board_id && $list_id){ ?>
	<p class="submit">  <button type="button" class="button-primary" id="ClearAll">Clear All</button></p>
   
<?php }else{ ?>	
    <p class="submit"><input type="submit" value="Submit" class="button-primary" name="trellsubmit"></p>
<?php } ?>
</form>		
<?php } ?>

   <script type="text/javascript" src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
    <script src="https://trello.com/1/client.js?key=<?php echo $app_key; ?>"></script>
    <script type="text/javascript">
        function showToken () {
            if (localStorage.getItem('trello_token')) {
                $(".create, #getButton, .disclaimer").hide();
                $(".get").show();
                $("#apiToken").val(localStorage.getItem('trello_token'));
            }
        };
        showToken()

        $("#getButton").on('click', function () {
                Trello.authorize({
                    name: ($("#appname").val()) ? $("#appname").val() : "Woocommerce Log",
                    scope: {
                        read: true,
                        write: true 
                    },
                    expiration: "never"
                });
        })
		 function getHashUrlVars(){
				var vars = [], hash;
				var hashes = window.location.href.slice(window.location.href.indexOf('#') + 1).split('&');
				for(var i = 0; i < hashes.length; i++)
				{
					hash = hashes[i].split('=');
					vars.push(hash[0]);
					vars[hash[0]] = hash[1];
				}
				return vars;
			}


		// Get all URL parameters
		var tokenval = getHashUrlVars()['token'];
		
		if(tokenval){
			 jQuery.ajax({
				url :ajaxurl,
				type : 'post',
				data : {
					action : 'update_token_value',
					token : tokenval
				},
				success : function( response ) {
					if(response == 'success'){
							
							window.location = '<?php  echo $_SERVER['REQUEST_URI']?>';
						
					}else{
					
					}
				}
			});
		}
		
		$("#ClearAll").on('click', function () {
			
			 jQuery.ajax({
				url :ajaxurl,
				type : 'post',
				data : {
					action : 'delete_stored_token',
					token : tokenval
				},
				success : function( response ) {
					if(response == 'success'){
							
							window.location = '<?php  echo $_SERVER['REQUEST_URI']?>';
						
					}else{
					
					}
				}
			});
			
		})
		
    </script>