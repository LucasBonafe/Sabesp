<?php
function multiexplode($delimiters,$string){$ready = str_replace($delimiters, $delimiters[0], $string);$launch = explode($delimiters[0], $ready);return  $launch;}

function Get_Tabela($mes,$ano){//Gerará a variável $tabela
    $url = 'http://www2.sabesp.com.br/mananciais/dadoscantareira/DadosRepresa.aspx';
    $data = array('__VIEWSTATE' => 'ThanvcIVQ1VIyInRrpdDh+qVMTdNGeWgyGZyW/8M3XDmSOF4duOpZwwgVhQbwo8uH8S3MxZ039Ob2s7OYAS2FYIcFsgcNKLCRbGyFS1ShM2q0DQ84gvAhD1NKVXip9HpTK8tBF3+GcS6xtwpVWZrd+oWW7pIh6E0JeFHiJcyov/BFl8grJ1rqBw/8cvhzb0WPwjTPDnFfW5PFovvJVp+Zm28Q8yvr1Xk6LjAZBt7vFWVH7aifg8N5MQ+aZiDd29+3GLiXdAoUYrJ3QRS1prk0wwskwQBs33F5KX+jVq1YwsCHh4SPkWgPY7Txqzt237BFyAL4btf6qFYYfJ4Su0/koyX5i9MTKcOQjj0vyUxQx855BuqsV+gKvKWW5Wx+1YZ5kpvDEpogsbIU/MzjDY98qh4gYGoNfmTdMrOJPOAStHM3fwoFjFw714WXPX0tSAyBik72amUZP90UCrAaMe0LLC+Hcv2j7D+m/HywAOm/P8cfEC2LXkIPjUfW4QeBoAJ/q/Nq+ePleoaxCaj3Zg6Sqy//DeHH/K71gsj8tUrlLoMRTfaIlQi8UhcFYlSj7/hOAnct9JLEwDnf5WJJ3k4/71xaeSMxyk7zS8CqSt9a2yUO/xBVcQm4HJUiWV5PsrXsW4gtYQjeBgtWiRpkqlDHQjo9mA=', '__VIEWSTATEENCRYPTED' => '', 'ctl00$cntTitulo$cmbMes' => "$mes", 'ctl00$cntTitulo$cmbAno' => "$ano", 'ctl00$cntTitulo$btnVizualiza' => 'Visualizar');
    
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        )
    );
    $context  = stream_context_create($options);
    $html = file_get_contents($url, false, $context);
    $html_tabela = multiexplode(array('<tbody>','</tbody>'), $html);
    $result = str_replace(array("\r\n", "\r", "\n"), "</tupla>", $html_tabela[1]); 
    return $result;
}

function Get_Dados($tabela){//Gerará a variável $dados
    $Tuplas= explode('</tr><tr></tupla>', $tabela);
    $tuplas= str_replace(',', '.', $Tuplas);
    //print_r($tuplas);
    foreach ($tuplas as $tupla){
        @$dados= multiexplode(array('<td>','</td><td>','</td></tupla>'), $tupla);
        array_shift($dados);//Retira o primeiro elemento de um array
        unset($dados[21]);//Remove o array 21
        //array_splice($dados, 4);
        //print_r($dados);
        $result[] = array($dados);
    }
    return $result;
}

function CSV($nome, $cabecalho, $dados){//Gera o arquivo CSV para Download
    header('Content-Type: text/csv; charset=utf-8');
    header("Content-Disposition: attachment; filename=$nome.csv");
    header('Pragma: no-cache');
    $saida = fopen('php://output', 'w') or die("Não foi possível abrir php://output");
    foreach ($dados as $linha){
        fputcsv($saida, $linha, ';');
    }
    fclose($saida);
}

/*function Baixar_Tabelas($anoInicial, $anoFinal){//Gera CSV's em massa EXPERIMENTAL
    ini_set('max_execution_time', 0); //300 segundos = 5 minutos e 0=Sem limite
    $anos= range($anoInicial,$anoFinal); //$anos= array(2004, 2005, 2006, 2007, 2008, 2009, 2010, 2011, 2012, 2013, 2014, 2015, 2016, 2017, 2018);
    $meses= range(1,12); //$meses= array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12);
    foreach ($anos as $ano){
        foreach ($meses as $mes){
            $tabela= Get_Tabela($mes, $ano);
            $dados= array_map(function($item){return $item[0];}, Get_Dados($tabela));
            $data_tabela= explode('01/', $dados[0][0]);
            $data= implode("",$data_tabela);
            $cabecalho= array('Data','NA (m)','Pluv (mm)','Qjus (m3/s)','NA (m)','Pluv (mm)','Qjus (m3/s)','NA (m)','Pluv (mm)','Qjus (m3/s)','NA (m)','Pluv (mm)','Qjus (m3/s)','NA (m)','Pluv (mm)','Qjus (m3/s)','F-25Bt (m3/s)','Q T7 (m3/s)','Q T6 (m3/s)','Q T5 (m3/s)', 'Q ESI (m3/s)');
            CSV($data, $cabecalho, $dados);
        }
    }
}//Baixar_Tabelas(2004,2018);*/


$Mes= $_POST['Mes'];
$Ano= $_POST['Ano'];
switch($Mes){
    case 1: $Tri= array('1º','Janeiro','Fevereiro','Março'); break;
    case 4: $Tri= array('2º','Abril','Maio','Junho'); break;
    case 7: $Tri= array('3º','Julho','Agosto','Setembro'); break;
    case 10:$Tri= array('4º','Outubro','Novembro','Dezembro'); break;}

//1º Mês
$tabela1= Get_Tabela($Mes, $Ano);//print_r($tabela1);
$dados1= array_map(function($item){return $item[0];}, Get_Dados($tabela1));//print_r($dados1);
$data_tabela1= explode('01/', $dados1[0][0]); $data1= implode("",$data_tabela1);//print_r($data1);
//CSV($data1, $cabecalho, $dados1);

//2º Mês
$tabela2= Get_Tabela($Mes+1, $Ano);//print_r($tabela2);
$dados2= array_map(function($item){return $item[0];}, Get_Dados($tabela2));//print_r($dados2);
$data_tabela2= explode('01/', $dados2[0][0]); $data2= implode("",$data_tabela2);//print_r($data2);
//CSV($data2, $cabecalho, $dados2);

//3º Mês
$tabela3= Get_Tabela($Mes+2, $Ano);//print_r($tabela3);
$dados3= array_map(function($item){return $item[0];}, Get_Dados($tabela3));//print_r($dados3);
$data_tabela3= explode('01/', $dados3[0][0]); $data3= implode("",$data_tabela3);//print_r($data3);
//CSV($data3, $cabecalho, $dados3);

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet= new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$spreadsheet->getProperties()
    ->setTitle("Sabesp Cantareiras $Ano")
    ->setSubject("Dados Demográficos")
    ->setKeywords("sabesp analise estatistica cantareiras")
    ->setCategory("Análise Estatística")
    ->setDescription("Dados das Cantareiras da Sabesp.")
    ->setCreator("https://github.com/LucasBonafe")
    ->setLastModifiedBy("https://github.com/LucasBonafe");

$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(10.71);
$spreadsheet->getActiveSheet()->getColumnDimension('W')->setWidth(10.71);
$spreadsheet->getActiveSheet()->getColumnDimension('AI')->setWidth(2.14);
$spreadsheet->getActiveSheet()->getColumnDimension('AK')->setWidth(3.00);

$cabecalho= array('NA (m)','Pluv (mm)','Qjus (m3/s)','NA (m)','Pluv (mm)','Qjus (m3/s)','NA (m)','Pluv (mm)','Qjus (m3/s)','NA (m)','Pluv (mm)','Qjus (m3/s)','NA (m)','Pluv (mm)','Qjus (m3/s)');
$spreadsheet->getActiveSheet()//Tabelas e Cabeçalhos
    ->fromArray($cabecalho,NULL,'B6')->setCellValue('A5','Data')->mergeCells('A5:A6')
    ->fromArray($cabecalho,NULL,'B42')->setCellValue('A41','Data')->mergeCells('A41:A42')
    ->fromArray($cabecalho,NULL,'B78')->setCellValue('A77','Data')->mergeCells('A77:A78')
    ->fromArray($dados1,NULL,'A7')
    ->fromArray($dados2,NULL,'A43')
    ->fromArray($dados3,NULL,'A79');

$vazao= array('F-25Bt (m3/s)','Q T7 (m3/s)','Q T6 (m3/s)','Q T5 (m3/s)', 'Q ESI (m3/s)');
$spreadsheet->getActiveSheet()//Vazão
    ->fromArray($vazao,NULL,'Q5')->mergeCells('Q5:Q6')->mergeCells('R5:R6')->mergeCells('S5:S6')->mergeCells('T5:T6')->mergeCells('U5:U6')
    ->fromArray($vazao,NULL,'Q41')->mergeCells('Q41:Q42')->mergeCells('R41:R42')->mergeCells('S41:S42')->mergeCells('T41:T42')->mergeCells('U41:U42')
    ->fromArray($vazao,NULL,'Q77')->mergeCells('Q77:Q78')->mergeCells('R77:R78')->mergeCells('S77:S78')->mergeCells('T77:T78')->mergeCells('U77:U78');

$wrapsl= array('Q','R','S','T','U');
$wrapsn= array('5','41','77');
foreach ($wrapsl as $wrapl){
    foreach ($wrapsn as $wrapn){
        $spreadsheet->getActiveSheet()//Quebra de Texto
            ->getStyle("$wrapl$wrapn")->getAlignment()->setWrapText(true);
    }
}

$spreadsheet->getActiveSheet()//Represas
    ->setCellValue('B5','Represa Jaguari/Jacareí')->mergeCells('B5:D5')
    ->setCellValue('E5','Represa Cachoeira')->mergeCells('E5:G5')
    ->setCellValue('H5','Represa Atibainha')->mergeCells('H5:J5')
    ->setCellValue('K5','Represa Paiva Castro')->mergeCells('K5:M5')
    ->setCellValue('N5','Represa Águas Claras')->mergeCells('N5:P5')
    ->setCellValue('B41','Represa Jaguari/Jacareí')->mergeCells('B41:D41')
    ->setCellValue('E41','Represa Cachoeira')->mergeCells('E41:G41')
    ->setCellValue('H41','Represa Atibainha')->mergeCells('H41:J41')
    ->setCellValue('K41','Represa Paiva Castro')->mergeCells('K41:M41')
    ->setCellValue('N41','Represa Águas Claras')->mergeCells('N41:P41')
    ->setCellValue('B77','Represa Jaguari/Jacareí')->mergeCells('B77:D77')
    ->setCellValue('E77','Represa Cachoeira')->mergeCells('E77:G77')
    ->setCellValue('H77','Represa Atibainha')->mergeCells('H77:J77')
    ->setCellValue('K77','Represa Paiva Castro')->mergeCells('K77:M77')
    ->setCellValue('N77','Represa Águas Claras')->mergeCells('N77:P77');

$spreadsheet->getActiveSheet()//Títulos Mês
    ->setCellValue('H2', "$Tri[0] Trimestre de $Ano")->mergeCells('H2:L3')
    ->setCellValue('A3', "$Tri[1]")->mergeCells('A3:B4')
    ->setCellValue('A39', "$Tri[2]")->mergeCells('A39:B40')
    ->setCellValue('A75', "$Tri[3]")->mergeCells('A75:B76');

$TiMess= array('H2','A3','A39','A75');
foreach ($TiMess as $TiMes){//Tamanho/Orientação dos Títulos Mês
    $spreadsheet->getActiveSheet()->getStyle($TiMes)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);//Orientação Horizontal
    if ($TiMes == 'H2'){
        $spreadsheet->getActiveSheet()->getStyle($TiMes)->getFont()->setSize(24);
    } else{
        $spreadsheet->getActiveSheet()->getStyle($TiMes)->getFont()->setSize(16);
    }
}

$Tudos= array('A5:U37', 'A41:U73', 'A77:U109','W5:AG99','AI5:AQ15','AI18:AQ28','AI31:AQ41','AI44:AQ54','AI57:AQ67');//Tabela inteira
$Titulos= array('A5:U6','A41:U42','A77:U78','W5:AG6','AI5:AL5','AI6:AQ6','AI18:AL18','AI19:AQ19','AI31:AL31','AI32:AQ32','AI44:AL44','AI45:AQ45','AI57:AL57','AI58:AQ58');//Somente títulos
$Celulas= array('B7:U37','B43:U73','B79:U109','X7:AG99','AJ7:AQ15','AJ20:AQ28','AJ33:AQ41','AJ46:AQ54','AJ59:AQ67');//Somente dados

$Datas= array('A7:A37','A43:A73','A79:A109','W7:W99');
$CData= 'FFFFFFFF';//Cor RGBA

$Jaguaris= array('B5:D37','B41:D73','B77:D109','X5:Y99','X103:Y105','X107:Y109','X111:Y114','X116:Y121','X123:Y124','X126:Y127','X129:Y130','X132:Y134','AI5:AL5','AI6:AQ14','AM15','AO15','AQ15');
$TJaguaris= array('B5:D6','B41:D42','B77:D78','X5:Y6','AI5:AL5','AI6:AQ6');//Títulos
$CJaguari= 'FFFFFFFF';//Cor RGBA

$Cachoeiras= array('E5:G37','E41:G73','E77:G109','Z5:AA99','Z103:AA105','Z107:AA109','Z111:AA114','Z116:AA121','Z123:AA124','Z126:AA127','Z129:AA130','Z132:AA134','AI18:AL18','AI19:AQ27','AM28','AO28','AQ28');
$TCachoeiras= array('E5:G6','E41:G42','E77:G78','Z5:AA6','AI18:AL18','AI19:AQ19');//Títulos
$CCachoeira= 'FFFFFFFF';//Cor RGBA

$Atibainhas= array('H5:J37','H41:J73','H77:J109','AB5:AC99','AB103:AC105','AB107:AC109','AB111:AC114','AB116:AC121','AB123:AC124','AB126:AC127','AB129:AC130','AB132:AC134','AI31:AL31','AI32:AQ40','AM41','AO41','AQ41');
$TAtibainhas= array('H5:J6','H41:J42','H77:J78','AB5:AC6','AI31:AL31','AI32:AQ32');//Títulos
$CAtibainha= 'FFFFFFFF';//Cor RGBA

$PaivaCastros= array('K5:M37','K41:M73','K77:M109','AD5:AE99','AD103:AE105','AD107:AE109','AD111:AE114','AD116:AE121','AD123:AE124','AD126:AE127','AD129:AE130','AD132:AE134','AI44:AL44','AI45:AQ53','AM54','AO54','AQ54');
$TPaivaCastros= array('K5:M6','K41:M42','K77:M78','AD5:AE6','AI44:AL44','AI45:AQ45');//Títulos
$CPaivaCastro= 'FFFFFFFF';//Cor RGBA

$AguasClarass= array('N5:P37','N41:P73','N77:P109','AF5:AG99','AF103:AG105','AF107:AG109','AF111:AG114','AF116:AG121','AF123:AG124','AF126:AG127','AF129:AG130','AF132:AG134','AI57:AL57','AI58:AQ66','AM67','AO67','AQ67');
$TAguasClarass= array('N5:P6','N41:P42','N77:P78','AF5:AG6','AI57:AL57','AI58:AQ58');//Títulos
$CAguasClaras= 'FFFFFFFF';//Cor RGBA

$F25Bts= array('Q5:Q37','Q41:Q73','Q77:Q109');
$QT7s= array('R5:R37','R41:R73','R77:R109');
$QT6s= array('S5:S37','S41:S73','S77:S109');
$QT5s= array('T5:T37','T41:T73','T77:T109');
$QESIs= array('U5:U37','U41:U73','U77:U109');

foreach ($Tudos as $Tudo){
    $spreadsheet->getActiveSheet()->getStyle($Tudo)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);//Orientação Horizontal
    $spreadsheet->getActiveSheet()->getStyle($Tudo)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);//Orientação Vertical
}

foreach ($Titulos as $Titulo){
    $spreadsheet->getActiveSheet()->getStyle($Titulo)->getFont()->setBold(true);//Negrito
    $spreadsheet->getActiveSheet()->getStyle($Titulo)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Baixo
}

foreach ($Celulas as $Celula){
    $spreadsheet->getActiveSheet()->getStyle($Celula)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);
}

foreach ($Datas as $Data){
    $spreadsheet->getActiveSheet()->getStyle($Data)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);//Textura da Célula
    $spreadsheet->getActiveSheet()->getStyle($Data)->getFill()->getStartColor()->setARGB($CData);//Cor da Célula
}

foreach ($Jaguaris as $Jaguari){
    $spreadsheet->getActiveSheet()->getStyle($Jaguari)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE);//Cor da Fonte
    $spreadsheet->getActiveSheet()->getStyle($Jaguari)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Cima
    $spreadsheet->getActiveSheet()->getStyle($Jaguari)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Baixo
    $spreadsheet->getActiveSheet()->getStyle($Jaguari)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Esquerda
    $spreadsheet->getActiveSheet()->getStyle($Jaguari)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Direita
}

foreach ($TJaguaris as $TJaguari){
    $spreadsheet->getActiveSheet()->getStyle($TJaguari)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);//Textura da Célula
    $spreadsheet->getActiveSheet()->getStyle($TJaguari)->getFill()->getStartColor()->setARGB($CJaguari);//Cor da Célula
}

foreach ($Cachoeiras as $Cachoeira){
    $spreadsheet->getActiveSheet()->getStyle($Cachoeira)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKRED);//Cor da Fonte
    $spreadsheet->getActiveSheet()->getStyle($Cachoeira)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Cima
    $spreadsheet->getActiveSheet()->getStyle($Cachoeira)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Baixo
    $spreadsheet->getActiveSheet()->getStyle($Cachoeira)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Esquerda
    $spreadsheet->getActiveSheet()->getStyle($Cachoeira)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Direita
}

foreach ($TCachoeiras as $TCachoeira){
    $spreadsheet->getActiveSheet()->getStyle($TCachoeira)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);//Textura da Célula
    $spreadsheet->getActiveSheet()->getStyle($TCachoeira)->getFill()->getStartColor()->setARGB($CCachoeira);//Cor da Célula
}

foreach ($Atibainhas as $Atibainha){
    $spreadsheet->getActiveSheet()->getStyle($Atibainha)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKGREEN);//Cor da Fonte
    $spreadsheet->getActiveSheet()->getStyle($Atibainha)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Cima
    $spreadsheet->getActiveSheet()->getStyle($Atibainha)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Baixo
    $spreadsheet->getActiveSheet()->getStyle($Atibainha)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Esquerda
    $spreadsheet->getActiveSheet()->getStyle($Atibainha)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Direita
}

foreach ($TAtibainhas as $TAtibainha){
    $spreadsheet->getActiveSheet()->getStyle($TAtibainha)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);//Textura da Célula
    $spreadsheet->getActiveSheet()->getStyle($TAtibainha)->getFill()->getStartColor()->setARGB($CAtibainha);//Cor da Célula
}

foreach ($PaivaCastros as $PaivaCastro){
    $spreadsheet->getActiveSheet()->getStyle($PaivaCastro)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKYELLOW);//Cor da Fonte
    $spreadsheet->getActiveSheet()->getStyle($PaivaCastro)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Cima
    $spreadsheet->getActiveSheet()->getStyle($PaivaCastro)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Baixo
    $spreadsheet->getActiveSheet()->getStyle($PaivaCastro)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Esquerda
    $spreadsheet->getActiveSheet()->getStyle($PaivaCastro)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Direita
}

foreach ($TPaivaCastros as $TPaivaCastro){
    $spreadsheet->getActiveSheet()->getStyle($TPaivaCastro)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);//Textura da Célula
    $spreadsheet->getActiveSheet()->getStyle($TPaivaCastro)->getFill()->getStartColor()->setARGB($CPaivaCastro);//Cor da Célula
}

foreach ($AguasClarass as $AguasClaras){
    //$spreadsheet->getActiveSheet()->getStyle($AguasClaras)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKMAGENTA);//Cor da Fonte
    $spreadsheet->getActiveSheet()->getStyle($AguasClaras)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Cima
    $spreadsheet->getActiveSheet()->getStyle($AguasClaras)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Baixo
    $spreadsheet->getActiveSheet()->getStyle($AguasClaras)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Esquerda
    $spreadsheet->getActiveSheet()->getStyle($AguasClaras)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Direita
}

foreach ($TAguasClarass as $TAguasClaras){
    $spreadsheet->getActiveSheet()->getStyle($TAguasClaras)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);//Textura da Célula
    $spreadsheet->getActiveSheet()->getStyle($TAguasClaras)->getFill()->getStartColor()->setARGB($CAguasClaras);//Cor da Célula
}

foreach ($F25Bts as $F25Bt){
    $spreadsheet->getActiveSheet()->getStyle($F25Bt)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Cima
    $spreadsheet->getActiveSheet()->getStyle($F25Bt)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Baixo
    $spreadsheet->getActiveSheet()->getStyle($F25Bt)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Esquerda
    $spreadsheet->getActiveSheet()->getStyle($F25Bt)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Direita
}

foreach ($QT7s as $QT7){
    $spreadsheet->getActiveSheet()->getStyle($QT7)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Cima
    $spreadsheet->getActiveSheet()->getStyle($QT7)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Baixo
    $spreadsheet->getActiveSheet()->getStyle($QT7)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Esquerda
    $spreadsheet->getActiveSheet()->getStyle($QT7)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Direita
}

foreach ($QT6s as $QT6){
    $spreadsheet->getActiveSheet()->getStyle($QT6)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Cima
    $spreadsheet->getActiveSheet()->getStyle($QT6)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Baixo
    $spreadsheet->getActiveSheet()->getStyle($QT6)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Esquerda
    $spreadsheet->getActiveSheet()->getStyle($QT6)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Direita
}

foreach ($QT5s as $QT5){
    $spreadsheet->getActiveSheet()->getStyle($QT5)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Cima
    $spreadsheet->getActiveSheet()->getStyle($QT5)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Baixo
    $spreadsheet->getActiveSheet()->getStyle($QT5)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Esquerda
    $spreadsheet->getActiveSheet()->getStyle($QT5)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Direita
}

foreach ($QESIs as $QESI){
    $spreadsheet->getActiveSheet()->getStyle($QESI)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Cima
    $spreadsheet->getActiveSheet()->getStyle($QESI)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Baixo
    $spreadsheet->getActiveSheet()->getStyle($QESI)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Esquerda
    $spreadsheet->getActiveSheet()->getStyle($QESI)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Direita
}

//==ESTATÍSTICAS===================================================================================================

$NA_Jaguaris= array('B7:B37','B43:B73','B79:B109');
$NA_Cachoeiras= array('E7:E37','E43:E73','E79:E109');
$NA_Atibainhas= array('H7:H37','H43:H73','H79:H109');
$NA_PaivaCastros= array('K7:K37','K43:K73','K79:K109');
$NA_AguasClarass= array('N7:N37','N43:N73','N79:N109');

$Est_Menus= array('Data','Jaguari/Jacareí','Cachoeira','Atibainha','Paiva Castro','Águas Claras');
$Est_Nomen= array('NA','ROL','NA','ROL','NA','ROL','NA','ROL','NA','ROL');

$spreadsheet->getActiveSheet()//Menu da Estatística
    ->setCellValue('W5','Data')->mergeCells('W5:W6')
    ->setCellValue('X5','Jaguari/Jacareí')->mergeCells('X5:Y5')
    ->setCellValue('Z5','Cachoeira')->mergeCells('Z5:AA5')
    ->setCellValue('AB5','Atibainha')->mergeCells('AB5:AC5')
    ->setCellValue('AD5','Paiva Castro')->mergeCells('AD5:AE5')
    ->setCellValue('AF5','Águas Claras')->mergeCells('AF5:AG5')
    ->fromArray($Est_Nomen,NULL,'X6');

/*$Est_Locais= array('X5:Y99','Z5:AA99','AB5:AC99','AD5:AE99','AF5:AG99','X5:AG6');
foreach ($Est_Locais as $Est_Local){
    $spreadsheet->getActiveSheet()->getStyle($Est_Local)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Cima
    $spreadsheet->getActiveSheet()->getStyle($Est_Local)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Baixo
    $spreadsheet->getActiveSheet()->getStyle($Est_Local)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Esquerda
    $spreadsheet->getActiveSheet()->getStyle($Est_Local)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Direita
}   $spreadsheet->getActiveSheet()->getStyle('X5:AG5')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Baixo
*/
function NA($NA_Represas, $Local1, $Local2, $spreadsheet){
    $Rol = array();
        foreach ($NA_Represas as $NA_Represa){
            $rol= $spreadsheet->getActiveSheet()->rangeToArray($NA_Represa);
            $Rol = array_merge($Rol, $rol);
        }
        $Rol = array_map('array_filter', $Rol);//Remove elementos vazios
        $Rol = array_filter($Rol);//de meses que possuem menos de 31 dias
        $spreadsheet->getActiveSheet()->fromArray($Rol, null, $Local1);
        $Rol2 = $Rol;
        sort($Rol2);//Ordenando o $Rol2
        $spreadsheet->getActiveSheet()->fromArray($Rol2, null, $Local2);
    }

NA($Datas, 'W7', 'W7', $spreadsheet);
NA($NA_Jaguaris, 'X7', 'Y7', $spreadsheet);
NA($NA_Cachoeiras, 'Z7', 'AA7', $spreadsheet);
NA($NA_Atibainhas, 'AB7', 'AC7', $spreadsheet);
NA($NA_PaivaCastros, 'AD7', 'AE7', $spreadsheet);
NA($NA_AguasClarass, 'AF7', 'AG7', $spreadsheet);

$E1= array(//Estatísticas Jaguari/Jacareí [0]¹ Linha  [0]² Coluna
    array('X103','X107','X111','X116','X123','X126','X129','X132'),//Texto
    array('Y104','Y108','Y113','X117:X121','Y123','Y126','Y129','Y132'),//Merge
    array('X105','X109','X114','X124','X127','X130','X133','X134'),//Respostas
    array('X103:Y105','X107:Y109','X111:Y114','X116:Y121','X123:Y124','X126:Y127','X129:Y130','X132:Y134',$CJaguari)//Bordas e Cor $E[3][8]
);

$E2= array(//Estatísticas Cachoeira [0]¹ Linha  [0]² Coluna
    array('Z103','Z107','Z111','Z116','Z123','Z126','Z129','Z132'),//Texto
    array('AA104','AA108','AA113','Z117:Z121','AA123','AA126','AA129','AA132'),//Merge
    array('Z105','Z109','Z114','Z124','Z127','Z130','Z133','Z134'),//Respostas
    array('Z103:AA105','Z107:AA109','Z111:AA114','Z116:AA121','Z123:AA124','Z126:AA127','Z129:AA130','Z132:AA134',$CCachoeira)//Bordas e Cor $E[3][8]
);

$E3= array(//Estatísticas Atibainha [0]¹ Linha  [0]² Coluna
    array('AB103','AB107','AB111','AB116','AB123','AB126','AB129','AB132'),//Texto
    array('AC104','AC108','AC113','AB117:AB121','AC123','AC126','AC129','AC132'),//Merge
    array('AB105','AB109','AB114','AB124','AB127','AB130','AB133','AB134'),//Respostas
    array('AB103:AC105','AB107:AC109','AB111:AC114','AB116:AC121','AB123:AC124','AB126:AC127','AB129:AC130','AB132:AC134',$CAtibainha)//Bordas e Cor $E[3][8]
);

$E4= array(//Estatísticas Paiva Castro [0]¹ Linha  [0]² Coluna
    array('AD103','AD107','AD111','AD116','AD123','AD126','AD129','AD132'),//Texto
    array('AE104','AE108','AE113','AD117:AD121','AE123','AE126','AE129','AE132'),//Merge
    array('AD105','AD109','AD114','AD124','AD127','AD130','AD133','AD134'),//Respostas
    array('AD103:AE105','AD107:AE109','AD111:AE114','AD116:AE121','AD123:AE124','AD126:AE127','AD129:AE130','AD132:AE134',$CPaivaCastro)//Bordas e Cor $E[3][8]
);

$E5= array(//Estatísticas Águas Claras [0]¹ Linha  [0]² Coluna
    array('AF103','AF107','AF111','AF116','AF123','AF126','AF129','AF132'),//Texto
    array('AG104','AG108','AG113','AF117:AF121','AG123','AG126','AG129','AG132'),//Merge
    array('AF105','AF109','AF114','AF124','AF127','AF130','AF133','AF134'),//Respostas
    array('AF103:AG105','AF107:AG109','AF111:AG114','AF116:AG121','AF123:AG124','AF126:AG127','AF129:AG130','AF132:AG134',$CAguasClaras)//Bordas e Cor $E[3][8]
);

$Es= array($E1,$E2,$E3,$E4,$E5);
foreach ($Es as $E){
$spreadsheet->getActiveSheet()
    ->setCellValue($E[0][0],'Amplitude de  Amostra (H)')->mergeCells($E[0][0].':'.$E[1][0])
    ->setCellValue($E[2][0],'H =')
    ->setCellValue($E[0][1],'Número de linhas  da distribuição (k)')->mergeCells($E[0][1].':'.$E[1][1])
    ->setCellValue($E[2][1],'k =')
    ->setCellValue($E[0][2],'Amplitude do Intervalo de Classe (h)')->mergeCells($E[0][2].':'.$E[1][2])
    ->setCellValue($E[2][2],'h =')
    ->setCellValue($E[0][4],'Desvio Padrão (S)')->mergeCells($E[0][4].':'.$E[1][4])
    ->setCellValue($E[2][3],'S =')
    ->setCellValue($E[0][5],'Moda (MO)')->mergeCells($E[0][5].':'.$E[1][5])
    ->setCellValue($E[2][4],'MO =')
    ->setCellValue($E[0][6],'Média (XM)')->mergeCells($E[0][6].':'.$E[1][6])
    ->setCellValue($E[2][5],'XM =')
    ->setCellValue($E[0][7],'Distribuição (VMP)')->mergeCells($E[0][7].':'.$E[1][7])
    ->setCellValue($E[2][6],'VMP+ =')
    ->setCellValue($E[2][7],'VMP- =');

$mediana= array('Mediana','n/2 =','Li =','Fac-¹ =','Fabs =',"h' =");
$Mediana= array_chunk($mediana, 1);//Converte para coluna
$spreadsheet->getActiveSheet()->fromArray($Mediana, null, $E[0][3]);

$resps= array($E[2][0],$E[2][1],$E[2][2],$E[1][3],$E[2][3],$E[2][4],$E[2][5],$E[2][6].':'.$E[2][7]);
foreach ($resps as $resp){
    $spreadsheet->getActiveSheet()->getStyle($resp)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);//Orientação Horizontal
}

$tits= array($E[0][0].':'.$E[1][0],$E[0][1].':'.$E[1][1],$E[0][2].':'.$E[1][2],$E[0][3],$E[0][4].':'.$E[1][4],$E[0][5].':'.$E[1][5],$E[0][6].':'.$E[1][6],$E[0][7].':'.$E[1][7]);
foreach ($tits as $tit){
    $spreadsheet->getActiveSheet()->getStyle($tit)->getAlignment()->setWrapText(true);//Quebra de Texto
    $spreadsheet->getActiveSheet()->getStyle($tit)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);//Orientação Horizontal
    $spreadsheet->getActiveSheet()->getStyle($tit)->getFont()->setBold(true);//Negrito
    $spreadsheet->getActiveSheet()->getStyle($Data)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);//Textura da Célula
    $spreadsheet->getActiveSheet()->getStyle($Data)->getFill()->getStartColor()->setARGB($E[3][8]);//Cor da Célula
    $spreadsheet->getActiveSheet()->getStyle($tit)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Baixo
}   $spreadsheet->getActiveSheet()->getStyle($E[0][3])->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Direita

$borders= array($E[3][0],$E[3][1],$E[3][2],$E[3][3],$E[3][4],$E[3][5],$E[3][6],$E[3][7]);
foreach ($borders as $border){
    $spreadsheet->getActiveSheet()->getStyle($border)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Cima
    $spreadsheet->getActiveSheet()->getStyle($border)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Baixo
    $spreadsheet->getActiveSheet()->getStyle($border)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Esquerda
    $spreadsheet->getActiveSheet()->getStyle($border)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);//Direita
}
}

//==RESULTADOS===================================================================================================

//          0     1       2      3      4      5      6      7      8      9      10     11     12     13     14     15     16     17     18
$R1= array('Y','Y7:Y99','Y100','AJ7','AL14','Y105','Y109','Y114','Y116','Y117','Y118','Y119','Y120','Y121','Y124','Y127','Y130','Y133','Y134');
$R2= array('AA','AA7:AA99','AA100','AJ20','AL27','AA105','AA109','AA114','AA116','AA117','AA118','AA119','AA120','AA121','AA124','AA127','AA130','AA133','AA134');
$R3= array('AC','AC7:AC99','AC100','AJ33','AL40','AC105','AC109','AC114','AC116','AC117','AC118','AC119','AC120','AC121','AC124','AC127','AC130','AC133','AC134');
$R4= array('AE','AE7:AE99','AE100','AJ46','AL53','AE105','AE109','AE114','AE116','AE117','AE118','AE119','AE120','AE121','AE124','AE127','AE130','AE133','AE134');
$R5= array('AG','AG7:AG99','AG100','AJ59','AL66','AG105','AG109','AG114','AG116','AG117','AG118','AG119','AG120','AG121','AG124','AG127','AG130','AG133','AG134');

$Rs= array($R1,$R2,$R3,$R4,$R5);
foreach ($Rs as $R){
$spreadsheet->getActiveSheet()
    ->setCellValue($R[2],"=COUNT($R[1])")
    ->setCellValue($R[3],"=MIN($R[1])")
    ->setCellValue($R[4],"=MAX($R[1])")
    ->setCellValue($R[5],"=$R[4]-$R[3]")//Amplitude de Amostra
    ->setCellValue($R[6],"=1+3.3*LOG10($R[0]100)")//Número de linhas
    ->setCellValue($R[7],"=$R[5]/$R[6]")//Amplitude do Intervalo
    ->setCellValue($R[8],"=$R[9]+($R[8]-$R[10])*$R[13]/$R[12]")
    ->setCellValue($R[9],"=$R[2]/2");

//$spreadsheet->getActiveSheet()->setAutoFilter('Y7:Y99');
//$spreadsheet->getActiveSheet()->setAutoFilter('AA7:AA99');
//$spreadsheet->getActiveSheet()->setAutoFilter('AC7:AC99');
//$spreadsheet->getActiveSheet()->setAutoFilter('AE7:AE99');
//$spreadsheet->getActiveSheet()->setAutoFilter('AG7:AG99');
}

//==FREQUÊNCIA===================================================================================================


$F1= array(//      0                  1     2     3     4     5
    array('Represa Jaguari/Jacareí','AI5','AL5','AI6','AI7','AK7'),
    array('AJ7','AL7','AJ8','AL8','AJ9','AL9','AJ10','AL10','AJ11','AL11','AJ12','AL12','AJ13','AL13','AJ14'),
    array('Y114','AM7','AM8','AM9','AM10','AM11','AM12','AM13','AM14','AM15'),
    array('Y7:Y99')
);

$F2= array(
    array('Represa Cachoeira','AI18','AL18','AI19','AI20','AK20'),
    array('AJ20','AL20','AJ21','AL21','AJ22','AL22','AJ23','AL23','AJ24','AL24','AJ25','AL25','AJ26','AL26','AJ27'),
    array('AA114','AM20','AM21','AM22','AM23','AM24','AM25','AM26','AM27','AM28'),
    array('AA7:AA99')
);

$F3= array(
    array('Represa Atibainha','AI31','AL31','AI32','AI33','AK33'),
    array('AJ33','AL33','AJ34','AL34','AJ35','AL35','AJ36','AL36','AJ37','AL37','AJ38','AL38','AJ39','AL39','AJ40'),
    array('AC114','AM33','AM34','AM35','AM36','AM37','AM38','AM39','AM40','AM41'),
    array('AC7:AC99')
);

$F4= array(
    array('Represa Paiva Castro','AI44','AL44','AI45','AI46','AK46'),
    array('AJ46','AL46','AJ47','AL47','AJ48','AL48','AJ49','AL49','AJ50','AL50','AJ51','AL51','AJ52','AL52','AJ53'),
    array('AE114','AM46','AM47','AM48','AM49','AM50','AM51','AM52','AM53','AM54'),
    array('AE7:AE99')
);

$F5= array(
    array('Represa Águas Claras','AI57','AL57','AI58','AI59','AK59'),
    array('AJ59','AL59','AJ60','AL60','AJ61','AL61','AJ62','AL62','AJ63','AL63','AJ64','AL64','AJ65','AL65','AJ66'),
    array('AG114','AM59','AM60','AM61','AM62','AM63','AM64','AM65','AM66','AM67'),
    array('AG7:AG99')
);

$Ftits= array('i','NA (m)','','','Fi','xm','xm*Fi','Fac',"S'");
$fnums= range(1,8);
$Fnums= array_chunk($fnums, 1);
$fr= array('|- ','|- ','|- ','|- ','|- ','|- ','|- ','|-|');
$Fr= array_chunk($fr, 1);

$Fs= array($F1,$F2,$F3,$F4,$F5);
foreach ($Fs as $F){
$spreadsheet->getActiveSheet()
    ->setCellValue($F[0][1],$F[0][0])->mergeCells($F[0][1].':'.$F[0][2])
    ->fromArray($Ftits,NULL,$F[0][3])
    ->fromArray($Fnums,NULL,$F[0][4])
    ->fromArray($Fr,NULL,$F[0][5])
    ->setCellValue($F[1][1],"=".$F[1][0]."+".$F[2][0])
    ->setCellValue($F[1][2],"=".$F[1][1])
    ->setCellValue($F[1][3],"=".$F[1][1]."+".$F[2][0])
    ->setCellValue($F[1][4],"=".$F[1][3])
    ->setCellValue($F[1][5],"=".$F[1][3]."+".$F[2][0])
    ->setCellValue($F[1][6],"=".$F[1][5])
    ->setCellValue($F[1][7],"=".$F[1][5]."+".$F[2][0])
    ->setCellValue($F[1][8],"=".$F[1][7])
    ->setCellValue($F[1][9],"=".$F[1][7]."+".$F[2][0])
    ->setCellValue($F[1][10],"=".$F[1][9])
    ->setCellValue($F[1][11],"=".$F[1][9]."+".$F[2][0])
    ->setCellValue($F[1][12],"=".$F[1][11])
    ->setCellValue($F[1][13],"=".$F[1][11]."+".$F[2][0])
    ->setCellValue($F[1][14],"=".$F[1][13])//Fim frequências
    //->setCellValue($F[2][1],"=COUNTIFS($F[3][0];\"<\"&$F[1][1];$F[3][0];\">\"&$F[1][0])")
    ;
}

/*header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=$Tri[0]$Ano.xlsx");
header('Cache-Control: max-age=0');

$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save('php://output');*/

$writer = new Xlsx($spreadsheet);
$writer->save("$Tri[0]$Ano.xlsx");

$spreadsheet->disconnectWorksheets();
unset($spreadsheet);
