<?php 

// Include config file
require_once "config.php";
require 'Post_class.php';

// Define variables and initialize with empty values
$reply = $reply_err = "";
 
// Processing form data when form is submitted
if(isset($_POST["id"]) && !empty($_POST["id"])){
    // Get hidden input values 
    $id = $_POST["id"];
    $replies = $_POST["replies"];

    // Validate reply
    $input_reply = trim($_POST["reply"]);
    $input_reply = filter_var($_POST['reply'], FILTER_SANITIZE_STRING);
    if(empty($input_reply)){
      $reply_err = "Please enter a reply.";
    } else {
      $reply = $input_reply;
    }
    
    // Check input errors before inserting in database
    if(empty($reply_err)){
        // Prepare an insert statement
        $sql = "UPDATE posts SET Replies=? WHERE PostID=?";
         
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "si", $param_replies, $param_id);
            
            // Set parameters
            // $post = new Post();
            // $post->title = $title;
            // $post->body = $body;

            // $param_post = json_encode($post);

            $ex_replies = explode('+', $replies);
            // $pop_replies = array_pop($ex_replies);
            // $replies_arr = array_push($pop_replies, $reply);
            $ex_replies[count($ex_replies)-1] = $reply;

            $param_replies = json_encode($ex_replies);
            $param_id = $id;
            
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
} else {
  // Check existence of id parameter before processing further
  if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
      // Get URL parameter
      $id = trim($_GET["id"]);

      // Prepare a select statement
      $sql = "SELECT * FROM posts WHERE PostID = ?";
      if($stmt = mysqli_prepare($conn, $sql)){
          // Bind variables to the prepared statement as parameters
          mysqli_stmt_bind_param($stmt, "i", $param_id);
          
          // Set parameters
          $param_id = $id;
          
          // Attempt to execute the prepared statement
          if(mysqli_stmt_execute($stmt)){
              $result = mysqli_stmt_get_result($stmt);
  
              if(mysqli_num_rows($result) == 1) {
                /* Fetch result row as an associative array. Since the result set
                contains only one row, we don't need to use while loop */
                $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                
                // Retrieve individual field value
                $post = json_decode($row["Post"]);
                $replies = json_decode($row["Replies"]);

              } else {
                // URL doesn't contain valid id. Redirect to error page
                header("location: error.php");
                exit();
              }
          } else{
            echo "Oops! Something went wrong. Please try again later.";
          }
      }
    
    // Close statement
    mysqli_stmt_close($stmt);
    
    // Close connection
    mysqli_close($conn);
  } else {
    // URL doesn't contain id parameter. Redirect to error page
    header("location: error.php");
    exit();
  }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
  <link href="css/styles.css" rel="stylesheet">
  <title>Create Reply</title>
</head>
<body>
  <?php 
  $post = json_decode($row['Post']);
  $replies = json_decode($row['Replies']);

  echo '<div class="post-container p-3 pb-1 mb-4">';
    echo '<span class="d-flex justify-content-end"><a href="modify.php?id='. $row['PostID'] . '" class="modify-button"><img id="modify-post-icon-' . $row['PostID'] . '" class="modify-icon" src="icons/exclamation-outline.svg" title="Modify Post (Admin Priveleges Required)" alt="exclamation sign"></a></span>';
    #echo '<span class="d-flex justify-content-end"><img id="show-hide-icon-'. $row['PostID'] .'" class="post-icon" src="icons/cheveron-down.svg" title="Hide Replies" alt="down-arrow"></span>';
    echo '<h2 class="post-title">' . $post->title. '</h2>';
    echo '<p class="post-text">' . $post->body. '</p>';
    #echo !empty($replies) ? '<p class="d-flex justify-content-end text-muted">' . count($replies). ' replies</p>' : '' ;
  echo '</div>';
  if ($replies) {
    for ($i = 0; $i < count($replies); $i++) {
      echo '<div class="reply-container p-3 pb-1 mb-4 post-reply-'. $row['PostID'] . '">';
      echo '<p>' . $replies[$i] . '</p>';
      echo '</div>';
    }
  }
  ?>
<div class="wrapper">
    <div class="container-fluid">
      <div class="row">
          <div class="col-md-12 p-0">
            <div class="page-header">
            </div>
              <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                  <div class="form-group <?php echo (!empty($reply_err)) ? 'has-error' : ''; ?>">
                      <label class="pb-2">Reply</label>
                      <textarea name="reply" class="form-control" rows="4" cols="50"></textarea>
                      <span class="help-block text-danger"><?php echo $reply_err;?></span>
                  </div>
                  <input type="hidden" name="replies" value="<?php foreach ($replies as $comment) {echo "$comment+";}?>"/>
                  <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                  <input type="submit" class="btn btn-primary mt-2" value="Submit">
                  <a href="index.php" class="btn btn-default mt-2">Cancel</a>
              </form>
          </div>
      </div>        
    </div>
  </div>
  <script></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js" integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous"></script>
</body>
</html>