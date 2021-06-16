<!DOCTYPE html>
<html>
<head>
  <title>Modify Posts & Replies</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<?php
  require 'config.php';
  require 'Post_class.php';

  $admin_user = 'Zen';
  $admin_pass = 'peace77()';

  $user = $pass = "";
  $user_err = $pass_err = "";
  
  session_start();
  if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 300)) {
    // last request was more than 5 minutes ago
    session_unset();     // unset $_SESSION variable for the run-time 
    session_destroy();   // destroy session data in storage
    echo '<p class="lead mx-2">Login Expired.</p>';
  }

  // Retrieve ID
  if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    // Get URL parameter
    $id = trim($_GET["id"]);
  }

  if (isset($_POST['confirm'])){
    // Edit/Delete Actions
    $mode = $_POST["mode"];
    $post_reply = intval(trim($_POST['post-reply']));

    if ($mode == 'edit') {
      // Edit Mode Action

      // Get Post
      $sql = "SELECT * FROM posts WHERE PostID = ?";

      if($stmt = mysqli_prepare($conn, $sql)){
        $id = trim($_POST["id"]);

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

      if ($post_reply == 0) {
        // Edit Post
        $id = trim($_POST["id"]);
        
        $sql = "UPDATE posts SET Post=? WHERE PostID=?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "si", $param_post, $param_id);

            // Set parameters
            $post = new Post();
            $post->title = $_POST['title'];
            $post->body = $_POST['body'];

            $param_post = json_encode($post);
            $param_id = $id;

            // $post = 
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Records deleted successfully. Redirect to landing page
                header("location: modify.php?id=" . $id);
                exit();
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }

        // Close statement
        mysqli_stmt_close($stmt);

        // Close connection
        mysqli_close($conn);
        
      } else {
        // Edit Reply

      }
    } else {
      // Delete Mode Action

      if ($post_reply == 0) {
      // Delete Post
      $sql = "DELETE FROM posts WHERE PostID = ?";
      
      if($stmt = mysqli_prepare($conn, $sql)){
          // Bind variables to the prepared statement as parameters
          mysqli_stmt_bind_param($stmt, "i", $param_id);

          // Set parameters
          $param_id = trim($_POST["id"]);

          // Attempt to execute the prepared statement
          if(mysqli_stmt_execute($stmt)){
              // Records deleted successfully. Redirect to landing page
              header("location: index.php");
              exit();
          } else{
              echo "Oops! Something went wrong. Please try again later.";
          }
      }

      // Close statement
      mysqli_stmt_close($stmt);

      // Close connection
      mysqli_close($conn);
    } else {
      // Get Post
      $sql = "SELECT * FROM posts WHERE PostID = ?";
      if($stmt = mysqli_prepare($conn, $sql)){
        $id = trim($_POST["id"]);

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

      // Delete Reply
      $sql = "UPDATE posts SET Replies=? WHERE PostID=?";
      
      if($stmt = mysqli_prepare($conn, $sql)){
          // Bind variables to the prepared statement as parameters
          mysqli_stmt_bind_param($stmt, "si", $param_replies, $param_id);

          // Set parameters
          $param_id = trim($_POST["id"]);

          array_splice($replies, $post_reply-1, 1);
          $param_replies = json_encode($replies);
          // Attempt to execute the prepared statement
          if(mysqli_stmt_execute($stmt)){
              // Records deleted successfully. Redirect to landing page
              header("location: modify.php?id=" . $id);
              exit();
          } else{
              echo "Oops! Something went wrong. Please try again later.";
          }
      }

      // Close statement
      mysqli_stmt_close($stmt);

      // Close connection
      mysqli_close($conn);
    }

  }
  // Process Edit/Delete Transaction

  } else if (isset($_POST['mode']) && !empty(trim($_POST["mode"])) && (isset($_POST['user'])) && (isset($_POST['pass']))) {
    // Edit/Delete Mode
    $mode = $_POST["mode"];
    $post_reply = intval(trim($_POST['post-reply']));
    
    // Edit Post
    if ($mode == 'edit') {
      $title_err = $body_err = $reply_err = "";

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

      if ($post_reply == 0) {
  
?>  
<div class="wrapper">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="page-header">
          <h2 class="my-3">Edit Post</h2>
        </div>
        <p>Change the text to change the post.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
          <div class="form-group <?php echo (!empty($title_err)) ? 'has-error' : ''; ?>">
            <label>Title</label>
            <input type="text" name="title" class="form-control" value="<?php echo $post->title; ?>">
            <span class="help-block text-danger"><?php echo $title_err;?></span>
          </div>
          <div class="form-group <?php echo (!empty($body_err)) ? 'has-error' : ''; ?>">
            <label>Post Body</label>
            <textarea type="text" name="body" class="form-control" rows="4" cols="50"><?php echo $post->body; ?></textarea>
            <span class="help-block text-danger"><?php echo $body_err;?></span>
          </div>
          <input type="hidden" name="mode" value="edit">
          <input type="hidden" name="post-reply" value="<?php echo trim($_POST["post-reply"]); ?>">
          <input type="hidden" name="id" value="<?php echo $id; ?>"/>
          <input type="hidden" name="user" value="<?php echo trim($_POST["user"]); ?>"/>
          <input type="hidden" name="pass" value="<?php echo trim($_POST["pass"]); ?>"/>
          <input type="hidden" name="confirm" value="confirm">
          <input type="submit" class="btn btn-primary mt-2" value="Submit">
          <!-- <a href="modify.php?id=<?php echo $id; ?>" class="btn btn-default mt-2">Cancel</a> -->
        </form>
        <form method="post" class="mx-2">
          <input type="hidden" name="id" value="<?php echo trim($_GET["id"]); ?>"/>
          <input type="hidden" name="user" value="<?php echo trim($_POST["user"]); ?>">
          <input type="hidden" name="pass" value="<?php echo trim($_POST["pass"]); ?>">
          <input type="submit" value="Cancel" class="btn btn-light">
        </form>
      </div>
    </div>        
  </div>
</div>
<?php
      } else {
        //edit reply
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
                    <textarea name="reply" class="form-control" rows="4" cols="50"><?php echo $replies[$post_reply-1] ?></textarea>
                    <span class="help-block text-danger"><?php echo $reply_err;?></span>
                </div>
                <input type="hidden" name="mode" value="edit">
                <input type="hidden" name="post-reply" value="<?php echo trim($_POST["post-reply"]); ?>">
                <input type="hidden" name="replies" value="<?php foreach ($replies as $comment) {echo "$comment+";}?>"/>
                <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                <input type="hidden" name="user" value="<?php echo trim($_POST["user"]); ?>">
                <input type="hidden" name="pass" value="<?php echo trim($_POST["pass"]); ?>">
                <input type="hidden" name="confirm" value="confirm">
                <input type="submit" class="btn btn-primary mt-2" value="Submit">
                <!-- <a href="modify.php?id=<?php echo $id; ?>" class="btn btn-default mt-2">Cancel</a> -->
            </form>
            <form method="post" class="mx-2">
              <input type="hidden" name="id" value="<?php echo trim($_GET["id"]); ?>"/>
              <input type="hidden" name="user" value="<?php echo trim($_POST["user"]); ?>">
              <input type="hidden" name="pass" value="<?php echo trim($_POST["pass"]); ?>">
              <input type="submit" value="Cancel" class="btn btn-light">
            </form>
        </div>
    </div>        
  </div>
</div>
<?php
      }
    } else {
      //delete mode
      if ($post_reply == 0) {
        //delete post
?>
<div class="p-2">
    <h1>Delete Post</h1>
    <p>Are you sure you want to delete this post?</p>
    <div class="d-flex">
      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <input type="hidden" name="id" value="<?php echo trim($_GET["id"]); ?>"/>
        <input type="hidden" name="mode" value="delete">
        <input type="hidden" name="post-reply" value="<?php echo trim($_POST["post-reply"]); ?>">
        <input type="hidden" name="user" value="<?php echo trim($_POST["user"]); ?>">
        <input type="hidden" name="pass" value="<?php echo trim($_POST["pass"]); ?>">
        <input type="hidden" name="confirm" value="confirm">
        <input type="submit" value="Yes" class="btn btn-danger">
      </form>
      <form method="post" class="mx-2">
        <input type="hidden" name="id" value="<?php echo trim($_GET["id"]); ?>"/>
        <input type="hidden" name="user" value="<?php echo trim($_POST["user"]); ?>">
        <input type="hidden" name="pass" value="<?php echo trim($_POST["pass"]); ?>">
        <input type="submit" value="Cancel" class="btn btn-light">
      </form>
    </div>
</div>
<?php
      } else {
        //delete reply
?>
<div class="p-2">
    <h1>Delete Reply</h1>
    <p>Are you sure you want to delete this reply?</p>
    <div class="d-flex">
      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
          <input type="hidden" name="id" value="<?php echo trim($_GET["id"]); ?>"/>
          <input type="hidden" name="mode" value="delete">
          <input type="hidden" name="post-reply" value="<?php echo trim($_POST["post-reply"]); ?>">
          <input type="hidden" name="user" value="<?php echo trim($_POST["user"]); ?>">
          <input type="hidden" name="pass" value="<?php echo trim($_POST["pass"]); ?>">
          <input type="hidden" name="confirm" value="confirm">
          <input type="submit" value="Yes" class="btn btn-danger">
      </form>
      <form method="post" class="mx-2">
        <input type="hidden" name="id" value="<?php echo trim($_GET["id"]); ?>"/>
        <input type="hidden" name="user" value="<?php echo trim($_POST["user"]); ?>">
        <input type="hidden" name="pass" value="<?php echo trim($_POST["pass"]); ?>">
        <input type="submit" value="Cancel" class="btn btn-light">
      </form>
    </div>
</div>
<?php
      }
    }
  } else if ((!isset($_POST['user'])) && (!isset($_POST['pass'])) && (!isset($_SESSION['LAST_ACTIVITY']))) {

  // visitor needs to enter a name and password
?>

  <div class="wrapper">
    <div class="container-fluid">
      <div class="row">
          <div class="col-md-12">
            <div class="page-header">
              <h2 class="my-3">Admin Login</h2>
            </div>
              <p>Provide the correct credentials to edit/delete the selected post and/or it's replies.</p>
              <form method="post">
                  <div class="form-group <?php echo (!empty($user_err)) ? 'has-error' : ''; ?>">
                      <label>Username</label>
                      <input type="text" name="user" class="form-control" id="user" /></p>
                      <span class="help-block text-danger"><?php echo $user_err;?></span>
                  </div>
                  <div class="form-group <?php echo (!empty($pass_err)) ? 'has-error' : ''; ?>">
                      <label>Password</label>
                      <input type="password" name="pass" class="form-control" id="pass" /></p>
                      <span class="help-block text-danger"><?php echo $pass_err;?></span>
                  </div>
                  <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                  <input type="submit" class="btn btn-primary mt-2" value="Submit">
                  <a href="index.php" class="btn btn-default mt-2">Cancel</a>
              </form>
          </div>
      </div>        
    </div>
  </div>

<?php
    if ((isset($_POST['user'])) || (isset($_POST['pass']))) {
      // Validate Username
      $input_user = filter_var(trim($_POST["user"]), FILTER_SANITIZE_STRING);
      if(empty($input_user)){
        $user_err = "Please enter a user.";
      } else {
        $user = $input_user;
      }
    
      // Validate Password
      $input_pass = filter_var(trim($_POST["pass"]), FILTER_SANITIZE_STRING);
      if(empty($input_pass)){
        $pass_err = "Please enter a pass.";
      } else {
        $pass = $input_pass;
      }
    }

  } else if(isset($_SESSION['LAST_ACTIVITY']) || ($_POST['user']==$admin_user) && ($_POST['pass']==$admin_pass)) {
  // visitor's name and password combination are correct
  require_once 'config.php';
  if (!isset($_SESSION['LAST_ACTIVITY'])) {
    $_SESSION['LAST_ACTIVITY'] = time();
    // echo "Just LAST_ACTIVITY!";
    // echo nl2br(" \n");
  }
  // echo nl2br(" \n");
  // echo 'The content of $_SESSION[\'LAST_ACTIVITY\'] is ' . $_SESSION['LAST_ACTIVITY'].'<br />';

    if(isset($_GET["id"]) && !empty($_GET["id"])){
      // Get hidden input value
      $id = $_GET["id"];
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
    echo '<h1>Modify Posts and Replies</h1>';
    echo '<div class="post-container p-3 pb-1 mb-4">';
      echo '<span class="d-flex justify-content-end">';
        // Edit Button
        echo '<form method="post">';
          echo '<input type="hidden" name="mode" value="edit">';
          echo '<input type="hidden" name="post-reply" value="0">';
          echo '<input type="hidden" name="user" value="'. $admin_user .'">';
          echo '<input type="hidden" name="pass" value="'. $admin_pass .'">';
          echo '<button class="link-button icon-button p-0">';
            echo '<img id="edit-post-icon-' . $row['PostID'] . '-1" class="link-icon" src="icons/edit-pencil.svg" title="Edit Post" alt="Pencil Icon">';
          echo '</button>';
        echo '</form>';
        // Delete Button
        echo '<form method="post">';
          echo '<input type="hidden" name="mode" value="delete">';
          echo '<input type="hidden" name="post-reply" value="0">';
          echo '<input type="hidden" name="user" value="'. $admin_user .'">';
          echo '<input type="hidden" name="pass" value="'. $admin_pass .'">';
          echo '<button class="link-button icon-button p-0">';
            echo '<img id="delete-post-icon-' . $row['PostID'] . '-1" class="link-icon" src="icons/trash.svg" title="Delete Post" alt="Trashcan Icon">';
          echo '</button>';
        echo '</form>';
      echo '</span>';
    // echo '<span class="d-flex justify-content-end"><img id="show-hide-icon-'. $row['PostID'] .'" class="post-icon" src="icons/cheveron-down.svg" title="Hide Replies" alt="down-arrow"></span>';
    if ($row['Image']) { 
      echo '<img class="post-image shadow" src="images/'. $row['Image'] .'"/>'; 
    }
    echo '<h2 class="post-title">' . $post->title. '</h2>';
    echo '<p class="post-text">' . $post->body. '</p>';
    // echo !empty($replies) ? '<p class="d-flex justify-content-end text-muted">' . count($replies). ' replies</p>' : '' ;
    echo '</div>';
    if ($replies) {
      for ($i = 0; $i < count($replies); $i++) {
        echo '<div class="reply-container p-3 pb-1 mb-4 post-reply-'. $row['PostID'] . '">';
        echo '<span class="d-flex justify-content-end">';
        // echo '<a href="edit_post.php?id='. $row['PostID'] . '?reply=' . $i .'" class="link-button"><img id="edit-post-icon-' . $row['PostID'] . '" class="link-icon" src="icons/edit-pencil.svg" title="Edit Post" alt="Pencil Icon"></a>';
        // echo '<a href="delete_post.php?id='. $row['PostID'] . '?reply=' . $i .'" class="link-button"><img id="delete-post-icon-' . $row['PostID'] . '" class="link-icon" src="icons/trash.svg" title="Delete Post" alt="Trashcan Icon"></a>';
          // Edit Button
          echo '<form method="post">';
            echo '<input type="hidden" name="mode" value="edit">';
            echo '<input type="hidden" name="post-reply" value="'. ($i+1) .'">';
            echo '<input type="hidden" name="user" value="'. $admin_user .'">';
            echo '<input type="hidden" name="pass" value="'. $admin_pass .'">';
            echo '<button class="link-button icon-button p-0">';
              echo '<img id="edit-post-icon-' . $row['PostID'] . '-'. ($i+2) . '" class="link-icon" src="icons/edit-pencil.svg" title="Edit Post" alt="Pencil Icon">';
            echo '</button>';
          echo '</form>';
          // Delete Button
          echo '<form method="post">';
          echo '<input type="hidden" name="mode" value="delete">';
          echo '<input type="hidden" name="post-reply" value="'. ($i+1) .'">';
          echo '<input type="hidden" name="user" value="'. $admin_user .'">';
          echo '<input type="hidden" name="pass" value="'. $admin_pass .'">';
          echo '<button class="link-button icon-button p-0">';
            echo '<img id="delete-post-icon-' . $row['PostID'] . '-'. ($i+2) .'" class="link-icon" src="icons/trash.svg" title="Delete Post" alt="Trashcan Icon">';
          echo '</button>';
        echo '</form>';
        echo '</span>';
        echo '<p>' . $replies[$i] . '</p>';
        echo '</div>';
      }
    }
    echo '<a class="btn btn-light" href="index.php">Back</a>';
  } else {
  // visitor's name and password combination are not correct
  echo '<h1>Incorrect Username/Password!</h1>
  <p>You are not authorized to use this resource.</p>';
  }
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js" integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous"></script>
</body>
</html>