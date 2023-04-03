<?php
require_once('config.inc.php');
?>
<footer class="container">
	<div class="footer">
		<ul class="breadcrumb">
			<li>&copy;<?php echo date('Y')?>&nbsp;<a href="<?= BASE_URL ?>/">QuickAuth</a></li>

		</ul>
	</div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js"></script>

<script>
	window.config = {
	'BASE_URL': '<?php echo BASE_URL;?>' /* No '/' at the end */
	};
</script>
<script src="static/util.js"></script>
<script src="static/script.js"></script>

