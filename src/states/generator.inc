<?php

function generator($districts,$max,$total,$state){
  include $state.".inc";
  $data = state_array();
  $result = '<div><p style="text-align:right; position:relative;">(This map is only for representation purpose)</p><p style="text-align:right; position:relative;">Total : '.$total.'</p><svg height="30px" style="float:right;position:relative;z-index:10">
  <defs
     id="defs7">
    <linearGradient
       id="grad1"
       x1="0"
       y1="0"
       x2="89.442719"
       y2="0"
       gradientTransform="scale(3.2925154,0.2236068)"
       gradientUnits="userSpaceOnUse">
      <stop
         offset="0%"
         style="stop-color:rgb(221, 198, 186);stop-opacity:1"
         id="stop2" />
      <stop
         offset="100%"
         style="stop-color:rgb(234, 85, 7);stop-opacity:1"
         id="stop4" />
    </linearGradient>
  </defs>
  <rect
     x="0"
     y="0"
     height="20"
     width="304.49152"
     id="rect9"
     style="fill:url(#grad1);stroke-width:0.85803777" />
  <text
     x="5"
     y="14"
     id="text11">0</text>
  <text
     x="265"
     y="15"
     id="text13">'.$max.'</text>
  </svg></div><h1>'.$state.'</h1><a onclick="map()">Back to India Map</a><svg
     xmlns="http://www.w3.org/2000/svg"
     viewBox="0 0 297 210"
     version="1.1">
    <g
       transform="translate(0,-87)">
      <g
         transform="'.$data['matrix'].'"
         title="'.$state.'"
         entity_type="state">';
     // To separate the state name from $data array
     $key = array_keys($data);
     // To separate the states name from $districts array
     $key2 = array_keys($districts);
     $i=0;
     foreach ($data as $m) {
       $j=0;
       $flag = false;
       foreach ($districts as $s) {
         // Checking $districts's state name in $map
         if($key[$i]==$key2[$j]){
           $flag = $districts[$key2[$j]];
           break;
         }
         $j++;
       }
       // Generating individual data for each states
       if($flag && $key[$i] != 'Mumbai' && $key[$i] != 'matrix'){
         $result .= '<path id="'.$key[$i].'" fill ="'.$flag.'" class="state" onclick="hello(\''.$state.'\',\''.$key[$i].'\')" stroke="#FFFFFF" stroke-width="0.25850111" d="'.$m.'"><title>'.$key[$i].'</title></path>';
       }else if($key[$i] == 'Mumbai' && $key[$i] != 'matrix') {
         $result .= '<path id="'.$key[$i].'" fill = "'.$flag.'" class="state" onclick="hello(\''.$state.'\',\''.$key[$i].'\')" stroke="#FFFFFF" stroke-width="0.25850111" d="m 201.72564,816.05709 c -0.45831,-0.6352 -0.50651,-0.9645 -0.32163,-2.19729 0.11962,-0.79756 0.40035,-1.71119 0.62386,-2.0303 0.22352,-0.3191 0.40639,-0.91978 0.40639,-1.33484 0,-1.02629 0.53963,-0.97085 2.13363,0.21918 l 1.3044,0.97382 -0.77678,2.19511 c -0.42723,1.20731 -0.98683,2.35465 -1.24354,2.54966 -0.74891,0.5689 -1.54665,0.42808 -2.12633,-0.37534 z"><title>'.$key[$i].'</title></path>';
         $result .= '<path id="'.$key[$i].'" fill = "'.$flag.'" class="state" onclick="hello(\''.$state.'\',\''.$key[$i].'\')" stroke="#FFFFFF" stroke-width="0.25850111" d="m 204.82796,810.44402 c -0.70086,-0.53664 -1.44057,-0.97572 -1.64381,-0.97572 -0.72782,0 -1.0216,-0.66921 -1.0216,-2.32717 0,-1.98582 0.66686,-3.10688 1.84811,-3.10688 1.18628,0 3.29582,1.59812 3.88523,2.94333 0.67637,1.54365 0.65641,1.67561 -0.25337,1.67561 -0.8439,0 -1.3289,0.66134 -1.46277,1.99461 l -0.0775,0.77195 z"><title>'.$key[$i].'</title></path>';
       }else if($key[$i] != 'Mumbai' && $key[$i] != 'matrix'){
         $result .= '<path id="'.$key[$i].'" fill = "#666666" class="state" onclick="hello(\''.$state.'\',\''.$key[$i].'\')" stroke="#FFFFFF" stroke-width="0.25850111" d="'.$m.'"><title>'.$key[$i].'</title></path>';
       }
       $i++;
     }
     $result .= '</g></g></svg>';

     return $result;
}


?>
