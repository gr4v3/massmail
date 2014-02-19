<html>
<head>
<?php $TC->show_head() ?>
<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript">google.load("language", "1");</script>
<style type="text/css">
	body {
		background-color: #f5f5f5;
		margin: 40px;
		font: 13px/20px normal Helvetica, Arial, sans-serif;
		color: #4F5155;
	}

	a {
		color: #003399;
		background-color: transparent;
		font-weight: normal;
	}

	h1 {
		color: #444;
		background-color: transparent;
		border-bottom: 1px solid #D0D0D0;
		font-size: 19px;
		font-weight: normal;
		margin: 0 0 14px 0;
		padding: 14px 15px 10px 15px;
	}

	code {
		font-family: Consolas, Monaco, Courier New, Courier, monospace;
		font-size: 12px;
		background-color: #f9f9f9;
		border: 1px solid #D0D0D0;
		color: #002166;
		display: block;
		margin: 14px 0 14px 0;
		padding: 12px 10px 12px 10px;
	}

	#body{
		margin: 0 15px 0 15px;
	}

	p.footer{
		text-align: center;
		font-size: 11px;
		border-top: 1px solid #D0D0D0;
		line-height: 20px;
		padding: 0 10px 0 10px;
		margin: 20px 0 0 0;
	}

	#container{
		background-color: #fff;
		width:800px;
		margin:0 auto;
		border: 1px solid #D0D0D0;
		-webkit-box-shadow: 0 0 8px #D0D0D0;
	}

	.reason td {
		text-align:right;
	}

	.error {
		position:absolute;
		visibility:hidden;
		background-color:red;
	}
</style>
</head>
<body>
<div id="container">
	<h1>MAILSERVER import system</h1>
	<div id="body">
		<p>
			<div class="main">

				<div class="top"><?php $TC->show_modules('top', FALSE, TRUE); ?></div>

					<div class="container">

						<table width="100%" cellpadding="0" cellspacing="0" border="0">
							<tr>
								<td class="content"><?php $TC->show_content(); ?></td>
							</tr>
							<tr>
								<td class="left"><?php $TC->show_modules('left', FALSE, TRUE); ?></td>
							</tr>
							<tr>
								<td class="right"><?php $TC->show_modules('right', FALSE, TRUE); ?></td>
							</tr>
						</table>

					</div>

				<div class="bottom"><?php $TC->show_modules('bottom', FALSE, TRUE); ?></div>

			</div>
		</p>
	</div>
</div>
</body>
</html>