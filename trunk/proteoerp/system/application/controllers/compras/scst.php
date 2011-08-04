<?php
class Scst extends Controller {

	function scst(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id(201,1);
		$this->back_dataedit='compras/scst/datafilter';
	}

	function index() {
		redirect('compras/scst/datafilter');
	}

	function datafilter(){
		$this->rapyd->load('datagrid','datafilter');
		$this->rapyd->uri->keep_persistence();

		$atts = array(
		  'width'      => '800',
		  'height'     => '600',
		  'scrollbars' => 'yes',
		  'status'     => 'yes',
		  'resizable'  => 'yes',
		  'screenx'    => '0',
		  'screeny'    => '0'
		);

		$modbus=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo Proveedor',
				'nombre'=>'Nombre',
				'rif'=>'RIF'),
			'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed'),
			'titulo'  =>'Buscar Proveedor');

		$boton=$this->datasis->modbus($modbus);

		$filter = new DataFilter('Filtro de Compras');
		$filter->db->select=array('numero','fecha','recep','vence','depo','nombre','montoiva','montonet','montotot','reiva','proveed','control','serie','usuario');
		$filter->db->from('scst');

		$filter->fechad = new dateonlyField('Desde', 'fechad','d/m/Y');
		$filter->fechah = new dateonlyField('Hasta', 'fechah','d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause='where';
		$filter->fechad->db_name =$filter->fechah->db_name='fecha';
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator='>=';
		$filter->fechah->operator='<=';
		$filter->fechah->group='Fecha Emisi&oacute;n';
		$filter->fechad->group='Fecha Emisi&oacute;n';

		$filter->numero = new inputField('Factura', 'numero');
		$filter->numero->size=20;

		$filter->proveedor = new inputField('Proveedor','proveed');
		$filter->proveedor->append($boton);
		$filter->proveedor->db_name = 'proveed';
		$filter->proveedor->size=20;

		$filter->buttons('reset','search');
		$filter->build('dataformfiltro');

		$uri  = anchor('compras/scst/dataedit/show/<#control#>','<#numero#>');
		$uri2 = anchor_popup('formatos/verhtml/COMPRA/<#control#>','Ver HTML',$atts);
		$uri3 = anchor_popup('compras/scst/dataedit/show/<#control#>','<#serie#>',$atts);

		$grid = new DataGrid();
		$grid->order_by('fecha','desc');
		$grid->per_page = 30;


		$uri_2  = "<a href='javascript:void(0);' ";
		$uri_2 .= 'onclick="window.open(\''.base_url()."compras/scst/serie/<#control#>', '_blank', 'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');".'" heigth="600"'.'>';
		$uri_2 .= img(array('src'=>'images/estadistica.jpeg','border'=>'0','alt'=>'Consultar','height'=>'12','title'=>'Consultar'));
		$uri_2 .= "</a>";

		$uri_2  = anchor('compras/scst/dataedit/show/<#control#>',img(array('src'=>'images/editar.png','border'=>'0','alt'=>'Editar','height'=>'16','title'=>'Editar')));
		$uri_2 .= "&nbsp;";
		$uri_2 .= anchor('formatos/verhtml/COMPRA/<#control#>',img(array('src'=>'images/html_icon.gif','border'=>'0','alt'=>'HTML')));

		$uri_3  = "<a href='javascript:void(0);' onclick='javascript:scstserie(\"<#control#>\")'>";
		$propiedad = array('src' => 'images/engrana.png', 'alt' => 'Modifica Nro de Serie', 'title' => 'Modifica Nro. de Serie','border'=>'0','height'=>'12');
		$uri_3 .= img($propiedad);
		$uri_3 .= "</a>";

		$grid->column('Acci&oacute;n',$uri_2);
		$grid->column_orderby('Factura',$uri,'numero');
		$grid->column_orderby('Serie',$uri_3.'<#serie#>','serie');
		$grid->column_orderby('Fecha','<dbdate_to_human><#fecha#></dbdate_to_human>','fecha','align=\'center\'');
		$grid->column_orderby('Recep','<dbdate_to_human><#recep#></dbdate_to_human>','recep','align=\'center\'');
		$grid->column_orderby('Vence','<dbdate_to_human><#vence#></dbdate_to_human>','vence','align=\'center\'');
		$grid->column_orderby('Alma','depo','depo');
		$grid->column_orderby('Prv.','proveed','proveed');
		$grid->column_orderby('Nombre','nombre','nombre');
		$grid->column_orderby('Base' ,'<nformat><#montotot#></nformat>','montotot','align=\'right\'');
		$grid->column_orderby('IVA'   ,'<nformat><#montoiva#></nformat>','montoiva','align=\'right\'');
		$grid->column_orderby('Importe' ,'<nformat><#montonet#></nformat>','montonet','align=\'right\'');
		$grid->column_orderby('Ret.IVA' ,'<nformat><#reteiva#></nformat>','reteiva','align=\'right\'');
		$grid->column_orderby('Control','control','control');
		$grid->column_orderby('Usuario','usuario','usuario');
//		$grid->column('Vista',$uri2,'align=\'center\'');

		$grid->add("compras/scst/dataedit/create");
		$grid->build('datagridST');

//************ SUPER TABLE ************* 
		$extras = '
<script type="text/javascript">
//<![CDATA[
(function() {
	var mySt = new superTable("demoTable", {
	cssSkin : "sSky",
	fixedCols : 1,
	headerRows : 1,
	onStart : function () {	this.start = new Date();},
	onFinish : function () {document.getElementById("testDiv").innerHTML += "Finished...<br>" + ((new Date()) - this.start) + "ms.<br>";}
	});
})();
//]]>
</script>
';
		$style ='
<style type="text/css">
.fakeContainer { /* The parent container */
    margin: 5px;
    padding: 0px;
    border: none;
    width: 740px; /* Required to set */
    height: 320px; /* Required to set */
    overflow: hidden; /* Required to set */
}
</style>	
';
//****************************************

$script ='
<script type="text/javascript">
function scstserie(mcontrol){
	//var mserie=Prompt("Numero de Serie");
	//jAlert("Cancelado","Informacion");
	jPrompt("Numero de Serie","" ,"Cambio de Serie", function(mserie){
		if( mserie==null){
			jAlert("Cancelado","Informacion");
		} else {
			$.ajax({ url: "'.site_url().'compras/scst/scstserie/"+mcontrol+"/"+mserie,
				success: function(msg){
					jAlert("Cambio Finalizado "+msg,"Informacion");
					location.reload();
					}
			});
		}
	})
}
</script>';


		$data['style']   = $style;
		$data['style']  .= style('superTables.css');
		$data['style']	.= style("jquery.alerts.css");

		$data['extras']  = $extras;		

		$data['script']  = script('jquery.js');
		$data["script"] .= script("jquery.alerts.js");
		$data["script"] .= script('superTables.js');
		$data["script"] .= $script;
		

		$data['content'] = $grid->output;
		$data['filtro']  = $filter->output;
		$data['head']    = $this->rapyd->get_head();

		$data['title']   =heading('Compras');
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
 		//$this->rapyd->load('dataedit','datadetalle','fields','datagrid');
		$this->rapyd->load('dataobject','datadetails');

 		$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'descrip'=>'Descripci&oacute;n'),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'codigo_<#i#>','descrip'=>'descrip_<#i#>','pond'=>'costo_<#i#>','iva'=>'iva_<#i#>','peso'=>'sinvpeso_<#i#>'),
			'p_uri'=>array(4=>'<#i#>'),
			'script'  => array('post_modbus_sinv(<#i#>)'),
			'titulo'  =>'Buscar Art&iacute;culo',
			'where'   =>'activo = "S"');

		$sprvbus=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo Proveedor',
				'nombre'=>'Nombre',
				'rif'=>'RIF'),
			'filtro'  => array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=> array('proveed'=>'proveed', 'nombre'=>'nombre'),
			'script'  => array('post_modbus_sprv()'),
			'titulo'  =>'Buscar Proveedor');

		$do = new DataObject('scst');
		$do->rel_one_to_many('itscst', 'itscst', 'control');
		$do->pointer('sprv' ,'sprv.proveed=scst.proveed','sprv.nombre AS sprvnombre','left');
		$do->rel_pointer('itscst','sinv','itscst.codigo=sinv.codigo','sinv.descrip AS sinvdescrip, sinv.base1 AS sinvprecio1, sinv.base2 AS sinvprecio2, sinv.base3 AS sinvprecio3, sinv.base4 AS sinvprecio4, sinv.iva AS sinviva, sinv.peso AS sinvpeso,sinv.tipo AS sinvtipo');

		$edit = new DataDetails('Compras',$do);
		$edit->set_rel_title('itscst','Producto <#o#>');

		$edit->pre_process('insert' ,'_pre_insert');
		$edit->pre_process('update' ,'_pre_update');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->back_url = $this->back_dataedit;

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->mode='autohide';
		$edit->fecha->size = 10;

		$edit->vence = new DateonlyField('Vence', 'vence','d/m/Y');
		$edit->vence->insertValue = date('Y-m-d');
		$edit->vence->size = 10;

		$edit->serie = new inputField('N&uacute;mero', 'serie');
		$edit->serie->size = 15;
		$edit->serie->autocomplete=false;
		$edit->serie->rule = 'required';
		$edit->serie->mode = 'autohide';
		$edit->serie->maxlength=12;

		$edit->proveed = new inputField('Proveedor', 'proveed');
		$edit->proveed->size     = 10;
		$edit->proveed->maxlength= 5;
		$edit->proveed->autocomplete=false;
		$edit->proveed->readonly=true;
		$edit->proveed->rule     = 'required';
		$edit->proveed->append($this->datasis->modbus($sprvbus));

		$edit->nombre = new hiddenField('Nombre', 'nombre');
		$edit->nombre->size = 50;
		$edit->nombre->maxlength=40;

		$edit->cfis = new inputField('N&uacute;mero f&iacute;scal', 'nfiscal');
		$edit->cfis->size = 15;
		$edit->cfis->autocomplete=false;
		$edit->cfis->rule = 'required';
		$edit->cfis->maxlength=12;

		$edit->almacen = new  dropdownField ('Almac&eacute;n', 'depo');
		$edit->almacen->options('SELECT ubica, CONCAT(ubica,\' \',ubides) nombre FROM caub ORDER BY ubica');
		$edit->almacen->rule = 'required';
		$edit->almacen->style='width:145px;';

		$edit->tipo = new dropdownField('Tipo', 'tipo_doc');
		$edit->tipo->option('FC','Factura a Cr&eacute;dito');
		$edit->tipo->option('NC','Nota de Cr&eacute;dito');
		$edit->tipo->option('NE','Nota de Entrega');
		$edit->tipo->rule = 'required';
		$edit->tipo->style='width:140px;';

		$edit->peso  = new hiddenField('Peso', 'peso');
		$edit->peso->size = 20;
		$edit->peso->css_class='inputnum';

		$edit->orden  = new inputField('Orden', 'orden');
		$edit->orden->size = 15;

		$edit->credito  = new inputField('Cr&eacute;dito', 'credito');
		$edit->credito->size = 20;
		$edit->credito->css_class='inputnum';
		$edit->credito->when=array('show');

		$edit->montotot  = new inputField('Subtotal', 'montotot');
		$edit->montotot->onchange='cmontotot()';
		$edit->montotot->size = 15;
		$edit->montotot->css_class='inputnum';

		$edit->montoiva  = new hiddenField('IVA', 'montoiva');
		$edit->montoiva->size = 20;
		$edit->montoiva->css_class='inputnum';

		$edit->montonet  = new hiddenField('Total', 'montonet');
		//$edit->montonet->size = 20;
		//$edit->montonet->css_class='inputnum';

		$edit->anticipo  = new inputField('Anticipo', 'anticipo');
		$edit->anticipo->size = 20;
		$edit->anticipo->css_class='inputnum';
		$edit->anticipo->when=array('show');

		$edit->inicial  = new inputField('Contado', 'inicial');
		$edit->inicial->size = 20;
		$edit->inicial->css_class='inputnum';
		$edit->inicial->when=array('show');

		$edit->rislr  = new inputField('Retenci&oacute;n ISLR', 'reten');
		$edit->rislr->size = 20;
		$edit->rislr->css_class='inputnum';
		$edit->rislr->when=array('show');

		$edit->riva  = new inputField('Retenci&oacute;n IVA', 'reteiva');
		$edit->riva->size = 20;
		$edit->riva->css_class='inputnum';
		$edit->riva->when=array('show');

		$edit->mdolar  = new inputField('Monto US $', 'mdolar');
		$edit->mdolar->size = 20;
		$edit->mdolar->css_class='inputnum';

		//Campos para el detalle
		$edit->codigo = new inputField('C&oacute;digo', 'codigo_<#i#>');
		$edit->codigo->size=10;
		$edit->codigo->db_name='codigo';
		$edit->codigo->append($this->datasis->p_modbus($modbus,'<#i#>'));
		$edit->codigo->autocomplete=false;
		$edit->codigo->db_name  = 'codigo';
		$edit->codigo->rule     = 'required|callback_chcodigoa';
		$edit->codigo->rel_id   = 'itscst';

		$edit->descrip = new hiddenField('Descripci&oacute;n', 'descrip_<#i#>');
		$edit->descrip->size=30;
		$edit->descrip->db_name='descrip';
		$edit->descrip->maxlength=12;
		$edit->descrip->rel_id  ='itscst';

		$edit->cantidad = new inputField('Cantidad', 'cantidad_<#i#>');
		$edit->cantidad->size=10;
		$edit->cantidad->db_name='cantidad';
		$edit->cantidad->maxlength=60;
		$edit->cantidad->autocomplete=false;
		$edit->cantidad->onkeyup  ='importe(<#i#>)';
		$edit->cantidad->css_class='inputnum';
		$edit->cantidad->rule     = 'require|numeric';
		$edit->cantidad->rel_id   = 'itscst';

		$edit->costo = new inputField('Costo', 'costo_<#i#>');
		$edit->costo->css_class='inputnum';
		$edit->costo->rule   = 'require|numeric';
		$edit->costo->onkeyup='importe(<#i#>)';
		$edit->costo->size=12;
		$edit->costo->autocomplete=false;
		$edit->costo->db_name='costo';
		$edit->costo->rel_id ='itscst';

		$edit->importe = new inputField('Importe', 'importe_<#i#>');
		$edit->importe->db_name='importe';
		$edit->importe->size=15;
		$edit->importe->rel_id='itscst';
		$edit->importe->autocomplete=false;
		$edit->importe->onkeyup='costo(<#i#>)';
		$edit->importe->css_class='inputnum';

		$edit->sinvpeso = new hiddenField('', 'sinvpeso_<#i#>');
		$edit->sinvpeso->db_name = 'sinvpeso';
		$edit->sinvpeso->rel_id  = 'itscst';
		$edit->sinvpeso->pointer = true;

		$edit->iva = new hiddenField('Impuesto', 'iva_<#i#>');
		$edit->iva->db_name = 'iva';
		$edit->iva->rel_id='itscst';
		//fin de campos para detalle

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$recep=strtotime($edit->get_from_dataobjetct('recep'));
		$fecha=strtotime($edit->get_from_dataobjetct('fecha'));

		if($recep < $fecha){
			$accion="javascript:window.location='".site_url('compras/scst/actualizar/'.$edit->_dataobject->pk['control'])."'";
			$accio2="javascript:window.location='".site_url('compras/scst/precios/'.$edit->_dataobject->pk['control'])."'";

			$edit->button_status('btn_actuali','Actualizar'     ,$accion,'TL','show');
			$edit->button_status('btn_precio' ,'Asignar precios',$accio2,'TL','show');
		}
		$edit->buttons('save', 'undo', 'back','add_rel');
		$edit->build();

		$smenu['link']  =  barra_menu('201');
		$data['smenu']  =  $this->load->view('view_sub_menu', $smenu,true);
		$conten['form'] =& $edit;

		$data['script']  = script('jquery.js');
		$data['script'] .= script('jquery-ui.js');
		$data['script'] .= script('plugins/jquery.numeric.pack.js');
		$data['script'] .= script('plugins/jquery.floatnumber.js');
		$data['script'] .= phpscript('nformat.js');

		$data['head']    = $this->rapyd->get_head();
		$data['head']   .= style('redmond/jquery-ui-1.8.1.custom.css');

		$data['content'] = $this->load->view('view_compras', $conten,true);
		$data['title']   = heading('Compras');
		$this->load->view('view_ventanas', $data);
	}

	function chcodigoa($codigo){
		$cana=$this->datasis->dameval('SELECT COUNT(*) FROM sinv WHERE activo=\'S\' AND codigo='.$this->db->escape($codigo));
		if(empty($cana) || $cana==0){
			$this->validation->set_message('chcodigoa', 'El campo %s contiene un codigo no v&aacute;lido o inactivo');
			return false;
		}
		return true;
	}

	function dpto() {
		$this->rapyd->load("dataform");
		$campo='ccosto'.$this->uri->segment(4);
 		$script='
 		function pasar(){
			if($F("departa")!="-!-"){
				window.opener.document.getElementById("'.$campo.'").value = $F("departa");
				window.close();
			}else{
				alert("Debe elegir un departamento");
			}
		}';

		$form = new DataForm('');
		$form->script($script);
		$form->fdepar = new dropdownField("Departamento", "departa");
		$form->fdepar->option('-!-','Seleccion un departamento');
		$form->fdepar->options("SELECT depto,descrip FROM dpto WHERE tipo='G' ORDER BY descrip");
		$form->fdepar->onchange='pasar()';
		$form->build_form();

		$data['content'] =$form->output;
		$data['head']    =script('prototype.js').$this->rapyd->get_head();
		$data['title']   =heading('Seleccione un departamento');
		$this->load->view('view_detalle', $data);
	}

	function scstserie(){
		$serie   = $this->uri->segment($this->uri->total_segments());
		$control = $this->uri->segment($this->uri->total_segments()-1);
		if (!empty($serie)) {
			$this->db->simple_query("UPDATE scst SET serie='$serie' WHERE control='$control'");
			echo " con exito ";
		} else {
			echo " NO se guardo ";
		}
		logusu('SCST',"Cambia Nro. Serie $control ->  $serie ");
	}

	function autocomplete($campo,$cod=FALSE){
		if($cod!==false){
			$cod=$this->db->escape_like_str($cod);
			$qformato=$this->datasis->formato_cpla();
			$data['control']="SELECT control AS c1,fecha AS c2,numero AS c3,nombre AS c4 FROM scst WHERE control LIKE '$cod%' ORDER BY control DESC LIMIT 10";
			if(isset($data[$campo])){
				$query=$this->db->query($data[$campo]);
				if($query->num_rows() > 0){
					foreach($query->result_array() AS $row){
						echo $row['c1'].'|'.dbdate_to_human($row['c2']).'|'.$row['c3'].'|'.$row['c4']."\n";
					}
				}
			}
		}
	}

	function actualizar($control){
		$this->rapyd->uri->keep_persistence();
		$this->rapyd->load('dataform');

		$form = new DataForm("compras/scst/actualizar/$control/process");

		$form->cprecio = new  dropdownField ('Actualizar precios', 'cprecio');
		$form->cprecio->option('S','Si');
		$form->cprecio->option('N','No');
		$form->cprecio->rule = 'required';

		/*$form->fecha = new dateonlyField('Fecha de llegada de la mercancia', 'fecha','d/m/Y');
		$form->fecha->insertValue = date('Y-m-d');
		$form->fecha->rule='required';
		$form->fecha->size=10;*/

		$form->submit('btnsubmit','Actualizar');
		$accion="javascript:window.location='".site_url('compras/scst/dataedit/show/'.$control)."'";
		$form->button('btn_regre','Regresar',$accion,'BR','show');
		$form->build_form();

		if ($form->on_success()){
			$cprecio  = $form->cprecio->newValue;
			//$actualiza= $form->fecha->newValue;
			$cambio = ($cprecio=='S') ? true : false;
			
			$rt=$this->_actualizar($control,$cambio);
			if($rt===false){
				$data['content']  = $this->error_string.br();
			}else{
				$data['content']  = 'Compra actualizada'.br();
			}

			$data['content'] .= anchor('compras/scst/dataedit/show/'.$control,'Regresar');
		}else{
			$data['content'] = $form->output;
		}

		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading('Actualizar compra');
		$this->load->view('view_ventanas', $data);
	}

	function _actualizar($control,$cprecio){
		$error =0;
		$pasa=$this->datasis->dameval('SELECT COUNT(*) FROM scst WHERE actuali>=fecha AND control='.$this->db->escape($control));

		if($pasa==0){
			$SQL='SELECT transac,depo,proveed,fecha,vence, nombre,tipo_doc,nfiscal,fafecta,reteiva,
			cexento,cgenera,civagen,creduci,civared,cadicio,civaadi,cstotal,ctotal,cimpuesto,numero
			FROM scst WHERE control=?';
			$query=$this->db->query($SQL,array($control));
			if($query->num_rows()==1){
				$row     = $query->row_array();
				$transac = $row['transac'];
				$depo    = $row['depo'];
				$proveed = $row['proveed'];
				$fecha   = str_replace('-','',$row['fecha']);
				$vence   = $row['vence'];
				$reteiva = $row['reteiva'];
				$actuali = date('Ymd');

				$itdata=array();
				$sql='SELECT a.codigo,a.cantidad,a.importe,a.importe/a.cantidad AS costo,
					a.precio1,a.precio2,a.precio3,a.precio4
					FROM itscst AS a JOIN sinv AS b ON a.codigo=b.codigo WHERE a.control=?';
				$qquery=$this->db->query($sql,array($control));
				if($qquery->num_rows()>0){
					foreach ($qquery->result() as $itrow){

						//Actualiza el inventario
						$mSQL='UPDATE sinv SET 
							pond=(existen*IF(formcal="P",pond,IF(formcal="U",ultimo,GREATEST(pond,ultimo)))+'.$itrow->importe.')/(existen+'.$itrow->cantidad.'),
							existen=existen+'.$itrow->cantidad.' WHERE codigo='.$this->db->escape($itrow->codigo);
						$ban=$this->db->simple_query($mSQL);
						if(!$ban){ memowrite($mSQL,'scst'); $error++; }

						$mSQL='UPDATE itsinv SET existen=existen+'.$itrow->cantidad.' WHERE codigo='.$this->db->escape($itrow->codigo).' AND alma='.$this->db->escape($depo);
						$ban=$this->db->simple_query($mSQL);
						if(!$ban){ memowrite($mSQL,'scst'); $error++; }

						if($itrow->precio1>0 and $itrow->precio2>0 and $itrow->precio3>0 and $itrow->precio4>0){
							$mSQL='UPDATE sinv SET 
								prov3=prov2, prepro3=prepro2, pfecha3=pfecha2, prov2=prov1, prepro2=prepro1, pfecha2=pfecha1,
								prov1='.$this->db->escape($proveed).',
								prepro1='.$itrow->costo.',
								pfecha1='.$this->db->escape($fecha).',
								ultimo='.$itrow->costo.',
								precio1='.$this->db->escape($itrow->precio1).',
								precio2='.$this->db->escape($itrow->precio2).',
								precio3='.$this->db->escape($itrow->precio3).',
								precio4='.$this->db->escape($itrow->precio4).'
								WHERE codigo='.$this->db->escape($itrow->codigo);
							$ban=$this->db->simple_query($mSQL);
							if(!$ban){ memowrite($mSQL,'ordi'); $error++; }
    
							if(!$cprecio){
								$mSQL='UPDATE sinv SET 
									base1=ROUND(precio1*10000/(100+iva))/100, 
									base2=ROUND(precio2*10000/(100+iva))/100, 
									base3=ROUND(precio3*10000/(100+iva))/100, 
									base4=ROUND(precio4*10000/(100+iva))/100, 
									margen1=ROUND(10000-(IF(formcal="P",pond,IF(formcal="U",ultimo,GREATEST(pond,ultimo)))*10000/base1))/100,
									margen2=ROUND(10000-(IF(formcal="P",pond,IF(formcal="U",ultimo,GREATEST(pond,ultimo)))*10000/base2))/100,
									margen3=ROUND(10000-(IF(formcal="P",pond,IF(formcal="U",ultimo,GREATEST(pond,ultimo)))*10000/base3))/100,
									margen4=ROUND(10000-(IF(formcal="P",pond,IF(formcal="U",ultimo,GREATEST(pond,ultimo)))*10000/base4))/100,
									activo="S"
								WHERE codigo='.$this->db->escape($itrow->codigo);
								$ban=$this->db->simple_query($mSQL);
								if(!$ban){ memowrite($mSQL,'ordi'); $error++; }
							}
						}
						//Fin de la actualizacion de inventario
					}
				}

				//Carga la CxP
				$mSQL='DELETE FROM sprm WHERE transac='.$this->db->escape($transac);
				$ban=$this->db->simple_query($mSQL);
				if(!$ban){ memowrite($mSQL,'ordi'); $error++; }

				$sprm=array();
				$causado = $this->datasis->fprox_numero('ncausado');
				$sprm['cod_prv']  = $proveed;
				$sprm['nombre']   = $row['nombre'];
				$sprm['tipo_doc'] = $row['tipo_doc'];
				$sprm['numero']   = $row['numero'];
				$sprm['fecha']    = $actuali;
				$sprm['vence']    = $vence;
				$sprm['monto']    = $row['ctotal'];
				$sprm['impuesto'] = $row['cimpuesto'];
				$sprm['abonos']   = 0;
				$sprm['observa1'] = 'FACTURA DE COMPRA';
				$sprm['reteiva']  = $reteiva;
				$sprm['causado']  = $causado;
				$sprm['estampa']  = date('Y-m-d H:i:s');
				$sprm['usuario']  = $this->session->userdata('usuario');
				$sprm['hora']     = date('H:i:s');
				$sprm['transac']  = $transac;
				//$sprm['montasa']  = $row['cimpuesto'];
				//$sprm['impuesto'] = $row['cimpuesto'];
				//$sprm['impuesto'] = $row['cimpuesto'];

				$mSQL=$this->db->insert_string('sprm', $sprm);
				$ban =$this->db->simple_query($mSQL);
				if(!$ban){ memowrite($mSQL,'ordi'); $error++; }
				//Fin de la carga de la CxP

				//Inicio de la retencion
				if($reteiva>0){
					$niva    = $this->datasis->fprox_numero('niva');
					$ivaplica=$this->datasis->ivaplica($fecha);

					$riva['nrocomp']    = $niva;
					$riva['emision']    = ($fecha > $actuali) ? $fecha : $actuali;
					$riva['periodo']    = substr($riva['emision'],0,6) ;
					$riva['tipo_doc']   = $row['tipo_doc'];
					$riva['fecha']      = $fecha; 
					$riva['numero']     = $row['numero'];
					$riva['nfiscal']    = $row['nfiscal'];
					$riva['afecta']     = $row['fafecta'];
					$riva['clipro']     = $proveed;
					$riva['nombre']     = $row['nombre'];
					$riva['rif']        = $this->datasis->dameval('SELECT rif FROM sprv WHERE proveed='.$this->db->escape($proveed));
					$riva['exento']     = $row['cexento'];
					$riva['tasa']       = $ivaplica['tasa'];
					$riva['tasaadic']   = $ivaplica['sobretasa'];
					$riva['tasaredu']   = $ivaplica['redutasa'];
					$riva['general']    = $row['cgenera'];
					$riva['geneimpu']   = $row['civagen'];
					$riva['adicional']  = $row['cadicio'];
					$riva['adicimpu']   = $row['civaadi'];
					$riva['reducida']   = $row['creduci'];
					$riva['reduimpu']   = $row['civared'];
					$riva['stotal']     = $row['cstotal'];
					$riva['impuesto']   = $row['cimpuesto'];
					$riva['gtotal']     = $row['ctotal'];
					$riva['reiva']      = $reteiva;
					$riva['transac']    = $transac;
					$riva['estampa']    = date('Y-m-d H:i:s');
					$riva['hora']       = date('H:i:s');
					$riva['usuario']    = $this->session->userdata('usuario');

					$mSQL=$this->db->insert_string('riva', $riva);
					$ban =$this->db->simple_query($mSQL);
					if(!$ban){ memowrite($mSQL,'scst'); $error++; }
				}//Fin de la retencion

				$mSQL='UPDATE scst SET `actuali`='.$actuali.', `recep`='.$actuali.' WHERE `control`='.$this->db->escape($control);
				$ban=$this->db->simple_query($mSQL);
				if(!$ban){ memowrite($mSQL,'scst'); $error++; }
			}else{
				$this->error_string='Compra no existe';
				return false;
			}
		}else{
			$this->error_string='No se puede actualizar una compra que ya fue actualizada';
			return false;
		}
	}

	function _pre_del($do){
		$codigo=$do->get('comprob');
		$chek =   $this->datasis->dameval("SELECT COUNT(*) FROM cpla WHERE codigo LIKE '$codigo.%'");
		$chek +=  $this->datasis->dameval("SELECT COUNT(*) FROM itcasi WHERE cuenta='$codigo'");

		if ($chek > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='Plan de Cuenta tiene derivados o movimientos';
			return False;
		}
		return True;
	}	
	function _pre_insert($do){
		$control = $this->datasis->fprox_numero('nscst');
		$transac = $this->datasis->fprox_numero('ntransa');
		$fecha   = $do->get('fecha');
		$numero  = substr($do->get('serie'),-8);
		$usuario = $do->get('usuario');
		$proveed = $do->get('proveed');
		$depo    = $do->get('depo');
		$estampa = date('Ymd');
		$hora    = date("H:i:s");

		$iva=$stotal=0;
		$cana=$do->count_rel('itscst');
		for($i=0;$i<$cana;$i++){
			$itcodigo  = $do->get_rel('itscst','codigo'  ,$i);
			$itcana    = $do->get_rel('itscst','cantidad',$i);
			$itprecio  = $do->get_rel('itscst','costo'   ,$i);
			$itiva     = $do->get_rel('itscst','iva'     ,$i);
			$itimporte = $itprecio*$itcana;
			$iiva      = $itimporte*($itiva/100);
			
			$mSQL='SELECT ultimo,existen,pond,standard,formcal,margen1,margen2,margen3,margen4 FROM sinv WHERE codigo='.$this->db->escape($itcodigo);
			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				$row = $query->row();


				$costo_pond=(($row->pond*$row->existen)+($itcana*$itprecio))/($itcana+$row->existen);
				$costo_ulti=$itprecio;
				switch($row->formcal){
				case 'P':
					$costo=$costo_pond;
					break;
				case 'U':
					$costo=$costo_ulti;
					break;
				case 'S':
					$costo=$row->standard;
					break;
				default:
					$costo=($costo_pond>$costo_ulti) ? $costo_pond : $costo_ulti;
				}
			}
			for($o=1;$o<5;$o++){
				$obj='margen'.$o;
				$pp=(($costo*100)/(100-$row->$obj))*(1+($itiva/100));
				$do->set_rel('itscst','precio'.$o ,$pp,$i);
			}

			$do->set_rel('itscst','importe' ,$itimporte,$i);
			$do->set_rel('itscst','montoiva',$iiva     ,$i);
			$do->set_rel('itscst','ultimo'  ,$row->ultimo,$i);
			$do->set_rel('itscst','fecha'   ,$fecha    ,$i);
			$do->set_rel('itscst','numero'  ,$numero   ,$i);
			$do->set_rel('itscst','proveed' ,$proveed  ,$i);
			$do->set_rel('itscst','depo'    ,$depo     ,$i);
			$do->set_rel('itscst','control' ,$control  ,$i);
			$do->set_rel('itscst','transac' ,$transac  ,$i);
			$do->set_rel('itscst','usuario' ,$usuario  ,$i);
			$do->set_rel('itscst','hora'    ,$hora     ,$i);
			$do->set_rel('itscst','estampa' ,$estampa  ,$i);

			$iva    += $iiva ;
			$stotal += $itimporte;
		}
		$gtotal=$stotal+$iva;
		$do->set('numero'  ,$numero);
		$do->set('control' ,$control);
		$do->set('estampa' ,$estampa);
		$do->set('hora'    ,$hora);
		$do->set('transac' ,$transac);
		$do->set('montotot',round($stotal,2));
		$do->set('montonet',round($gtotal,2));
		$do->set('montoiva',round($iva   ,2));
		//$do->set('estampa', 'CURDATE()', FALSE);
		//$do->set('hora'   , 'CURRENT_TIME()', FALSE);

		return true;
	}

	function _post_insert($do){
		$codigo  = $do->get('numero');
		$control = $do->get('control');
		logusu('snte',"Compra $codigo control $control CREADA");
	}

	function _pre_update($do){
		return false;
	}

	function _post_delete($do){
		$codigo=$do->get('numero');
		logusu('scst',"Compra $codigo ELIMINADA");
	}
}