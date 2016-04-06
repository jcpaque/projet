<?php
session_start();
?>
<html>
<head>
<title>
Pr&eacute;visions Mazout
</title>
</head>
<body>

<?php
$tab1 = file('prixpetrole.csv');//Lecture du fichier csv
$tab1 = array_map('trim', $tab1);
$tabpet = array();
foreach($tab1 as $ele){
 $tabpet[] = explode(';', $ele);//création d'un tableau multidimensionnel dans lequel on a les prix 
}

$get=file_get_contents("http://www.finances.net/matieres_premieres/temps_reel/prix_petrole?type=WTI"); 
	$first_bracket=strpos($get,'"">');  
	$second_bracket=strpos($get,'</div>',$first_bracket); 
	$pet=substr($get,$first_bracket+3,$second_bracket-$first_bracket-3);  
	$petrole = floatval(str_replace(',', '.', $pet));
	$nbreligne3=count($tabpet)-1;
//echo $nbreligne;

// $hier1=explode("/",$tabpet[$nbreligne3][0]); //date d'hier au format numérique pour comparaison
// $date_jour1 = date ('d/m/Y'); //date du jour
// $ajd1=explode("/",$date_jour1); //explode pour mettre au format numérique
// if($ajd1 != $hier1) //si la date de la dernière ligne est différente de la date d'ajd, alors on crée une nouvelle ligne pour y stocker les données d'ajd
// {
// $ligne3=$nbreligne3;
// $tabpet[$ligne3][0]=$date_jour1;
// $tabpet[$ligne3][1]=$petrole;
// $petrole=$petrole*1.13801;
// $fp1=fopen("prixpetrole.csv","a");
// $data1=array($date_jour1,$petrole);
// fputcsv($fp1,$data1,";");

// fclose($fp1);
// }
$max=count($tabpet)-1;	

//PENTE 7JOURS PRIX PETROLE
$pentepet=array();

for($i=0;$i<=$max;$i++)
{
	$pentepet[$i][0]=$i;
}

for($i=0;$i<=5;$i++)
{
	$pentepet[$i][1]=0;
}

for($i=6;$i<=$max;$i++)
{
	$pentepet[$i]=($tabpet[$i][1]-$tabpet[$i-6][1])/($pentepet[$i][0]-$pentepet[$i-6][0]);
}


//PENTE 1JOUR PRIX PETROLE
$normal1jour=array();
for($i=0;$i<=$max;$i++)
{
	$normal1jour[$i][0]=$i;
}

$normal1jour[0][1]=0;

for($i=1;$i<=$max;$i++)
{
	$normal1jour[$i][1]=($tabpet[$i][1]-$tabpet[$i-1][1])/($normal1jour[$i][0]-$normal1jour[$i-1][0]);
}
// echo '<pre>';
// print_r($normal1jour);
// echo '</pre>';

//CHECK OCCURRENCE PENTE POSITIVE/NEGATIVE/NULLE 1JOUR 

//1JOUR----------------------------------------------------------------------------------------------
$maxlignes=count($normal1jour);
$normaljours11=array();
$normaljours12=array();
$normaljours13=array();
$nbr0normaljours1=array();
$resultnormaljours1=array();
$resultnormaljours12=array();
$resultnormaljours13=array();
$nbrposnormaljours1=array();
$nbrnegnormaljours1=array();


for($i=0;$i<=$maxlignes-1;$i++)
{
	
	switch($normal1jour[$i][1])
	{
		case "0":
		$nbrzero[$i]=count($normal1jour[$i][1]);
		$zero=count($normal1jour[$i][1]);
		$normaljours11[$i]=$nbrzero;
		break;
		
		case ($normal1jour[$i][1]<0):
		$nbrnega[$i]=count($normal1jour[$i][1]);
		$normaljours12[$i]=$nbrnega;
		break;
		
		case ($normal1jour[$i][1]>0):
		$nbrpositif[$i]=count($normal1jour[$i][1]);
		$normaljours13[$i]=$nbrpositif;
		break;
		
		
		
	}


}
for($i=0;$i<=$maxlignes;$i++)
{
	$resultnormaljours1[$i]=$normaljours11[count($resultnormaljours1)][$i];
	$resultnormaljours12[$i]=$normaljours12[count($resultnormaljours12)][$i];
	$resultnormaljours13[$i]=$normaljours13[count($resultnormaljours13)][$i];
	
}
echo '<pre>';
print_r($resultnormaljours1);
echo '</pre>';

// echo '<pre>';
// print_r($resultnormaljours12);
// echo '</pre>';

// echo '<pre>';
// print_r($resultnormaljours13);
// echo '</pre>';

//pente nulle
$indiceTabnormaljours1 = 0;
 
for ($x = 0 ; $x < count($resultnormaljours1) ; $x ++)
{
    if ($resultnormaljours1[$x] == 1)
	{
        $y = $x;
		
        while($resultnormaljours1[$y] != 0)
		{
            $nbr0normaljours1[$indiceTabnormaljours1]++;
            $y++;

		}
        $indiceTabnormaljours1 ++;
		
        $x=$y;
		
	}

}

//pente négative
$indiceTabnormaljours12 = 0;
 
for ($x = 0 ; $x < count($resultnormaljours12) ; $x ++)
{
    if ($resultnormaljours12[$x] == 1)
	{
        $y = $x;
		
        while($resultnormaljours12[$y] != 0)
		{
            $nbrnegnormaljours1[$indiceTabnormaljours12]++;
            $y++;

		}
        $indiceTabnormaljours12 ++;
		
        $x=$y;
		
	}

}

//pente positive
$indiceTabnormaljours13 = 0;
 
for ($x = 0 ; $x < count($resultnormaljours13) ; $x ++)
{
    if ($resultnormaljours13[$x] == 1)
	{
        $y = $x;
		
        while($resultnormaljours13[$y] != 0)
		{
            $nbrposnormaljours1[$indiceTabnormaljours13]++;
            $y++;

		}
        $indiceTabnormaljours13 ++;
		
        $x=$y;
		
	}

}

// echo '<pre>';
// print_r($nbr0normaljours1);
// echo '</pre>';
$max0=max($nbr0normaljours1);
echo "Max jours d'affil&eacute;s avec pente 0 :";
echo "</br>";
echo $max0;
echo "</br>";
$moyenne0=array_sum($nbr0normaljours1)/count($nbr0normaljours1);
echo "Moyenne jours d'affil&eacute;s avec pente 0:";
echo "</br>";
echo $moyenne0;
echo "</br>";

// echo '<pre>';
// print_r($nbrnegnormaljours1);
// echo '</pre>';
$maxneg=max($nbrnegnormaljours1);
echo "Max jours d'affil&eacute;s avec pente < 0 :";
echo "</br>";
echo $maxneg;
echo "</br>";
$moyenneneg=array_sum($nbrnegnormaljours1)/count($nbrnegnormaljours1);
echo "Moyenne jours d'affil&eacute;s avec pente <0:";
echo "</br>";
echo $moyenneneg;
echo "</br>";

// echo '<pre>';
// print_r($nbrposnormaljours1);
// echo '</pre>';
$maxpos=max($nbrposnormaljours1);
echo "Max jours d'affil&eacute;s avec pente > 0 :";
echo "</br>";
echo $maxpos;
echo "</br>";
$moyennepos=array_sum($nbrposnormaljours1)/count($nbrposnormaljours1);
echo "Moyenne jours d'affil&eacute;s avec pente > 0:";
echo "</br>";
echo $moyennepos;
echo "</br>";

//-----------------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------

//-----------------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------
//-----------------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------
//---------SPLIT LE TABLEAU DE 0 ET 1 EN SOUS TABLEAUX CONTENANT UNIQUEMENT LES 1 CONSECUTIFS

$indexTableau= 0;
$newTab=array();
for ($x = 0 ; $x < count($resultnormaljours1) ; $x ++)
{
    if ($resultnormaljours1[$x] == 1)
	{
        $y = $x;
		
        while($resultnormaljours1[$y] != 0)
		{
            $newTab[$indexTableau][$y]=$resultnormaljours1[$y];
            $y++;

		}
        $indexTableau ++;
		
        $x=$y;
		
	}

}

$finTableau=count($newTab)-1;
// echo $finTableau;
$finalJoursCons=array();
for($i=0;$i<count($resultnormaljours1);$i++)
{
$finalJoursCons[0][$i]=$newTab[$finTableau][$i];
$finalJoursCons[0][$i]=(int)$finalJoursCons[0][$i];
$sommeJours=$finalJoursCons[0][$i]+$sommeJours;

}

$indices=array_keys($resultnormaljours1,"1");

$indexLigne=$indices[count($indices)-1];
// echo $indexLigne;
$indexLigne=(int)$indexLigne;
$_SESSION['indexLignePetrole']=$indexLigne;


// echo "<pre>";
// print_r($indices);
// echo "</pre>";

// echo "<pre>";
// print_r($newTab);
// echo "</pre>";

// echo "<pre>";
// print_r($finalJoursCons);
// echo "</pre>";

// $nbr=
// echo $nbr;

$_SESSION['penteNullePetrole']=$sommeJours+1;
if($resultnormaljours1[count($resultnormaljours1)]=="1")
{
echo "<br/>";
echo "Stabilit&eacute; actuelle: ";
echo $sommeJours+1;
echo " jours";
}else
{
 $_SESSION['penteNullePetrole']=0;
 
}
?>
</body>
</html>