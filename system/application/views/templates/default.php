<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta content="utf-8" http-equiv="encoding">
    <?php $TC->show_head() ?>
</head>
<body>

	<div class="main">

		<div class="top"><?php $TC->show_modules('top', FALSE, TRUE); ?></div>

		<div class="container">

			<table width="100%" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td class="left"><?php $TC->show_modules('left', FALSE, TRUE); ?></td>
					<td class="content"><?php $TC->show_content(); ?></td>
					<td class="right"><?php $TC->show_modules('right', FALSE, TRUE); ?></td>
				</tr>
			</table>

		</div>

		<div class="bottom"><?php $TC->show_modules('bottom', FALSE, TRUE); ?></div>

	</div>

</body>
</html>
