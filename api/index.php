<?php
require 'conn.php';
require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();
$app->add(new \Slim\Middleware\ContentTypes());
$app->response()->header('Content-Type', 'application/json;charset=utf-8');
$app->get('/', function() use($app) {
    $app->response->setStatus(200);
    echo "Welcome the data API - RESBR";
}); 
# GET .../indgraf/api/listas/
$app->group('/listas', function () use ($app) {
        $app->get('/uf','listaUF');
        $app->get('/graficos/','listaGraficos');
		$app->get('/regioes','listaRegioes');		

});
# GET .../indgraf/api/dados/regiao/99999/grafico/99/ano/9999 (0 - para todos)
$app->group('/dados', function () use ($app) {
		$app->get('/grafico/:graficoId/regiao/:regiaoId/ano/:ano','dadosGrafico');
});
$app->notFound(function(){echo 'humm...not sure what you mean';});
$app->run();

#lista de estados
function listaUF() {
	$sql = "SELECT ufID,ufNome,ufSigla FROM tbufs order by ufNome";
	try {
		$db = getDB();
		$stmt = $db->query($sql); 
		$estados = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo '{"uf": ' . json_encode($estados,JSON_NUMERIC_CHECK) . '}';	
		#echo verifica_json();

	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}


#lista regiões de saúde
function listaRegioes() {
	$sql = " SELECT regiaoId,regiaoNome,ufNome,ufSigla FROM tbregioes";
	$sql .= " join tbufs using(ufId)";
	$sql .= " order by ufSigla,regiaoId";
	try {
		$db = getDB();
		$stmt = $db->query($sql); 
		$regioes = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
                if($regioes){
                    echo '{"regioes": ' . json_encode($regioes,JSON_NUMERIC_CHECK) . '}';	
                }else{
                    echo '{"warning":{"text":"dado não encontrado."}}';
                }
		#echo verifica_json();

	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}

#lista gráficos
function listaGraficos() {
	$sql = " SELECT * FROM tbgraficos";
	try {
		$db = getDB();
		$stmt = $db->query($sql); 
		$regioes = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
                if($regioes){
                    echo '{"graficos": ' . json_encode($regioes,JSON_NUMERIC_CHECK) . '}';	
                }else{
                    echo '{"warning":{"text":"dado não encontrado."}}';
                }
		#echo verifica_json();

	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}




#lista de indicadores
function listaIndicadores($ind_tipo) {
        $where ="";
        if($ind_tipo !="all"){
            $where .= " WHERE ind_tipo=:ind_tipo";
        }    
	$sql  = " SELECT * FROM indicadores";
        $sql .= $where;
        $sql .= " ORDER BY ind_ordem";
        
	try {
		$db = getDB();
                $stmt = $db->prepare($sql);
                if($ind_tipo !="all"){
                   $stmt->bindParam(':ind_tipo', $ind_tipo, PDO::PARAM_STR); 
                }
                $stmt->execute();	
		$indicad = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
                if($indicad){
                    echo '{"indicadores": ' . json_encode($indicad,JSON_NUMERIC_CHECK) . '}';	
                }else{
                    echo '{"warning":{"text":"dado não encontrado."}}';
                }                                
		#echo verifica_json();

	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}


#retorna os dados para o tipo de gráfico por região
function dadosGrafico($graficoId,$regiaoId,$ano) {
    $where ="";
    $where= "gi.graficoId=:graficoId";
    if($regiaoId > 0){
        $where .= " and regiaoId=:regiaoId ";
    }    
    if($ano > 0){
        $where .= " and dadoAno=:ano";
    }	
	switch($graficoId){
		case 1://piramide
			$campos = "d.regiaoId, i.indicadorLabel,d.dadoAno,d.dadoValorHomem as homem,d.dadoValorMulher as mulher";
			$sql =  " SELECT {$campos}  from tbgrafind gi";
			$sql .= " join tbdados_piramide as d using(indicadorId)";        
			$sql .= " join tbindicadores as i using(indicadorId)";        	
			$sql .= " WHERE " .$where;
			$sql .= " order by indicadorOrdem desc,indicadorLabel";
		break;	

		case 2://tx crescimento
			$campos = "d.regiaoId, i.indicadorLabel,d.dadoAno,d.dadoValor";
			$sql =  " SELECT {$campos}  from tbgrafind gi";
			$sql .= " join tbdados as d using(indicadorId)";        
			$sql .= " join tbindicadores as i using(indicadorId)";        	
			$sql .= " WHERE " .$where;
			$sql .= " order by indicadorOrdem asc,indicadorLabel";
		break;			
		
	}
	//echo $sql;
    try {
            $db = getDB();
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':graficoId', $graficoId, PDO::PARAM_INT);
            if($regiaoId > 0){
                $stmt->bindParam(':regiaoId', $regiaoId, PDO::PARAM_INT);
            }
            if($ano > 0){
                $stmt->bindParam(':ano', $ano, PDO::PARAM_INT);
            }			
            $stmt->execute();		
            $dadosreg = $stmt->fetchAll(PDO::FETCH_OBJ);
			switch($graficoId){
				case 1://piramide
					foreach($dadosreg as $indice => $campos){		
						$result[]=array("idade"=>$campos->indicadorLabel,'Homens' => ($campos->homem) * -1, 'Mulheres'=>$campos->mulher);				
					}
				break;	
				case 2://tx cresc
					foreach($dadosreg as $indice => $campos){		
						$result[]=array("faixa"=>$campos->indicadorLabel,'valor' => ($campos->dadoValor));				
					}
				break;	
			}
			//print_r($result);
			
            $db = null;
            if($dadosreg){
			   echo json_encode($result,JSON_NUMERIC_CHECK);	 
            }else{
                echo '{"warning":{"text":"dado não encontrado."}}';
            }
            
           // echo verifica_json();

    } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

#verifica saída da função json_encode
function verifica_json(){
		if (json_last_error() == 0) { 
		$msg = '- Nao houve erro! O parsing foi perfeito'; 
	}else{	
		$msg = 'Erro!</br>'; 
		switch (json_last_error()) {
			case JSON_ERROR_DEPTH: 
				$msg .= ' - profundidade maxima excedida';
				break;
			case JSON_ERROR_STATE_MISMATCH: 
				$msg .= ' - state mismatch'; 
				break; 
			case JSON_ERROR_CTRL_CHAR: 
				$msg .= ' - Caracter de controle encontrado'; 
				break; 
			case JSON_ERROR_SYNTAX: 
				$msg .=' - Erro de sintaxe! String JSON mal-formada!'; 
				break; 
			case JSON_ERROR_UTF8: 
				$msg .=' - Erro na codificação UTF-8'; 
				break; 
			default: 
				$msg .=' – Erro desconhecido'; 
				break; 
		}
	}
	return $msg;
}
?> 