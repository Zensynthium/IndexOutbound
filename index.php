<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Index Outbound</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
  <link href="css/styles.css" rel="stylesheet">
</head>
<body>
  <h1 class="text-center">Index Outbound</h1>
  <?php 
    // function require_multi($files) {
      // $files = func_get_args();
      // foreach($files as $file)
          // require_once($file);
    // }
// 
    #import Post Class and Connect to the Database
// 
    // require_multi(
      // 'Post_class.php',
      // 'config.php'
    // );

    require 'Post_class.php';
    require 'config.php'; 

    $sql = "SELECT * FROM posts";
    if($result = mysqli_query($conn, $sql)){
      if(mysqli_num_rows($result) > 0){
        //do something once
        while($row = mysqli_fetch_array($result)){
          $post = json_decode($row['Post']);
          $replies = json_decode($row['Replies']);
          $image = $row['Image'];
          echo '<div class="post-container p-3 pb-1 mb-4">';
            echo '<span class="d-flex justify-content-end"><a href="modify.php?id='. $row['PostID'] . '" class="modify-button"><img id="modify-post-icon-' . $row['PostID'] . '" class="modify-icon" src="icons/exclamation-outline.svg" title="Modify Post (Admin Priveleges Required)" alt="exclamation sign"></a>';
            echo !empty($replies) ? '<img id="show-hide-icon-'. $row['PostID'] .'" class="post-icon" src="icons/cheveron-down.svg" title="Hide Replies" alt="down-arrow">' : '';
            echo '</span>';
            if ($image) { 
              echo '<img class="post-image shadow" src="images/'. $image .'"/>'; 
            }
            echo '<h2 class="post-title">' . $post->title. '</h2>';
            echo '<p class="post-text">' . $post->body. '</p>';
            echo '<a href="create_reply.php?id='. $row['PostID'] . '" class="btn btn-outline-secondary mb-3">Reply</a>';
            echo !empty($replies) ? '<p class="d-flex justify-content-end text-muted">' . (count($replies) > 1 ? count($replies). ' replies</p>' : '1 reply</p>') : '' ;
          echo '</div>';
          if ($replies) {
            for ($i = 0; $i < count($replies); $i++) {
              if($i > 3) { break; }
              echo '<div class="reply-container p-3 pb-1 mb-4 post-reply-'. $row['PostID'] . '">';
              echo '<p>' . $replies[$i] . '</p>';
              echo '</div>';
            }
          }
        }
        mysqli_free_result($result);
      } else {
        echo "<p class='lead'><em>No records were found.</em></p>";
      }
    } else {
      echo "ERROR: Could not execute $sql. " . mysqli_error($conn);
    }
    mysqli_close($conn);
  ?>
  <?php 
    #function get_posts_from_db() {
    #  try {
    #    $filePath = getcwd().DIRECTORY_SEPARATOR."postDatabase.txt";
    #    if (file_exists($filePath)) {
    #      $postData = file_get_contents($filePath);
    #      $postOutput = explode("<!-- E -->", $postData);
    #
    #      $count = count($postOutput);
    #
    #      $postArray = [];
    #      for ($i = 0; $i < $count; $i++) {
    #        $post = unserialize($postOutput[$i]);
    #        array_push($postArray, $post);
    #      }
    #      return $postArray;
    #    }
    #  } catch (Exception $e){
    #    echo 'Caught exception: ', $e->getMessage(), "\n";
    #  }
    #} 
  ?>

  <?php 
    # The commented code below only needs to run once, or else the same post is created on every page load

    # $firstPost = new Post(); 
    # $firstPost->title = "Testing! Can anyone hear me?";
    # $firstPost->body = "Lorem ipsum dolor sit amet consectetur adipisicing elit. Ullam temporibus voluptatem similique delectus? Fugiat quidem hic ipsum quae, facere eum porro numquam nihil, officiis magnam consectetur culpa voluptate dolore alias?";
    # $firstPost->likes = 32;

    # $firstPost->save_post();

    # $allPosts = get_posts_from_db();
    # 
    # for ($i = 0; $i < count($allPosts)-1; $i++) {
    #   $allPosts[$i]->show_post();
    # }
  ?>

  <a id="create-button" href="create_post.php" class="text-center m-2 mx-auto link-button">Create New Post</a>
  <!--  Old Reply Functionality -->

  <!-- <form> -->
    <!-- <label>reply</label> -->
    <!-- <input type="text" id="post"> -->
    <!-- <input type="submit" value="Create Post"> -->
  <!-- </form> -->
  
  <!-- Deprecated create post form (moved to another page) -->

  <!-- <form id="create-post" class="text-center m-2 mx-auto" method="post"> -->
    <!-- <label for="create-title">Post Title</label> -->
    <!-- <input type="text" name="title" id="create-title" class="create p-1" placeholder="Title"></input> -->
    <!-- <label for="create-body">Text</label> -->
    <!-- <textarea name="body" id="create-body" class="create p-1" cols="30" rows="10" placeholder="Put post text here!"></textarea> -->
    <!-- <div> -->
      <!-- <button type="button" id="create-cancel" class="text-center m-2">Cancel</button> -->
      <!-- <button type="submit" formaction="create_post.php" class="text-center m-2" value="create-post">Post</button> -->
    <!-- </div> -->
  <!-- </form> -->
<script>
  // var createButton = document.getElementById("create-button");
  // var cancelButton = document.getElementById("create-cancel");
  // 
  // var createForm = document.getElementById("create-post");
  // 
  // createButton.addEventListener("click", toggleVis);
  // cancelButton.addEventListener("click", toggleVis);
  // 
  var postIcons = document.getElementsByClassName('post-icon');
  for (var i=0;i<postIcons.length;i++) {
    postIcons[i].addEventListener("click", toggleVis);
  }

  function toggleVis(event) {
    repliesIcon = event.target;
    
    if (repliesIcon.getAttribute('src') == 'icons/cheveron-down.svg') {
      repliesIcon.setAttribute('src', 'icons/cheveron-up.svg');
      repliesIcon.setAttribute('alt', 'down-arrow');
      repliesIcon.setAttribute('title', 'Show Replies');

      var postNum = repliesIcon.getAttribute('id').split("-")[3];
      var replies = document.getElementsByClassName('post-reply-' + postNum);
      
      
      for (let i = 0; i < replies.length; i++) {
        // replies[i].style.display = 'none';
        slideUp(replies[i], 200);
        replies[i].classList.remove('reply-container');
      }
    } else {
      repliesIcon.setAttribute('src', 'icons/cheveron-down.svg');
      repliesIcon.setAttribute('alt', 'up-arrow');
      repliesIcon.setAttribute('title', 'Hide Replies');

      var postNum = repliesIcon.getAttribute('id').split("-")[3];
      var replies = document.getElementsByClassName('post-reply-' + postNum);

      for (let i = 0; i < replies.length; i++) {
        // replies[i].style.display = 'block';
        slideDown(replies[i], 200);
        replies[i].classList.add('reply-container');
      }
    }
  }
</script>
<script src="js/slide.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js" integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous"></script>
</body>
</html>