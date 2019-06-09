<?php
header('Content-Type: text/html; charset=utf-8', true);
ini_set('error_reporting', E_ALL)       ;
ini_set('display_errors', 1)            ;
ini_set('display_startup_errors', 1)    ;


$host       = "padilo00.mysql.tools";
$db         = "padilo00_auto"      ;
$db_login   = "padilo00_auto"      ;
$db_pass    = "!OA37e@fh9"         ;

// база
try {
    $DB 	=	new PDO("mysql:host=$host;dbname=$db;charset=utf8;", $db_login, $db_pass);
    $DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch(PDOException $e) {
    echo 'Ошибка: ' . $e->getMessage();
}

function dd($var) {
    echo '<pre>';
        print_r($var);
    echo '</pre>';
    die();
}

require_once(__DIR__.'/phpQuery-onefile.php'); // библа для удомного разбора ДОМа
$BASE_URL           = 'https://www.atm-chiptuning.com/chiptuning/';
$BASE_URL_LENGTH    = strlen($BASE_URL);
$ARRAY_TARGET_URL   = array();
$siteMapXML         = file_get_contents('https://www.atm-chiptuning.com/sitemap.xml');
$siteMapXML         = new SimpleXMLElement($siteMapXML);

// получаем все необходимые страницы
foreach($siteMapXML->url as $item){
    if(stristr($item->loc, $BASE_URL) && strlen($item->loc) > $BASE_URL_LENGTH){
        $ARRAY_TARGET_URL[] = (string) $item->loc;   
    }
}

$ALL = [];
// крутим цикл столько сколько страниц
$maxPage = count($ARRAY_TARGET_URL);
for($a = 8282; $a <= $maxPage  ; $a++ ){

    $automobil  = []; // В этом массиве буте вся инфа про один автомобиль

    /* Базовая страница автомобиля */

        $html                   = file_get_contents($ARRAY_TARGET_URL[$a]); // нужный урл
        $document               = phpQuery::newDocument($html); 
        $href                   = $document->find('body > main > div.Chiptuning-details > div > div.Chiptuning-details__info > div.Chiptuning-quote-small > a')->attr('href'); // ссылка на страницу с таблицей параметров


        $automobil['val_standart_PK']        = $document->find('body > main > div.Chiptuning-details > div > div.Chiptuning-details__info > div.Chiptuning-comparison.Clear > div div > div.Chiptuning-comparison__number.tuning-p-pre')->text();
        $automobil['value_chiptuners_PK']    = $document->find('body > main > div.Chiptuning-details > div > div.Chiptuning-details__info > div.Chiptuning-comparison.Clear > div div > div.Chiptuning-comparison__number.tuning-p-post > div')->text();
        $automobil['value_difference_PK']    = $document->find('body > main > div.Chiptuning-details > div > div.Chiptuning-details__info > div.Chiptuning-comparison.Clear > div div > div.Chiptuning-comparison__number.Chiptuning-comparison__number--atm.tuning-p-diff > div')->text();
        $automobil['val_standart_Nm']        = $document->find('body > main > div.Chiptuning-details > div > div.Chiptuning-details__info > div.Chiptuning-comparison.Clear > div:nth-child(3) > div:nth-child(2) > div')->text();
        $automobil['value_chiptuners_Nm']    = $document->find('body > main > div.Chiptuning-details > div > div.Chiptuning-details__info > div.Chiptuning-comparison.Clear > div:nth-child(3) > div:nth-child(3) > div > div')->text();
        $automobil['value_difference_Nm']    = $document->find('body > main > div.Chiptuning-details > div > div.Chiptuning-details__info > div.Chiptuning-comparison.Clear > div:nth-child(3) > div:nth-child(4) > div > div')->text();

    // ===========


    $html       = file_get_contents($href)      ; // нужный урл
    $document   = phpQuery::newDocument($html)  ; 

    $automobil['Brand']      = $document->find('div.Form__left > div.CarInfo.CarInfo--all > table  tr:nth-child(1) td:nth-child(2) ')->text();
    $automobil['Model']      = $document->find('div.Form__left > div.CarInfo.CarInfo--all > table  tr:nth-child(2) td:nth-child(2) ')->text();
    $automobil['Generation'] = $document->find('div.Form__left > div.CarInfo.CarInfo--all > table  tr:nth-child(3) td:nth-child(2) ')->text();
    $automobil['Type']       = $document->find('div.Form__left > div.CarInfo.CarInfo--all > table  tr:nth-child(4) td:nth-child(2) ')->text();

    $automobil['Brand']      = str_replace(":", "", $automobil['Brand'])      ;
    $automobil['Model']      = str_replace(":", "", $automobil['Model'])      ;
    $automobil['Generation'] = str_replace(":", "", $automobil['Generation']) ;
    $automobil['Type']       = str_replace(":", "", $automobil['Type'])       ;

    $ALL[] = $automobil;

    $dataDB =   array(
                    $automobil['Model']      ,
                    $automobil['Brand']      ,
                    $automobil['Generation'] ,
                    $automobil['Type']       ,

                    $automobil['val_standart_PK']       ,
                    $automobil['value_chiptuners_PK']   ,
                    $automobil['value_difference_PK']   ,

                    $automobil['val_standart_Nm']       ,
                    $automobil['value_chiptuners_Nm']   ,
                    $automobil['value_difference_Nm']   ,
                );

    $db_str  = "INSERT INTO `main_row`(`model`,`brand`,`generatie`,`type`, `value_standart`, `value_chiptuners`,`value_difference`,`val_standart_Nm`,`value_chiptuners_Nm`,`value_difference_Nm`) VALUES (?,?,?,?,?,?,?,?,?,?)";
    $addRow  = $DB->prepare($db_str);
    $result  =  $addRow->execute($dataDB);
    
    continue;
    // ДАЛЬШЕ КОД ИЗ СТАРОЙ ХУЙНИ (ОСТАВИЛ ПРОСТО ТАК)

    // пишем в базу по одной строке (используем подготовленный запрос что-бы не ломать строки)
    foreach($comment as $item){
        $db_str  = "INSERT INTO `rewiew`(`date`, `text`, `ip`, `name`) VALUES (?,?,?,?)";
        $addRow  = $DB->prepare($db_str);
        $addRow->execute(array($item['date'],$item['text'],$item['ip'],$item['name']));
    }
    
    sleep(rand(5,15 )); // рандомная пауза от 5 до 15 сек что-бы меньше капчу ловить
}
dd($ALL);
?>