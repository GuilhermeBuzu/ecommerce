<?php

/*
Desenvolvido por: Vanessa Schissato
Data: 12/12/2005
Calendario dinamico com navegacao pelos meses.

	(c) 2005,2006,2009 dos autores.

	PERMISS�O PARA ALTERAR, DIVULGAR E UTILIZAR ESTE C�DIGO DESDE QUE MANTIDO
	OS NOMES DOS DESENVOLVEDORES ORIGINAIS.

	THIS SCRIPT MAY BE USED AND CHANGED FREELY AS LONG AS THIS COPYRIGHT IS INTACT.

	Change log
	v1.2  07/10/2006 Fabio Issamu Oshiro
	As datas come�am pelo Domingo
	Hash de Feriados incluindo a P�scoa, Carnaval e outras datas m�veis

	Change log
	v2.0  07/12/2009 Marco Aurelio Curado - aureliocurado@gmail.com
	Altera��o no layout.
	Inclus�o de feriados em tabela mysql.
	Edi��o dos campos de feriados com ajax.
	Foi criada tabela de feriados fixos e a edi��o come�a com '_'.
	Foi criada tabela de feriados crist�o e outros feriados variaveis.
	Para resetar tabela mysql, chamar funcao criar_tabelas().
	Colocar usuario e senha do mysql para acessar as tabelas.
*/


//juntado variaveis get e post e utilizando somente $_POST
$_GET = $_GET + $_POST;
$_POST = $_GET;
$mysql = true;



///////////////////////////
/// continuacao do ajax ///
///////////////////////////
if (isset($_POST["ajax_salvar_id"])) {
	if (!$mysql)
		die;
	$data = $_POST['ajax_salvar_id'];
	$valor = $_POST['valor'];
	if (strlen($valor) && $valor[0] == "_") $fixo = true;
	else $fixo = false;

	//data com calendario fixo.  (20101207)
	if ($fixo) $data =substr($data,4,2)."-".substr($data,6,2);
	//data com calendario catolico.
	else $data = substr($data,0,4)."-".substr($data,4,2)."-".substr($data,6,2);
	//retirar espacos.
	if ($fixo) $valor[0] = " ";
	$valor = trim($valor);

	//mysql
	$conexao = conexao_mysql($mysql);
	if ($fixo) {
			$sql = "SELECT * from adm_feriado_fixo WHERE mes_dia = '$data'";
			echo $sql."<br>\r\n";
			$_feriado = 0;
			$dados = mysql_query($sql);
			$total = mysql_num_rows($dados);
			if ($total > 0)
				$_feriado = mysql_fetch_array($dados);
			if ($_feriado && $valor != "")
					$sql = "UPDATE adm_feriado_fixo
							SET mes_dia = '$data', descricao = '$valor'
							WHERE codigo = '".$_feriado['codigo']."'";
			else if ($_feriado && $valor == "")
					$sql = "DELETE FROM adm_feriado_fixo
							WHERE codigo = '".$_feriado['codigo']." LIMIT 1'";
			else if ($valor != "")
					$sql = "INSERT INTO adm_feriado_fixo
							SET mes_dia = '$data', descricao = '$valor'";
	} else {
			$sql = "SELECT * from adm_feriado_catolico WHERE data = '$data'";
			echo $sql."<br>\r\n";
			$_feriado = 0;
			$dados = mysql_query($sql);
			$total = mysql_num_rows($dados);
			if ($total > 0)
				$_feriado = mysql_fetch_array($dados);
			if ($_feriado && $valor != "")
					$sql = "UPDATE adm_feriado_catolico
							SET data = '$data', descricao = '$valor'
							WHERE codigo = '".$_feriado['codigo']."'";
			else if ($_feriado && $valor == "")
					$sql = "DELETE FROM adm_feriado_catolico
							WHERE codigo = '".$_feriado['codigo']." LIMIT 1'";
			else if ($valor != "")
					$sql = "INSERT INTO adm_feriado_catolico
							SET data = '$data', descricao = '$valor'";
	}
	echo $sql."<br>\r\n";
	$query = mysql_query($sql);
	die;
}




//print_var("_POST", $_POST);
//resetar  tabelas do mysql
//criar_tabelas();

//gera calendario
echo calendario($mysql);




///////////////////////////////////
//// funcao retorna calendario ////
///////////////////////////////////
function calendario($mysql){
	//mysql
	// $conexao = conexao_mysql($mysql);

	//mes e ano do calendario a ser montado
	If($_POST['mes'] and $_POST['ano']){
	   $mes = $_POST['mes'];
	   $ano = $_POST['ano'];
	} Else {
	   $mes = date("m");
	   $ano = date("Y");
	}

	//Vari�vel de retorno do c�digo em HTML
	$retorno="";
	$retorno.= "<form id=\"mainfrm\" name=\"mainfrm\" method=POST action=".$_SERVER['PHP_SELF'].">";
	//Primeira linha do calend�rio
	$arr_dias = array("Dom","Seg","Ter","Qua","Qui","Sex","S�b");
	$arr_mes = array("Janeiro", "Fevereiro", "Mar�o", "Abril", "Maio", "Junho",	"Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro");

	//Ler tabela de feriado fixo.
	$feriados = array();
	if ($mysql) {
		$sql = "SELECT * FROM adm_feriado_fixo ORDER BY mes_dia ASC";
		$query_feriado_fixo = mysql_query($sql);
		$qtd_feriado_fixo = mysql_num_rows($query_feriado_fixo);
		for ($i = 0; $i < $qtd_feriado_fixo; $i++) {
				$_feriado_fixo = mysql_fetch_array($query_feriado_fixo);
				if ($_feriado_fixo['descricao'] != "") {
					$inx = $ano."-".$_feriado_fixo['mes_dia'];
					$feriados["$inx"] = "_".$_feriado_fixo['descricao'];
				}
		}
		//Ler tabela de feriado catolico.
		$sql = "SELECT * FROM adm_feriado_catolico ORDER BY data ASC";
		//echo "$sql<br>";
		$query_feriado_catolico = mysql_query($sql);
		$qtd_feriado_catolico = mysql_num_rows($query_feriado_catolico);
		for ($i = 0; $i < $qtd_feriado_catolico; $i++) {
				$_feriado_catolico = mysql_fetch_array($query_feriado_catolico);
				if ($_feriado_catolico['descricao'] != "") {
					$inx = $_feriado_catolico['data'];
					$feriados["$inx"] = $_feriado_catolico['descricao'];
				}
		}
	}
	ksort($feriados);
	//print_var("feriados", $feriados);

	//dados do mes atual
	$cont_mes = 1;
	$dia_semana = date("w", mktime(0, 0, 0, $mes, 1, $ano));
	$t_mes = date("t", mktime(0, 0, 0, $mes, 1, $ano)); //no. total de dias no mes
	$dia_hoje = date("d", mktime(0, 0, 0, date("m"), date("d"), date("Y")));

	//dados do mes passado
	$dia_semana_ant = ((date("d", mktime(0, 0, 0, $mes, 0, $ano))+1)-$dia_semana);
	$mes_ant = date("m", mktime(0, 0, 0, $mes, 0, $ano));
	$ano_ant = date("Y", mktime(0, 0, 0, $mes, 0, $ano));

	//dados do mes seguinte
	$dia_semana_post = 1;
	$mes_post = date("m", mktime(0, 0, 0, $mes, $t_mes+1, $ano));
	$ano_post = date("Y", mktime(0, 0, 0, $mes, $t_mes+1, $ano));

	//titulo do calendario
	$retorno.="<center>";
	$retorno.= "<font style=\"font-family:verdana,arial,serif;font-size:16\"><b>".$arr_mes[$mes-1]."/".$ano."</b></font><br>";

	//montagem do calendario
	$retorno.= "<table><tr><td>&nbsp;</td><td>";

	$retorno.= "<table border=1 width=580 cellpadding=5 cellspacing=5 style='border-collapse: collapse' id=AutoNumber1 bordercolor=#333333>";
	//primeira linha do calendario
	$retorno.= "<tr bgcolor=#B5B5B5 face=verdana,arial,serif>";
	for($i=0;$i<7;$i++){
		if ($i==0 || $i==6){
			//� domingo ou s�bado
			$retorno.= "<td bgcolor=#994444><font color=#ffffff face=verdana,arial,serif>$arr_dias[$i]&nbsp;&nbsp;&nbsp;".
			           ($i == 6 ? '<a  onclick="modo_edicao();" >&reg;</a>' : '')."</font></td>";
		}else{
			$retorno.= "<td><font color=#ffffff face=verdana,arial,serif>$arr_dias[$i]</font></td>";
		}
	}
	while ($t_mes >= $cont_mes)
	{
	   $cont_semana = 0;
	   $retorno.= "<tr>";
	   if ($dia_semana == 7)
				$dia_semana = 0;

	   while ($dia_semana < 7)	{
			if ($cont_semana == 0 || $cont_semana == 6)	$cor ="#fff4f4";
			else  $cor ="#ffffff";

		  if ($cont_mes <= $t_mes)
		  {
			 If ($dia_semana == $cont_semana) ////// celulas de dias do mes //////
			 {
			 	//$dia_hoje = 25;
			 	$cor_domingo = $cont_semana == 0 ? "color:red" : "";
				if ($ano ==	date("Y") && $mes == date("m") &&  $cont_mes == $dia_hoje)
						$e_hoje = "background-color:#ffcc44";
				else
						$e_hoje = "";
				$txtb = $cont_semana == 0 ? "font-weight:bold" : "";

				$inx = $ano."-".($mes < 10 ? "0".(integer)$mes : $mes)."-".($cont_mes < 10 ? "0".(integer)$cont_mes : $cont_mes);
				$nome_feriado = $feriados[$inx];
				if ($nome_feriado != "")
						$cor = "#ffff88";

 				$retorno.= "<td valign=top style='background-color:".$cor."' width=170 height=80>";
				$retorno.= "<span style=\"$e_hoje; $cor_domingo; $txtb; font-family:verdana; font-size:12\">&nbsp;$cont_mes&nbsp;</span>";
				if ($_POST['metodo'] == "editar")
					$retorno.= "<br><font color=#000000 face=arial,verdana,serif size=1>" . "$cont_mes/$mes/$ano" . "</font>";

				/************************************************************/
				/******** Conteudo do calendario, se tiver, aqui!!!! ********/
				/************************************************************/
				if ($nome_feriado!=""){
					if ($_POST['metodo'] != "editar") {
						if (strlen($nome_feriado) && $nome_feriado[0] == "_")
								$nome_feriado[0] = " ";
						$retorno.= "<br><font color=#4040ff face=arial,verdana,serif size=2>" . $nome_feriado . "</font>";
					}
				}
				$inx = $ano."".($mes < 10 ? "0".(integer)$mes : $mes)."".($cont_mes < 10 ? "0".(integer)$cont_mes : $cont_mes);
				//echo $inx." ";
				if ($_POST['metodo'] == "editar") {
					$retorno .= "<input type='text' id='$inx' name='$inx' size='6' value='$nome_feriado' onchange='delay_salvar_form(\"$inx\");'  onblur='salvar_form(\"$inx\");'  onKeyPress='delay_salvar_form(\"$inx\");' ";
				}

				$retorno.= "</td>\r\n";
				$cont_mes++;
				$dia_semana++;
				$cont_semana++;
			 }
			 Else ////// celulas vazias no inicio (mes anterior) //////
			 {
			 	$cor_domingo = $cont_semana == 0 ? " color:#ffAAAA " : " color:#AAAAAA ";
				$txtb = $cont_semana == 0 ? "font-weight:bold" : "";

				$retorno.= "<td valign=top bgcolor=".$cor.">";
				$retorno.= "<span style=\"$cor_domingo; $txtb; font-family:verdana; font-size:12\">&nbsp;$dia_semana_ant&nbsp;</span>";
				//data
				if ($_POST['metodo'] == "editar")
					$retorno.= "<br><font $cor_domingo face=arial,verdana,serif size=1>" . "$dia_semana_ant/$mes_ant/$ano_ant" . "</font>";
				$retorno.= "</td>";
				$cont_semana++;
				$dia_semana_ant++;
			 }
		  }
		  Else
		  {
				While ($cont_semana < 7) ////// celulas vazias no fim (mes posterior) //////
				{
					if ($cont_semana == 0 || $cont_semana == 6) $cor ="#fff4f4";
					else  $cor ="#ffffff";
					$retorno.= "<td valign=top bgcolor=".$cor.">";
					$retorno.= "<font color=#AAAAAA face=verdana,arial,serif size=2>&nbsp;".$dia_semana_post."&nbsp;</font>";
					//data
					if ($_POST['metodo'] == "editar")
						$retorno.= "<br><font color=#AAAAAA face=arial,verdana,serif size=1>" . "$dia_semana_post/$mes_post/$ano_post" . "</font>";
					$retorno.= "</td>";
					$cont_semana++;
					$dia_semana_post++;
				}
			 break 2;
		  }
	   }
	   $retorno.= "</tr>\r\n";
	}

	$retorno.= "</table>";
	$retorno.= "</td></tr></table>";
	$retorno.= "<br>";

	//links para mes anterior e mes posterior
	$retorno.= "<table width=100%><tr><td width=50% align=right>";
	$retorno.= "<span style=\"font-family:verdana,arial,serif;font-size:12\"><a href=".$_SERVER['PHP_SELF']."?mes=".$mes_ant."&ano=".$ano_ant.">".$arr_mes[$mes_ant-1]."/".$ano_ant."</a></span></td>";
	$retorno.= "<td> | </td><td width=50%>";
	$retorno.= "<span style=\"font-family:verdana,arial,serif;font-size:12\"><a href=".$_SERVER['PHP_SELF']."?mes=".$mes_post."&ano=".$ano_post.">".$arr_mes[$mes_post-1]."/".$ano_post."</a></span>";
	$retorno.= "</td></tr></table>";

	//formulario para escolha de um mes
	$retorno.= "<font style=\"font-family:verdana,arial,serif;font-size:12\">M&#234;s: </font><select name=mes>";
	$retorno.= "<option></option>";
	For($cont=1;$cont<=12;$cont++) {
		$selected = $cont == $_POST['mes'] ? 'selected="selected"' : "";
	   $retorno.= "<option $selected value=".$cont.">".$arr_mes[$cont-1]."</option>";
	}
	$retorno.= "</select>";

	//formulario para escolha de um ano
	$retorno.= "<font style=\"font-family:verdana,arial,serif;font-size:12\">&nbsp;&nbsp;Ano: </font><select name=ano>";
	$retorno.= "<option></option>";
	For($cont=date("Y")-80;$cont<=date("Y")+25;$cont++)	{
		$selected = $cont == $_POST['ano'] ? 'selected="selected"' : "";
	   $retorno.= "<option $selected value=".$cont.">".$cont."</option>";
	}
	$retorno.= "</select>";

	if (isset($_POST['metodo']))
		$retorno .= "<input type='hidden' name='metodo' value='editar'>";
	$retorno.= "&nbsp;&nbsp;<input type=submit value=Ok>";
	$retorno.= "</form>";

	$retorno.= "</center>";
	return $retorno;
}





////////////////////////
/// funcoes diversas ///
////////////////////////


function print_var($txt, $var) {
	echo '<pre><font size="3" color="#0000FF">'.$txt." -> "; print_r($var); echo '</font></pre>';
}


// function conexao_mysql($mysql)
// {
// 		$host = "localhost";
// 		$usuario = "";
// 		$senha = "";
// 		$nomeBD = "";

// 		if ($mysql) {
// 			$cria_conexao = mysql_connect($host, $usuario, $senha);
// 			$res = mysql_select_db($nomeBD,$cria_conexao);
// 		}
// 		return ($cria_conexao);
// }


function criar_tabelas($mysql)
{
	$conexao = conexao_mysql($mysql);

	$sql = "DROP TABLE IF EXISTS `adm_feriado_fixo`;";
	$query = mysql_query($sql);

	$sql = "CREATE TABLE IF NOT EXISTS `adm_feriado_fixo` (
			  `codigo` int(11) NOT NULL auto_increment,
			  `mes_dia` char(16) NOT NULL default '0000-00-00',
			  `descricao` varchar(1024) NOT NULL,
			  PRIMARY KEY  (`codigo`),
			  KEY `mes_dia` (`mes_dia`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
	$query = mysql_query($sql);

	$sql = "INSERT INTO `adm_feriado_fixo` (`codigo`, `mes_dia`, `descricao`) VALUES
			(2, '01-01', 'Confraterniza��o Universal'),
			(6, '04-21', 'Tiradentes'),
			(7, '05-01', 'Dia do Trabalho'),
			(9, '09-07', 'Independ�ncia do Brasil'),
			(10, '10-12', 'Nossa Sra Aparecida - Padroeira do Brasil'),
			(11, '11-02', 'Finados'),
			(12, '11-15', 'Proclama��o da Rep�blica'),
			(13, '12-25', 'Natal');	";
	$query = mysql_query($sql);

	$sql = "DROP TABLE IF EXISTS `adm_feriado_catolico`;";
	$query = mysql_query($sql);

	$sql = "CREATE TABLE IF NOT EXISTS `adm_feriado_catolico` (
			  `codigo` int(11) NOT NULL auto_increment,
			  `data` date NOT NULL default '0000-00-00',
			  `descricao` varchar(1024) NOT NULL,
			  PRIMARY KEY  (`codigo`),
			  KEY `mes_dia` (`data`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
	$query = mysql_query($sql);

	$sql = "INSERT INTO `adm_feriado_catolico` (`codigo`, `data`, `descricao`) VALUES
			(2, '2010-04-04', 'P�scoa'),
			(6, '2010-04-02', 'Paix�o'),
			(7, '2010-02-17', 'Cinzas'),
			(8, '2010-06-03', 'Corpus Christi'),
			(14, '2010-02-15', 'Carnaval'),
			(15, '2010-02-16', 'Carnaval'); ";
	$query = mysql_query($sql);
}




///////////////////////
///////  ajax  ////////
///////////////////////
?>
<script language="JavaScript">

var c = 0;
var t;
timedCount();

function modo_edicao()
{
	document.mainfrm.action="<? echo $_SERVER['PHP_SELF'].($_POST['metodo'] == "editar" ? '?metodo=mostrar' : '?metodo=editar') ?>";
	document.mainfrm.submit();
}

function timedCount()
{
	t = setTimeout("timedCount()",10000);
	if (c > 0) {
		var x = document.getElementById("mainfrm");
		var str;
		for (var i = 0; i < x.length; i++) {
			str = x.elements[i].id;
			if (x.elements[i].value != "" && str.length == 8)
				salvar_form(x.elements[i].id);
		}
		c = 0;
	}
}

function ajaxInit()
{
	var req;

	try {
	 	req = new ActiveXObject("Microsoft.XMLHTTP");
	} catch(e) {
		try {
		  		req = new ActiveXObject("Msxml2.XMLHTTP");
		} catch(ex) {
			try {
			 	req = new XMLHttpRequest();
			} catch(exc) {
			   	alert("Esse browser n�o tem recursos para uso do Ajax");
			   	req = null;
			}
	 	}
	}
	return req;
}

function salvar_form(id)
{
	var inx = document.getElementById(id);
	ajax = ajaxInit();
	if(ajax){
		ajax.open("POST", "<? echo $_SERVER['PHP_SELF'] ?>", true);
		ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		ajax.send("ajax_salvar_id=" + id + "&valor=" + inx.value);
	}
}

function delay_salvar_form(id)
{
	c++;
	if (c == 7) {
		salvar_form(id);
		c = 0;
	}
}


</script>
