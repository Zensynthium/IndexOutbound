<?php 
  class Post {
    public $title;
    public $body;
    public $replies = [];

    //deprecated
    function show_post() {
      echo '
      <div class="post-container p-3 mb-4">
        <h2 class="post-title">' . $this->title . '</h2>
        <p class="post-text">' . $this->body . ' </p>
        <button onclick="">Reply</button>
      </div>
      ';
      for ($i = 0; $i < count($this->replies)-1; $i++) {
        echo $this->replies[$i]->body;
      }
    }
    
    function reply() {
      $reply = new Post();
      $reply->title = '';
      $reply->body = '';
      array_push($replies, $reply);
    }

    //deprecated
    function save_post() {
      try {
        $postData = serialize($this)."<!-- E -->";
    
        $filePath = getcwd().DIRECTORY_SEPARATOR."postDatabase.txt";
        if (is_writable($filePath)) {
          file_put_contents($filePath, $postData, FILE_APPEND);
        }
      } catch (Exception $e){
        echo 'Caught exception: ', $e->getMessage(), "\n";
      }
    }
  }
?>