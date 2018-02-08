<?php 
	require_once('../DBHandler.php');

	if ( isset($_POST['free_message']) )
	{
		$new_msg = $_POST['free_message'];
		$uploaded_by = $_POST['message_uploaded_by'];
		$upload_date = date('Y-m-d');

		$connection = new DBHandler();
		$result = $connection->uploadMessage($new_msg, $uploaded_by);
		echo "here: " . $_POST['message_uploaded_by']; 

		unset($_POST['free_message']);
	}

?>
<html>
<head>
  <link href='../../../views/style/supervisor.css' rel='stylesheet' media='all'>
  <link href='../../../views/style/bootstrap.min.css' rel='stylesheet' media='all'>
</head>
  </html>
	<div class='panel panel-primary {{display_mode}}'>
		<div class='panel-body'>
			<div>
				<div id="menu"></div>
				<script type="text/javascript" src="nicEdit.js"></script>
				<script type="text/javascript">
					bkLib.onDomLoaded(function() { nicEditors.allTextAreas() });
				</script>

				<!-- Upload Free Messages Form -->
				<form action="<?php $_PHP_SELF ?>" method="post" enctype="multipart/form-data" target="dummyframe">
				<textarea name="free_message" style="width: 99%; height:250px">
					{{id}}
				</textarea>
				<input type="hidden" name="message_uploaded_by" value="{{id}}">
				<br />
					<button type='submit' class='btn btn-success' ng-click="refresh()">
						<span class="glyphicon glyphicon-upload"></span> Post Free Message
					</button>
				<br>
				</form>
			</div>
		</div>
	</div>

	<!--<iframe width="0" height="0" border="0" name="dummyframe" id="dummyframe" style="display:none"</iframe>-->