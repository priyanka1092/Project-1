<?php

//turn on debugging messages
ini_set('display_errors', 'On');
error_reporting(E_ALL);



//Class to load classes it finds the file when the program starts to fail for calling a missing class
class Manage {
    public static function autoload($class)
    {
       include $class . '.php';
    }
}

spl_autoload_register(array('Manage', 'autoload'));

$obj = new main();


class main {

    public function __construct()
    {

        //set default page request when no parameters are in URL
        $pageRequest = 'uploadPage';
        $viewPage = 'viewPage';

        //check if there are parameters
        if(isset($_REQUEST['page'])) {

            //load the type of page the request wants into page request
            $pageRequest = $_REQUEST['page'];
        }
        //instantiate the class that is being requested
         $page = new $pageRequest;


        if($_SERVER['REQUEST_METHOD'] == 'GET') {
            $page->get();
        } else {
            $page->post();
        }

    }

}

abstract class page
{
    protected $html;

    public function __construct()
    {
        $this->html .= '<html>';
        $this->html .= '<body>';
    }
    public function __destruct()
    {
        $this->html .= '</body></html>';
        stringFunctions::printThis($this->html);
    }
}

class uploadPage extends page
{

  //To display the upload form
  public function get()
    {
        $form = '<form method="post" enctype="multipart/form-data">';

        //To accept only CSV files and make it a required field
        $form .= '<input type="file" name="fileToUpload" id="fileToUpload" accept=".csv" required>';
        $form .= '<input type="submit" value="Upload file" name="submit">';
        $form .= '</form> ';
        $this->html .= '<h1>Upload CSV File</h1>';
        $this->html .= $form;

    }

    //Uploading the file to AFS and navigating to viewPage
    public function post()
    {
        $target_dir = "UPLOADS/";
        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);

        $fileType = pathinfo($target_file,PATHINFO_EXTENSION);

        $uploadOK = true;

        $uploadOK = $this->checkFileExists($target_file);

        if($uploadOK== true)
        {
            $uploadOK = $this->checkFileType($fileType);

            if($uploadOK == true)
            {
              $this->uploadFile($_FILES["fileToUpload"]["tmp_name"], $target_file);
              $this->navigate($_FILES["fileToUpload"]["name"]);
            }

        }
    }

    // To check whether the file already exists
    function checkFileExists($target_file)
    {
        if (file_exists($target_file)) {
            echo "Sorry, file already exists.";
            return false;
        }
        else
          return true;
    }

    //To check whether the file type is CSV
    function checkFileType($fileType)
    {
        if($fileType != "csv") {
            echo "Sorry, only CSV files are allowed.";
            return false;
        }
        else
          return true;
    }

    function uploadFile($tmpName, $targetFile)
    {
       move_uploaded_file($tmpName, $targetFile);
    }


     // Navigate to the specified file name
    function navigate($filename)
    {
        header("Location: https://web.njit.edu/~pb435/Project-1/untitled.php?page=viewPage&filename=".$filename);
    }
}


//Display the requested CSV File
class viewPage extends page
{
  public function get()
  {

    $file = $this->getFileName();

    $fileDataCSV = fopen($file,"r");

    $firstIndex = true;

    $this->html .= '<table border=1>';


// Convert CSV File to Array
    while (($currentLine = fgetcsv($fileDataCSV)) !== false)
    {
            $this->html .= '<tr>';


             //Display table heading
            if($firstIndex)
            {
                foreach ($currentLine as $cell)
                {
                    $this->tableHeading($cell);
                }
                $firstIndex = false;
            }
            else
            {

                //Display table row data
                foreach ($currentLine as $cell)
                {
                    $this->tableRowData($cell);
                }
            }
            $this->html .= '</tr>';
    }
    fclose($fileDataCSV);

    $this->html .= '</table>';
  }

  function getFileName()
  {
    return "UPLOADS/".$_REQUEST['filename'];
  }

  function tableHeading($heading)
  {
      $this->html .= '<th>' . $heading . '</th>';
  }

  function tableRowData($rowData)
  {
      $this->html .= '<td>' . htmlspecialchars($rowData) . '</td>';
  }

}

class stringFunctions {
     static public function printThis($inputText) {
        return print($inputText);
     }
     static public function stringLength($text) {
        return strLen($text);
     }
  }

?>

