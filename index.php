<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Statical IR Model</title>
        <style>
            .error {color: #7b241c ;}
            .message {color: #2471a3;}
            .resizedTextbox {width:500px;}
        </style>
    </head>
    <body>
        <?php
        /*
        this program takes weighted query as an input 
        then uses statistical model to retrieve relevant documents locally
        all documents should be in folder named "documents"
        */
        $inputVal = $inputErr = $msg = "";
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if ($_POST["Query"] != "") {
                $inputVal = $_POST["Query"];
                if (test_input($_POST["Query"]) == -1) {
                    $inputErr = "Invalid input";
                    $msg = "Weight must be between 0 and 1";
                } else if (test_input($_POST["Query"]) == 0) {
                    $inputErr = "Invalid input";
                    $msg = "Every word must be followed by it's weight.";
                } else {
                    $Query = test_input($_POST["Query"]);
                    $files = scandir("documents");  //scan local files
                    unset($files[0]);
                    unset($files[1]);
                    $rank = set($Query, $files);
                }
            }
        }
        ?>
    <center>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <h2 style="color:#000080">Enter Weighted Query</h2>    
            <table>
                <tr>
                    <td><center><input type="text" name="Query" class="resizedTextbox" value="<?php echo $inputVal; ?>"></center></td>               
                <td><center><input type="image" src="search-icon.png" height="20px" ></center></td>
                </tr>
                <tr><td colspan="2"><center><span class = "error"><?php echo $inputErr; ?></span></center></td></tr>
                <tr><td colspan="2"><center><span class = "message"><?php echo $msg; ?></span></center></td></tr>
            </table>            
        </form>
    </center>
    <?php
    if (!empty($_POST["Query"]) && $inputErr != "Invalid input") {

        ranking($rank, $files);
    }

    function ranking($rank, $files) { //sorts relevant docs 
        $j=count($rank);
        for($i=0;$i<$j;$i++)
        {
            if($rank[$i]==0) //neglect irrelevant docs
            {
                unset($rank[$i]);
            }
        }
        foreach ($rank as $value) {
            $max = max($rank);
            $D = $files[array_search($max, $rank) + 2];
            $link = "documents/" . $D;
            echo "<a href=$link>$D</a>";
            echo"<br>";
            unset($rank[array_search($max, $rank)]);
        }
    }

    function set($Query, $files) { //calculates score for each file
        for ($i = 0; $i < count($files); $i++) {
            $f = fopen("documents/" . $files[$i + 2], "r");
            $string = fread($f, filesize("documents/" . $files[$i + 2]));
            $rank[$i] = 0;
            foreach ($Query as $key => $value) {
                $Query[$key][$i + 2] = substr_count($string, $Query[$key][0]) / str_word_count($string);
                $rank[$i]+=$Query[$key][$i + 2] * $Query[$key][1];
            }
        }
        return $rank;
    }

    function test_input($data) { //prepare inputs
        $data = str_ireplace(":", " ", $data);
        $data = str_ireplace(";", " ", $data);
        $data = str_ireplace("<", " ", $data);
        $data = str_ireplace(">", " ", $data);
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        $data = preg_split("/[\s]+/", trim($data));
        $flag = 1;
        if (count($data) % 2 != 0)// check invalid inputs 
            return !$flag;
        for ($i = 0; $i < count($data); $i+=2) {
            if (!preg_match("/^[a-zA-Z ]*$/", $data[$i])) {
                $flag = 0;
                break;
            }
            if (preg_match("/[^0-9(.{1})]/", $data[$i + 1])) {
                $flag = 0;
                break;
            }
            if (($data[$i + 1] > 1) || ($data[$i + 1] < 0)) {
                $flag = -1;
                break;
            }
        }
        if ($flag==1) {
            $Query = array(array());
            for ($i = 0; $i < (count($data) / 2); $i++) {
                for ($j = 0; $j < (count($data) / (count($data) / 2)); $j++) {
                    $Query[$i][$j] = $data[$j + ($i * 2)];
                }
            }
            return $Query;
        } else {
            return $flag;
        }
    }
    ?>
</body>
</html>
