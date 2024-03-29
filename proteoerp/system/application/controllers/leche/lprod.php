<?php
/**
 * ProteoERP
 *
 * @autor    Andres Hocevar
 * @license  GNU GPL v3
*/
//reproceso en  cierre
class Lprod extends Controller {
	var $mModulo = 'LPROD';
	var $titp    = 'Control de producci&oacute;n';
	var $tits    = 'Control de producci&oacute;n';
	var $url     = 'leche/lprod/';

	function Lprod(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		$this->datasis->modulo_nombre( 'LPROD', $ventana=0 );
	}

	function index(){
		$this->instalar();
		$this->datasis->modintramenu( 800, 600, substr($this->url,0,-1) );
		$this->datasis->creaintramenu(array('modulo'=>'223','titulo'=>'Control de Producción','mensaje'=>'Control de Producción','panel'=>'LECHE','ejecutar'=>'leche/lprod','target'=>'popu','visible'=>'S','pertenece'=>'2','ancho'=>900,'alto'=>600));
		redirect($this->url.'jqdatag');
	}

	//***************************
	//Layout en la Ventana
	//
	//***************************
	function jqdatag(){

		$grid = $this->defgrid();
		$grid->setHeight('150');
		$param['grids'][] = $grid->deploy();

		$grid1   = $this->defgridit();
		$grid1->setHeight('180');
		$param['grids'][] = $grid1->deploy();

		// Configura los Paneles
		$readyLayout = $grid->readyLayout2( 212, 210, $param['grids'][0]['gridname'],$param['grids'][1]['gridname']);

		//Funciones que ejecutan los botones
		$bodyscript = $this->bodyscript( $param['grids'][0]['gridname'], $param['grids'][1]['gridname'] );

		//Botones Panel Izq
		$grid->wbotonadd(array('id'=>'imprimir', 'img'=>'assets/default/images/print.png','alt' => 'Reimprimir','label'=>'Reimprimir Documento'));

		if($this->datasis->sidapuede('LCIERRE','INCLUIR%')){
			$grid->wbotonadd(array('id'=>'bacidez', 'img'=>'images/lab.png'     ,'alt' => 'Colocar acidez'              ,'label'=>'1-Definir Producto'));
			$grid->wbotonadd(array('id'=>'bcierre', 'img'=>'images/candado.png' ,'alt' => 'Cierre Producci&oacute;n'    ,'label'=>'2-Cierre Producci&oacute;n'));
			$grid->wbotonadd(array('id'=>'bconsol', 'img'=>'images/acuerdo.png' ,'alt' => 'Consolidar Producci&oacute;n','label'=>'3-Finalizar Producci&oacute;n'));
		}
		$WestPanel = $grid->deploywestp();

		//Panel Central
		$centerpanel = $grid->centerpanel( $id = 'radicional', $param['grids'][0]['gridname'], $param['grids'][1]['gridname'] );

		$adic = array(
			array('id'=>'fedita' , 'title'=>'Agregar/Editar Producci&oacute;n'),
			array('id'=>'fshow'  , 'title'=>'Mostrar producci&oacute;n'),
			array('id'=>'fciecon', 'title'=>'Editar Producci&oacute;n')
		);

		$SouthPanel = $grid->SouthPanel($this->datasis->traevalor('TITULO1'), $adic);

		$param['WestPanel']    = $WestPanel;
		//$param['script']       = script('plugins/jquery.ui.autocomplete.autoSelectOne.js');
		$param['readyLayout']  = $readyLayout;
		$param['SouthPanel']   = $SouthPanel;
		$param['listados']     = $this->datasis->listados('LPROD', 'JQ');
		$param['otros']        = $this->datasis->otros('LPROD', 'JQ');
		$param['centerpanel']  = $centerpanel;
		$param['temas']        = array('proteo','darkness','anexos1');
		$param['bodyscript']   = $bodyscript;
		$param['tabs']         = false;
		$param['encabeza']     = $this->titp;
		$param['tamano']       = $this->datasis->getintramenu( substr($this->url,0,-1) );
		$this->load->view('jqgrid/crud2',$param);

	}

	//***************************
	//Funciones de los Botones
	//***************************
	function bodyscript( $grid0, $grid1 ){
		$bodyscript = '<script type="text/javascript">';

		$bodyscript .= '
		function lprodadd() {
			$.post("'.site_url($this->url.'dataedit/create').'",
			function(data){
				$("#fedita").html(data);
				$("#fedita").dialog( "open" );
			});
		};';


		$bodyscript .= '
		function lproddel() {
			var id = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if(id){
				if(confirm(" Seguro desea eliminar el registro?")){
					var ret    = $("#newapi'.$grid0.'").getRowData(id);
					$.post("'.site_url($this->url.'dataedit/do_delete').'/"+id, function(data){
						$("#fborra").html(data);
						$("#fborra").dialog( "open" );
					});
				}
			}else{
				$.prompt("<h1>Por favor Seleccione un Registro</h1>");
			}
		};';


		$bodyscript .= '
		function lprodshow() {
			var id = jQuery("#newapi'. $grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if(id){
				$.post("'.site_url($this->url.'dataedit/show').'/"+id,
					function(data){
						$("#fshow").html(data);
						$("#fshow").dialog( "open" );
					});
			}else{
				$.prompt("<h1>Por favor Seleccione un registro</h1>");
			}
		};';

		$bodyscript .= '
		function lprodedit() {
			var id = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if(id){
				var ret = $("#newapi'.$grid0.'").getRowData(id);
				if(ret.status!="A"){
					$.prompt("<h1>Solo se pueden modificar las producciones abiertas</h1>");
				}else{
					$.post("'.site_url($this->url.'dataedit/modify').'/"+id, function(data){
						$("#fedita").html(data);
						$("#fedita").dialog( "open" );
					});
				}
			}else{
				$.prompt("<h1>Por favor Seleccione un Registro</h1>");
			}
		};';


		//Wraper de javascript
		$bodyscript .= '
		$(function(){
			$("#dialog:ui-dialog").dialog( "destroy" );
			var mId = 0;
			var montotal = 0;
			var ffecha = $("#ffecha");
			var grid = jQuery("#newapi'.$grid0.'");
			var s;
			var allFields = $( [] ).add( ffecha );
			var tips = $( ".validateTips" );
			s = grid.getGridParam(\'selarrrow\');
			';

		$bodyscript.= '
		$("#imprimir").click( function(){
			var id = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if(id){
				var ret = jQuery("#newapi'.$grid0.'").jqGrid(\'getRowData\',id);
				window.open(\''.site_url('formatos/ver/LPROD/').'/\'+id, \'_blank\', \'width=900,height=800,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-450), screeny=((screen.availWidth/2)-400)\');
			}else{
				$.prompt("<h1>Por favor Seleccione una producci&oacute;n</h1>");
			}
		});';

		$bodyscript .= '
		$("#bcierre").click( function(){
			var id = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if( id ){
				var ret = $("#newapi'.$grid0.'").getRowData(id);
				if(ret.status != "A"){
					$.prompt("<h1>Solo se pueden cerrar las producciones abiertas</h1>");
				} else {
					if ( ret.acidez != 0 ) {
						mId = id;
						$.post("'.site_url($this->url.'datacie/modify').'/"+id, function(data){
							$("#fciecon").html(data);
							$("#fciecon").dialog( { title:"Cierre de Produccion", width:350, height:250} );
							$("#fciecon").dialog( "open" );
						});
					} else {
						$.prompt("<h1>Debe definir el producto primero</h1>");
					}
				}
			}else{
				$.prompt("<h1>Por favor Seleccione un Registro</h1>");
			}
		});';

		$bodyscript .= '
		$("#bacidez").click( function(){
			var id = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if(id){
				var ret = $("#newapi'.$grid0.'").getRowData(id);
				if(ret.status!="A"){
					$.prompt("<h1>Solo se pueden trabajar con las producciones abiertas</h1>");
				}else{
					mId = id;
					$.post("'.site_url($this->url.'dataacid/modify').'/"+id, function(data){
						$("#fciecon").html(data);
						$("#fciecon").dialog( { title:"Definir Producto", width:400, height:250 } );
						$("#fciecon").dialog( "open" );
					});
				}
			}else{
				$.prompt("<h1>Por favor Seleccione un Registro</h1>");
			}
		});';


		$bodyscript .= '
		$("#bconsol").click( function(){
			var id = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if(id){
				var ret = $("#newapi'.$grid0.'").getRowData(id);
				if(ret.status=="C"){
					mId = id;
					$.post("'.site_url($this->url.'datacon/modify').'/"+id, function(data){
						$("#fciecon").html(data);
						$("#fciecon").dialog( { title:"Finalizacion de Produccion", width:400, height:400 } );
						$("#fciecon").dialog( "open" );
					});
				}else{
					$.prompt("<h1>Debe cerrar primero la producci&oacute;n para consolidarla</h1>");
				}
			}else{
				$.prompt("<h1>Por favor Seleccione un Registro</h1>");
			}
		});';

			//var id = jQuery("#newapi'. $grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			//if (id)	{
			//	var ret      = jQuery("#newapi'.$grid0.'").jqGrid(\'getRowData\',id);
			//	var idcierre = $.ajax({ type: "POST", url: "'.site_url($this->url.'getcierre').'/"+ret.fecha, async: false }).responseText;
            //
			//	if(idcierre == "0"){
			//		$.post("'.site_url('leche/lcierre/dataedit/').'/"+ret.fecha+"/create",
			//		function(data){
			//			$("#fedita").html(data);
			//			$("#fedita").dialog( "open" );
			//		});
			//	}else{
			//		var status = $.ajax({ type: "POST", url: "'.site_url($this->url.'getstatus').'/"+ret.fecha, async: false }).responseText;
            //
			//		if(status=="A"){
			//			$.post("'.site_url('leche/lcierre/dataedit/').'/"+ret.fecha+"/modify/"+idcierre,
			//			function(data){
			//				$("#fedita").html(data);
			//				$("#fedita").dialog( "open" );
			//			});
			//		}else{
			//			$.post("'.site_url('leche/lcierre/dataedit/').'/show/"+idcierre,
			//			function(data){
			//				$("#fshow").html(data);
			//				$("#fshow").dialog( "open" );
			//			});
			//		}
			//	}
			//} else {
			//	$.prompt("<h1>Por favor Seleccione un Movimiento</h1>");
			//}

		$bodyscript .= '
		$("#fshow").dialog({
			autoOpen: false, height: 500, width: 700, modal: true,
			buttons: {
				"Aceptar": function() {
					$("#fshow").html("");
					$( this ).dialog( "close" );
				},
			},
			close: function() {
				$("#fshow").html("");
				allFields.val( "" ).removeClass( "ui-state-error" );
			}
		});';

		$bodyscript .= '
		$("#fedita").dialog({
			autoOpen: false, height: 500, width: 750, modal: true,
			buttons: {
			"Guardar": function() {
				var bValid = true;
				var murl = $("#df1").attr("action");
				allFields.removeClass( "ui-state-error" );
				$.ajax({
					type: "POST", dataType: "html", async: false,
					url: murl,
					data: $("#df1").serialize(),
					success: function(r,s,x){
						try{
							var json = JSON.parse(r);
							if (json.status == "A"){
								apprise("Registro Guardado");
								$( "#fedita" ).dialog( "close" );
								grid.trigger("reloadGrid");
								//'.$this->datasis->jwinopen(site_url('formatos/ver/LRECE').'/\'+res.id+\'/id\'').';
								return true;
							}else{
								apprise(json.mensaje);
							}
						}catch(e){
							$("#fedita").html(r);
						}
					}
				})
			},
			"Cancelar": function() {
					$("#fedita").html(""); $( this ).dialog( "close" );
				}
			},
			close: function() { $("#fedita").html(""); allFields.val( "" ).removeClass( "ui-state-error" );}
		});';

		$bodyscript .= '
		$("#fciecon").dialog({
			autoOpen: false, height: 420, width: 320, modal: true,
			buttons: {
			"Guardar": function() {
				var bValid = true;
				var murl = $("#df1").attr("action");
				allFields.removeClass( "ui-state-error" );
				$.ajax({
					type: "POST", dataType: "html", async: false,
					url: murl,
					data: $("#df1").serialize(),
					success: function(r,s,x){
						try{
							var json = JSON.parse(r);
							if (json.status == "A"){
								apprise("Registro Guardado");
								$( "#fciecon" ).dialog( "close" );
								grid.trigger("reloadGrid");
								//'.$this->datasis->jwinopen(site_url('formatos/ver/LRECE').'/\'+res.id+\'/id\'').';
								return true;
							}else{
								apprise(json.mensaje);
							}
						}catch(e){
							$("#fciecon").html(r);
						}
					}
				})
			},
			"Cancelar": function() {
					$("#fciecon").html(""); $( this ).dialog( "close" );
				}
			},
			close: function() { $("#fciecon").html(""); allFields.val( "" ).removeClass( "ui-state-error" );}
		});';

		$bodyscript .= '});';
		$bodyscript .= '</script>';
		return $bodyscript;
	}

	//***************************
	//Definicion del Grid y la Forma
	//***************************
	function defgrid( $deployed = false ){
		$i      = 1;
		$editar = 'false';

		$grid  = new $this->jqdatagrid;

		$grid->addField('id');
		$grid->label('N&uacute;mero');
		$grid->params(array(
			'align'         => "'center'",
			'frozen'        => 'true',
			'width'         => 50,
			'editable'      => 'false',
			'search'        => 'false'
		));

		$grid->addField('status');
		$grid->label('Estatus');
		$grid->params(array(
			'align'         => "'center'",
			'frozen'        => 'true',
			'width'         => 50,
			'editable'      => 'false',
			'search'        => 'true',
			'editoptions'   => '{value: {"A":"Abierto", "C":"Cerrado", "O":"Consolidado" } }',
			'cellattr'      => 'function(rowId, tv, aData, cm, rdata){
				var tips = "";
				if(aData.status !== undefined){
					if(aData.status=="A"){
						tips = "Abierto";
					}else if(aData.status=="C"){
						tips = "Cerrado";
					}else{
						tips = "Finalizado";
					}
				}
				return \'title="\'+tips+\'"\';
			}'
		));

		$grid->addField('fecha');
		$grid->label('Fecha');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));

		$grid->addField('codigo');
		$grid->label('C&oacute;digo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'left'",
			'edittype'      => "'text'",
			'width'         => 60,
			'editrules'     => '{ required:true }',
			//'editoptions'   => '{ size:5, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',

		));


		$grid->addField('descrip');
		$grid->label('Descripci&oacute;n');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'left'",
			'edittype'      => "'text'",
			'width'         => 200,
			'editrules'     => '{ required:true }',
			//'editoptions'   => '{ size:20, maxlength: 20, dataInit: function (elem) { $(elem).numeric(); }  }',
		));


		$grid->addField('litros');
		$grid->label('Litros');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			//'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
		));


		$grid->addField('sal');
		$grid->label('Sal');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
		));

		$grid->addField('acidez');
		$grid->label('Acidez');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			//'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
		));

		$grid->addField('inventario');
		$grid->label('Inventario');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
		));

		$grid->addField('unidades');
		$grid->label('Unidades');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
		));

		$grid->addField('unidadespeso');
		$grid->label('U.Producidas');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
		));


		$grid->addField('producido');
		$grid->label('P.Producido');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			//'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
		));

		$grid->addField('peso');
		$grid->label('P.Saliente');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			//'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
		));


		$grid->addField('usuario');
		$grid->label('Usuario');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 12 }',
		));

		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('290');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		$grid->setOnSelectRow('
			function(id){
				if (id){
					jQuery(gridId2).jqGrid("setGridParam",{url:"'.site_url($this->url.'getdatait/').'/"+id+"/", page:1});
					jQuery(gridId2).trigger("reloadGrid");
				}
			}'
		);

		$grid->setAfterInsertRow('
			function( rid, aData, rowe){
				if(aData.status == "A"){
					$(this).jqGrid( "setCell", rid, "status","", {color:"#FFFFFF", background:"#047D04" });
				}else if(aData.status == "C"){
					$(this).jqGrid( "setCell", rid, "status","", {color:"#FFFFFF", background:"#EFCD00" });
				}else if(aData.status == "F"){
					$(this).jqGrid( "setCell", rid, "status","", {color:"#FFFFFF", background:"#1D1F7D" });
				}
			}
		');

		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setAfterSubmit("$('#respuesta').html('<span style=\'font-weight:bold; color:red;\'>'+a.responseText+'</span>'); return [true, a ];");

		#show/hide navigations buttons
		$grid->setAdd(    $this->datasis->sidapuede('LPROD','INCLUIR%' ));
		$grid->setEdit(   $this->datasis->sidapuede('LPROD','MODIFICA%'));
		$grid->setDelete( $this->datasis->sidapuede('LPROD','BORR_REG%'));
		$grid->setSearch( $this->datasis->sidapuede('LPROD','BUSQUEDA%'));
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

		$grid->setBarOptions('addfunc: lprodadd, editfunc: lprodedit,delfunc: lproddel, viewfunc: lprodshow');

		#Set url
		$grid->setUrlput(site_url($this->url.'setdata/'));

		#GET url
		$grid->setUrlget(site_url($this->url.'getdata/'));

		if ($deployed) {
			return $grid->deploy();
		} else {
			return $grid;
		}
	}

	/**
	* Busca la data en el Servidor por json
	*/
	function getdata(){
		$grid       = $this->jqdatagrid;

		// CREA EL WHERE PARA LA BUSQUEDA EN EL ENCABEZADO
		$mWHERE = $grid->geneTopWhere('lprod');

		$response   = $grid->getData('lprod', array(array()), array(), false, $mWHERE, 'id','desc' );
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	/****************************
	* Guarda la Informacion
	*/
	function setData(){
		$this->load->library('jqdatagrid');
		$oper   = $this->input->post('oper');
		$id     = $this->input->post('id');
		$data   = $_POST;
		$mcodp  = '??????';
		$check  = 0;

		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			if(false == empty($data)){
				$check = $this->datasis->dameval("SELECT count(*) FROM lprod WHERE $mcodp=".$this->db->escape($data[$mcodp]));
				if($check == 0 ){
					$this->db->insert('lprod', $data);
					echo "Registro Agregado";

					logusu('LPROD',"Registro ????? INCLUIDO");
				}else{
					echo "Ya existe un registro con ese $mcodp";
				}
			}else{
				echo "Fallo Agregado!!!";
			}
		} elseif($oper == 'edit') {
			$nuevo  = $data[$mcodp];
			$anterior = $this->datasis->dameval("SELECT ${mcodp} FROM lprod WHERE id=${id}");
			if($nuevo <> $anterior ){
				//si no son iguales borra el que existe y cambia
				$this->db->query("DELETE FROM lprod WHERE ${mcodp}=?", array($mcodp));
				$this->db->query("UPDATE lprod SET ${mcodp}=? WHERE ${mcodp}=?", array( $nuevo, $anterior ));
				$this->db->where('id', $id);
				$this->db->update('lprod', $data);
				logusu('LPROD',"${mcodp} Cambiado/Fusionado Nuevo:".$nuevo." Anterior: ".$anterior." MODIFICADO");
				echo "Grupo Cambiado/Fusionado en clientes";
			}else{
				unset($data[$mcodp]);
				$this->db->where("id", $id);
				$this->db->update('lprod', $data);
				logusu('LPROD',"Grupo de Cliente  ".$nuevo." MODIFICADO");
				echo "$mcodp Modificado";
			}

		} elseif($oper == 'del') {
			$meco = $this->datasis->dameval("SELECT ${mcodp} FROM lprod WHERE id=${id}");
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM lprod WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM lprod WHERE id=$id ");
				logusu('LPROD',"Registro ????? ELIMINADO");
				echo "Registro Eliminado";
			}
		};
	}

	//***************************
	//Definicion del Grid y la Forma
	//***************************
	function defgridit( $deployed = false ){
		$i      = 1;
		$editar = 'false';

		$grid  = new $this->jqdatagrid;

		/*$grid->addField('id');
		$grid->label('N&uacute;mero');
		$grid->params(array(
			'align'         => "'center'",
			'frozen'        => 'true',
			'width'         => 40,
			'editable'      => 'false',
			'search'        => 'false'
		));


		$grid->addField('id_lprod');
		$grid->label('Id_lprod');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 0 }'
		));*/


		$grid->addField('codrut');
		$grid->label('Ruta');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:4, maxlength: 4 }',
		));


		$grid->addField('nombre');
		$grid->label('Nombre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:50, maxlength: 50 }',
		));


		$grid->addField('litros');
		$grid->label('Litros');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
		));


		$grid->setShrinkToFit('false');
		#Set url
		$grid->setUrlput(site_url($this->url.'setdatait/'));

		#GET url
		$grid->setUrlget(site_url($this->url.'getdatait/'));

		if ($deployed) {
			return $grid->deploy();
		} else {
			return $grid;
		}
	}

	/**
	* Busca la data en el Servidor por json
	*/
	function getdatait( $id = 0 ){
		if ($id === 0 ){
			$id = $this->datasis->dameval("SELECT MAX(id) FROM lprod");
		}
		if(empty($id)) return "";

		$dbid  = $this->db->escape($id);
		$grid  = $this->jqdatagrid;
		$mSQL  = "SELECT * FROM itlprod WHERE id_lprod=$dbid";
		$response   = $grid->getDataSimple($mSQL);
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	/**
	* Guarda la Informacion
	*/
	function setDatait(){
	}

	//***********************************
	// DataEdit
	//***********************************
	function dataedit(){
		$this->rapyd->load('datadetails','dataobject');

		$do = new DataObject('lprod');
		//$do->pointer('scli' ,'scli.cliente=rivc.cod_cli','sprv.tipo AS sprvtipo, sprv.reteiva AS sprvreteiva','left');
		$do->rel_one_to_many('itlprod' ,'itlprod' ,array('id'=>'id_lprod'));

		$edit = new DataDetails($this->tits, $do);
		$edit->on_save_redirect=false;

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_update');
		$edit->pre_process('delete','_pre_delete');

		$edit->codigo = new inputField('Producto','codigo');
		$edit->codigo->rule='required';
		$edit->codigo->size =8;
		$edit->codigo->maxlength =10;

		$edit->descrip = new inputField('Descripci&oacute;n','descrip');
		$edit->descrip->type='inputhidden';
		$edit->descrip->size =12;
		$edit->descrip->maxlength =10;

		$edit->fecha = new dateField('Fecha','fecha');
		$edit->fecha->rule='chfecha|required';
		$edit->fecha->size =11;
		$edit->fecha->insertValue=date('Y-m-d');
		$edit->fecha->maxlength =8;
		$edit->fecha->calendar=false;

		$edit->inventario = new inputField('Leche de Inventario','inventario');
		$edit->inventario->rule='max_length[12]|numeric|required';
		$edit->inventario->css_class='inputnum';
		$edit->inventario->size =7;
		$edit->inventario->insertValue='0';
		$edit->inventario->onkeyup='totalizar();';
		$edit->inventario->maxlength =9;

		$edit->litros = new inputField('Total Lts.','litros');
		$edit->litros->rule='max_length[12]|numeric';
		$edit->litros->css_class='inputnum';
		$edit->litros->type='inputhidden';
		$edit->litros->size =8;
		$edit->litros->maxlength =12;

		$edit->peso = new inputField('Peso','peso');
		$edit->peso->rule='max_length[12]|numeric';
		$edit->peso->css_class='inputnum';
		$edit->peso->type='inputhidden';
		$edit->peso->size =14;
		$edit->peso->maxlength =12;


		$edit->grasa = new inputField('Grasa %','grasa');
		//$edit->grasa->rule='required';
		$edit->grasa->css_class='inputnum';
		$edit->grasa->size =4;
		$edit->grasa->maxlength =10;

		$edit->sal = new inputField('Sal','sal');
		//$edit->grasa->rule='required';
		$edit->sal->css_class='inputnum';
		$edit->sal->size =4;
		$edit->sal->maxlength =10;

		$edit->tina = new dropdownField('Tina', 'tina');
		$edit->tina->rule ='required';
		$edit->tina->option('','Seleccionar');
		$edit->tina->options("SELECT codigo, CONCAT( codigo, '-', descripcion, '-', capacidad) descrip FROM tinaq ORDER BY codigo");
		$edit->tina->style ='width:150px;';
/*
		$edit->reciclaje = new inputField('Reproceso','reciclaje');
		$edit->reciclaje->css_class='inputnum';
		$edit->reciclaje->insertValue='0';
		$edit->reciclaje->size =8;
		$edit->reciclaje->maxlength =10;
*/
		//Inicio del detalle
		$edit->itid = new hiddenField('','itid_<#i#>');
		$edit->itid->db_name = 'id';
		$edit->itid->rel_id  = 'itlprod';

		$edit->itcodrut = new inputField('ruta','codrut_<#i#>');
		$edit->itcodrut->db_name = 'codrut';
		$edit->itcodrut->rule='max_length[4]';
		$edit->itcodrut->size =7;
		$edit->itcodrut->maxlength =4;
		$edit->itcodrut->rel_id   ='itlprod';

		$edit->itnombre = new inputField('ruta','itnombre_<#i#>');
		$edit->itnombre->db_name = 'nombre';
		$edit->itnombre->type='inputhidden';
		$edit->itnombre->size =14;
		$edit->itnombre->maxlength =12;
		$edit->itnombre->rel_id   ='itlprod';

		$edit->itlitros = new inputField('litros','itlitros_<#i#>');
		$edit->itlitros->db_name = 'litros';
		$edit->itlitros->rule='max_length[12]|numeric|mayorcero|callback_chlitros[<#i#>]';
		$edit->itlitros->css_class='inputnum';
		$edit->itlitros->size =14;
		$edit->itlitros->maxlength =12;
		$edit->itlitros->onkeyup='totalizar();';
		$edit->itlitros->rel_id   ='itlprod';

		$edit->itbufala = new inputField('bufala','itbufala_<#i#>');
		$edit->itbufala->db_name = 'bufala';
		$edit->itbufala->rule='max_length[12]|numeric|mayorcero|callback_chlitros[<#i#>]';
		$edit->itbufala->css_class='inputnum';
		$edit->itbufala->size =14;
		$edit->itbufala->maxlength =12;
		$edit->itbufala->onkeyup='totalizar();';
		$edit->itbufala->rel_id   ='itlprod';
		//Fin del detalle

		$edit->usuario = new autoUpdateField('usuario', $this->secu->usuario(), $this->secu->usuario());

		$edit->buttons('add_rel');
		$edit->build();

		if($edit->on_success()){
			$rt=array(
				'status' =>'A',
				'mensaje'=>'Registro guardado',
				'pk'     =>$edit->_dataobject->pk
			);
			echo json_encode($rt);
		}else{
			//echo $edit->output;
			$conten['form']  =& $edit;
			$this->load->view('view_lprod', $conten);
		}
	}

	//******************************************************************
	// Finalizacion de Produccion
	//******************************************************************
	function datacon(){
		$this->rapyd->load('dataedit');
		$script= '
		$(function() {
			$("#fecha").datepicker({dateFormat:"dd/mm/yy"});
			$(".inputnum").numeric(".");

			$("#bruto").focusout(
				function(){
					var mcestas = $("#cestas").val();
					var mbruto = $("#bruto").val();
					if ( mcestas == "" ) { mcestas=0;};
					if ( mbruto == "" ) { mbruto=0;};
					
					$("#peso").val(mbruto-mcestas*2.4);

					//alert(mcestas);
			
			});

		});';

		$edit = new DataEdit('', 'lprod');

		$edit->script($script,'modify');
		$edit->script($script,'create');
		$edit->on_save_redirect=false;

		$edit->post_process('insert','_post_con_insert');
		$edit->post_process('update','_post_con_update');
		$edit->post_process('delete','_post_con_delete');
		$edit->pre_process( 'insert', '_pre_con_insert');
		$edit->pre_process( 'update', '_pre_con_update');
		$edit->pre_process( 'delete', '_pre_con_delete');

		$edit->codigo = new inputField('Producto','codigo');
		$edit->codigo->rule='';
		$edit->codigo->size =17;
		$edit->codigo->maxlength =15;
		$edit->codigo->mode='autohide';

		$edit->descrip = new inputField('Descripci&oacute;n','descrip');
		$edit->descrip->rule='';
		$edit->descrip->size =47;
		$edit->descrip->in='codigo';
		$edit->descrip->maxlength =45;
		$edit->descrip->mode='autohide';

		$edit->fecha = new dateField('Fecha de producci&oacute;n','fecha');
		$edit->fecha->mode='autohide';

		$edit->litros = new inputField('Volumen procesado','litros');
		$edit->litros->rule='numeric';
		$edit->litros->css_class='inputnum';
		$edit->litros->size =14;
		$edit->litros->maxlength =12;
		$edit->litros->mode='autohide';
		$edit->litros->showformat='decimal';
		$edit->litros->append("Litros");

		$edit->medida = new freeField("Litros","medida","<b>Litros</b>");
		$edit->medida->in = "litros";

		$edit->unidades = new inputField('Produccion','unidades');
		$edit->unidades->mode = 'autohide';
		$edit->unidades->showformat = 'integer';

		$edit->medidau = new freeField("Unidades","medidau","<b>Unidades</b>");
		$edit->medidau->in = "unidades";

		$edit->cestas = new inputField('Cestas','cestas');
		$edit->cestas->rule='integer|required';
		$edit->cestas->css_class='inputonlynum';
		$edit->cestas->size =10;
		$edit->cestas->maxlength =10;
		$edit->cestas->style = 'font-size: 1.5em;font-weight:bold;';
		$edit->cestas->append("Unidades");

		$edit->bruto = new inputField('Peso Bruto','bruto');
		$edit->bruto->rule='numeric|required';
		$edit->bruto->css_class='inputonlynum';
		$edit->bruto->size =10;
		$edit->bruto->maxlength =10;
		$edit->bruto->style = 'font-size: 1.5em;font-weight:bold;';
		$edit->bruto->append("Kg.");

		$edit->peso = new inputField('Peso neto','peso');
		$edit->peso->rule='numeric|required';
		$edit->peso->css_class='inputonlynum';
		$edit->peso->size =10;
		$edit->peso->maxlength =10;
		$edit->peso->style = 'font-size: 1.5em;font-weight:bold;';
		$edit->peso->append("Kg.");

		$edit->unidadespeso = new inputField('Produccion Efectiva','unidadespeso');
		$edit->unidadespeso->rule='integer|required|callback_chunidadespeso';
		$edit->unidadespeso->css_class='inputonlynum';
		$edit->unidadespeso->size =10;
		$edit->unidadespeso->maxlength =10;
		$edit->unidadespeso->style = 'font-size: 1.5em;font-weight:bold;';
		$edit->unidadespeso->append("Unidades");

		$edit->reproceso = new inputField('Peso para <b>Reproceso</b>','reproceso');
		$edit->reproceso->rule='numeric|required';
		$edit->reproceso->css_class='inputonlynum';
		$edit->reproceso->size =10;
		$edit->reproceso->maxlength =10;
		$edit->reproceso->style = 'font-size: 1.5em;font-weight:bold;';
		$edit->reproceso->append("Kg.");

/*
		Cestas = 2.4kg c/u 
		* peso neto = peso_bruto-(peso_cestas*cestas)

*/

		$edit->almacen= new dropdownField ('Almac&eacute;n', 'almacen');
		$edit->almacen->options('SELECT ubica,ubides FROM caub WHERE gasto="N" ORDER BY ubides');
		$edit->almacen->rule='required';
		$edit->almacen->style='width:130px;';

		$edit->build();

		if($edit->on_success()){
			$rt=array(
				'status' =>'A',
				'mensaje'=>'Registro guardado',
				'pk'     =>$edit->_dataobject->pk
			);
			echo json_encode($rt);
		}else{
			echo $edit->output;
		}
	}

	function chunidadespeso($unidades){
		//Chequea que las unidades producidas no sean mayor que las pautadas
		//falta implementar
		return true;
	}

	function _pre_con_update($do){
		$status = $do->get('status');
		if($status!='C'){
			$do->error_message_ar['pre_upd']='Solo se puede consolidar una produccion cerrada';
			return false;
		}
		$do->set('status','F');
		return true;
	}

	function _pre_con_insert($do){
		$do->error_message_ar['pre_ins']='No permitido';
		return false;
	}

	function _pre_con_delete($do){
		$do->error_message_ar['pre_del']='No permitido';
		return false;
	}

	function _post_con_update($do){
		$peso  = $do->get('peso');
		$codigo= $do->get('codigo');
		$depo  = $do->get('almacen');

		//Agrega a inventario
		$this->datasis->sinvcarga($codigo,$depo, $peso );
		$primary =implode(',',$do->pk);
		logusu($do->table,"Finalizo produccion ${primary} ");
	}

	function _post_con_delete($do){ }


	//******************************************************************
	// Definir Producto y acidez
	//******************************************************************
	function dataacid(){
		$this->rapyd->load('dataedit');
		$script= '
		$(function() {
			$("#fecha").datepicker({dateFormat:"dd/mm/yy"});
			$(".inputnum").numeric(".");

			$("#codigo").autocomplete({
				delay: 600,
				autoFocus: true,
				source: function( req, add){
					$.ajax({
						url:  "'.site_url('ajax/buscasinv').'",
						type: "POST",
						dataType: "json",
						data: {\'q\':req.term,\'fecha\':$("#fecha").val()},
						success:
							function(data){
								var sugiere = [];
								if(data.length==0){
									$("#codigo").val("")
									$("#descrip").val("");
									$("#descrip_val").text("");
								}else{
									$.each(data,
										function(i, val){
											sugiere.push( val );
										}
									);
									add(sugiere);
								}
							},
					})
				},
				minLength: 2,
				select: function( event, ui ) {
					$("#codigo").attr("readonly", "readonly");

					$("#codigo").val(ui.item.codigo);
					$("#descrip").val(ui.item.descrip);
					$("#descrip_val").text(ui.item.descrip);
					$("#inventario").focus();
					$("#inventario").select();

					setTimeout(function() {  $("#codigo").removeAttr("readonly"); }, 1500);
				}
			});

		});';

		$edit = new DataEdit('', 'lprod');

		$edit->script($script,'modify');
		$edit->script($script,'create');
		$edit->on_save_redirect=false;

		$edit->post_process('insert','_post_acid_insert');
		$edit->post_process('update','_post_acid_update');
		$edit->post_process('delete','_post_acid_delete');
		$edit->pre_process( 'insert', '_pre_acid_insert');
		$edit->pre_process( 'update', '_pre_acid_update');
		$edit->pre_process( 'delete', '_pre_acid_delete');

		$edit->codigo = new inputField('Producto','codigo');
		$edit->codigo->rule='required';
		$edit->codigo->size =10;
		$edit->codigo->maxlength =10;

		$edit->descrip = new inputField('Descripci&oacute;n','descrip');
		$edit->descrip->type='inputhidden';
		$edit->descrip->size =12;
		$edit->descrip->maxlength =10;

		$edit->fecha = new dateField('Fecha de producci&oacute;n','fecha');
		$edit->fecha->mode='autohide';

		$edit->litros = new inputField('Volumen procesado','litros');
		$edit->litros->rule='numeric';
		$edit->litros->css_class='inputnum';
		$edit->litros->size =14;
		$edit->litros->maxlength =12;
		$edit->litros->mode='autohide';
		$edit->litros->showformat='decimal';
		$edit->litros->style = 'font-size: 1.5em;font-weight:bold;';
		
		$edit->medida = new freeField("Litros","medida","<b>Litros</b>");
		$edit->medida->in = "litros";

		$edit->acidez = new inputField('Acidez del suero','acidez');
		$edit->acidez->rule='numeric|required';
		$edit->acidez->css_class='inputonlynum';
		$edit->acidez->size =8;
		$edit->acidez->maxlength =10;
		$edit->acidez->style = 'font-size: 1.5em;font-weight:bold;';
		$edit->acidez->append('<b>Unidades</b>');

		$edit->build();

		if($edit->on_success()){
			$rt=array(
				'status' =>'A',
				'mensaje'=>'Registro guardado',
				'pk'     =>$edit->_dataobject->pk
			);
			echo json_encode($rt);
		}else{
			echo $edit->output;
		}
	}

	function _pre_acid_update($do){
		$status = $do->get('status');
		if($status!='A'){
			$do->error_message_ar['pre_upd']='Debe trabajar solo con producciones abiertas';
			return false;
		}
		return true;
	}

	function _pre_acid_insert($do){
		$do->error_message_ar['pre_ins']='No permitido';
		return false;
	}

	function _pre_acid_delete($do){
		$do->error_message_ar['pre_del']='No permitido';
		return false;
	}

	function _post_acid_update($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Modifico acidez $primary ");
	}

	function _post_acid_delete($do){ }

	//******************************************************************
	// Cierre de Produccion
	//******************************************************************
	function datacie(){
		$this->rapyd->load('dataedit');
		$script= '
		$(function() {
			$("#fecha").datepicker({dateFormat:"dd/mm/yy"});
			$(".inputnum").numeric(".");
		});';

		$edit = new DataEdit('', 'lprod');

		$edit->script($script,'modify');
		$edit->script($script,'create');
		$edit->on_save_redirect=false;

		$edit->post_process('insert','_post_cie_insert');
		$edit->post_process('update','_post_cie_update');
		$edit->post_process('delete','_post_cie_delete');
		$edit->pre_process( 'insert', '_pre_cie_insert');
		$edit->pre_process( 'update', '_pre_cie_update');
		$edit->pre_process( 'delete', '_pre_cie_delete');

		$edit->codigo = new inputField('Producto','codigo');
		$edit->codigo->rule='';
		$edit->codigo->size =17;
		$edit->codigo->maxlength =15;
		$edit->codigo->mode='autohide';

		$edit->descrip = new inputField('Descripci&oacute;n','descrip');
		$edit->descrip->rule='';
		$edit->descrip->size =47;
		$edit->descrip->in='codigo';
		$edit->descrip->maxlength =45;
		$edit->descrip->mode='autohide';

		$edit->fecha = new dateField('Fecha de producci&oacute;n','fecha');
		$edit->fecha->mode='autohide';

		$edit->litros = new inputField('Volumen procesado','litros');
		$edit->litros->rule='numeric';
		$edit->litros->css_class='inputnum';
		$edit->litros->size =14;
		$edit->litros->maxlength =12;
		$edit->litros->mode='autohide';
		$edit->litros->rowformat='decimal';
		
		$edit->medida = new freeField('Litros','medida','<b>Litros<b>');
		$edit->medida->in='litros';

		$edit->unidades = new inputField('Produccion','unidades');
		$edit->unidades->rule='integer|required';
		$edit->unidades->css_class='inputonlynum';
		$edit->unidades->size =8;
		$edit->unidades->maxlength =10;
		$edit->unidades->style = 'font-size: 1.5em;font-weight:bold;';
		$edit->unidades->append('Unidades');

		$edit->build();

		if($edit->on_success()){
			$rt=array(
				'status' =>'A',
				'mensaje'=>'Registro guardado',
				'pk'     =>$edit->_dataobject->pk
			);
			echo json_encode($rt);
		}else{
			echo $edit->output;
		}
	}

	function _pre_cie_update($do){
		$status = $do->get('status');
		if($status=='C'){
			$do->error_message_ar['pre_upd']='Debe primero cerrar la produccion para luego consolidar';
			return false;
		}
		$do->set('status','C');
		return true;
	}

	function _pre_cie_insert($do){
		$do->error_message_ar['pre_ins']='No permitido';
		return false;
	}

	function _pre_cie_delete($do){
		$do->error_message_ar['pre_del']='No permitido';
		return false;
	}

	function _post_cie_update($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Modifico $this->tits $primary ");
	}

	function _post_cie_delete($do){ }

	function chlitros($litros,$ind){
		$bufala = round(floatval($this->input->post('itbufala_'.$ind)),2);
		$vaca   = round(floatval($this->input->post('itlitros_'.$ind)),2);
		$litros = $vaca+$bufala;

		$ruta   = $this->input->post('codrut_'.$ind);
		$fecha  = human_to_dbdate($this->input->post('fecha'));
		$id     = $this->input->post('itid_'.$ind);
		if(!empty($id)){
			$ww='AND a.id <> '.$this->db->escape($id);
		}else{
			$ww='';
		}

		$dbfecha= $this->db->escape($fecha);
		$dbruta = $this->db->escape($ruta);

		$usados = round($this->datasis->dameval("SELECT SUM(a.litros) FROM itlprod AS a JOIN lprod AS b ON a.id_lprod=b.id WHERE a.codrut=${dbruta} AND b.fecha=${dbfecha} ${ww}"),2);
		$recibi = round($this->datasis->dameval("SELECT SUM(litros)   FROM lrece   WHERE ruta=${dbruta} AND fecha=${dbfecha}"),2);

		$disponible = $recibi-$usados-$litros;
		if ($disponible < 0){
			if($recibi-$usados < 0) $disponible = 0; else $disponible = $recibi-$usados ;

			$this->validation->set_message('chlitros',"No hay suficiente leche recibida de la ruta ${ruta} para producir, disponible: ".nformat(abs($disponible)));
			return false;
		}else{
			return true;
		}
	}

	function getcierre($fecha){
		$dbfecha = $this->db->escape($fecha);
		$cierre  = $this->datasis->dameval("SELECT id FROM lcierre WHERE fecha=${dbfecha}");
		if(empty($cierre)){
			echo '0';
		}else{
			echo $cierre;
		}
	}

	function getstatus($fecha){
		$dbfecha = $this->db->escape($fecha);
		$status = $this->datasis->dameval("SELECT status FROM lcierre WHERE fecha=${dbfecha}");
		if($status=='A'){
			echo 'A';
		}else{
			echo 'C';
		}
	}

	function _pre_insert($do){
		//$do->set('fecha',date('Y-m-d'));
		$leche  = floatval($do->get('inventario'));
		$fecha  = $do->get('fecha');
		$dbfecha= $this->db->escape($fecha);
		$cana   = $this->datasis->dameval('SELECT COUNT(*) AS cana FROM lcierre WHERE fecha='.$dbfecha);
		$do->set('status','A');

		if($cana>0){
			$do->error_message_ar['pre_ins'] = $do->error_message_ar['insert'] = 'Ya el d&iacute;a '.dbdate_to_human($fecha).' fue cerrado.';
			$do->error_message_ar['pre_upd'] = $do->error_message_ar['update'] = 'Ya el d&iacute;a '.dbdate_to_human($fecha).' fue cerrado.';
			return false;
		}

		$itcana=$do->count_rel('itlprod');
		for($i=0;$i<$itcana;$i++){
			$codrut = $do->get_rel('itlprod','codrut' ,$i);
			$vaca   = floatval($do->get_rel('itlprod','litros' ,$i));
			$bufala = floatval($do->get_rel('itlprod','bufala' ,$i));
			$pleche = $vaca+$bufala;

			if(empty($codrut) || $pleche == 0 ){
				$do->rel_rm('itlprod',$i);
			}else{
				$leche += $pleche;
			}
		}

		if($leche <= 0){
			$do->error_message_ar['pre_ins'] = $do->error_message_ar['insert'] = 'No puede tener una produccion sin leche como materia prima.';
			return false;
		}

		return true;
	}

	function _pre_update($do){
		$status = $do->get('status');
		if($status!='A'){
			$do->error_message_ar['pre_upd'] = $do->error_message_ar['update'] = 'Solo se pueden modificar las producciones abiertas.';
			return false;
		}
		return $this->_pre_insert($do);
	}

	function _pre_delete($do){
		$fecha  = $do->get('fecha');
		$dbfecha= $this->db->escape($fecha);
		$cana   = $this->datasis->dameval("SELECT COUNT(*) AS cana FROM lcierre WHERE fecha=".$dbfecha);

		if($cana>0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete'] = 'Ya el d&iacute;a '.dbdate_to_human($fecha).' fue cerrado.';
			return false;
		}
		return true;
	}

	function _post_insert($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Creo $this->tits ${primary} ");
	}

	function _post_update($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Modifico $this->tits ${primary} ");
	}

	function _post_delete($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Elimino $this->tits ${primary} ");
	}

	function instalar(){
		if(!$this->db->table_exists('lprod')){
			$mSQL = "CREATE TABLE `lprod` (
				`id` INT(10) NOT NULL AUTO_INCREMENT,
				`codigo` VARCHAR(15) NULL DEFAULT NULL,
				`descrip` VARCHAR(45) NULL DEFAULT NULL,
				`fecha` DATE NULL DEFAULT NULL,
				`peso` DECIMAL(12,2) NULL DEFAULT NULL,
				`unidadespeso` INT(11) NULL DEFAULT NULL,
				`unidades` INT(10) NULL DEFAULT NULL,
				`reproceso` DECIMAL(12,2) NULL DEFAULT NULL,
				`reciclaje` DECIMAL(12,2) NULL DEFAULT NULL,
				`status` CHAR(1) NOT NULL DEFAULT 'A',
				`litros` DECIMAL(12,2) NULL DEFAULT NULL,
				`inventario` DECIMAL(12,2) NULL DEFAULT NULL,
				`grasa` DECIMAL(12,2) NULL DEFAULT NULL,
				`acidez` DECIMAL(12,2) NULL DEFAULT NULL,
				`almacen` VARCHAR(4) NULL DEFAULT NULL,
				`estampa` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
				`usuario` VARCHAR(15) NULL DEFAULT NULL,
				PRIMARY KEY (`id`),
				INDEX `codigo` (`codigo`),
				INDEX `fecha` (`fecha`)
			)
			COMMENT='Control de produccion de lacteos'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM";
			$this->db->simple_query($mSQL);
		}

		$campos=$this->db->list_fields('lprod');
		if(!in_array('grasa',$campos)){
			$mSQL="ALTER TABLE `lprod` ADD COLUMN `grasa` DECIMAL(12,2) NULL DEFAULT NULL AFTER `inventario`";
			$this->db->simple_query($mSQL);
		}

		if(!in_array('sal',$campos)){
			$mSQL="ALTER TABLE `lprod` ADD COLUMN `sal` DECIMAL(12,2) NULL DEFAULT NULL AFTER `grasa`";
			$this->db->simple_query($mSQL);
		}

		if(!in_array('acidez',$campos)){
			$mSQL="ALTER TABLE `lprod` ADD COLUMN `acidez` DECIMAL(12,2) NULL DEFAULT NULL AFTER `grasa`";
			$this->db->simple_query($mSQL);
		}

		if(!in_array('unidades',$campos)){
			$mSQL = "ALTER TABLE `lprod` ADD COLUMN `unidades` INT(10) NULL DEFAULT NULL AFTER `peso`, ADD COLUMN `producido` DECIMAL(12,2) NULL DEFAULT NULL AFTER `unidades`, ADD COLUMN `status` CHAR(1) NOT NULL DEFAULT 'A' AFTER `producido`";
			$this->db->simple_query($mSQL);
		}

		if(!in_array('unidadespeso',$campos)){
			$mSQL="ALTER TABLE `lprod` ADD COLUMN `unidadespeso` INT NULL DEFAULT NULL AFTER `peso`";
			$this->db->simple_query($mSQL);
		}

		if(!in_array('unidadespeso',$campos)){
			$mSQL="ALTER TABLE `lprod` CHANGE COLUMN `producido` `reproceso` DECIMAL(12,2) NULL DEFAULT NULL AFTER `unidades`";
			$this->db->simple_query($mSQL);
		}

		if(!in_array('almacen',$campos)){
			$mSQL="ALTER TABLE `lprod` ADD COLUMN `almacen` VARCHAR(4) NULL DEFAULT NULL AFTER `acidez`";
			$this->db->simple_query($mSQL);
		}

		if(!in_array('reciclaje',$campos)){
			$mSQL="ALTER TABLE `lprod` ADD COLUMN `reciclaje` DECIMAL(12,2) NULL DEFAULT NULL AFTER `reproceso`";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->table_exists('itlprod')){
			$mSQL = "CREATE TABLE `itlprod` (
				`id` INT(10) NOT NULL AUTO_INCREMENT,
				`id_lprod` INT(10) NOT NULL DEFAULT '0',
				`codrut` CHAR(4) NOT NULL DEFAULT '0',
				`nombre` VARCHAR(50) NOT NULL DEFAULT '0',
				`litros` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
				`bufala` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
				PRIMARY KEY (`id`),
				INDEX `id_lprod` (`id_lprod`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM";
			$this->db->simple_query($mSQL);
		}

		$campos=$this->db->list_fields('itlprod');
		if(!in_array('bufala',$campos)){
			$mSQL="ALTER TABLE `itlprod` ADD COLUMN `bufala` DECIMAL(12,2) NOT NULL DEFAULT '0.00' AFTER `litros`";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->table_exists('lcierre')){
			$mSQL = "CREATE TABLE `lcierre` (
				`id` INT(10) NOT NULL AUTO_INCREMENT,
				`fecha` DATE NULL DEFAULT NULL,
				`dia` VARCHAR(50) NULL DEFAULT NULL,
				`status` CHAR(1) NULL DEFAULT 'A',
				`recepcion` DECIMAL(12,2) NULL DEFAULT NULL,
				`enfriamiento` DECIMAL(12,2) NULL DEFAULT NULL,
				`requeson` DECIMAL(12,2) NULL DEFAULT NULL,
				`requesonteorico` DECIMAL(12,2) NULL DEFAULT NULL,
				`requesonreal` DECIMAL(12,2) NULL DEFAULT NULL,
				`usuario` VARCHAR(50) NULL DEFAULT NULL,
				PRIMARY KEY (`id`)
			)
			COMMENT='Cierre de produccion de lacteos'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->table_exists('itlcierre')){
			$mSQL = "CREATE TABLE `itlcierre` (
				`id` INT(10) NOT NULL AUTO_INCREMENT,
				`id_lcierre` INT(10) NULL DEFAULT NULL,
				`codigo` VARCHAR(15) NULL DEFAULT NULL,
				`descrip` VARCHAR(45) NULL DEFAULT NULL,
				`unidades` DECIMAL(10,2) NULL DEFAULT NULL,
				`cestas` DECIMAL(10,2) NULL DEFAULT NULL,
				`peso` DECIMAL(10,2) NULL DEFAULT NULL,
				PRIMARY KEY (`id`),
				INDEX `id_lcierre` (`id_lcierre`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM";
			$this->db->simple_query($mSQL);
		}
	}
}
