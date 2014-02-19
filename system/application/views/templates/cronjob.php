<html>
<head>
<?php $TC->show_head() ?>
</head>
<body>

	<div class="main">

		<div class="top"><?php $TC->show_modules('top', FALSE, TRUE); ?></div>

		<div class="container">

			<div class="left"><?php $TC->show_modules('left', FALSE, TRUE); ?></div>
			<div class="content"><?php $TC->show_content(); ?></div>
			<div class="right"><?php $TC->show_modules('right', FALSE, TRUE); ?></div>

		</div>

		<div class="bottom"><?php $TC->show_modules('bottom', FALSE, TRUE); ?></div>

	</div>

</body>
</html>
