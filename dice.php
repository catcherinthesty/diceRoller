<?php
if(isset($_POST['roll'])) {
    $roll=$_POST['roll'];
} else {
    $roll='-2dF+11+1d%';
}

/* Global variables */

$dice_regex='/^[\+\-]?(([\d]*[dxb](F|%|[\d]*)?(([lh])\d+)?((?!\4)[lh]\d+)?)|([\d]+))([\+\-](([\d]*[dxb](F|%|[\d]*)?(([lh])\d+)?((?!\12)[lh]\d+)?)|([\d]+)))*([\/\*]\d+)?$/';
$dice_types=[ 'x','d','b', ];
$dice_text_out="";
$dice_total=0;

diceRoller_roll($roll);

function diceRoller_roll($roll) {
    global $dice_text_out, $dice_total;
    $roll=diceRoller_validate($roll);
    $roll=diceRoller_breakRoll($roll);
    $roll=diceRoller_processRoll($roll);
    $roll=diceRoller_executeRoll($roll);
    echo $dice_text_out.": ".$dice_total;
}

function diceRoller_executeRoll($roll) {
    global $dice_text_out;
    foreach($roll as $item) {
        if(count($item) == 2) {
           # count 2 means it's a modifier and should be added.
            $dice_text_out .= $item[0].$item[1];
            dice_addTotal($item[1],$item[0]);
        } elseif(count($item) == 7) {
           # Now we need to pass the information to the appropriate roller.
            $dice_text_out .= "<i>".$item[0].$item[6].$item[1]."</i> ";
            diceRoller_rollStandard($item[0],$item[1],$item[2],$item[3],$item[4],$item[5],$item[6]);
        }
    }
}

function diceRoller_rollStandard($dice,$sides,$drop_low,$drop_high,$sign,$range,$type) {
    global $dice_text_out;
   # declare necessary local variables
    $result=[]; $drop=[];
       # We need to mark F and % appropriately.
        if($sides=="F") {
            $sides=1;
            $range=-1;
        }
        if($sides=="%") {
            $sides=100;
        }
   # Use a while loop to track the rolls.
    $i=1; $high=0; $low=0;
    while($i<=$dice) {
       # Simple random roll.
        $roll=rand($range,$sides);
       # temp is used by exploding dice.
        $temp=0;
       # breeding dice with max result extend the count.
        if($type=='b' && $roll==$sides) {
            $i--;
        };
       # exploding dice with max result grow.
        if($type=='x') {
            $temp=$roll;
            while ($roll==$sides) {
                $roll=rand($range,$sides);
                $temp+=$roll;
            }
        $roll=$temp;
        }
        $result[]=$roll;
        $i++;
    }
   # Dice dropping functions are called.
    if($drop_low) {
        $low=diceRoller_dropLow($result,$drop_low);
    }
   # High drop function is exactly the same as the low one.
    if($drop_high) {
        $high=diceRoller_dropHigh($result,$drop_high);
    }
   # merge the arrays
    if(!empty($high) && !empty($low)) {
        $drop=array_merge($high,$low);
    } elseif(!empty($high)) {
        $drop=$high;
    } elseif(!empty($low)) {
        $drop=$low;
    }
   # find and mark collisions.
    foreach($drop as $i) {
        $j=array_search($i,$result);
        $result[$j]="<b class='red'>$result[$j]</b>";
    }
   # Cleverly, this won't sum the ones we marked.
    $total = array_sum($result);
   # We need to mark exploded or bred dice.
    if($type == 'b' || $type == 'x') {
        foreach($result as $i => $roll) {
            if($roll>=$sides) {
                $result[$i]="<b class='blue'>$result[$i]</b>";
            }
        }
    }
    $dice_text_out.= "(".implode(",",$result).") ";
    dice_addTotal($total,$sign);
}

function diceRoller_dropLow($arr,$dr) {
    $low=[];
    sort($arr);
    for ($i=1;$i<=$dr;$i++) {
        $low[]=array_shift($arr);
    }
    return $low;
}

function diceRoller_dropHigh($arr,$dr) {
    $high=[];
    sort($arr);
    for ($i=1;$i<=$dr;$i++) {
        $high[]+=array_pop($arr);
    }
    return $high;
}


function diceRoller_validate($roll) {
    global $dice_regex;
    if(preg_match($dice_regex,$roll)) { return $roll; } else { return Null; }
}


function diceRoller_breakRoll($roll) {
   #####
   # We need to break the roll into digestible chunks for the next step of the parser.
   # This function takes an arbitrarily long dice string, breaks it according to regex,
   # and returns an array of useable values.
   #
   # First, let's delineate all the possible operands that might break strings.
    $oper=['+','-','/','*'];
    $next="";
    $list=[];
    while(preg_match('/\+|\-|\/|\*/',$roll)) {
        $matches=[];
        foreach ($oper as $i) {
            $matches[]=strrpos($roll,$i);
            $next=max($matches);
        }
        $list[]=(substr($roll,$next));
        $roll=(substr($roll,0,($next-strlen($roll))));
    }
    if($roll) {
        if(!preg_match('/\+|\-|\/|\*/',$roll)) {
            $roll="+".$roll;
        }
        $list[]=$roll;
    }
    return array_reverse($list);
}

function diceRoller_processRoll($roll) {
   #####
   #
   # Next step: process the rolls into components which will be passed to another function
   # We need three things when we're done:
   #     1.) Original dice string (padded as necessary)
   #     2.) The outcome of rolls (parenthetical string of comma-separated values, formatted)
   #     3.) The total
   # The original dice string will be retained by this function and added to the global
   # variable $dice_text_out right before the roller adds individual rolls for that
   # dice string as a side-effect.
   # Finally, the roller returns the value of the roll. I will note that this could be
   # useful for adding a quick and dirty roll function that works off this PHP script
   # without the need for the parser later.
   #
   # First, we get the list of rolls, each now tidily by itself.
    global $dice_types;
    $output=[]; $n=0;
    foreach($roll as $item) {

       # Initiate variables used in the loop
        $drop_low=0; $drop_high=0; $type=""; $range=1;

       # strip and capture the sign
        $sign=substr($item,0,1);
        $item=substr($item,1);

       # Next, we need to know if it's a roll and, if so, what kind (x,b or d)
        foreach($dice_types as $t) {
           # Use "Not identical to," because position 0 is not the same as boolean false
           # (The docs for strpos point out this issue)
            if(strpos($item,$t) !== false) {
                $type = $t;
            }
        }

       # If $type is empty, the result is a modifier, not a roll.
        if(empty($type)) {
            $output[$n]=[$sign,$item];
        } else {
       # We're down to the final bits. Don't panic. First, let's chop it up. AGAIN.
       # First, let's capture and get rid of the drop notation items if they exist.
        $a=[];
        $a[0] = strpos($item,"l");
        $a[1] = strpos($item,"h");
       # There're some mathematical gynmastics happening here.
        if(!empty($a[0])) {
            $drop_low = substr($item,$a[0]+1,(strlen($item)-$a[1]-1));
        }
        if(!empty($a[1])) {
            $drop_high = substr($item,$a[1]+1,(strlen($item)-$a[0]-1));
        }
       # Now we just need to strip the l and h.
        if(!empty($a[0]) && !empty($a[1])) {
           # The math on this is fairly clever, but nothing to marvel upon
            $item=substr($item,0,-(strlen($item)-min($a)));
        } elseif(!empty($a[0]) || !empty($a[1])) {
           # This is kind of sneaky lazy. It'll just pull whichever one exists.
            $item=substr($item,0,-(strlen($item)-max($a)));
        }
       # We pad it out as necessary
        if(substr($item,0,1) == $type) { $item="1".$item; }
        if(substr($item,-1,1) == $type) { $item=$item."6"; }
        $rolls = explode($type,$item);
       # We now have every variable we could want to pass to roll!
        $output[$n]=[$rolls[0],$rolls[1],$drop_low,$drop_high,$sign,$range,$type];
        }
    $n++;
    }
    return $output;
}





function dice_addTotal($value,$sign) {
    global $dice_total;
   # We could just eval() instead of stripping signs, but that's considered bad practice.
   # Nevermind the fact that with the controls we've put in place it's safer
   # and less accident prone than using SQL. Nope. Not important.
    switch($sign) {
        case "+": $dice_total+=$value; break;
        case "-": $dice_total-=$value; break;
        case "/": $dice_total/=$value; break;
        case "*": $dice_total*=$value; break;
       # It's probably good practice to add a default. I don't care.
    }
}





function dice_rollDice($dice,$sides,$drop_low,$drop_high,$sign,$range) {
    $a=[$dice,$sides,$drop_low,$drop_high,$sign,$range];
    foreach($a as $b) {
    echo $b."\n";
    }
    echo "\n";
}

