<h1><?=__('CKwan | TeamLeader Technical Challenge')?></h1>

<p>
	The API URL for the discount calculator is here <b><?=Router::url(array(
		'controller' => 'orders',
		'action' => 'discount'
	),true)?>.json</b>
</p>

<div class="row">
	<div class="col-md-5">
		<div class="alert alert-dark">
			<h4>For the installation:</h4>
			<p>
				<ul>
					<li>Place the complete folder into a web server or local http server</li>
					<li>Be sure that <b><?=dirname(APP)?>/app/tmp</b> is writable</li>
				</ul>
			</p>
		</div>
		<div class="alert alert-dark">
			<h4>General information</h4>
			<p>
				<ul>
					<li>In the ROOT path of the project, is the <b>discounts.json</b> file that have the configuration of the discounts</li>
					
					<li>In the ROOT path of the project, is the <b>data</b> folder that have the json of <i>customers</i> and <i>products</i></li>
					<li>In the ROOT path of the project, is the <b>example-orders</b> folder that have the json of the <i>orders</i> for testing</li>
					
				</ul>
			</p>
		</div>
	</div>

	<div class="col-md-7">
		<div class="alert alert-success">
			<h4>Client for API test:</h4>
			Please select the JSON file for the test:
			<form action="" method="post">
				<select class="form-control" name="file">
					<?php foreach($files as $file){?>
						<option  <?=(isset($_POST['file']) && $_POST['file']==$file) ? "selected" : ""?> value="<?=$file?>"><?=$file?></option>
					<?php } ?>
				</select><br>
				<input class="form-control" type="submit" value="<?=__('Send file to API')?>"/>
			</form>
			
			<?php if(isset($json_send)){?>
				<br>
			<div class="alert alert-warning">
				<h5>JSON Send:</h5>
				<pre><?=$json_send?></pre>
			</div>
			
			<div class="alert alert-primary">
				<h5>Response receive:</h5>
				<?=$json_response?>
			</div>
			<?php } ?>
		</div>
	</div>
</div>

