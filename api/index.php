<?php
////config
include("../common.php");
////Get path segments to use for routing. Shift off the first segment, which is always 'api'
$segs = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
array_shift($segs);

////Get card data
if($segs[0] == 'getCard') {
    $card_id = $segs[1];

    if(!is_numeric($card_id)) {
          echo "Please add a card id number to the url path"; die;
    }

    $scq = "select card_id, circuit_id, user_mast_id, right_now, comment, card, coursepar, userpar from cards WHERE card_id = :card_id";
    try {
        $scstmt = $db->prepare($scq);
        $result = $scstmt->execute(array(':card_id' => $card_id));
        $cardcount = $scstmt->rowCount();
    } catch(PDOException $ex) {
        die("Failed to run query: " . $ex->getMessage());
    }
    if ($cardcount > 0) {
        while ($scrow = $scstmt->fetch()) {
            $card = $scrow;
            $rawCard = unserialize($card['card']);
            foreach($rawCard[0] as $foo => $bar) {
                $fixedCard[] = array(
                    'name' => $bar,
                    'par' => $rawCard[1][$foo],
                    'score' => $rawCard[2][$foo] 
                );
            }
            
            $card['card'] = $fixedCard;

            header('Content-Type: application/json');
            echo json_encode(array(
                'card' => $card)
            );
            
            die;
        }
    }
    else {
        header('Content-Type: application/json');
        echo json_encode(array('error' => 'No card for that id.'));
        die;
    }
}
