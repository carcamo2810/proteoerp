<?php
/*  FUNCIONES INVOCADAS POR AJAX
 *
 *
 *
 *  BUSQUEDAS
 *		PROVEEDORES 	buscasprv
 *
 *		CLIENTES	buscascli
 *
 *		INVENTARIO	buscasinv
 *				buscascstart   (Busca sinv solo articulos para compras con codigos alternos)
 *				buscasinvart   (Busca sinv solo articulos)
 *
 *		FACTURAS	buscasfacdev   (Busca facturas para aplicarles devolucion)
 *
 *		FORMAS DE PAGO	buscasfpadev   (Busca las formas de pago de una factura para devolverlos)
 *
 *		PLAN DE CUENTAS buscacpla
 *
 *
 *
 *
 *
 *
*/
class Ajax extends Controller {
	var $autolimit=50; //Limite en el autocomplete;

	function Ajax(){
		parent::Controller();
		session_write_close();
	}

	function index(){

	}

	//***************************************
	//           Auto complete
	//***************************************
	function buscasprv(){
		$mid  = $this->input->post('q');
		if($mid == false) $mid  = $this->input->post('term');

		$qdb  = $this->db->escape('%'.$mid.'%');
		$qmid = $this->db->escape($mid);

		$data = '[]';
		if($mid !== false){
			$retArray = $retorno = array();

			//Cheque si existe el codigo
			$mSQL="SELECT TRIM(nombre) AS nombre, TRIM(rif) AS rif, proveed, direc1 AS direc, reteiva
				FROM sprv WHERE proveed=${qmid} LIMIT 1";
			$query = $this->db->query($mSQL);
			if ($query->num_rows() == 1){
				$row = $query->row_array();
				$retArray['value']   = $row['proveed'];
				$retArray['label']   = '('.$row['rif'].') '.utf8_encode($row['nombre']);
				$retArray['rif']     = $row['rif'];
				$retArray['nombre']  = utf8_encode($row['nombre']);
				$retArray['proveed'] = $row['proveed'];
				$retArray['direc']   = utf8_encode($row['direc']);
				$retArray['reteiva'] = $row['reteiva'];
				array_push($retorno, $retArray);
				$ww=" AND proveed<>${qmid}";
			}else{
				$ww='';
			}

			$mSQL="SELECT TRIM(nombre) AS nombre, TRIM(rif) AS rif, proveed, direc1 AS direc, reteiva
				FROM sprv WHERE rif LIKE ${qdb} OR nombre LIKE ${qdb} ${ww}
				ORDER BY rif LIMIT ".$this->autolimit;
			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['value']   = $row['proveed'];
					$retArray['label']   = '('.$row['rif'].') '.utf8_encode($row['nombre']);
					$retArray['rif']     = $row['rif'];
					$retArray['nombre']  = utf8_encode($row['nombre']);
					$retArray['proveed'] = $row['proveed'];
					$retArray['direc']   = utf8_encode($row['direc']);
					$retArray['reteiva'] = $row['reteiva'];
					array_push($retorno, $retArray);
				}
			}
			if(count($retorno)>0){
				$data = json_encode($retorno);
			}
		}
		echo $data;
		return true;
	}

	/**************************************************************
	 *
	 *  BUSCA LOS CLIENTES
	 *
	*/
	function buscascli(){
		$mid  = $this->input->post('q');
		if($mid == false) $mid  = $this->input->post('term');

		$qmid = $this->db->escape($mid);
		$qdb  = $this->db->escape('%'.$mid.'%');

		$data = '[ ]';
		if($mid !== false){
			$retArray = $retorno = array();

			//Mira si existe el codigo
			$mSQL="SELECT id,TRIM(nombre) AS nombre, TRIM(rifci) AS rifci, cliente, tipo, dire11 AS direc
				FROM scli WHERE cliente=${qmid} AND tipo<>0  LIMIT 1";
			$query = $this->db->query($mSQL);
			if ($query->num_rows() == 1){
				$row = $query->row_array();
				$retArray['value']   = $row['cliente'];
				$retArray['label']   = '('.$row['rifci'].') '.utf8_encode($row['nombre']);
				$retArray['rifci']   = $row['rifci'];
				$retArray['nombre']  = utf8_encode($row['nombre']);
				$retArray['cod_cli'] = $row['cliente'];
				$retArray['tipo']    = $row['tipo'];
				$retArray['direc']   = utf8_encode($row['direc']);
				$retArray['id']      = $row['id'];
				array_push($retorno, $retArray);
				$ww=" AND cliente<>${qmid}";
			}else{
				$ww='';
			}

			$mSQL="SELECT id,TRIM(nombre) AS nombre, TRIM(rifci) AS rifci, cliente, tipo , dire11 AS direc
				FROM scli WHERE (cliente LIKE ${qdb} OR rifci LIKE ${qdb} OR nombre LIKE ${qdb}) AND tipo<>0 $ww
				ORDER BY rifci LIMIT ".$this->autolimit;

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['value']   = $row['cliente'];
					$retArray['label']   = '('.$row['rifci'].') '.utf8_encode($row['nombre']);
					$retArray['rifci']   = $row['rifci'];
					$retArray['nombre']  = utf8_encode($row['nombre']);
					$retArray['cod_cli'] = $row['cliente'];
					$retArray['tipo']    = $row['tipo'];
					$retArray['direc']   = utf8_encode($row['direc']);
					$retArray['id']      = $row['id'];
					array_push($retorno, $retArray);
				}
			}
			if(count($data)>0)
				$data = json_encode($retorno);
		}
		echo $data;
		return true;
	}

	/**************************************************************
	 *
	 *   BUSCA LOS PRINCIPIOS ACTIVOS PARA SUNDECOB
	 *
	*/
	function buscasundecob($tabla){

		$data = '[{ }]';
		if (in_array($tabla,array('dcomercial','forma','marca','material','pactivo','rubro','subrubro','unidad'))) {
			$mid  = $this->input->post('q');
			$qdb  = $this->db->escape('%'.$mid.'%');

			if($mid !== false){
				$retArray = $retorno = array();
				$mSQL="SELECT codigo, TRIM(descrip) AS descrip
					FROM sc_$tabla
					WHERE (codigo LIKE ${qdb} OR descrip LIKE ${qdb})
					ORDER BY descrip LIMIT ".$this->autolimit;
				$query = $this->db->query($mSQL);
				if ($query->num_rows() > 0){
					foreach( $query->result_array() as  $row ) {
						$retArray['value']    = $row['codigo'];
						$retArray['label']    = '('.$row['codigo'].') '.utf8_encode($row['descrip']);
						$retArray['descrip']  = utf8_encode($row['descrip']);
						array_push($retorno, $retArray);
					}
				}
				if(count($data)>0)
					$data = json_encode($retorno);
			}
		}
		echo $data;
		return true;
	}

	function buscastarifa(){
		$mid  = $this->input->post('q');
		$qdb  = $this->db->escape('%'.$mid.'%');

		$data = '[{ }]';
		if($mid !== false){
			$retArray = $retorno = array();
			$mSQL  = "SELECT minimo, actividad, id
			FROM tarifa
			WHERE actividad LIKE $qdb
			ORDER BY actividad LIMIT ".$this->autolimit;
			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['value']    = $row['id'];
					$retArray['minimo']   = $row['minimo'];
					$retArray['actividad']= utf8_encode($row['actividad']);
					$retArray['label']    = utf8_encode($row['actividad'].' ( '.$row['minimo'].')');
					array_push($retorno, $retArray);
				}
			}
			if(count($data)>0)
				$data = json_encode($retorno);
		}
		echo $data;
		return true;
	}

	/**************************************************************
	 *
	 *  BUSCA LOS CLIENTES PARA COBRO DE SERVICIO
	 *
	*/
	function buscascliser(){
		$mid  = $this->input->post('q');
		if($mid == false) $mid  = $this->input->post('term');

		$qmid = $this->db->escape($mid);
		$qdb  = $this->db->escape('%'.$mid.'%');

		$ut = $this->datasis->dameval("SELECT valor FROM utributa ORDER BY fecha DESC LIMIT 1");

		$data = '[ ]';
		if($mid !== false){
			$retArray = $retorno = array();
			$mSQL="SELECT TRIM(a.nombre) AS nombre, TRIM(a.rifci) AS rifci, a.cliente, a.tipo , a.dire11 AS direc,
				IF(a.tarimonto>0,ROUND(a.tarimonto*$ut,2), ROUND(b.minimo*$ut,2)) precio1, a.upago, a.telefono, b.id codigo,
				IF(a.tarimonto>0,a.tarimonto,b.minimo) AS utribu,b.tipo AS taritipo
				FROM scli AS a
				JOIN tarifa AS b ON a.tarifa=b.id
				WHERE (cliente LIKE ${qdb} OR rifci LIKE ${qdb} OR nombre LIKE ${qdb})
				ORDER BY rifci LIMIT ".$this->autolimit;
			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$dt1 = new DateTime($row['upago'].'01');
					$dt2 = new DateTime();
					$interval = $dt1->diff($dt2);

					$retArray['value']    = $row['cliente'];
					$retArray['label']    = '('.$row['rifci'].') '.utf8_encode($row['nombre']);
					$retArray['rifci']    = $row['rifci'];
					$retArray['nombre']   = utf8_encode($row['nombre']);
					$retArray['cod_cli']  = utf8_encode($row['cliente']);
					$retArray['codigo']   = $row['codigo'];
					$retArray['tipo']     = $row['tipo'];
					$retArray['precio1']  = $row['precio1'];
					$retArray['telefono'] = $row['telefono'];
					$retArray['upago']    = $row['upago'];
					$retArray['utribu']   = $row['utribu'];
					$retArray['taritipo'] = $row['taritipo'];
					$retArray['direc']    = utf8_encode($row['direc']);
					$retArray['cana']     = $interval->format('%m');
					array_push($retorno, $retArray);
				}
			}
			if(count($data)>0)
				$data = json_encode($retorno);
		}
		echo $data;
		return true;
	}

	/**************************************************************
	 *
	 *  BUSCA LAS RUTAS DE VAQUERAS
	 *
	*/
	function buscalruta(){
		$mid  = $this->input->post('q');
		if($mid == false) $mid  = $this->input->post('term');

		$fecha = $this->input->post('fecha');
		if($fecha == false) $fecha=date('Y-m-d');

		$qmid  = $this->db->escape($mid);
		$qdb   = $this->db->escape('%'.$mid.'%');
		$qfecha =$this->db->escape($fecha);

		$data = '[ ]';
		if($mid !== false){
			$retArray = $retorno = array();
			$mSQL="SELECT DISTINCT TRIM(a.nombre) AS nombre, TRIM(a.codigo) AS codigo
				FROM lruta AS a
				JOIN lrece AS b ON a.codigo=b.ruta AND b.fecha=${qfecha}
				WHERE (a.codigo LIKE ${qdb} OR a.nombre LIKE ${qdb})
				ORDER BY a.nombre LIMIT ".$this->autolimit;
			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['value']    = utf8_encode($row['codigo']);
					$retArray['label']    = utf8_encode('('.$row['codigo'].') '.$row['nombre']);
					$retArray['nombre']   = utf8_encode($row['nombre']);
					array_push($retorno, $retArray);
				}
			}
			if(count($data)>0)
				$data = json_encode($retorno);
		}
		echo $data;
		return true;
	}

	/**************************************************************
	 *
	 *  BUSCA LAS VAQUERAS
	 *
	*/
	function buscalvaca(){
		$mid  = $this->input->post('q');
		if($mid == false) $mid  = $this->input->post('term');

		$qmid = $this->db->escape($mid);
		$qdb  = $this->db->escape('%'.$mid.'%');

		$data = '[ ]';
		if($mid !== false){
			$retArray = $retorno = array();
			$mSQL="SELECT a.id,TRIM(a.codigo) AS codigo, TRIM(a.nombre) AS nombre
				FROM lvaca AS a
				WHERE (a.codigo LIKE ${qdb} OR a.nombre LIKE ${qdb})
				ORDER BY a.nombre LIMIT ".$this->autolimit;
			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['value']    = utf8_encode($row['codigo']);
					$retArray['label']    = utf8_encode('('.$row['codigo'].') '.$row['nombre']);
					$retArray['nombre']   = utf8_encode($row['nombre']);
					$retArray['id']       = $row['id'];
					array_push($retorno, $retArray);
				}
			}
			if(count($data)>0)
				$data = json_encode($retorno);
		}
		echo $data;
		return true;
	}

	/**************************************************************
	 *
	 *  BUSCA LOS INVENTARIO
	 *
	*/
	function buscasinv(){
		$comodin= $this->datasis->traevalor('COMODIN');
		$mid    = $this->input->post('q');
		if($mid == false) $mid  = $this->input->post('term');

		if(strlen($comodin)==1 && $comodin!='%' && $mid!==false){
			$mid=str_replace($comodin,'%',$mid);
		}
		$qdb  = $this->db->escape($mid.'%');
		$qba  = $this->db->escape($mid);

		$data = '[]';
		if($mid !== false){
			$retArray = $retorno = array();

			$mSQL="
				SELECT DISTINCT TRIM(a.descrip) AS descrip, TRIM(a.codigo) AS codigo,
				a.precio1,precio2,precio3,precio4, a.iva,a.existen,a.tipo,a.peso, a.ultimo, a.pond, a.barras
				FROM sinv AS a
				LEFT JOIN barraspos AS b ON a.codigo=b.codigo
				WHERE (a.codigo LIKE $qdb OR a.descrip LIKE  $qdb OR a.barras LIKE $qdb OR b.suplemen=$qba) AND a.activo='S'

				ORDER BY a.descrip LIMIT ".$this->autolimit;
			$cana=1;

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['label']   = '('.$row['codigo'].')'.utf8_encode($row['descrip']).' Bs.'.$row['precio1'].'  '.$row['existen'].'';
					$retArray['value']   = $row['codigo'];
					$retArray['codigo']  = $row['codigo'];
					$retArray['cana']    = $cana;
					$retArray['tipo']    = $row['tipo'];
					$retArray['peso']    = $row['peso'];
					$retArray['ultimo']  = $row['ultimo'];
					$retArray['pond']    = $row['pond'];
					$retArray['base1']   = round($row['precio1']*100/(100+$row['iva']),2);
					$retArray['base2']   = round($row['precio2']*100/(100+$row['iva']),2);
					$retArray['base3']   = round($row['precio3']*100/(100+$row['iva']),2);
					$retArray['base4']   = round($row['precio4']*100/(100+$row['iva']),2);
					$retArray['descrip'] = utf8_encode($row['descrip']);
					$retArray['barras']  = $row['barras'];
					//$retArray['descrip'] = wordwrap($row['descrip'], 25, '<br />');
					$retArray['iva']     = $row['iva'];
					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
	        }
		}
		echo $data;
	}

	/**************************************************************
	 *
	 *  BUSCA LOS INVENTARIO
	 *
	*/
	function buscarnoti(){
		$comodin= $this->datasis->traevalor('COMODIN');
		$mid    = $this->input->post('q');
		if($mid == false) $mid  = $this->input->post('term');

		if(strlen($comodin)==1 && $comodin!='%' && $mid!==false){
			$mid=str_replace($comodin,'%',$mid);
		}
		$qdb  = $this->db->escape($mid.'%');
		$qba  = $this->db->escape($mid);

		$data = '[]';
		if($mid !== false){
			$retArray = $retorno = array();

			$mSQL="
				SELECT DISTINCT a.id AS numero, a.serial AS codigo,
				a.precio1,precio2,precio3,precio4, a.iva,a.existen,a.tipo,a.peso, a.ultimo, a.pond, a.barras
				FROM rnoti AS a
				WHERE (a.id LIKE $qdb OR a.nomcliente LIKE  $qdb OR a.serial=$qba) a.estado<>'ENTREGADO'
				ORDER BY a.id LIMIT ".$this->autolimit;
			$cana=1;

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['label']   = '('.$row['codigo'].')'.utf8_encode($row['descrip']).' Bs.'.$row['precio1'].'  '.$row['existen'].'';
					$retArray['value']   = $row['codigo'];
					$retArray['codigo']  = $row['codigo'];
					$retArray['cana']    = $cana;
					$retArray['tipo']    = $row['tipo'];
					$retArray['peso']    = $row['peso'];
					$retArray['ultimo']  = $row['ultimo'];
					$retArray['pond']    = $row['pond'];
					$retArray['base1']   = round($row['precio1']*100/(100+$row['iva']),2);
					$retArray['base2']   = round($row['precio2']*100/(100+$row['iva']),2);
					$retArray['base3']   = round($row['precio3']*100/(100+$row['iva']),2);
					$retArray['base4']   = round($row['precio4']*100/(100+$row['iva']),2);
					$retArray['descrip'] = utf8_encode($row['descrip']);
					$retArray['barras']  = $row['barras'];
					//$retArray['descrip'] = wordwrap($row['descrip'], 25, '<br />');
					$retArray['iva']     = $row['iva'];
					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
	        }
		}
		echo $data;
	}



	//Busca icon
	function buscaicon(){
		$comodin= $this->datasis->traevalor('COMODIN');
		$mid    = $this->input->post('q');
		if(strlen($comodin)==1 && $comodin!='%' && $mid!==false){
			$mid=str_replace($comodin,'%',$mid);
		}
		$qdb    = $this->db->escape($mid.'%');
		$qba    = $this->db->escape($mid);
		$tipo   = $this->input->post('tipo');

		$data = '[]';
		if($mid !== false && $tipo!==false){
			if($tipo=='E'){
				$tipo='I';
			}elseif($tipo=='S'){
				$tipo='E';
			}else{
				echo $data;
				return;
			}

			$dbtipo = $this->db->escape($tipo);
			$retArray = $retorno = array();

			$mSQL="SELECT TRIM(a.codigo) AS codigo, a.concepto
				FROM icon AS a
				WHERE (a.codigo LIKE $qdb OR a.concepto LIKE  $qdb) AND tipo=$dbtipo
				ORDER BY a.concepto LIMIT ".$this->autolimit;

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['label']    = utf8_encode('('.$row['codigo'].') '.trim($row['concepto']));
					$retArray['value']    = $row['codigo'];
					$retArray['concepto'] = utf8_encode(trim($row['concepto']));
					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
	        }
		}
		echo $data;
	}

	//Busca sinv solo articulos para compras con codigos alternos
	function buscascstart(){
		$comodin= $this->datasis->traevalor('COMODIN');
		$mid    = $this->input->post('q');
		if(strlen($comodin)==1 && $comodin!='%' && $mid!==false){
			$mid=str_replace($comodin,'%',$mid);
		}
		$qdb    = $this->db->escape($mid.'%');
		$qba    = $this->db->escape($mid);
		$sprv   = $this->input->post('sprv');
		$dbsprv = $this->db->escape($sprv);

		$data = '[{ }]';
		if($mid !== false){
			$retArray = $retorno = array();

			$mSQL="SELECT DISTINCT TRIM(a.descrip) AS descrip, TRIM(a.codigo) AS codigo, a.precio1,precio2,precio3,precio4, a.iva,a.existen,a.tipo
				,a.peso, a.ultimo, a.pond
				FROM sinv AS a
				LEFT JOIN barraspos AS b ON a.codigo=b.codigo
				LEFT JOIN sinvprov  AS c ON c.proveed=$dbsprv AND c.codigo=a.codigo
				WHERE (a.codigo LIKE $qdb OR a.descrip LIKE  $qdb OR a.barras LIKE $qdb OR b.suplemen=$qba OR a.alterno LIKE $qba OR c.codigop=$qdb) AND a.activo='S' AND a.tipo='Articulo'
				ORDER BY a.descrip LIMIT ".$this->autolimit;
			$cana=1;

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['label']   = '('.$row['codigo'].') '.$row['descrip'].' '.$row['precio1'].' Bs. - '.$row['existen'];
					$retArray['value']   = $row['codigo'];
					$retArray['codigo']  = $row['codigo'];
					$retArray['cana']    = $cana;
					$retArray['tipo']    = $row['tipo'];
					$retArray['peso']    = $row['peso'];
					$retArray['ultimo']  = $row['ultimo'];
					$retArray['pond']    = $row['pond'];
					$retArray['base1']   = $row['precio1']*100/(100+$row['iva']);
					$retArray['base2']   = $row['precio2']*100/(100+$row['iva']);
					$retArray['base3']   = $row['precio3']*100/(100+$row['iva']);
					$retArray['base4']   = $row['precio4']*100/(100+$row['iva']);
					$retArray['descrip'] = utf8_encode($row['descrip']);
					//$retArray['descrip'] = wordwrap($row['descrip'], 25, '<br />');
					$retArray['iva']     = $row['iva'];
					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
	        }
		}
		echo $data;
	}

	//Busca sinv solo articulos
	function buscasinvart(){
		$comodin= $this->datasis->traevalor('COMODIN');
		$mid    = $this->input->post('q');
		if($mid == false) $mid  = $this->input->post('term');

		if(strlen($comodin)==1 && $comodin!='%' && $mid!==false){
			$mid=str_replace($comodin,'%',$mid);
		}
		$qdb  = $this->db->escape($mid.'%');
		$qba  = $this->db->escape($mid);

		$data = '[{ }]';
		if($mid !== false){
			$retArray = $retorno = array();

			$mSQL="SELECT DISTINCT TRIM(a.descrip) AS descrip, TRIM(a.codigo) AS codigo, a.precio1,precio2,precio3,precio4, a.iva,a.existen,a.tipo
				,a.peso, a.ultimo, a.pond FROM sinv AS a
				LEFT JOIN barraspos AS b ON a.codigo=b.codigo
				WHERE (a.codigo LIKE $qdb OR a.descrip LIKE  $qdb OR a.barras LIKE $qdb OR b.suplemen=$qba OR a.alterno LIKE $qba) AND a.activo='S' AND a.tipo='Articulo'
				ORDER BY a.descrip LIMIT ".$this->autolimit;
			$cana=1;

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['label']   = '('.$row['codigo'].') '.$row['descrip'].' '.$row['precio1'].' Bs. - '.$row['existen'];
					$retArray['value']   = $row['codigo'];
					$retArray['codigo']  = $row['codigo'];
					$retArray['cana']    = $cana;
					$retArray['tipo']    = $row['tipo'];
					$retArray['peso']    = $row['peso'];
					$retArray['ultimo']  = $row['ultimo'];
					$retArray['pond']    = $row['pond'];
					$retArray['base1']   = $row['precio1']*100/(100+$row['iva']);
					$retArray['base2']   = $row['precio2']*100/(100+$row['iva']);
					$retArray['base3']   = $row['precio3']*100/(100+$row['iva']);
					$retArray['base4']   = $row['precio4']*100/(100+$row['iva']);
					$retArray['descrip'] = utf8_encode($row['descrip']);
					//$retArray['descrip'] = wordwrap($row['descrip'], 25, '<br />');
					$retArray['iva']     = $row['iva'];
					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
	        }else{
				$retArray[0]['label']    = 'No se consiguieron productos';
				$retArray[0]['value']    = '';
				$retArray[0]['codigo']   = '';
				$retArray[0]['cana']     = 0;
				$retArray[0]['tipo']     = 0;
				$retArray[0]['peso']     = 0;
				$retArray[0]['ultimo']   = 0;
				$retArray[0]['pond']     = 0;
				$retArray[0]['base1']    = 0;
				$retArray[0]['base2']    = 0;
				$retArray[0]['base3']    = 0;
				$retArray[0]['base4']    = 0;
				$retArray[0]['descrip']  = 0;
				$retArray[0]['iva']      = 0;

				$data = json_encode($retArray);
			}
		}
		echo $data;
	}

	function ordpart(){
		$ordp  = $this->input->post('ordp');
		$esta  = $this->input->post('esta');
		$tipo  = $this->input->post('tipo');

		$data = '[{ }]';
		if($ordp !== false &&  $esta !== false &&  $tipo!== false){
			$dbnumero=$this->db->escape($ordp);
			if($tipo=='E'){
				$mSQL="SELECT c.codigo,COALESCE(b.descrip,c.descrip) AS descrip
				,SUM(COALESCE(b.cantidad*IF(tipoordp='E',-1,1),0)) AS tracana
				,c.cantidad
				FROM stra AS a
				JOIN itstra AS b ON a.numero=b.numero
				RIGHT JOIN ordpitem AS c ON a.ordp=c.numero AND b.codigo=c.codigo
				WHERE c.numero=$dbnumero
				GROUP BY c.codigo";

				$retArray=$retorno=array();
				$query = $this->db->query($mSQL);
				if ($query->num_rows() > 0){
					foreach( $query->result_array() as  $row ) {
						$cana=$row['cantidad']+$row['tracana'];
						if($cana>0){
							$retArray['codigo']   = $row['codigo'];
							$retArray['cantidad'] = $cana;
							$retArray['descrip']  = utf8_encode($row['descrip']);

							array_push($retorno, $retArray);
						}
					}
					$data = json_encode($retorno);
				}
			}else{
				$dbesta=$this->db->escape($esta);
				$mSQL="SELECT b.codigo, b.descrip
				SUM(b.cantidad*IF(a.tipoordp='E',1,-1)) AS cantidad
				FROM stra AS a
				JOIN itstra AS b ON a.numero=b.numero
				WHERE a.ordp=$dbnumero AND a.esta=$dbesta
				GROUP BY b.codigo";

				$retArray=$retorno=array();
				$query = $this->db->query($mSQL);
				if ($query->num_rows() > 0){
					foreach( $query->result_array() as  $row ) {
						$cana=$row['cantidad'];
						if($cana>0){
							$retArray['codigo']   = $row['codigo'];
							$retArray['cantidad'] = $cana;
							$retArray['descrip']  = utf8_encode($row['descrip']);

							array_push($retorno, $retArray);
						}
					}
					$data = json_encode($retorno);
				}
			}
			echo $data;
		}

	}

	//Busca facturas para aplicarles devolucion
	function buscasfacdev(){
		$mid   = $this->input->post('q');
		$scli  = $this->input->post('scli');
		$qdb   = $this->db->escape('%'.$mid.'%');
		$sclidb= $this->db->escape($scli);

		$data = '[{ }]';

		if($mid !== false){
			$retArray = $retorno = array();

			$mSQL="SELECT a.numero, a.totalg, a.cod_cli, a.nombre,b.rifci, TRIM(b.nombre) AS nombre, TRIM(b.rifci) AS rifci, b.tipo, b.dire11 AS direc
				FROM  sfac AS a
				JOIN scli AS b ON a.cod_cli=b.cliente
				WHERE a.numero LIKE $qdb AND a.tipo_doc='F' AND MID(a.numero,1,1)<>'_'
				ORDER BY numero DESC LIMIT ".$this->autolimit;

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['label']   = $row['numero'].'-'.$row['nombre'].' '.$row['totalg'].' Bs.';
					$retArray['value']   = $row['numero'];
					$retArray['cod_cli'] = $row['cod_cli'];
					$retArray['rifci']   = $row['rifci'];
					$retArray['tipo']    = $row['tipo'];
					$retArray['direc']   = utf8_encode($row['direc']);
					$retArray['nombre']  = utf8_encode($row['nombre']);

					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
			}else{
				$retArray[0]['label']   = 'No se consiguieron facturas para aplicar';
				$retArray[0]['value']   = '';
				$retArray[0]['cod_cli'] = '';
				$retArray[0]['nombre']  = '';
				$data = json_encode($retArray);
			}
		}
		echo $data;
	}

	//Busca las formas de pago de una factura para devolverlos
	function buscasfpadev(){
		$mid = $this->input->post('q');

		$data = '[{ }]';
		if($mid !== false){
			$dbfactura = $this->db->escape($mid);
			$referen   = $this->datasis->dameval('SELECT referen FROM sfac WHERE tipo_doc=\'F\' AND numero='.$dbfactura);
			$retArray = $retorno = array();
			$mSQL="SELECT SUM(ROUND((bb.cana-bb.dev)*preca*(1+bb.iva/100),2)) AS monto FROM (
				SELECT aa.cana,SUM(COALESCE(d.cana,0)) AS dev,aa.codigo,aa.iva,aa.preca
				FROM (SELECT SUM(b.cana) AS cana,TRIM(a.codigo) AS codigo,a.iva,b.preca,b.numa
				FROM sinv AS a
				JOIN sitems AS b ON a.codigo=b.codigoa
				WHERE b.numa=$dbfactura AND b.tipoa='F'
				GROUP BY b.codigoa,b.preca) AS aa
				LEFT JOIN sfac   AS c  ON aa.numa=c.factura AND c.tipo_doc='D'
				LEFT JOIN sitems AS d ON c.numero=d.numa AND c.tipo_doc=d.tipoa AND aa.codigo=d.codigoa AND aa.preca=d.preca
				GROUP BY aa.codigo,aa.preca
				) AS bb";

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $id=>$row ) {
					$retArray['tipo']    = ($referen=='C')? '' : 'EF';
					$retArray['monto']   = round($row['monto'],2);
					$retArray['num_ref'] = '';
					$retArray['banco']   = '';
					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
	        }
		}
		echo $data;
	}

	//Busca los articulos de una factura para devolverlos
	function buscasinvdev(){
		$mid = $this->input->post('q');

		$data = '[ ]';
		if($mid !== false){
			$dbfactura = $this->db->escape($mid);
			$retArray = $retorno = array();
			$mSQL="SELECT
				aa.descrip,aa.cana,SUM(COALESCE(d.cana,0)) AS dev,aa.codigo, aa.precio1,aa.precio2,aa.precio3,aa.precio4,
				aa.iva,aa.existen,aa.tipo,aa.peso, aa.ultimo, aa.pond,aa.preca,aa.detalle
				FROM (SELECT TRIM(a.descrip) AS descrip,SUM(b.cana) AS cana,TRIM(a.codigo) AS codigo, a.precio1,a.precio2,a.precio3,a.precio4,
				a.iva,a.existen,a.tipo,a.peso, a.ultimo, a.pond,b.preca,b.numa,b.detalle
				FROM sinv AS a
				JOIN sitems AS b ON a.codigo=b.codigoa
				WHERE b.numa=$dbfactura AND b.tipoa='F'
				GROUP BY b.codigoa,b.preca) AS aa
				LEFT JOIN sfac   AS c  ON aa.numa=c.factura AND c.tipo_doc='D'
				LEFT JOIN sitems AS d ON c.numero=d.numa AND c.tipo_doc=d.tipoa AND aa.codigo=d.codigoa AND aa.preca=d.preca
				GROUP BY aa.codigo,aa.preca
				ORDER BY aa.descrip";

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $id=>$row ) {
					if(empty($row['cana'])) $row['cana']=0;
					if(empty($row['dev']))  $row['dev'] =0;
					$saldo = $row['cana']-$row['dev'];
					if($saldo <=0) continue;
					$retArray['codigo']  = utf8_encode($row['codigo']);
					$retArray['detalle'] = utf8_encode(trim($row['detalle']));
					$retArray['cana']    = $saldo;
					$retArray['tipo']    = $row['tipo'];
					$retArray['peso']    = $row['peso'];
					$retArray['ultimo']  = $row['ultimo'];
					$retArray['pond']    = $row['pond'];
					$retArray['preca']   = round($row['preca'],2);
					$retArray['base1']   = round($row['precio1']*100/(100+$row['iva']),2);
					$retArray['base2']   = round($row['precio2']*100/(100+$row['iva']),2);
					$retArray['base3']   = round($row['precio3']*100/(100+$row['iva']),2);
					$retArray['base4']   = round($row['precio4']*100/(100+$row['iva']),2);
					$retArray['descrip'] = utf8_encode($row['descrip']);
					$retArray['iva']     = $row['iva'];
					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
	        }
		}
		echo $data;
	}

	//Busca los articulos que esten por rma
	function buscastrarma(){
		$sprv = $this->input->post('sprv');
		$alma = $this->input->post('alma');

		$data = '[ ]';
		if($sprv !== false && $alma !== false){
			$dbsprv = $this->db->escape($sprv);
			$dbalma = $this->db->escape($alma);
			$retArray = $retorno = array();
			$mSQL="SELECT a.codigo, SUM(IF(b.envia=$dbalma,-1,1)*a.cantidad) AS cantidad, a.descrip
				FROM itstra AS a
				JOIN stra AS b ON a.numero=b.numero
				WHERE b.proveed=$dbsprv AND (b.envia=$dbalma OR b.recibe=$dbalma)
				GROUP BY a.codigo
				HAVING cantidad>0";

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $id=>$row ) {
					$retArray['codigo']  = utf8_encode($row['codigo']);
					$retArray['descrip'] = utf8_encode($row['descrip']);
					$retArray['cantidad']= $row['cantidad'];
					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
	        }
		}
		echo $data;
	}

	//
	// Busca Plan de cuentas
	//
	function buscacpla(){
		$mid   = $this->input->post('q');
		if($mid == false) $mid  = $this->input->post('term');

		$qdb   = $this->db->escape($mid.'%');

		$data = '[{ }]';
		if($mid !== false){
			$qformato=$this->datasis->formato_cpla();
			$retArray = $retorno = array();

			$mSQL="SELECT codigo, descrip, departa, ccosto
			FROM cpla WHERE codigo LIKE $qdb AND codigo LIKE \"$qformato\"";

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['label']    = $row['codigo'].'-'.utf8_encode($row['descrip']);
					$retArray['value']    = $row['codigo'];
					$retArray['descrip']  = utf8_encode($row['descrip']);
					$retArray['departa']  = $row['departa'];
					$retArray['ccosto']   = $row['ccosto'];

					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
			}else{
				//Busca por Descripcion
				$qdb   = $this->db->escape('%'.$mid.'%');
				$mSQL="SELECT codigo, descrip, departa, ccosto FROM cpla WHERE descrip LIKE $qdb AND codigo LIKE \"$qformato\"";;
				$query = $this->db->query($mSQL);
				if ($query->num_rows() > 0){
					foreach( $query->result_array() as  $row ) {
						$retArray['label']    = $row['codigo'].'-'.utf8_encode($row['descrip']);
						$retArray['value']    = $row['codigo'];
						$retArray['descrip']  = utf8_encode($row['descrip']);
						$retArray['departa']  = $row['departa'];
						$retArray['ccosto']   = $row['ccosto'];

						array_push($retorno, $retArray);
					}
					$data = json_encode($retorno);
				}else{
					$retArray[0]['label']    = 'No se consiguieron cuentas';
					$retArray[0]['value']    = '';
					$retArray[0]['descrip']  = '';
					$retArray[0]['departa']  = '';
					$retArray[0]['ccosto']   = '';

					$data = json_encode($retArray);
				}
			}
		}
		echo $data;
	}

	//Autocomplete para buscar las reservaciones
	function buscares(){
		$mid   = $this->input->post('q');
		$qdb   = $this->db->escape('%'.$mid.'%');
		$scli  = $this->input->post('scli');

		$data = '[{ }]';
		if($mid !== false){
			$qformato=$this->datasis->formato_cpla();
			$retArray = $retorno = array();
			if(!empty($scli)) $ww='AND cliente='.$this->db->escape($scli); else $ww='';

			$mSQL="SELECT a.id,a.numero,a.fecha,a.cliente,a.edificacion,a.inmueble,a.reserva,b.nombre,b.rifci, b.tipo AS sclitipo,dire11 AS direc,c.uso
			FROM edres AS a
			JOIN scli AS b ON a.cliente=b.cliente
			JOIN edinmue AS c ON a.inmueble=c.id
			WHERE numero LIKE $qdb  $ww";

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['label']    = $row['numero'].'-'.utf8_encode($row['nombre']);
					$retArray['value']    = $row['numero'];
					$retArray['nombre']   = utf8_encode($row['nombre']);
					$retArray['edifi']    = $row['edificacion'];
					$retArray['inmue']    = $row['inmueble'];
					$retArray['rifci']    = $row['rifci'];
					$retArray['cliente']  = $row['cliente'];
					$retArray['sclitipo'] = $row['sclitipo'];
					$retArray['direc']    = $row['direc'];
					$retArray['uso']      = $row['uso'];

					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
			}else{
				$retArray['label']    = 'No se consiguieron reservaciones';
				$retArray['value']    = '';
				$retArray['nombre']   = '';
				$retArray['edifi']    = '';
				$retArray['inmue']    = '';
				$retArray['rifci']    = '';
				$retArray['cliente']  = '';
				$retArray['sclitipo'] = '';
				$retArray['direc']    = '';
				$retArray['uso']      = '';

				$data = json_encode($retArray);
			}
		}
		echo $data;
	}

	//Autocomplete para mgas
	function automgas(){
		$q   = $this->input->post('q');

		$data = '[{ }]';
		if($q!==false){
			$mid = $this->db->escape('%'.$q.'%');
			$mSQL = "SELECT a.codigo, a.descrip
				FROM mgas AS a
			WHERE a.codigo LIKE ${mid} OR a.descrip LIKE ${mid} ORDER BY a.descrip LIMIT ".$this->autolimit;

			$query = $this->db->query($mSQL);
			$retArray = array();
			$retorno = array();
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['value']      = $row['codigo'];
					$retArray['label']      = trim($row['codigo']).' - '.utf8_encode(trim($row['descrip']));
					$retArray['codigo']     = utf8_encode(trim($row['codigo']));
					$retArray['descrip']    = utf8_encode(trim($row['descrip']));

					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
			}
		}
		echo $data;
	}

	//Autocomplete para las labores de sinv
	function buscaordplabor(){
		$mid   = $this->input->post('q');
		$data = '[{ }]';
		if($mid!==false){
			$mid  = $this->db->escape($mid);
			$mSQL = "SELECT a.estacion,a.nombre,a.actividad,a.tunidad,a.tiempo
				FROM sinvplabor AS a
			WHERE a.producto=${mid}";

			$query = $this->db->query($mSQL);
			$retArray = array();
			$retorno = array();
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['nombre']   = utf8_encode(trim($row['nombre']));
					$retArray['actividad']= $row['actividad'];
					$retArray['tunidad']  = $row['tunidad'];
					$retArray['tiempo']   = $row['tiempo'];
					$retArray['estacion'] = $row['estacion'];

					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
			}
		}
		echo $data;
	}

	//Autocomplete para las recetas de sinv
	function buscaordpitem(){
		$mid   = $this->input->post('q');
		$data = '[{ }]';
		if($mid!==false){
			$mid  = $this->db->escape($mid);
			$mSQL = "SELECT a.codigo, a.descrip,a.cantidad,a.merma,a.ultimo
				FROM sinvpitem AS a
			WHERE a.producto=${mid}";

			$query = $this->db->query($mSQL);
			$retArray = array();
			$retorno = array();
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['codigo']  = utf8_encode(trim($row['codigo']));
					$retArray['descrip'] = utf8_encode(trim($row['descrip']));
					$retArray['merma']   = (empty($row['merma']))? 0 : $row['merma'];
					$retArray['cantidad']= $row['cantidad'];
					$retArray['ultimo']  = $row['ultimo'];

					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
			}
		}
		echo $data;
	}

	//Para cerrar la ventana luego de una operacion exitosa
	function reccierraventana($reload=null){
		if($reload!='N') $rr='$(window).unload(function() { window.opener.location.reload(); });'; else $rr='';
		$script='
		<script language="javascript" type="text/javascript">
		$(function(){
			'.$rr.'
			window.close();
		});
		</script>';

		$data['content'] = '<center>Operaci&oacute;n Exitosa</center>';
		$data['head']    = script('jquery.js').$script;
		$data['title']   = '';
		$this->load->view('view_ventanas', $data);
	}

	function buscasinv2(){
		//busca por CODIGO comience por la busqueda LIKE 'BUSQUEDA%',
		//sino busca por CODIGO en cualquier parte LIKE '%BUSQUEDA%',
		//sino consigue buscar los que comiencen en DESCRIP LIKE 'BUSQUEDA%'
		//sino busca por DESCRIP LIKE '%BUSQUEDA%'
		//acepta el parametro comodin
		//busca solo los activos

		$comodin=$this->datasis->traevalor('COMODIN');
		$mid  = $this->input->post('q');
		if(strlen($comodin)==1){
			$mid=str_replace($comodin,'%',$mid);
		}

		$data = '[{ }]';
		if($mid !== false){
			$retArray = $retorno = array();

			$mSQL="SELECT DISTINCT TRIM(a.descrip) AS descrip, TRIM(a.codigo) AS codigo, a.precio1,precio2,precio3,precio4, a.iva,a.existen,a.tipo
				,a.peso, a.ultimo, a.pond,a.formcal,a.id FROM sinv AS a
				WHERE a.codigo LIKE ".$this->db->escape($mid.'%')." AND a.activo='S'
				ORDER BY a.descrip";


			$query = $this->db->query($mSQL);
			$cant=$query->num_rows();
			if(!($cant>0)){
				$mSQL="SELECT DISTINCT TRIM(a.descrip) AS descrip, TRIM(a.codigo) AS codigo, a.precio1,precio2,precio3,precio4, a.iva,a.existen,a.tipo
				,a.peso, a.ultimo, a.pond,a.formcal,a.id FROM sinv AS a
				WHERE a.codigo LIKE ".$this->db->escape('%'.$mid.'%')." AND a.activo='S'
				ORDER BY a.descrip";
				$query = $this->db->query($mSQL);
				$cant=$query->num_rows();
			}

			if(!($cant>0)){
				$mSQL="SELECT DISTINCT TRIM(a.descrip) AS descrip, TRIM(a.codigo) AS codigo, a.precio1,precio2,precio3,precio4, a.iva,a.existen,a.tipo
				,a.peso, a.ultimo, a.pond,a.formcal,a.id FROM sinv AS a
				WHERE a.descrip LIKE ".$this->db->escape($mid.'%')." AND a.activo='S'
				ORDER BY a.descrip";
				$query = $this->db->query($mSQL);
				$cant=$query->num_rows();
			}

			if(!($cant>0)){
				$mSQL="SELECT DISTINCT TRIM(a.descrip) AS descrip, TRIM(a.codigo) AS codigo, a.precio1,precio2,precio3,precio4, a.iva,a.existen,a.tipo
				,a.peso, a.ultimo, a.pond,a.formcal,a.id FROM sinv AS a
				WHERE a.descrip LIKE ".$this->db->escape('%'.$mid.'%')." AND a.activo='S'
				ORDER BY a.descrip";
				$query = $this->db->query($mSQL);
				$cant=$query->num_rows();
			}

			$cana=1;
			if ($cant > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['label']   = '('.$row['codigo'].') '.$row['descrip'].' '.$row['precio1'].' Bs. - '.$row['existen'];
					$retArray['value']   = $row['codigo'];
					$retArray['codigo']  = $row['codigo'];
					$retArray['cana']    = $cana;
					$retArray['tipo']    = $row['tipo'];
					$retArray['peso']    = $row['peso'];
					$retArray['ultimo']  = $row['ultimo'];
					$retArray['pond']    = $row['pond'];
					$retArray['formcal'] = $row['formcal'];
					$retArray['id']      = $row['id'];
					$retArray['base1']   = $row['precio1']*100/(100+$row['iva']);
					$retArray['base2']   = $row['precio2']*100/(100+$row['iva']);
					$retArray['base3']   = $row['precio3']*100/(100+$row['iva']);
					$retArray['base4']   = $row['precio4']*100/(100+$row['iva']);
					$retArray['descrip'] = utf8_encode($row['descrip']);
					//$retArray['descrip'] = wordwrap($row['descrip'], 25, '<br />');
					$retArray['iva']     = $row['iva'];
					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
	        }
		}
		echo $data;
	}

	//Busca sinv solo articulos
	function buscaaran(){
		$comodin= $this->datasis->traevalor('COMODIN');
		$mid    = $this->input->post('q');
		if(strlen($comodin)==1 && $comodin!='%' && $mid!==false){
			$mid=str_replace($comodin,'%',$mid);
		}
		$qdb  = $this->db->escape($mid.'%');
		$qba  = $this->db->escape($mid);

		$data = '[{ }]';
		if($mid !== false){
			$retArray = $retorno = array();
			$mSQL="SELECT a.codigo, a.descrip, a.tarifa
			FROM aran AS a
			WHERE descrip LIKE $qdb OR codigo LIKE $qdb";

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['label']   = '('.$row['codigo'].') '.$row['descrip'].' '.$row['tarifa'];
					$retArray['value']   = $row['codigo'];
					$retArray['tarifa']  = $row['tarifa'];
					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
	        }
		}
		echo $data;
	}

	//Funcion para traer los clientes en los pedidos ligeros
	function scliex(){
		$comodin= $this->datasis->traevalor('COMODIN');
		$mid    = $this->input->post('q');
		if(strlen($comodin)==1 && $comodin!='%' && $mid!==false){
			$mid=str_replace($comodin,'%',$mid);
		}
		$qdb  = $this->db->escape($mid.'%');
		$qba  = $this->db->escape($mid);

		$data = '[{ }]';
		if($mid !== false){
			$retArray = $retorno = array();

			//Cheque si existe el codigo
			$mSQL="SELECT TRIM(nombre) AS nombre, TRIM(rifci) AS rifci, cliente
				FROM scli WHERE cliente=${qba} LIMIT 1";
			$query = $this->db->query($mSQL);
			if ($query->num_rows() == 1){
				$row = $query->row_array();

				$retArray['rifci']   = $row['rifci'];
				$retArray['nombre']  = utf8_encode($row['nombre']);
				$retArray['cod_cli'] = $row['cliente'];
				array_push($retorno, $retArray);
				$ww=" AND cliente<>${qba}";
			}else{
				$ww='';
			}

			$mSQL="SELECT TRIM(nombre) AS nombre, TRIM(rifci) AS rifci, cliente
				FROM scli WHERE (cliente LIKE ${qdb} OR rifci LIKE ${qdb} OR nombre LIKE ${qdb}) $ww
				ORDER BY rifci LIMIT ".$this->autolimit;

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['rifci']   = $row['rifci'];
					$retArray['nombre']  = utf8_encode($row['nombre']);
					$retArray['cod_cli'] = $row['cliente'];
					array_push($retorno, $retArray);
				}
			}
			if(count($data)>0)
				$data = json_encode($retorno);
		}
		echo $data;
		return true;
	}

	// Para JQGRID
	function ddsucu(){
		$mSQL = "SELECT TRIM(codigo) codigo, CONCAT(TRIM(codigo),' ',TRIM(sucursal)) sucursal FROM sucu ORDER BY codigo";
		echo $this->datasis->llenaopciones($mSQL, true);
	}

	function ddtarjeta(){
		$mSQL = "SELECT tipo, CONCAT(tipo,' ',nombre) nombre FROM tarjeta WHERE activo!='N' AND tipo NOT IN ('EF', 'DE', 'NC','RI','IR','RP')";
		echo $this->datasis->llenaopciones($mSQL, true);
	}

	function ddbanco(){
		$mSQL = "SELECT cod_banc, CONCAT(cod_banc, ' ', nomb_banc) banco FROM tban WHERE cod_banc<>'CAJ' ORDER BY nomb_banc ";
		echo $this->datasis->llenaopciones($mSQL, true);
	}

	function ddbanc(){
		$tipo = $this->uri->segment(3);
		$id   = $this->uri->segment(4);
		$mSQL  = "SELECT codbanc, CONCAT(codbanc, ' ', banco, numcuent) banco ";
		$mSQL .= "FROM banc ";
		$mSQL .= "WHERE activo='S'  ";
		if ( $tipo == 'B' ) $mSQL .= " AND tbanco<>'CAJ' ";
		if ( $tipo == 'C' ) $mSQL .= " AND tbanco='CAJ' ";
		$mSQL .= "ORDER BY (tbanco='CAJ'), codbanc ";
		echo $this->datasis->llenaopciones($mSQL, true, $id);
	}


	function ddusuario(){
		$mSQL = "SELECT us_codigo, CONCAT(us_codigo, ' ', us_nombre) us_nombre FROM usuario ORDER BY us_codigo";
		echo $this->datasis->llenaopciones($mSQL, true);
	}

	function ddcajero(){
		$mSQL = "SELECT cajero, CONCAT(cajero, ' ', nombre) nombre FROM scaj ORDER BY nombre";
		echo $this->datasis->llenaopciones($mSQL, true);
	}
	function ddcaub(){
		$mSQL = "SELECT ubica, CONCAT(ubica, ' ', ubides) ubides FROM caub ORDER BY ubica ";
		echo $this->datasis->llenaopciones($mSQL, true);
	}
	function ddvende(){
		$mSQL = "SELECT TRIM(vendedor) vendedor, CONCAT(trim(vendedor), ' ', trim(nombre)) nombre FROM vend ORDER BY vendedor ";
		echo $this->datasis->llenaopciones($mSQL, true);
	}

	function dddivi(){
		$mSQL = "SELECT division, CONCAT(division,' ',descrip) descrip  FROM divi ORDER BY division";
		echo $this->datasis->llenaopciones($mSQL, true);
	}

	function dddepag(){
		$mSQL = "SELECT depto, CONCAT(depto,' ',descrip) descrip FROM dpto WHERE tipo='G' ORDER BY depto";
		echo $this->datasis->llenaopciones($mSQL, true);
	}

	function dddepai(){
		$mSQL = "SELECT depto, CONCAT(depto,' ',descrip) descrip FROM dpto WHERE tipo='I' ORDER BY depto";
		echo $this->datasis->llenaopciones($mSQL, true);
	}
	function ddgrcl(){
		$mSQL = "SELECT grupo, CONCAT(grupo, ' ', gr_desc) banco FROM grcl ORDER BY grupo ";
		echo $this->datasis->llenaopciones($mSQL, true);
	}



	//***************************************
	//          BUSCA GASTO
	//***************************************
	function buscamgas(){
		$mid  = $this->input->post('q');
		if($mid == false) $mid  = $this->input->post('term');

		$qdb  = $this->db->escape('%'.$mid.'%');
		$qmid = $this->db->escape($mid.'%');

		$data = '[{ }]';
		if($mid !== false){
			$retArray = $retorno = array();

			//Cheque si existe el codigo
			/*
			$mSQL="SELECT TRIM(descrip) AS descrip, codigo FROM mgas WHERE codigo=${qmid} LIMIT 1";
			$query = $this->db->query($mSQL);
			if ($query->num_rows() == 1){
				$row = $query->row_array();
				$retArray['value']   = $row['codigo'];
				$retArray['label']   = '('.$row['codigo'].') '.utf8_encode($row['nombre']);
				array_push($retorno, $retArray);
				$ww=" AND codigo<>${qmid}";
			}else{
				$ww='';
			}
			*/

			$mSQL="SELECT TRIM(descrip) AS nombre, codigo FROM mgas WHERE descrip LIKE ${qdb} OR codigo LIKE ${qmid} ORDER BY descrip LIMIT 20";
			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['value']   = $row['codigo'];
					$retArray['label']   = utf8_encode($row['nombre']).'('.$row['codigo'].') ';
					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
			}
		}
		echo $data;
		return true;
	}

	//***************************************
	//          BUSCA GASTO o PROVEEDOR
	//***************************************
	function buscasprvmgas(){
		$tipo  = $this->input->post('cargo');
		$cta   = $this->input->post('acelem');

		if ( $cta == 'ctade')
			$tipo = substr($tipo,0,1);
		else
			$tipo = substr($tipo,2,1);

		if ( $tipo == 'P')
			$this->buscasprv();
		else
			$this->buscamgas();
	}

	//***************************************
	//          BUSCA PERSONA
	//***************************************
	function buscapers(){
		$mid  = $this->input->post('q');
		if($mid == false) $mid  = $this->input->post('term');

		$qdb  = $this->db->escape('%'.$mid.'%');
		$qmid = $this->db->escape($mid.'%');

		$data = '[{ }]';
		if($mid !== false){
			$retArray = $retorno = array();
			$mSQL="	SELECT codigo, CONCAT(TRIM(apellido),', ',TRIM(nombre),' (',nacional,TRIM(cedula),')') AS label,
					CONCAT(TRIM(apellido),', ',TRIM(nombre))  nombre, sueldo, enlace
				FROM pers WHERE nombre LIKE ${qdb} OR apellido LIKE ${qdb} OR codigo LIKE ${qmid} ORDER BY nombre LIMIT 20";
			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['value']   = $row['codigo'];
					$retArray['label']   = utf8_encode($row['label']);
					$retArray['nombre']  = utf8_encode($row['nombre']);
					$retArray['sueldo']  = $row['sueldo'];
					$retArray['enlace']  = utf8_encode($row['enlace']);
					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
			}
		}
		echo $data;
		return true;
	}

	//***************************************
	//          BUSCA PERSONA
	//***************************************
	function buscaconc(){
		$mid  = $this->input->post('q');
		if($mid == false) $mid  = $this->input->post('term');

		$qdb  = $this->db->escape('%'.$mid.'%');
		$qmid = $this->db->escape($mid.'%');

		$data = '[{ }]';
		if($mid !== false){
			$retArray = $retorno = array();
			$mSQL="	SELECT concepto, CONCAT(TRIM(descrip),' (',concepto,')') AS label,
					IF(tipo='A','Asignacion',IF(tipo='D','Deduccion','Otros')) tipo,
					TRIM(descrip) descrip, formula
				FROM conc WHERE descrip LIKE ${qdb} OR concepto LIKE ${qmid} ORDER BY concepto LIMIT 20";
			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['value']   = $row['concepto'];
					$retArray['label']   = utf8_encode($row['label']);
					$retArray['tipo']    = utf8_encode($row['tipo']);
					$retArray['descrip'] = utf8_encode($row['descrip']);
					$retArray['formula'] = utf8_encode($row['formula']);
					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
			}
		}
		echo $data;
		return true;
	}


	//***************************************
	//      BUSCA EFECTOS DE CLIENTE
	//***************************************
	function buscasmovep(){
		$mid  = $this->input->post('q');
		if($mid == false) $mid  = $this->input->post('term');

		$qdb  = $this->db->escape('%'.$mid.'%');
		$qmid = $this->db->escape($mid.'%');

		$cod_cli = $this->input->post('cargo');

		$data = '[{ }]';
		if($mid !== false){
			$retArray = $retorno = array();
			$mSQL="	SELECT numero, CONCAT(tipo_doc, numero, ' ', fecha, ' Monto:', monto-abonos) label, tipo_doc, monto-abonos monto, abonos FROM smov
				WHERE cod_cli=".$this->db->escape($cod_cli)." AND monto>abonos AND tipo_doc IN ('FC','ND') AND numero LIKE ${qmid} ORDER BY tipo_doc, numero LIMIT 20";
			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['value']    = $row['numero'];
					$retArray['label']    = utf8_encode($row['label']);
					$retArray['tipo_doc'] = $row['tipo_doc'];
					$retArray['monto']    = $row['monto'];
					$retArray['abonos']   = $row['abonos'];
					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
			}
		}
		echo $data;
		return true;
	}

	function ajaxsprv(){
		$rif=$this->input->post('rif');
		if($rif!==false){
			$dbrif=$this->db->escape($rif);
			$nombre=$this->datasis->dameval("SELECT nombre FROM provoca WHERE rif=$dbrif");
			if(empty($nombre)){
				$nombre=$this->datasis->dameval("SELECT nombre FROM sprv WHERE rif=$dbrif");
			}
			if(empty($nombre)){
				if(preg_match("/(^[VEJG][0-9]{9}[[:blank:]]*$)/", $rif)>0){
					$t=$this->_crif($rif);
					$nombre=$t['nombre'];
				}elseif(preg_match("/(^[VE][0-9]+[[:blank:]]*$)/", $rif)>0){
					$t=$this->_cced($rif);
					$nombre=$t['nombre'];
				}
			}
			echo $nombre;
		}
	}

	//***************************************
	//  CONSULTA LA CEDULA O RIF EN INTERNET
	//***************************************
	function traerif(){
		$rifci = $this->input->post('rifci');
		$t=array(
			'error' =>1,
			'msj'   =>'Cedula o rif no valido',
			'nombre'=>''
		);

		if($rifci == false) echo json_encode($t);

		if(preg_match("/(^[VEJG][0-9]{9}[[:blank:]]*$)/", $rifci)>0){
			$t=$this->_crif($rifci);
		}elseif(preg_match("/(^[VE][0-9]+[[:blank:]]*$)/", $rifci)>0){
			$t=$this->_cced($rifci);
		}
		echo json_encode($t);
	}

	function _crif($rif){
		$rt=array(
			'error' =>0,
			'msj'   =>'',
			'nombre'=>''
		);

		$postdata = http_build_query(array('p_rif' => strtoupper($rif)));
		$opts = array('http' =>array(
				'method'  => 'POST',
				'timeout' => 7,
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => $postdata
			)
		);
		$url=trim($this->datasis->traevalor('CONSULRIF'));
		$context = stream_context_create($opts);
		$result = @file($url, false, $context);
		if($result===false){
			$rt['error']=1;
			$rt['msj']  ='Recurso no disponible';
		}else{
			foreach($result as $line){
				if(stripos($line,$rif)!==false){
					$linea=str_replace('&nbsp;','',$line);
					$linea=html_entity_decode(strip_tags($linea));
					break;
				}
			}
			$linea = preg_replace('/\(.*\)/', '', $linea);
			$nombre= trim(str_ireplace($rif,'',$linea));
			if(stripos($nombre,'No existe')===false){
				$rt['nombre'] =utf8_encode($nombre);
			}else{
				$rt['error']=1;
				$rt['msj']  ='Contribuyente no encontrado';
			}
		}
		return $rt;
	}

	function _cced($ced){
		$rt=array(
			'error' =>0,
			'msj'   =>'',
			'nombre'=>''
		);

		$postdata = http_build_query(array(
			'nacionalidad' => strtoupper($ced[0]),
			'cedula'       => substr($ced,1)
			)
		);
		$opts = array('http' =>array(
				'method'  => 'GET',
				'timeout' => 7,
				'header'  => 'Content-type: application/x-www-form-urlencoded',
			)
		);
		$context = stream_context_create($opts);
		$result = @file('http://www.cne.gob.ve/web/registro_electoral/ce.php?'.$postdata, false, $context);
		if($result===false){
			$rt['error']=1;
			$rt['msj']  ='Recurso no disponible';
		}else{
			$act=false;
			foreach($result as $line){
				if($act){
					$linea=html_entity_decode(strip_tags($line));
					break;
				}elseif(stripos($line,'Nombre')!==false){
					$act=true;
				}
			}
			if(isset($linea)){
				$linea = preg_replace('/\(.*\)/', '', $linea);
				$rt['nombre'] = utf8_encode(trim($linea));
			}else{
				$rt['error']=1;
				$rt['msj']  ='Cedula no encontrada';
			}
		}
		return $rt;
	}

}