<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Post Successful!</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
  <link href="css/styles.css" rel="stylesheet">
</head>
<?php
// Include config file
require_once "config.php";
require 'Post_class.php';

// Define variables and initialize with empty values
$title = $body = $image = "";
$title_err = $body_err = $image_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate Title
    $input_title = trim($_POST["title"]);
    $input_title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
    if(empty($input_title)){
      $title_err = "Please enter a title.";
    } else {
      $title = $input_title;
    }
    
    // Validate body
    $input_body = trim($_POST["body"]);
    $input_body = filter_var($_POST['body'], FILTER_SANITIZE_STRING);
    if(empty($input_body)){
        $body_err = "Please enter an body.";     
    } else{
        $body = $input_body;
    }
    
    // Validate image
    if($_FILES['the_file']['size'] != 0){
      if ($_FILES['the_file']['error'] > 0) {
        switch ($_FILES['the_file']['error']) {
          case 1:
          $image_err = 'File exceeded upload_max_filesize.';
          break;
          case 2:
          $image_err = 'File exceeded max_file_size.';
          break;
          case 3:
          $image_err = 'File only partially uploaded.';
          break;
          case 4:
          $image_err = 'No file uploaded.';
          break;
          case 6:
          $image_err = 'Cannot upload file: No temp directory specified.';
          break;
          case 7:
          $image_err = 'Upload failed: Cannot write to disk.';
          break;
          case 8:
          $image_err = 'A PHP extension blocked the file upload.';
          break;
        }
        exit;
      }

      // Does the file have the right MIME type?
      if ($_FILES['the_file']['type'] != 'image/png' && $_FILES['the_file']['type'] != 'image/jpg' && $_FILES['the_file']['type'] != 'image/jpeg' && $_FILES['the_file']['type'] != 'image/gif') {
        $image_err = 'Problem: file is not a PNG/JPG/JPEG image or GIF.';
        exit;
      }

      // Put the file where we'd like it (Current Directory + images\fileName)
      // C:\xampp\htdocs\php files\Data Driven Websites\Index Outbound
      if (getcwd() == "C:\\xampp\htdocs\php files\Data Driven Websites\Index Outbound") {
        $uploaded_file = getcwd() . '\images\\' . $_FILES['the_file']['name'];
      } else {
        $uploaded_file = '\images\\' . $_FILES['the_file']['name'];
      }
      $i = 1;
      while (file_exists($uploaded_file)){
        // If file already exists use same name with (1) right before the file extension to avoid rewriting saved images
        // This code also handles file names that have . in them before the file extension.
        $file_parts = explode('.', $uploaded_file);
        $extension = array_pop($file_parts);
        $file_parts = array(implode('.', $file_parts), $extension);
        $file_parts[0] = $file_parts[0] . ' ('. $i .').'; 
        $uploaded_file = $file_parts[0] . $file_parts[1];
        $i++;
      }

      if (is_uploaded_file($_FILES['the_file']['tmp_name'])) {
        if (!move_uploaded_file($_FILES['the_file']['tmp_name'], $uploaded_file)) {
        $image_err = 'Problem: Could not move file to destination directory.';
        exit;
        }
      } else {
        $image_err = 'Problem: Possible file upload attack. Filename: ' . $_FILES['the_file']['name'];
        exit;
      }

      $image = $_FILES['the_file']['name'];
    }

    echo $image_err;
    // Check input errors before inserting in database
    if(empty($title_err) && empty($body_err)){
        // Prepare an insert statement
        $sql = "INSERT INTO posts (Post, Image) VALUES (?, ?)";
         
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ss", $param_post, $param_image);
            
            // Set parameters
            $post = new Post();
            $post->title = $title;
            $post->body = $body;

            $param_post = json_encode($post);
            $param_image = $image;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Records created successfully. Redirect to Books page
                header("location: index.php");
                exit();
            } else{
                echo "Something went wrong. Please try again later.";
            }
        }
         
        // Close statement
        mysqli_stmt_close($stmt);
    }
    
    // Close connection
    mysqli_close($conn);
}
?>

<body>
<div class="wrapper">
    <div class="container-fluid">
      <div class="row">
          <div class="col-md-12">
            <div class="page-header">
              <h2 class="my-3">Create Post</h2>
            </div>
              <p>Fill this in to create a post!</p>
              <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                  <div class="form-group <?php echo (!empty($title_err)) ? 'has-error' : ''; ?>">
                      <label>Title</label>
                      <input type="text" name="title" class="form-control" value="<?php echo $title; ?>">
                      <span class="help-block text-danger"><?php echo $title_err;?></span>
                  </div>
                  <div class="form-group <?php echo (!empty($body_err)) ? 'has-error' : ''; ?>">
                      <label>Post Body</label>
                      <textarea name="body" class="form-control" rows="4" cols="50"></textarea>
                      <!-- <input type="text" name="body" class="form-control" value="<?php echo $body; ?>"> -->
                      <span class="help-block text-danger"><?php echo $body_err;?></span>
                  </div>
                  <div>
                    <input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
                    <label for="the_file">Upload an Image (.png, .jpg, .jpeg, .gif):</label>
                    <input type="file" name="the_file" id="the_file"/>
                  </div>
                  <input type="submit" class="btn btn-primary mt-2" value="Submit">
                  <a href="index.php" class="btn btn-default mt-2">Cancel</a>
              </form>
          </div>
      </div>        
    </div>
  </div>
  <script>
  // function backToPosts() {
    // Testing Redirect
    // location.replace("http://localhost/php%20files/Data%20Driven%20Websites/Class%20Project/?create-body=dsds");
    // 
    // Production Redirect
    // location.replace("http://student061.webdev.seminolestate.edu/");
  // }
  //Redirect to main page in 2 seconds.
  // setTimeout(function(){ backToPosts(); }, 2000);
  </script>
</body>
</html>