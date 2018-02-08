<div class='col-sm-8 text-left'>
	<h3>Free Message Upload</h3>
	<hr>

	<!-- Upload Document Panel -->
	<div class='panel panel-primary {{display_mode}}'>
		<div class='panel-heading'><label>Message Description <span class="glyphicon glyphicon-envelope"></span></label></div>
		<div class='panel-body'>
			<div>
				<!-- Upload Document Form -->
				<form method="post" action="../app/php/postMessage.php" target="dummyframe">
					<label>Message Title: </label>
					<input class='form-control fields {{display_mode}}' type="text" 
						   placeholder="Title ..." name="freeTitle" required />
						<br>
					<label>Message Description: </label>
					<input class='form-control fields {{display_mode}}' type="text" 
						   placeholder="Short Detail ..." name="message_description" required />
						<br>
					<label>Message: </label>
					<textarea class='form-control fields {{display_mode}}'
							  style='width:100%'
							  rows="12" name='freeMsg' placeholder="Free Text ..." required />
					</textarea>
					<input type='hidden' name='message_by' value="{{id}}" />
					<br>
					<input type='checkbox' name='pinned_message' />
					<label>Pin Message <span class='glyphicon glyphicon-pushpin'></span></label>
					<br><br>
					<button type='submit' class='btn btn-success' ng-click='refresh()'><span class="glyphicon glyphicon-upload"></span> Post Message</button>
					  <!-- success/error message for upload message function -->
					<br>
				</form>
			</div>
		</div>
	</div>

	<div class='panel panel-primary' ng-init='getMessages()'>
		<div class='panel-heading'>Edit Messages</div>

		<!-- TO DO: Overflow not detected until page reloads -->
		<div class='panel-body {{display_mode}}' style="height:350px; overflow-y:scroll">
			<div>
				<table class="table table-striped {{display_mode}} table-hover" style='margin:auto; width:100%'>
					<form class="form-inline">
						<div class="form-group">
							<label>Search</label>
							<input  type="text" ng-model="msgSearch" class="form-control fields {{display_mode}}" placeholder="Search" />
						</div>
					</form>
					<thead>
						<tr>
							<th class="text-center">#</th>
							<th ng-click="sortTable('title')">Title</th>
							<th ng-click="sortTable('description')">Description</th>
							<th ng-click="sortTable('created_at')">Posted</th>
							<th class="text-center">Edit</th>
						</tr>
					</thead>
					<tbody>
						<!-- <tr ng-repeat='d in documents | orderBy:orderBy |filter:cat_filter.name'> -->
						<tr {{display_mode}} ng-repeat="m in messages | orderBy:orderBy | filter:msgSearch">
							<td class='text-center'>{{$index + 1}}</td>
							<td>{{m.title}}</td>
							<td>{{m.description}}</td>
							<td>{{m.created_at}}</td>
							<td class='text-center'><button class="btn-default" ng-click="editMessage(m.id, id, m.title, m.message, m.description)"><span class="glyphicon glyphicon-edit"></span> Edit</button></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="modal fade" id="editMessageModal" role="dialog" ng-controller='supervisorCtrl'>
		<div class="modal-dialog">

			<!-- Modal content-->
			<div class="modal-content {{display_mode_modal}}" >
				<div class="modal-header " style="padding:15px 15px;">
					<button type="button" class="close" data-dismiss="modal" ng-click='updateErrorMessage = updateSuccessMessage = false'>&times;</button>
					<h4><span class="glyphicon glyphicon-pencil"></span> Edit Message </h4>
				</div>
				<div class="modal-body" style="padding:40px 50px;">
					<button ng-click='removeMessage()' class="btn btn-danger pull-right"><span class="glyphicon glyphicon-remove"></span>Delete Message</button>
					<br>
					<form role="form" ng-submit='updateMessage()'>
						<div class="form-group">
							<label>Message Title</label>
							<input class='form-control fields {{display_mode}}' type="text" ng-model="updateTitle" required />
						</div>
						<div class="form-group">
							<lable>Message Description</lable>
							<input class='form-control fields {{display_mode}}' type="text" ng-model="updateDescription" required />
						</div>
						<div class="form-group" >
							<label>Message: </label>
							<textarea class='form-control fields {{display_mode}}' style='width:100%' rows="15" ng-model='updateFreeMsg' required /></textarea>
						<input type='hidden' ng-model='updateMsgId' />
						<input type='hidden' ng-model='updateOfficerId' />
						</div>

						<br>
						<!-- TO DO: Don't show successful update message when no changes were made to the user -->
						<button type="submit" class="btn btn-success btn-block">Update Message</button>
					<br>
						<br>
					</form>
				</div>
			</div>
		</div>
	</div>
	<iframe width="0" height="0" border="0" name="dummyframe" id="dummyframe" style="display:none"</iframe>
</div>