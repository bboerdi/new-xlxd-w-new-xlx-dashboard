<?php

if (!isset($_SESSION['FilterCallSign'])) {
    $_SESSION['FilterCallSign'] = null;
}

if (!isset($_SESSION['FilterModule'])) {
    $_SESSION['FilterModule'] = null;
}

if (isset($_POST['do'])) {
    if ($_POST['do'] == 'SetFilter') {

        if (isset($_POST['txtSetCallsignFilter'])) {
            $_POST['txtSetCallsignFilter'] = trim($_POST['txtSetCallsignFilter']);
            if ($_POST['txtSetCallsignFilter'] == "") {
                $_SESSION['FilterCallSign'] = null;
            }
            else {
                $_SESSION['FilterCallSign'] = $_POST['txtSetCallsignFilter'];
                if (strpos($_SESSION['FilterCallSign'], "*") === false) {
                    $_SESSION['FilterCallSign'] = "*".$_SESSION['FilterCallSign']."*";
                }
            }

        }

        if (isset($_POST['txtSetModuleFilter'])) {
            $_POST['txtSetModuleFilter'] = trim($_POST['txtSetModuleFilter']);
            if ($_POST['txtSetModuleFilter'] == "") {
                $_SESSION['FilterModule'] = null;
            }
            else {
                $_SESSION['FilterModule'] = $_POST['txtSetModuleFilter'];
            }

        }
    }
}

if (isset($_GET['do'])) {
    if ($_GET['do'] == "resetfilter") {
        $_SESSION['FilterModule'] = null;
        $_SESSION['FilterCallSign'] = null;
    }
}

?>

<div class="row">
   <div class="col-md-9">
      <table class="table table-sm table-dark table-striped table-hover">
<?php
if ($PageOptions['UserPage']['ShowFilter']) {
  echo '
 <tr>
   <th colspan="8">
      <table width="100%" border="0">
         <tr>
            <td align="left">
               <form name="frmFilterCallSign" method="post" action="./index.php">
                  <input type="hidden" name="do" value="SetFilter" />
                  <input type="text" class="form-control-sm" value="'.$_SESSION['FilterCallSign'].'" name="txtSetCallsignFilter" placeholder="Rufzeichen" onfocus="SuspendPageRefresh();" onblur="setTimeout(ReloadPage, '.$PageOptions['PageRefreshDelay'].');" />
                  <input type="submit" value="Filter" class="btn btn-outline-secondary btn-sm mb-1" />
               </form>
            </td>';
              if (($_SESSION['FilterModule'] != null) || ($_SESSION['FilterCallSign'] != null)) {
                  echo '
         <td><a href="./index.php?do=resetfilter" class="smalllink">Disable filters</a></td>';
              }
              echo '            
            <td align="right" style="padding-right:3px;">
               <form name="frmFilterModule" method="post" action="./index.php">
                  <input type="hidden" name="do" value="SetFilter" />
                  <input type="text" class="form-control-sm" value="'.$_SESSION['FilterModule'].'" name="txtSetModuleFilter" placeholder="Raum" onfocus="SuspendPageRefresh();" onblur="setTimeout(ReloadPage, '.$PageOptions['PageRefreshDelay'].');" />
                  <input type="submit" value="Filter" class="btn btn-outline-secondary btn-sm mb-1" />
               </form>
            </td>
      </table>
   </th>
</tr>';
}
?>
    
    <tbody class="table-group-divider">
        <tr>   
            <th class="col-md-1">#</th>
            <th class="col-md-1">Land</th>
            <th class="col-md-2">Rufzeichen</th>
            <th class="col-md-2" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="Suffix, Rufzeichenerweiterung" data-bs-placement="left">Erweiterung</th>
            <th class="col-md-1">DPRS</th>
            <th class="col-md-2">Einstieg / Peer</th>
            <th class="col-md-2">Zuletzt geh&ouml;rt</th>
            <th class="col-md-1" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="OM/YL in Raum:" data-bs-placement="bottom"><img class="invert_img" src="./img/ear.png" alt="in Raum" /> Raum</th>
         </tr>
    </tbody>
    <tbody class="table-group-divider"> 
        <tr><td colspan="8"></td></tr>
    </tbody>
    
<?php

$Reflector->LoadFlags();
for ($i=0;$i<$Reflector->StationCount();$i++) {
    $ShowThisStation = true;
    if ($PageOptions['UserPage']['ShowFilter']) {
        $CS = true;
        if ($_SESSION['FilterCallSign'] != null) {
            if (!fnmatch($_SESSION['FilterCallSign'], $Reflector->Stations[$i]->GetCallSign(), FNM_CASEFOLD)) {
                $CS = false;
            }
        }
        $MO = true;
        if ($_SESSION['FilterModule'] != null) {
            if (trim(strtolower($_SESSION['FilterModule'])) != strtolower($Reflector->Stations[$i]->GetModule())) {
                $MO = false;
            }
        }

        $ShowThisStation = ($CS && $MO);
    }

    if ($ShowThisStation) {

        echo '
        
        <tr class="table-center text-white">
       <td>';
        if ($i == 0 && $Reflector->Stations[$i]->GetLastHeardTime() > (time() - 60)) {
            echo '<img src="./img/tx.gif" style="margin-top:3px;" height="20"/>';
        } else {
            echo($i + 1);
        }


        echo '</td>
        <td>';

        list ($Flag, $Name) = $Reflector->GetFlag($Reflector->Stations[$i]->GetCallSign());
        if (file_exists("./img/flags/" . $Flag . ".png")) {
            echo '<a href="#" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="' . $Name . '"><img src="./img/flags/' . $Flag . '.png" alt="' . $Name . '"></a>';
        }
        echo '</td>
   <td><a href="https://www.qrz.com/db/' . $Reflector->Stations[$i]->GetCallsignOnly() . '" class="ps-1 nav-link" target="_qrz" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="QRZ?" data-bs-placement="left">' . $Reflector->Stations[$i]->GetCallsignOnly() . '</a></td>
   <td>' . $Reflector->Stations[$i]->GetSuffix() . '</td>
   <td><a href="https://aprsdirect.de/views/overview.php?c=' . $Reflector->Stations[$i]->GetCallsignOnly() . '" class="ps-1 nav-link" target="_aprs" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-placement="left" data-bs-title="QTH?"><img class="invert_img" src="./img/sat.png" alt=""></a></td>
   <td>' . $Reflector->Stations[$i]->GetVia();
        if ($Reflector->Stations[$i]->GetPeer() != $Reflector->GetReflectorName()) {
            echo ' / ' . $Reflector->Stations[$i]->GetPeer();
        }
        echo '</td>
   <td>' . @date("d.m.Y H:i", $Reflector->Stations[$i]->GetLastHeardTime()) . '</td>
   <td>' . $Reflector->Stations[$i]->GetModule() . '</td>
 </tr>';
    }
    if ($i == $PageOptions['LastHeardPage']['LimitTo']) {
        $i = $Reflector->StationCount() + 1;
    }
}

?> 
      </table>
   </div>
   <div class="col-md-3 pt-5">
      <table class="table table-striped table-hover">
         <?php 

$Modules = $Reflector->GetModules();
sort($Modules, SORT_STRING);
echo '<tr>';
for ($i=0;$i<count($Modules);$i++) {
   
   if (isset($PageOptions['ModuleNames'][$Modules[$i]])) {
      echo '<th class="text-white">'.$PageOptions['ModuleNames'][$Modules[$i]];
       if (trim($PageOptions['ModuleNames'][$Modules[$i]]) != "") {
           echo '<br />Raum:&nbsp;';
       }
      echo $Modules[$i].' (Link-Befehl: "XLX409' .$Modules[$i]. 'L")</th>';
   }
   else {
   echo '
  
      <th class="text-white">'.$Modules[$i].'</th>';
   }
}

echo '</tr><tr>';

$GlobalPositions = array();

for ($i=0;$i<count($Modules);$i++) {
    
   $Users = $Reflector->GetNodesInModulesByID($Modules[$i]);
   echo '<td><table class="table table-hover text-white">';

   $UserCheckedArray = array();
   
   for ($j=0;$j<count($Users);$j++) {
       $Displayname = $Reflector->GetCallsignAndSuffixByID($Users[$j]);
      echo '
            <tr>
               <td><a data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="QTH?" data-bs-placement="left" href="https://aprsdirect.de/views/overview.php?c='.$Displayname.'" class="ps-1 nav-link text-white" target="_aprs">'.$Displayname.'</a> </td>
            </tr>';
      $UserCheckedArray[] = $Users[$j];
   }
   echo '</table></td>';
}

echo '</tr>';

?>
      </table>
   </div>
</div>
