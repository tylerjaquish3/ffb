<!DOCTYPE html>
<html lang="en">

<head>

    <style>
        .btn {
            padding: 10px;
            font-size: 16px;
            color: white;
        }

        .red {
            background: red;
        }

        .green {
            background: green;
        }
    </style>

</head>

<body>

<!-- <div style="padding:100px;">
<a href="">Choose Directory</a>
</div> -->

<?php

$dirname = "./conv/";
$images = glob($dirname."*");

// var_dump($images);die;
$count = 0;
$goodStuff = [];

// var_dump($dirname);die;

foreach($images as $image) {
    // var_dump($image);die;
        
    //   $image = $files[$i];
    $supported_file = array(
        'gif',
        'jpg',
        'jpeg',
        'png'
    );

    $ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
    if (in_array($ext, $supported_file)) {
        
        $goodStuff[] = $image;
        
        // show only image name if you want to show full path then use this code // echo $image."<br />";
        // echo basename($image); 
        // echo '<img src="'.$image .'" width="500px" />';

        // echo '<a onclick="">Remove</a>';
    } else {
        continue;
    }       
}

$goodStuff = json_encode($goodStuff);
?>

<div style="display: inline-block">
    <div style="float: left">
        <button class="btn green" onclick="removeLeft(false);">Change Left</button>
        <button class="btn red" onclick="removeLeft(true);">Remove Left</button>
    </div>
    <div style="margin-left: 500px;float: right">
        <button class="btn green" onclick="removeRight(false);">Change Right</button>
        <button class="btn red" onclick="removeRight(true);">Remove Right</button>
    </div>
</div>
<br />
<div style="display: inline-block">
    <div id="left" style="float: left">
        <img src="" id="left-image" width="600px">
    </div>
    <div id="right" style="float: right">
        <img src="" id="right-image" width= "600px">
    </div>
</div>


<script src="jquery-full.js"></script>
<script>

var imageArray = []
imageArray = <?php echo $goodStuff; ?>;

var leftFilled = false;
var rightFilled = false;
imageArray.forEach(function (image, index) {

    if (!leftFilled) {
        fillLeft(image, index);
        leftFilled = true;
        return;
    }

    if (!rightFilled) {
        fillRight(image, index);
        rightFilled = true;
        return;
    }
    
});

function fillLeft(image, index) {
    console.log(image);
    console.log(index);
    $('#left-image').attr('src', image);
    $('#left-image').attr('data-id', index);
}

function fillRight(image, index) {
    $('#right-image').attr('src', image);
    $('#right-image').attr('data-id', index);
}

function removeLeft(canDelete) {
    var leftImageId = $('#left-image').attr('data-id');
    var rightImageId = $('#right-image').attr('data-id');
    
    var next = parseInt(rightImageId) + 1;
    if (leftImageId > rightImageId) {
        next = parseInt(leftImageId) + 1;
    }

    fillLeft(imageArray[next], next);
}

function removeRight(canDelete) {
    var leftImageId = $('#left-image').attr('data-id');
    var rightImageId = $('#right-image').attr('data-id');

    file = $('#right-image').attr('src');
    
    var next = parseInt(rightImageId) + 1;
    if (leftImageId > rightImageId) {
        next = parseInt(leftImageId) + 1;
    }

    fillRight(imageArray[next], next);

    if (canDelete) {
        $.ajax({
          url: 'delete.php',
          data: {'file' : file },
          success: function (response) {
             // do something
          },
          error: function () {
             // do something
          }
        });
    }
}


</script>

</body>
</html>