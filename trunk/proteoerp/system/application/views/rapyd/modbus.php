<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-type" content="text/html; charset=<?=$this->config->item('charset'); ?>" />
<title>Proteo ERP | <?=$encab?></title>
<?=style("modbus.css");?>
<?=$rapyd_head?>
</head>
<body>
<div id='cerrar'><?=image('cerrar.png','Cerrar ventana',array('onclick'=>'window.close();')); ?></div>
<div id='encab'>
	<table>
		<tr>
			<td><?=image('logo.png','Proteo ERP', array("height"=>"50px" )); ?></td>
			<td width='100%' align='center'><div style='font-size: 22px;font-weight: bold;'><?=$encab ?></div></td>
		</tr>
	</table>
</div>
<div id="content">
	<div class="left"><?=$lista ?></div>
	<div class="right"><?=$content?>
		<div class="line"></div>
		<div class="code"><?=$code?></div>
	</div>
	<div class="line"></div>
	<div id="footer"></div>
</div>
</body>
</html>