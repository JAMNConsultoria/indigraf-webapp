#!/usr/bin/php
<?php
set_time_limit(0);
function geraArquivoCSV($fileCSV,$outupFile,$dir){
	
	echo "Processando arquivo .... {$fileCSV}\n";
	
	$username="root";
	$password ="";
	try {
	$conn = new PDO('mysql:host=localhost;dbname=indgraf', $username, $password);
} catch(PDOException $e) {
    echo 'ERROR: ' . $e->getMessage();
}
	$fp = fopen ($dir.$fileCSV,"r");
	$primeiro = true;
	$cabec = array();
	$dados="";
	while (($data = fgetcsv($fp, filesize($dir.$fileCSV), ";")) !== FALSE) {
		$num = count ($data);
		if($primeiro){
					for ($c=0; $c < $num; $c++) {
							$cabec[$c]=$data[$c];
					}
					#$output = fopen ($outupFile,"w+");
					$primeiro = false;
		}else{   
				 $dados="";
				 $values="";
				 $coldados = $num - 3;
				 for ($d=3;$d<$num;$d++){
					 $dados .= "{$data[0]};{$data[1]};{$data[2]};{$cabec[$d]};{$data[$d]}\n";
					 $values .= "({$data[0]},{$data[2]},0,'{$cabec[$d]}','{$data[$d]}'),";
				 }
				 $values=substr($values,0,strlen($values)-1);
				 $stmt = $conn->query("insert into tbdados(regiaoId,dadoAno,indicadorId,indicadorXls,dadoValor) values $values");
				 echo "<pre>{$dados}";			 
				 echo "<pre>{$values}";	
				 echo "insert into tbdados(regiaoId,dadoAno,indicadorId,indicadorXls,dadoValor) values($values);\n";
				 #fwrite ($output,$dados);
		}
	}
	fclose ($fp);
	#fclose ($output);
}
$saida ="arquivoGeral.csv";
$pasta="C:/xampp/htdocs/indgraf/bdPreparo/";
$filesCSV = glob($dir."*.CSV");
print_r($filesCSV);

for ($f=0; $f<count($filesCSV); $f++){
	geraArquivoCSV($filesCSV[$f],$saida, $pasta);
}
?>