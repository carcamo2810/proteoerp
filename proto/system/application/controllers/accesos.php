<?php

class Accesos extends Controller
{
	function Accesos(){
		parent::Controller();
	}

	function index(){
		$this->session->set_userdata('panel', 9);
		$this->datasis->modulo_id(904,1);
		
		// Set the template valiables
		$mSQL='SELECT us_codigo, CONCAT( us_codigo,\' - \' ,us_nombre ) FROM usuario WHERE us_nombre != \'SUPERVISOR\' ORDER BY us_codigo';
		$dropdown=$this->datasis->consularray($mSQL);
		$data['content']  = form_open('accesos/crear');
		$data['content'] .= form_dropdown('usuario',$dropdown);
		$data['content'] .= form_submit('pasa','Aceptar');
		$data['content'] .= form_close();
		// Build the thing
		//$this->layout->buildPage('accesos/home', $data);
		
		$data["head"]    = '';
		$data['title']   = '<h1>Administraci&oacute;n de accesos</h1>';
		$this->load->view('view_ventanas', $data);
	}
	function crear(){
		$this->datasis->modulo_id(904,1);
		
		if (isset($_POST['usuario']))
			$usuario = $_POST['usuario'];
		else
			$usuario = $this->uri->segment(3);
		if (empty($usuario)) 
			redirect('/accesos');
		if(isset($_POST['copia']))
			$copia=$_POST['copia'];
		else
			$copia='';
		
		// Set the template valiables
		$mSQL='SELECT us_codigo, CONCAT( us_codigo,\' - \' ,us_nombre ) FROM usuario WHERE us_nombre != \'SUPERVISOR\' ORDER BY us_codigo';
		$dropdown=$this->datasis->consularray($mSQL);
		$data['title'] = '<h1>Accesos del usuario: '.$usuario.'</h1>';
		$data['content']  = form_open('accesos/crear');
		$data['content'] .= 'Copiar de: '.form_dropdown('copia',$dropdown,$copia);
		$data['content'] .= form_submit('pasa','Copiar');
		$data['content'] .= form_hidden('usuario',$usuario).form_close();
				
		$query = $this->db->query("SELECT us_nombre FROM usuario WHERE us_codigo='$usuario'");
		if($query->num_rows() == 1){
			if(!empty($copia))
				$acceso=$copia;
			else
				$acceso=$usuario;
			
			//$mSQL  = "SELECT DISTINCT a.modulo, a.titulo, 
			//          (SELECT acceso FROM intrasida b WHERE a.modulo=b.modulo AND usuario='$acceso') acceso, panel 
			//          FROM intramenu a 
			//          WHERE MID(a.modulo,1,1)!= '0' 
			//	  ORDER BY CONCAT(MID(modulo,1,1), LENGTH(modulo)), panel, modulo";

			
			$mSQL="SELECT a.modulo,a.titulo, IFNULL(b.acceso,'N') AS acceso ,a.panel
			FROM intramenu AS a
			LEFT JOIN intrasida AS b ON a.modulo=b.modulo AND b.usuario='$acceso'
			WHERE MID(a.modulo,1,1)!=0 ORDER BY MID(a.modulo,1,1),CHAR_LENGTH(TRIM(a.modulo)) ,a.panel, MID(a.modulo,2,3)";
             
			$mc = $this->db->query($mSQL);
			$data['content'].=form_open('accesos/guardar').form_hidden('usuario',$usuario).'<div id=\'ContenedoresDeData\'><table width=100% cellspacing="0">';
			$i=0;
			$panel = '';
			foreach( $mc->result() as $row ){
				if($row->acceso=='S') $row->acceso=TRUE; else $row->acceso=FALSE;
				
				if(strlen($row->modulo)==1) {
					$data['content'] .= '<tr><th colspan=2>'.$row->titulo.'</th></tr>';
					$panel = '';
				}elseif( strlen($row->modulo)==3 ) {
					if ($panel <> $row->panel ) {
					    $data['content'] .= '<tr><td colspan=2 bgcolor="#CCDDCC">'.$row->panel.'</td></tr>';
					    $panel = $row->panel ;
					};
					
					$data['content'] .= '<tr><td>'.$row->titulo.'</td><td>'.form_checkbox('accesos['.$i.']',$row->modulo,$row->acceso).'</td></tr>';
					$i++;
				}else{
					$data['content'] .= '<tr><td><b>&nbsp;&nbsp;-&nbsp;</b>'.$row->titulo.'</td><td>'.form_checkbox('accesos['.$i.']',$row->modulo,$row->acceso).'</td></tr>';
					$i++;
				}
			}
			$data['content'].='</table></div>';
			$data['content'].=form_hidden('usuario',$usuario).form_submit('pasa','Guardar').form_close().anchor('/accesos','Regresar');;     
		}else
			$data['content']='Usuario no V&aacute;lido, por favor selecione un usuario correcto.';
		
		$data["head"]    = style('estilos.css');
		$data['title']   = "<h1>Administraci&oacute;n de accesos, usuario <b>$usuario</b></h1>";
		$this->load->view('view_ventanas', $data);
		// Build the thing
		//$this->layout->buildPage('accesos/crear', $data);
	}

	function guardar(){
		$this->datasis->modulo_id(904001);
		$usuario = $_POST['usuario'];
		//$data['vaina'] ='';
		$modprin=0;
		$mSQL="DELETE FROM intrasida WHERE usuario='$usuario'";
		$this->db->simple_query($mSQL);
		
		if (isset($_POST['accesos']) > 0 ){
			foreach( $_POST['accesos'] as $codigo ){
				if($modprin != $codigo[0]){
					$modprin=$codigo[0];
					$mSQL="INSERT INTO intrasida (usuario,modulo,acceso) VALUES('$usuario','$modprin' ,'S')";
					$this->db->simple_query($mSQL);
				}
				$mSQL="INSERT INTO intrasida (usuario,modulo,acceso) VALUES('$usuario','$codigo' ,'S')";
				$this->db->simple_query($mSQL);
			}
		}
		
		$data["head"]    = style('estilos.css');
		$data['title']   = "<h1>Accesos Guardados para el usuario: $usuario</h1>";
		$data['content'] = anchor('/accesos','Regresar');
		$this->load->view('view_ventanas', $data);
		
		//$data['titulo1'] = "<h1>Accesos Guardados para el usuario: $usuario</h1>\n";
		//$data['vaina'] = $_POST;
		//// Build the thing
		//$this->layout->buildPage('accesos/guardar', $data);
	}
}

?>