<?php

header('Content-Type: text/html; charset=utf-8');

require_once(__DIR__.'/../classes/TabularCopyPaste.php');

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

  </head>

  <body>

    <div class="container">

        <h1>Tabular Copy Paste</h1>

        <?php

        if (isset($_POST['tabular_data'])) {
            $_POST['tabular_data'] = utf8_encode($_POST['tabular_data']);
            $sData = $_POST['tabular_data'];
            $bHeaderIncluded = TRUE;

            try {

                $oTabData = new TabularData();
                $oTabData->loadByString($sData, $bHeaderIncluded);

                $sOutput = $oTabData->rendertoQuery(true);
            } catch (Exception $oException) {
                echo '<div class="alert alert-danger" role="alert">'.$oException->getMessage().'</div>';
            }

            // DEBUG
            echo "<h3>Columns</h3>";
            echo '<table class="table table-bordered table-striped table-condensed">';
            echo '<tr>';
            echo '<th>Index</th>';
            echo '<th>Original</th>';
            echo '<th>Name</th>';
            echo '<th>Datatype</th>';
            echo '<th>Comment</th>';
            echo '</tr>';
            foreach($oTabData->aColumns as $iIndex => $oColumn) {
                /** @var $oColumn TabularColumn */
                if ($oColumn->comment) {
                    echo '<tr class="warning">';
                } else {
                    echo '<tr>';
                }
                
                echo '<td>'.$iIndex.'</td>';
                echo '<td>'.$oColumn->originalName.'</td>';
                echo '<td>'.$oColumn->name.'</td>';
                echo '<td>'.$oColumn->datatype->name.'</td>';
                echo '<td>'.$oColumn->comment.'</td>';
                echo '</tr>';
            }
            echo "</table>";

            echo "<h3>SQL PostgreSQL</h3>";
            if (isset($sOutput) && $sOutput <> '') {
                echo '<pre>'.$sOutput.'</pre>';
            }
        }

        ?>

        <form action="" method="post">
          <div class="form-group">
            <label for="tabularDataInput">Input tabular data</label>
            <textarea name="tabular_data" class="form-control" id="tabularDataInput" placeholder="Paste Excel data here" rows="16"><?php if (isset($_POST['tabular_data'])) echo $_POST['tabular_data'];?></textarea>
          </div>
          <!--
          <div class="checkbox">
            <label>
              <input name="header_included" type="checkbox"> Header included
            </label>
          </div>
          -->
          <button type="submit" class="btn btn-default">Submit</button>
        </form>

    </div><!-- /.container -->

  </body>
</html>

