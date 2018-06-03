<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <title>APS 3º Semestre</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    </head>
    <body>
    <form action="xlsx.php" method="post">
        <select name="Mes" id="ctl00_cntTitulo_cmbMes" class="FormText" style="width:98px;">
        <?php $meses= array(1,4,7,10); $mesesn= array("Jan a Mar","Abr a Jun","Jul a Set","Out a Dez");
        $mess= array_map(null, $meses, $mesesn);
        foreach ($mess as $mes) { ?>
        <option value="<?php echo $mes[0].'">'.$mes[1] ?></option>
        <?php } ?>
        </select>
        <select name="Ano" id="ctl00_cntTitulo_cmbAno" class="FormText" style="width:66px;">
        <?php $anos= range(2018,2004); foreach ($anos as $ano) { ?>
        <option value="<?php echo $ano.'">'.$ano ?></option>
        <?php } ?>
        </select>
        <input name="ctl00$cntTitulo$btnVizualiza" type="submit" value="Visualizar">
    </form>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</body>
</html>
