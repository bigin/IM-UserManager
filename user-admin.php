<?php if(!defined('IN_GS')){ die('you cannot load this page directly.'); } ?>
<!DOCTYPE html>
<html>
	<head>
		<title>User management with ItemManager</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- UIkit CSS -->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.0.0-rc.9/css/uikit.min.css" />
		<!-- UIkit JS -->
		<script src="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.0.0-rc.9/js/uikit.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.0.0-rc.9/js/uikit-icons.min.js"></script>
	</head>
	<body>
	<div class="uk-section">
		<div class="uk-container uk-container-small">
			<h1>User management with ItemManager</h1>
			<?php
			echo $view->renderMessages();
			echo $view->renderContent();
			?>
		</div>
	</div>
	</body>
</html>