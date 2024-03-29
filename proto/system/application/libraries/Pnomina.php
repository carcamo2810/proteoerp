<?php
class fnomina{

	var $ci;
	var $CODIGO;
	var $fdesde;
	var $fhasta;

	function fnomina(){
		$this->ci =& get_instance();
	}

	function SUELDO_MES(){
		$CODIGO=$this->ci->db->escape($this->CODIGO);
		$SUELDOA = 0;
		$mFRECU  = $this->ci->datasis->dameval("SELECT b.tipo FROM pers a JOIN noco b ON a.contrato=b.codigo WHERE a.codigo=$CODIGO");
		$mMONTO  = $this->ci->datasis->dameval("SELECT sueldo FROM pers WHERE codigo=$CODIGO");

		if($mFRECU == 'O') $mFRECU  = $this->ci->datasis->dameval("SELECT tipo FROM pers WHERE codigo=$CODIGO");
		if($mFRECU == 'S') $SUELDOA = $mMONTO * 52 / 12;
		if($mFRECU == 'B') $SUELDOA = $mMONTO * 26 / 12;
		if($mFRECU == 'Q') $SUELDOA = $mMONTO * 2;
		if($mFRECU == 'M') $SUELDOA = $mMONTO;
		return $SUELDOA;
	}

	function SUELDO_QUI(){
		$CODIGO=$this->ci->db->escape($this->CODIGO);
		$SUELDOA = 0;
		$mFRECU  = $this->ci->datasis->dameval("SELECT b.tipo FROM pers a JOIN noco b ON a.contrato=b.codigo WHERE a.codigo=$CODIGO");
		$mMONTO  = $this->ci->datasis->dameval("SELECT sueldo FROM pers WHERE codigo=$CODIGO");

		if($mFRECU == 'O') $mFRECU = $this->ci->datasis->dameval("SELECT tipo FROM pers WHERE codigo=$CODIGO");
		if($mFRECU == 'S') $SUELDOA = $mMONTO * 52 / 24;
		if($mFRECU == 'B') $SUELDOA = $mMONTO * 26 / 24;
		if($mFRECU == 'Q') $SUELDOA = $mMONTO;
		if($mFRECU == 'M') $SUELDOA = $mMONTO/2;
		return $SUELDOA;
	}

	function SUELDO_SEM(){
		$CODIGO=$this->ci->db->escape($this->CODIGO);
		$SUELDOA = 0;
		$mFRECU  = $this->ci->datasis->dameval("SELECT b.tipo FROM pers a JOIN noco b ON a.contrato=b.codigo WHERE a.codigo=$CODIGO");
		$mMONTO  = $this->ci->datasis->dameval("SELECT sueldo FROM pers WHERE codigo=$CODIGO");

		if($mFRECU == 'O') $mFRECU  = $this->ci->datasis->dameval("SELECT tipo FROM pers WHERE codigo=$CODIGO");
		if($mFRECU == 'S') $SUELDOA = $mMONTO;
		if($mFRECU == 'B') $SUELDOA = $mMONTO/2;
		if($mFRECU == 'Q') $SUELDOA = $mMONTO*24/52;
		if($mFRECU == 'M') $SUELDOA = $mMONTO*12/52 ;
		return $SUELDOA;
	}

	function SUELDO_DIA(){
		$CODIGO=$this->ci->db->escape($this->CODIGO);
		$SUELDOA = 0;
		$mFRECU  = $this->ci->datasis->dameval("SELECT b.tipo FROM pers a JOIN noco b ON a.contrato=b.codigo WHERE a.codigo=$CODIGO");
		$mMONTO  = $this->ci->datasis->dameval("SELECT sueldo FROM pers WHERE codigo=$CODIGO");

		if($mFRECU == 'O') $mFRECU  = $this->ci->datasis->dameval("SELECT tipo FROM pers WHERE codigo=$CODIGO");
		if($mFRECU == 'S') $SUELDOA = $mMONTO/7 ;
		if($mFRECU == 'B') $SUELDOA = $mMONTO/14;
		if($mFRECU == 'Q') $SUELDOA = $mMONTO/15;
		if($mFRECU == 'M') $SUELDOA = $mMONTO/30 ;
		return $SUELDOA;
	}

	function SUELDO_HOR(){
		$SUELDOA = $this->SUELDO_DIA()/8;
		return $SUELDOA;
	}

	function ANTIGUEDAD($mHASTA){
		$CODIGO=$this->ci->db->escape($this->CODIGO);
		$mDESDE  = $this->ci->datasis->dameval("SELECT inicio FROM pers WHERE codigo=$CODIGO");
		//if (empty($mHASTA)) $mHASTA = date();
		//return { mANOS, mMES, mDIAS };
	}

	function TRAESALDO($mmCONC){
		$CODIGO=$this->ci->db->escape($this->CODIGO);
		$mTCONC = $this->ci->datasis->dameval("SELECT COUNT(*) FROM prenom WHERE codigo=$CODIGO AND concepto='$mmCONC' ");
		if ($mTCONC == 1)
			$mTEMPO = $this->ci->datasis->dameval("SELECT valor FROM prenom WHERE codigo=$CODIGO AND concepto='$mmCONC' ");
		return $mTEMPO;
	}

	function TABUSCA($par){
		return 1;
	}

	function SUELDO_INT(){
		return 1;
	}

	function GRUPO($parr){
		return 1;
	}

	function ASIGNA(){
		return 1;
	}

	function SEMANAS(){
		$dsemana  = 1; //1 para lunes, 2 para martes .... 7 domingo
		$dated    = new DateTime($this->fdesde);
		$dateh    = new DateTime($this->fhasta);
		$interva1 = new DateInterval('P1D');
		$interva2 = new DateInterval('P7D');
		$dias  = 0;

		while($dated<=$dateh){
			if(date('N',$dated->getTimestamp())==$dsemana) $dias++;
			if($dias>0)
				$dated->add($interva1);
			else
				$dated->add($interva2);
		}
		return $dias;
	}
}

class Pnomina extends fnomina{

	var $MONTO;

	function pnomina(){
		parent::fnomina();
		//$this->CODIGO = $codigo;
	}

	function evalform($formula){
		$MONTO=$this->MONTO;
		$fformula=$this->_traduce($formula);
		$retorna='$rt='.$fformula.';';
		eval($retorna);
		return $rt;
	}

	function _traduce($formula){
		$CODIGO=$this->ci->db->escape($this->CODIGO);

		//para los if
		$long=strlen($formula);
		$pos=$long+1;
		while(1){
			$desp=$pos-$long-1;
			if(abs($desp)>=$long-1) break;
			$pos=strrpos($formula,'IF(',$desp);
			if($pos===false) break;
			$ig=null;
			$remp='?';
			for($i=$pos+2; $i<$long;$i++){
				if(preg_match('/[\'"]/',$formula[$i])>0 and is_null($ig)){
					$ig=$formula[$i];
				}elseif($formula[$i]==$ig and is_null($ig)===false){
					$ig=null;
				}elseif(is_null($ig)){
					switch ($formula[$i]) {
						case ',':
							$formula[$i]=$remp;
							$remp=':';
							break;
						case '(':
							$pila[]=$formula[$i];
							break;
						case ')':
							array_pop($pila);
							break;
					}
				}
				if(count($pila)==0) break;
			}
		}
		$formula=str_replace('IF(','(',$formula);
		//fin de if

		$metodos=get_class_methods('fnomina');
		foreach($metodos AS $metodo){
			$formula=str_replace($metodo.'(','$this->'.$metodo.'(',$formula);
		}

		$query = $this->ci->db->query("SELECT * FROM pers WHERE codigo=$CODIGO");
		if ($query->num_rows() > 0){
			$rows = $query->row_array();

			foreach($rows AS $ind=>$valor){
				if($ind!='fnomina'){
					$valor=trim($valor);
					$ind='X'.strtoupper($ind);
					$formula=str_replace($ind,$valor,$formula);
				}
			}
		}
		$formula=str_replace('XMONTO','$MONTO',$formula);
		$formula=str_replace('.AND.','&&',$formula);
		$formula=str_replace('.OR.','||',$formula);
		$formula=str_replace('.NOT.','!',$formula);

		return $formula;
	}

}
?>
