<?php
    require_once('DBHandler.php');
    if (isset($_GET['message_id']))
    {
        $id = $_GET['message_id'];
    }
    else
        $id = 3;
    $connection = new DBHandler();
    $result = $connection->searchMessage( $id );
    if ( $result )
    {
        echo 
        "<!DOCTYPE html>
        <html ng-app='officer' lang='en'>
            <head>
          <link href='../../views/style/normalize.css' rel='stylesheet' media='all'>
          <link href='../../views/style/bootstrap.min.css' rel='stylesheet' media='all'>
            </head>

            <body>
                <div style='margin: auto; width: 60%'>
                    <h3> ". $result["name"] . "</h3>
                    <hr class='style18'>   
                
                <div style='width:90%; padding-left:20px;'>
                    <h3>". $result["doc_description"] . "</h3>
                    <p><h3 style='line-height: 1.5em; font-family:Georgia; font-size:1.1em'>" . $result["message"] .
                "</div> 
                <br>
                    <hr class='style18'> 
                    <div style='text-align:right'><h4>" . $result["officer"] . "</h4></div>
                </div>
            </body>
        </html>";
    }
?>