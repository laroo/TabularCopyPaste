<?php

header('Content-Type: text/html; charset=utf-8');

require_once(__DIR__.'/../classes/TabularCopyPaste.php');

function renderDropDown($psName, array $paArray, $psSelected)
{
    $sOut = '<select name="'.htmlentities($psName).'" class="form-control">'.chr(10);
    foreach ($paArray as $sKey => $sValue) {
        $sSelected = ($sKey == $psSelected) ? 'selected="selected"' : '';
        $sOut .= '<option value="'.htmlentities($sValue).'" '.$sSelected.'>'.htmlentities($sValue).'</option>'.chr(10);
    }
    $sOut .= '</select>'.chr(10);
    return $sOut;
}

function renderDropDownDataTypes($psName, $psSelected)
{
    $aList = array(
        'string' => 'string',
        'integer' => 'integer',
        'decimal' => 'decimal',
        'date' => 'date',
    );
    return renderDropDown($psName, $aList, $psSelected);
}

$sActiveTab = 'input';
if (isset($_POST['tabular_data'])) {
    $sActiveTab = 'customize';
    $sData = utf8_encode($_POST['tabular_data']);
    $bHeaderIncluded = TRUE;

    try {

        $oTabData = new TabularData();
        $oTabData->loadByString($sData, $bHeaderIncluded);

        if (isset($_POST['custom']) && isset($_POST['custom']['name']) && is_array($_POST['custom']['name'])) {
            foreach ($oTabData->aColumns AS $iIndex => $oColumn) {
                /** @var $oColumn TabularColumn */
                if (!isset($_POST['custom']['name'][$iIndex])) continue;

                if (trim($_POST['custom']['name'][$iIndex]) == '') {
                    unset($oTabData->aColumns[$iIndex]);
                }

                $oColumn->name = $_POST['custom']['name'][$iIndex];
            }
        }
        if (isset($_POST['custom']) && isset($_POST['custom']['datatype']) && is_array($_POST['custom']['datatype'])) {
            foreach ($oTabData->aColumns AS $iIndex => $oColumn) {
                /** @var $oColumn TabularColumn */
                if (isset($_POST['custom']['datatype'][$iIndex])) {
                    $oColumn->datatype = $oTabData->getDataTypeObject_byName($_POST['custom']['datatype'][$iIndex]);
                }
            }
        }

    } catch (Exception $oException) {
        echo '<div class="alert alert-danger" role="alert">'.$oException->getMessage().'</div>';
    }

}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">

    <title>Copy paste to db table</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

  </head>

  <body>

    <div class="container">

        <h1>Tabular Copy Paste</h1>

        <form action="" method="post">

        <ul class="nav nav-tabs">
            <li <?php if ($sActiveTab == 'input') echo 'class="active"'; ?>><a data-toggle="tab" href="#input">Input</a></li>
            <li <?php if ($sActiveTab == 'customize') echo 'class="active"'; ?>><a data-toggle="tab" href="#customize">Customize</a></li>
            <li><a data-toggle="tab" href="#out_postgresql">Output PostgreSQL</a></li>
        </ul>

        <div class="tab-content">
            <div id="input" class="tab-pane fade <?php if ($sActiveTab == 'input') echo 'in active'; ?>">
                <h3>Input</h3>
                <div class="form-group">
                    <label for="tabularDataInput">Tabular data</label>
                    <textarea name="tabular_data" class="form-control" id="tabularDataInput" placeholder="Paste tabular data here" rows="16"><?php if (isset($_POST['tabular_data'])) echo $_POST['tabular_data'];?></textarea>
                </div>
                <button type="submit" class="btn btn-default">Submit</button>
            </div>
            <div id="customize" class="tab-pane fade <?php if ($sActiveTab == 'customize') echo 'in active'; ?>">
                <h3>Customize</h3>
                <?php

                if (isset($_POST['tabular_data'])) {
                    $sData = utf8_encode($_POST['tabular_data']);

                    echo "<h4>Columns</h4>";
                    echo '<table class="table table-bordered table-striped table-condensed">'.chr(10);
                    echo '<tr>';
                    echo '<th>Index</th>';
                    echo '<th>Original</th>';
                    echo '<th>Name</th>';
                    echo '<th>Datatype</th>';
                    echo '<th>Comment</th>';
                    echo '</tr>'.chr(10);
                    foreach($oTabData->aColumns as $iIndex => $oColumn) {
                        /** @var $oColumn TabularColumn */
                        if ($oColumn->comment) {
                            echo '<tr class="warning">'.chr(10);
                        } else {
                            echo '<tr>'.chr(10);
                        }

                        echo '<td>'.$iIndex.'</td>'.chr(10);
                        echo '<td>'.$oColumn->originalName.'</td>'.chr(10);
                        echo '<td><input type="text" class="form-control" name="custom[name]['.$iIndex.']" value="'.htmlentities($oColumn->name).'" /></td>'.chr(10);
                        echo '<td>'.renderDropDownDataTypes("custom[datatype][{$iIndex}]", $oColumn->datatype->name).'</td>'.chr(10);
                        echo '<td>'.$oColumn->comment.'</td>'.chr(10);
                        echo '</tr>'.chr(10);
                    }
                    echo '<tr>';
                    echo '<td colspan="5"><button type="submit" class="btn btn-default">Change</button></td>';
                    echo '</tr>'.chr(10);
                    echo "</table>".chr(10);

                }

                ?>

            </div>
            <div id="out_postgresql" class="tab-pane fade">
                <h3>SQL PostgreSQL</h3>
                <?php

                $oRenderer = new TabularRendererPostgresql;
                $sOutput = $oRenderer->render($oTabData);

                if (isset($sOutput) && $sOutput <> '') {
                    echo '<pre>'.$sOutput.'</pre>';
                }

                ?>
            </div>
        </div>

        </form>

    </div><!-- /.container -->

  </body>
</html>

