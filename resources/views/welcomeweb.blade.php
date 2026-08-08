<!DOCTYPE html>
<html>
<head>

    <title>Live Face Detection</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>

        body{
            font-family:Arial;
            text-align:center;
            margin-top:30px;
        }

        video{
            width:700px;
            border:3px solid #000;
            border-radius:10px;
        }

        canvas{
            display:none;
        }

        #status{
            margin-top:20px;
            color:green;
            font-size:20px;
        }

    </style>

</head>
<body>

<h2>Live Camera</h2>

<video id="video" autoplay playsinline></video>

<canvas id="canvas"></canvas>

<div id="status"></div>

<script>

const video=document.getElementById("video");
const canvas=document.getElementById("canvas");
const ctx=canvas.getContext("2d");

navigator.mediaDevices.getUserMedia({
    video:true
})
.then(function(stream){

    video.srcObject=stream;

})
.catch(function(err){

    alert(err);

});

function sendFrame(){

    canvas.width=video.videoWidth;
    canvas.height=video.videoHeight;

    ctx.drawImage(video,0,0);

    let image=canvas.toDataURL("image/jpeg");

    fetch("{{ route('face.detect') }}",{

        method:"POST",

        headers:{

            "Content-Type":"application/json",

            "X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').content

        },

        body:JSON.stringify({

            image:image

        })

    })

    .then(res=>res.json())

    .then(data=>{

        document.getElementById("status").innerHTML="Frame Sent Successfully";

        console.log(data);

    });

}

setInterval(sendFrame,1000);

</script>

</body>
</html>
